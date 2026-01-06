<?php

namespace Tests\Feature\Property;

use App\Models\Property;
use App\Models\PropertyOwner;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PropertyTest extends TestCase
{
    use RefreshDatabase;

    protected $tenant;
    protected $admin;
    protected $propertyOwner;
    protected $ownerUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles and permissions for testing
        $this->artisan('db:seed', ['--class' => 'RolePermissionSeeder']);

        $this->tenant = Tenant::factory()->create([
            'company_name' => 'Test Company',
        ]);

        $this->admin = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'email' => 'admin@test.com',
        ]);
        $this->admin->assignRole('company_admin');

        $this->ownerUser = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'email' => 'owner@test.com',
        ]);
        $this->ownerUser->assignRole('property_owner');

        $this->propertyOwner = PropertyOwner::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->ownerUser->id,
            'email' => 'owner@test.com',
        ]);
    }

    /** @test */
    public function owner_can_register_property()
    {
        Sanctum::actingAs($this->ownerUser);

        $response = $this->postJson('/api/properties', [
            'name' => 'Test Property',
            'property_type' => 'apartment',
            'description' => 'A test property',
            'address_line_1' => '123 Test Street',
            'city' => 'Nairobi',
            'state' => 'Nairobi',
            'country' => 'Kenya',
            'total_units' => 10,
            'commission_percentage' => 10.00,
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'name',
                    'status',
                ]
            ]);

        $this->assertDatabaseHas('properties', [
            'property_name' => 'Test Property',
            'status' => 'pending_approval',
        ]);
    }

    /** @test */
    public function owner_cannot_register_property_without_required_fields()
    {
        Sanctum::actingAs($this->ownerUser);

        $response = $this->postJson('/api/properties', [
            'name' => 'Test Property',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['property_type', 'address_line_1', 'city', 'state', 'country', 'total_units', 'commission_percentage']);
    }

    /** @test */
    public function admin_can_approve_property()
    {
        Sanctum::actingAs($this->admin);

        $property = Property::factory()->create([
            'tenant_id' => $this->tenant->id,
            'property_owner_id' => $this->propertyOwner->id,
            'status' => 'pending_approval',
        ]);

        $response = $this->patchJson("/api/properties/{$property->id}/approve");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Property approved successfully',
            ]);

        $this->assertDatabaseHas('properties', [
            'id' => $property->id,
            'status' => 'active',
            'approved_by' => $this->admin->id,
        ]);
    }

    /** @test */
    public function admin_can_reject_property_with_reason()
    {
        Sanctum::actingAs($this->admin);

        $property = Property::factory()->create([
            'tenant_id' => $this->tenant->id,
            'property_owner_id' => $this->propertyOwner->id,
            'status' => 'pending_approval',
        ]);

        $response = $this->patchJson("/api/properties/{$property->id}/reject", [
            'rejection_reason' => 'Incomplete documentation',
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('properties', [
            'id' => $property->id,
            'status' => 'rejected',
            'rejection_reason' => 'Incomplete documentation',
        ]);
    }

    /** @test */
    public function owner_can_resubmit_rejected_property()
    {
        Sanctum::actingAs($this->ownerUser);

        $property = Property::factory()->create([
            'tenant_id' => $this->tenant->id,
            'property_owner_id' => $this->propertyOwner->id,
            'status' => 'rejected',
            'rejection_reason' => 'Incomplete documentation',
        ]);

        $response = $this->postJson("/api/properties/{$property->id}/resubmit");

        $response->assertStatus(200);

        $this->assertDatabaseHas('properties', [
            'id' => $property->id,
            'status' => 'pending_approval',
            'rejection_reason' => null,
        ]);
    }

    /** @test */
    public function owner_cannot_update_approved_property()
    {
        Sanctum::actingAs($this->ownerUser);

        $property = Property::factory()->create([
            'tenant_id' => $this->tenant->id,
            'property_owner_id' => $this->propertyOwner->id,
            'status' => 'active',
        ]);

        $response = $this->putJson("/api/properties/{$property->id}", [
            'name' => 'Updated Name',
        ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function admin_can_assign_property_manager()
    {
        Sanctum::actingAs($this->admin);

        $manager = User::factory()->create([
            'tenant_id' => $this->tenant->id,
        ]);
        $manager->assignRole('company_staff');

        $property = Property::factory()->create([
            'tenant_id' => $this->tenant->id,
            'property_owner_id' => $this->propertyOwner->id,
            'status' => 'active',
        ]);

        $response = $this->postJson("/api/properties/{$property->id}/assign-manager", [
            'manager_id' => $manager->id,
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('properties', [
            'id' => $property->id,
            'manager_id' => $manager->id,
        ]);
    }

    /** @test */
    public function unauthorized_user_cannot_approve_property()
    {
        $otherUser = User::factory()->create([
            'tenant_id' => $this->tenant->id,
        ]);
        $otherUser->assignRole('company_staff');

        Sanctum::actingAs($otherUser);

        $property = Property::factory()->create([
            'tenant_id' => $this->tenant->id,
            'property_owner_id' => $this->propertyOwner->id,
            'status' => 'pending_approval',
        ]);

        $response = $this->patchJson("/api/properties/{$property->id}/approve");

        $response->assertStatus(403);
    }

    /** @test */
    public function property_list_filtered_by_role()
    {
        Sanctum::actingAs($this->ownerUser);

        Property::factory()->count(3)->create([
            'tenant_id' => $this->tenant->id,
            'property_owner_id' => $this->propertyOwner->id,
        ]);

        $otherOwner = PropertyOwner::factory()->create([
            'tenant_id' => $this->tenant->id,
        ]);

        Property::factory()->count(2)->create([
            'tenant_id' => $this->tenant->id,
            'property_owner_id' => $otherOwner->id,
        ]);

        $response = $this->getJson('/api/properties');

        $response->assertStatus(200);
        $this->assertCount(3, $response->json('data'));
    }

    /** @test */
    public function admin_can_delete_property_without_active_leases()
    {
        Sanctum::actingAs($this->admin);

        $property = Property::factory()->create([
            'tenant_id' => $this->tenant->id,
            'property_owner_id' => $this->propertyOwner->id,
            'status' => 'active',
        ]);

        $response = $this->deleteJson("/api/properties/{$property->id}");

        $response->assertStatus(200);

        $this->assertDatabaseHas('properties', [
            'id' => $property->id,
            'status' => 'deleted',
        ]);
    }

    /** @test */
    public function can_filter_properties_by_status()
    {
        Sanctum::actingAs($this->admin);

        Property::factory()->count(2)->create([
            'tenant_id' => $this->tenant->id,
            'property_owner_id' => $this->propertyOwner->id,
            'status' => 'active',
        ]);

        Property::factory()->count(3)->create([
            'tenant_id' => $this->tenant->id,
            'property_owner_id' => $this->propertyOwner->id,
            'status' => 'pending_approval',
        ]);

        $response = $this->getJson('/api/properties?status=pending_approval');

        $response->assertStatus(200);
        $this->assertCount(3, $response->json('data'));
    }

    /** @test */
    public function can_filter_properties_by_type()
    {
        Sanctum::actingAs($this->admin);

        Property::factory()->count(2)->create([
            'tenant_id' => $this->tenant->id,
            'property_owner_id' => $this->propertyOwner->id,
            'property_type' => 'apartment',
        ]);

        Property::factory()->count(1)->create([
            'tenant_id' => $this->tenant->id,
            'property_owner_id' => $this->propertyOwner->id,
            'property_type' => 'villa',
        ]);

        $response = $this->getJson('/api/properties?property_type=apartment');

        $response->assertStatus(200);
        
        // The response is paginated, so data is nested
        $data = $response->json('data');
        $properties = $data['data'] ?? $data;
        $this->assertCount(2, $properties);
    }

    /** @test */
    public function can_view_property_details_with_units_and_amenities()
    {
        Sanctum::actingAs($this->admin);

        $property = Property::factory()->create([
            'tenant_id' => $this->tenant->id,
            'property_owner_id' => $this->propertyOwner->id,
            'status' => 'active',
        ]);

        $response = $this->getJson("/api/properties/{$property->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'name',
                    'property_type',
                    'address',
                    'status',
                    'owner',
                    'units',
                    'amenities',
                ]
            ]);
    }

    /** @test */
    public function owner_can_update_pending_property()
    {
        Sanctum::actingAs($this->ownerUser);

        $property = Property::factory()->create([
            'tenant_id' => $this->tenant->id,
            'property_owner_id' => $this->propertyOwner->id,
            'status' => 'pending_approval',
            'name' => 'Original Name',
        ]);

        $response = $this->putJson("/api/properties/{$property->id}", [
            'name' => 'Updated Name',
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('properties', [
            'id' => $property->id,
            'property_name' => 'Updated Name',
        ]);
    }

    /** @test */
    public function owner_can_update_rejected_property()
    {
        Sanctum::actingAs($this->ownerUser);

        $property = Property::factory()->create([
            'tenant_id' => $this->tenant->id,
            'property_owner_id' => $this->propertyOwner->id,
            'status' => 'rejected',
            'property_name' => 'Original Name',
        ]);

        $response = $this->putJson("/api/properties/{$property->id}", [
            'name' => 'Updated Name',
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('properties', [
            'id' => $property->id,
            'property_name' => 'Updated Name',
        ]);
    }

    /** @test */
    public function cannot_approve_already_approved_property()
    {
        Sanctum::actingAs($this->admin);

        $property = Property::factory()->create([
            'tenant_id' => $this->tenant->id,
            'property_owner_id' => $this->propertyOwner->id,
            'status' => 'active',
        ]);

        $response = $this->patchJson("/api/properties/{$property->id}/approve");

        $response->assertStatus(403);
    }
}
