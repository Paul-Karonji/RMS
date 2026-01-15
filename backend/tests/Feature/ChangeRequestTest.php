<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\PropertyOwner;
use App\Models\Property;
use App\Models\Unit;
use App\Models\ChangeRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ChangeRequestTest extends TestCase
{
    use RefreshDatabase;

    protected User $owner;
    protected User $admin;
    protected PropertyOwner $propertyOwner;
    protected Property $property;
    protected Unit $unit;

    protected function setUp(): void
    {
        parent::setUp();
        
        $tenant = \App\Models\Tenant::factory()->create();
        
        $this->propertyOwner = PropertyOwner::factory()->create([
            'tenant_id' => $tenant->id,
        ]);
        
        $this->owner = User::factory()->create([
            'tenant_id' => $tenant->id,
            'property_owner_id' => $this->propertyOwner->id,
        ]);
        $this->owner->assignRole('property_owner');
        
        $this->admin = User::factory()->create([
            'tenant_id' => $tenant->id,
        ]);
        $this->admin->assignRole('company_admin');
        
        $this->property = Property::factory()->create([
            'tenant_id' => $tenant->id,
            'owner_id' => $this->propertyOwner->id,
        ]);
        
        $this->unit = Unit::factory()->create([
            'property_id' => $this->property->id,
            'monthly_rent' => 10000,
        ]);
    }

    /** @test */
    public function owner_can_create_change_request()
    {
        $data = [
            'property_id' => $this->property->id,
            'unit_id' => $this->unit->id,
            'request_type' => 'unit_price',
            'current_value' => '10000',
            'requested_value' => '12000',
            'reason' => 'Market rate increase due to property improvements and inflation',
            'affects_existing_leases' => false,
        ];

        $response = $this->actingAs($this->owner)
            ->postJson('/api/change-requests', $data);

        $response->assertStatus(201)
            ->assertJson(['success' => true])
            ->assertJsonStructure([
                'data' => ['id', 'request_type', 'status', 'current_value', 'requested_value']
            ]);

        $this->assertDatabaseHas('change_requests', [
            'property_owner_id' => $this->propertyOwner->id,
            'request_type' => 'unit_price',
            'status' => 'pending',
        ]);
    }

    /** @test */
    public function admin_can_approve_change_request()
    {
        $changeRequest = ChangeRequest::factory()->create([
            'tenant_id' => $this->admin->tenant_id,
            'property_owner_id' => $this->propertyOwner->id,
            'unit_id' => $this->unit->id,
            'request_type' => 'unit_price',
            'current_value' => '10000',
            'requested_value' => '12000',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->admin)
            ->patchJson("/api/change-requests/{$changeRequest->id}/approve", [
                'notes' => 'Approved based on market analysis'
            ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $changeRequest->refresh();
        $this->assertEquals('approved', $changeRequest->status);
        $this->assertEquals($this->admin->id, $changeRequest->reviewed_by);
        
        // Verify changes applied
        $this->unit->refresh();
        $this->assertEquals(12000, $this->unit->monthly_rent);
    }

    /** @test */
    public function admin_can_reject_change_request()
    {
        $changeRequest = ChangeRequest::factory()->create([
            'tenant_id' => $this->admin->tenant_id,
            'property_owner_id' => $this->propertyOwner->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->admin)
            ->patchJson("/api/change-requests/{$changeRequest->id}/reject", [
                'reason' => 'Requested increase exceeds market rate'
            ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $changeRequest->refresh();
        $this->assertEquals('rejected', $changeRequest->status);
    }

    /** @test */
    public function owner_cannot_approve_own_request()
    {
        $changeRequest = ChangeRequest::factory()->create([
            'tenant_id' => $this->owner->tenant_id,
            'property_owner_id' => $this->propertyOwner->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->owner)
            ->patchJson("/api/change-requests/{$changeRequest->id}/approve");

        $response->assertStatus(403);
    }
}
