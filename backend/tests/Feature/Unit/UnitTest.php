<?php

namespace Tests\Feature\Unit;

use App\Models\Property;
use App\Models\PropertyOwner;
use App\Models\Tenant;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UnitTest extends TestCase
{
    use RefreshDatabase;

    protected $tenant;
    protected $admin;
    protected $manager;
    protected $property;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::factory()->create([
            'company_name' => 'Test Company',
        ]);

        $this->admin = User::factory()->create([
            'tenant_id' => $this->tenant->id,
        ]);
        $this->admin->assignRole('company_admin');

        $this->manager = User::factory()->create([
            'tenant_id' => $this->tenant->id,
        ]);
        $this->manager->assignRole('company_staff');

        $propertyOwner = PropertyOwner::factory()->create([
            'tenant_id' => $this->tenant->id,
        ]);

        $this->property = Property::factory()->create([
            'tenant_id' => $this->tenant->id,
            'property_owner_id' => $propertyOwner->id,
            'status' => 'active',
            'manager_id' => $this->manager->id,
        ]);
    }

    /** @test */
    public function admin_can_add_unit_to_approved_property()
    {
        Sanctum::actingAs($this->admin);

        $response = $this->postJson("/api/properties/{$this->property->id}/units", [
            'unit_number' => 'A101',
            'unit_type' => '2BR',
            'bedrooms' => 2,
            'bathrooms' => 2,
            'size_sqft' => 850,
            'floor_level' => '1',
            'monthly_rent' => 50000,
            'deposit_amount' => 50000,
            'description' => 'Spacious 2BR unit',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'unit_number',
                    'status',
                ]
            ]);

        $this->assertDatabaseHas('units', [
            'property_id' => $this->property->id,
            'unit_number' => 'A101',
            'status' => 'available',
        ]);
    }

    /** @test */
    public function cannot_add_unit_to_pending_property()
    {
        Sanctum::actingAs($this->admin);

        $pendingProperty = Property::factory()->create([
            'tenant_id' => $this->tenant->id,
            'status' => 'pending_approval',
        ]);

        $response = $this->postJson("/api/properties/{$pendingProperty->id}/units", [
            'unit_number' => 'A101',
            'unit_type' => '2BR',
            'bedrooms' => 2,
            'bathrooms' => 2,
            'monthly_rent' => 50000,
            'deposit_amount' => 50000,
        ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function unit_number_must_be_unique_per_property()
    {
        Sanctum::actingAs($this->admin);

        Unit::factory()->create([
            'tenant_id' => $this->tenant->id,
            'property_id' => $this->property->id,
            'unit_number' => 'A101',
        ]);

        $response = $this->postJson("/api/properties/{$this->property->id}/units", [
            'unit_number' => 'A101',
            'unit_type' => '2BR',
            'bedrooms' => 2,
            'bathrooms' => 2,
            'monthly_rent' => 50000,
            'deposit_amount' => 50000,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['unit_number']);
    }

    /** @test */
    public function admin_can_update_vacant_unit()
    {
        Sanctum::actingAs($this->admin);

        $unit = Unit::factory()->create([
            'tenant_id' => $this->tenant->id,
            'property_id' => $this->property->id,
            'status' => 'available',
            'monthly_rent' => 50000,
        ]);

        $response = $this->putJson("/api/units/{$unit->id}", [
            'monthly_rent' => 55000,
            'description' => 'Updated description',
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('units', [
            'id' => $unit->id,
            'monthly_rent' => 55000,
            'description' => 'Updated description',
        ]);
    }

    /** @test */
    public function cannot_update_occupied_unit_rent()
    {
        Sanctum::actingAs($this->admin);

        $unit = Unit::factory()->create([
            'tenant_id' => $this->tenant->id,
            'property_id' => $this->property->id,
            'status' => 'occupied',
            'monthly_rent' => 50000,
        ]);

        $response = $this->putJson("/api/units/{$unit->id}", [
            'monthly_rent' => 55000,
        ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function admin_can_delete_vacant_unit()
    {
        Sanctum::actingAs($this->admin);

        $unit = Unit::factory()->create([
            'property_id' => $this->property->id,
            'status' => 'available',
        ]);

        $response = $this->deleteJson("/api/units/{$unit->id}");

        $response->assertStatus(200);

        $this->assertDatabaseHas('units', [
            'id' => $unit->id,
            'status' => 'deleted',
        ]);
    }

    /** @test */
    public function cannot_delete_occupied_unit()
    {
        Sanctum::actingAs($this->admin);

        $unit = Unit::factory()->create([
            'property_id' => $this->property->id,
            'status' => 'occupied',
        ]);

        $response = $this->deleteJson("/api/units/{$unit->id}");

        $response->assertStatus(400);
    }

    /** @test */
    public function manager_can_add_unit_to_assigned_property()
    {
        Sanctum::actingAs($this->manager);

        $response = $this->postJson("/api/properties/{$this->property->id}/units", [
            'unit_number' => 'B201',
            'unit_type' => '1BR',
            'bedrooms' => 1,
            'bathrooms' => 1,
            'size_sqft' => 500,
            'monthly_rent' => 35000,
            'deposit_amount' => 35000,
        ]);

        $response->assertStatus(201);
    }

    /** @test */
    public function can_filter_units_by_property()
    {
        Sanctum::actingAs($this->admin);

        Unit::factory()->count(3)->create([
            'tenant_id' => $this->tenant->id,
            'property_id' => $this->property->id,
        ]);

        $otherProperty = Property::factory()->create([
            'tenant_id' => $this->tenant->id,
            'status' => 'active',
        ]);

        Unit::factory()->count(2)->create([
            'tenant_id' => $this->tenant->id,
            'property_id' => $otherProperty->id,
        ]);

        $response = $this->getJson("/api/units?property_id={$this->property->id}");

        $response->assertStatus(200);
        $this->assertCount(3, $response->json('data'));
    }

    /** @test */
    public function can_filter_units_by_status()
    {
        Sanctum::actingAs($this->admin);

        Unit::factory()->count(2)->create([
            'property_id' => $this->property->id,
            'status' => 'available',
        ]);

        Unit::factory()->count(3)->create([
            'property_id' => $this->property->id,
            'status' => 'occupied',
        ]);

        $response = $this->getJson('/api/units?status=available');

        $response->assertStatus(200);
        
        // The response is paginated, so data is nested
        $data = $response->json('data');
        $units = $data['data'] ?? $data;
        $this->assertCount(2, $units);
    }

    /** @test */
    public function can_view_unit_details_with_photos()
    {
        Sanctum::actingAs($this->admin);

        $unit = Unit::factory()->create([
            'property_id' => $this->property->id,
        ]);

        $response = $this->getJson("/api/units/{$unit->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'unit_number',
                    'unit_type',
                    'bedrooms',
                    'bathrooms',
                    'monthly_rent',
                    'status',
                    'property',
                    'photos',
                ]
            ]);
    }

    /** @test */
    public function manager_can_update_unit_in_assigned_property()
    {
        Sanctum::actingAs($this->manager);

        $unit = Unit::factory()->create([
            'tenant_id' => $this->tenant->id,
            'property_id' => $this->property->id,
            'status' => 'available',
        ]);

        $response = $this->putJson("/api/units/{$unit->id}", [
            'description' => 'Updated by manager',
        ]);

        $response->assertStatus(200);
    }
}
