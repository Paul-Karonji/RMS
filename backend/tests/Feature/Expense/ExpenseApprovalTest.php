<?php

namespace Tests\Feature\Expense;

use Tests\TestCase;
use App\Models\User;
use App\Models\Tenant;
use App\Models\Property;
use App\Models\PropertyOwner;
use App\Models\Expense;
use App\Models\MaintenanceRequest;
use App\Models\OwnerBalance;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ExpenseApprovalTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $manager;
    private Tenant $tenant;
    private Property $property;
    private PropertyOwner $propertyOwner;
    private Expense $expense;

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

        // Create property owner
        $this->propertyOwner = PropertyOwner::factory()->create([
            'tenant_id' => $this->tenant->id,
        ]);

        // Create owner balance
        OwnerBalance::factory()->create([
            'tenant_id' => $this->tenant->id,
            'property_owner_id' => $this->propertyOwner->id,
            'amount_owed' => 50000.00,
        ]);

        // Create property
        $this->property = Property::factory()->create([
            'tenant_id' => $this->tenant->id,
            'owner_id' => $this->propertyOwner->id,
            'expense_sharing_enabled' => true,
            'owner_expense_percentage' => 70.00,
            'platform_expense_percentage' => 30.00,
        ]);

        // Create maintenance request
        $maintenanceRequest = MaintenanceRequest::factory()->create([
            'tenant_id' => $this->tenant->id,
            'property_id' => $this->property->id,
        ]);

        // Create pending expense
        $this->expense = Expense::factory()->create([
            'tenant_id' => $this->tenant->id,
            'property_id' => $this->property->id,
            'maintenance_request_id' => $maintenanceRequest->id,
            'amount' => 10000.00,
            'owner_share' => 7000.00,
            'platform_share' => 3000.00,
            'status' => 'pending',
            'created_by' => $this->manager->id,
        ]);
    }

    /** @test */
    public function admin_can_approve_expense()
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->patchJson("/api/expenses/{$this->expense->id}/approve");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Expense approved successfully',
            ]);

        $this->assertDatabaseHas('expenses', [
            'id' => $this->expense->id,
            'status' => 'approved',
            'approved_by' => $this->admin->id,
        ]);

        // Verify approved_at is set
        $this->expense->refresh();
        $this->assertNotNull($this->expense->approved_at);
    }

    /** @test */
    public function approving_expense_deducts_from_owner_balance()
    {
        $initialBalance = OwnerBalance::where('property_owner_id', $this->propertyOwner->id)
            ->first()
            ->amount_owed;

        $this->actingAs($this->admin, 'sanctum')
            ->patchJson("/api/expenses/{$this->expense->id}/approve");

        $ownerBalance = OwnerBalance::where('property_owner_id', $this->propertyOwner->id)
            ->first();

        // Owner's share should be deducted
        $expectedBalance = $initialBalance - $this->expense->owner_share;
        $this->assertEquals($expectedBalance, $ownerBalance->amount_owed);
        $this->assertEquals($initialBalance + $this->expense->owner_share, $ownerBalance->total_expenses);
    }

    /** @test */
    public function admin_can_reject_expense()
    {
        $rejectionReason = 'Receipt is not clear, please resubmit';

        $response = $this->actingAs($this->admin, 'sanctum')
            ->patchJson("/api/expenses/{$this->expense->id}/reject", [
                'rejection_reason' => $rejectionReason,
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Expense rejected',
            ]);

        $this->assertDatabaseHas('expenses', [
            'id' => $this->expense->id,
            'status' => 'rejected',
            'rejection_reason' => $rejectionReason,
        ]);
    }

    /** @test */
    public function rejection_requires_reason()
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->patchJson("/api/expenses/{$this->expense->id}/reject", []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['rejection_reason']);
    }

    /** @test */
    public function manager_cannot_approve_expense()
    {
        $response = $this->actingAs($this->manager, 'sanctum')
            ->patchJson("/api/expenses/{$this->expense->id}/approve");

        $response->assertStatus(403);
    }

    /** @test */
    public function cannot_approve_already_approved_expense()
    {
        // First approval
        $this->expense->update([
            'status' => 'approved',
            'approved_by' => $this->admin->id,
            'approved_at' => now(),
        ]);

        // Try to approve again
        $response = $this->actingAs($this->admin, 'sanctum')
            ->patchJson("/api/expenses/{$this->expense->id}/approve");

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Expense has already been approved',
            ]);
    }

    /** @test */
    public function cannot_approve_rejected_expense()
    {
        $this->expense->update([
            'status' => 'rejected',
            'rejection_reason' => 'Invalid receipt',
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->patchJson("/api/expenses/{$this->expense->id}/approve");

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
            ]);
    }

    /** @test */
    public function user_cannot_approve_expense_from_other_tenant()
    {
        // Create another tenant and expense
        $otherTenant = Tenant::factory()->create();
        $otherAdmin = User::factory()->create([
            'tenant_id' => $otherTenant->id,
            'role' => 'company_admin',
        ]);
        $otherPropertyOwner = PropertyOwner::factory()->create([
            'tenant_id' => $otherTenant->id,
        ]);
        $otherProperty = Property::factory()->create([
            'tenant_id' => $otherTenant->id,
            'owner_id' => $otherPropertyOwner->id,
        ]);
        $otherMaintenanceRequest = MaintenanceRequest::factory()->create([
            'tenant_id' => $otherTenant->id,
            'property_id' => $otherProperty->id,
        ]);
        $otherExpense = Expense::factory()->create([
            'tenant_id' => $otherTenant->id,
            'property_id' => $otherProperty->id,
            'maintenance_request_id' => $otherMaintenanceRequest->id,
            'status' => 'pending',
            'created_by' => $otherAdmin->id,
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->patchJson("/api/expenses/{$otherExpense->id}/approve");

        $response->assertStatus(403);
    }
}
