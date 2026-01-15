<?php

namespace Tests\Feature\Maintenance;

use Tests\TestCase;
use App\Models\User;
use App\Models\Tenant;
use App\Models\Property;
use App\Models\PropertyOwner;
use App\Models\Unit;
use App\Models\TenantUser;
use App\Models\MaintenanceRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MaintenanceAssignmentTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $manager;
    private Tenant $tenant;
    private MaintenanceRequest $request;

    protected function setUp(): void
    {
        parent::setUp();

        // Create tenant
        $this->tenant = Tenant::factory()->create();

        // Create admin
        $this->admin = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'role' => 'company_admin',
        ]);

        // Create manager
        $this->manager = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'role' => 'property_manager',
        ]);

        // Create property and unit
        $propertyOwner = PropertyOwner::factory()->create([
            'tenant_id' => $this->tenant->id,
        ]);
        $property = Property::factory()->create([
            'tenant_id' => $this->tenant->id,
            'owner_id' => $propertyOwner->id,
        ]);
        $unit = Unit::factory()->create([
            'tenant_id' => $this->tenant->id,
            'property_id' => $property->id,
        ]);

        // Create tenant renter
        $tenantUser = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'role' => 'tenant',
        ]);
        $tenantRenter = TenantUser::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $tenantUser->id,
        ]);

        // Create maintenance request
        $this->request = MaintenanceRequest::factory()->create([
            'tenant_id' => $this->tenant->id,
            'property_id' => $property->id,
            'unit_id' => $unit->id,
            'tenant_user_id' => $tenantRenter->id,
            'status' => 'pending',
        ]);
    }

    /** @test */
    public function admin_can_assign_maintenance_request()
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->patchJson("/api/maintenance/{$this->request->id}/assign", [
                'assigned_to' => $this->manager->id,
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Maintenance request assigned successfully',
            ]);

        $this->assertDatabaseHas('maintenance_requests', [
            'id' => $this->request->id,
            'assigned_to' => $this->manager->id,
            'status' => 'assigned',
        ]);
    }

    /** @test */
    public function manager_can_assign_maintenance_request()
    {
        $response = $this->actingAs($this->manager, 'sanctum')
            ->patchJson("/api/maintenance/{$this->request->id}/assign", [
                'assigned_to' => $this->manager->id,
            ]);

        $response->assertStatus(200);
    }

    /** @test */
    public function assignment_requires_valid_user()
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->patchJson("/api/maintenance/{$this->request->id}/assign", [
                'assigned_to' => 'invalid-uuid',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['assigned_to']);
    }

    /** @test */
    public function can_reassign_maintenance_request()
    {
        // First assignment
        $this->request->update([
            'assigned_to' => $this->manager->id,
            'status' => 'assigned',
        ]);

        // Create another manager
        $newManager = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'role' => 'property_manager',
        ]);

        // Reassign
        $response = $this->actingAs($this->admin, 'sanctum')
            ->patchJson("/api/maintenance/{$this->request->id}/assign", [
                'assigned_to' => $newManager->id,
            ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('maintenance_requests', [
            'id' => $this->request->id,
            'assigned_to' => $newManager->id,
        ]);
    }

    /** @test */
    public function cannot_assign_to_user_from_different_tenant()
    {
        // Create another tenant and user
        $otherTenant = Tenant::factory()->create();
        $otherManager = User::factory()->create([
            'tenant_id' => $otherTenant->id,
            'role' => 'property_manager',
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->patchJson("/api/maintenance/{$this->request->id}/assign", [
                'assigned_to' => $otherManager->id,
            ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function assigned_user_can_update_request_status()
    {
        $this->request->update([
            'assigned_to' => $this->manager->id,
            'status' => 'assigned',
        ]);

        $response = $this->actingAs($this->manager, 'sanctum')
            ->patchJson("/api/maintenance/{$this->request->id}", [
                'status' => 'in_progress',
            ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('maintenance_requests', [
            'id' => $this->request->id,
            'status' => 'in_progress',
        ]);
    }

    /** @test */
    public function unassigned_manager_cannot_update_request()
    {
        $this->request->update([
            'assigned_to' => $this->manager->id,
            'status' => 'assigned',
        ]);

        // Create another manager
        $otherManager = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'role' => 'property_manager',
        ]);

        $response = $this->actingAs($otherManager, 'sanctum')
            ->patchJson("/api/maintenance/{$this->request->id}", [
                'status' => 'in_progress',
            ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function can_complete_maintenance_request()
    {
        $this->request->update([
            'assigned_to' => $this->manager->id,
            'status' => 'in_progress',
        ]);

        $response = $this->actingAs($this->manager, 'sanctum')
            ->patchJson("/api/maintenance/{$this->request->id}/complete", [
                'completion_notes' => 'Fixed the issue',
            ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('maintenance_requests', [
            'id' => $this->request->id,
            'status' => 'completed',
        ]);

        $this->request->refresh();
        $this->assertNotNull($this->request->completed_at);
    }
}
