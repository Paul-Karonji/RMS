<?php

namespace Tests\Feature\Financial;

use Tests\TestCase;
use App\Models\User;
use App\Models\Tenant;
use App\Models\Property;
use App\Models\PropertyOwner;
use App\Models\Unit;
use App\Models\Lease;
use App\Models\DepositDeduction;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DepositDeductionTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Tenant $tenant;
    private Lease $lease;

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

        // Create lease with deposit
        $this->lease = Lease::factory()->create([
            'tenant_id' => $tenantUser->id,
            'property_id' => $property->id,
            'unit_id' => $unit->id,
            'property_owner_id' => $propertyOwner->id,
            'deposit_amount' => 50000.00,
            'status' => 'terminated',
        ]);
    }

    /** @test */
    public function admin_can_create_deposit_deduction()
    {
        $deductionData = [
            'lease_id' => $this->lease->id,
            'amount' => 10000.00,
            'reason' => 'Damaged wall paint',
            'description' => 'Wall in bedroom needs repainting',
        ];

        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/deposit-deductions', $deductionData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'amount',
                    'reason',
                    'status',
                ],
            ]);

        $this->assertDatabaseHas('deposit_deductions', [
            'lease_id' => $this->lease->id,
            'amount' => 10000.00,
            'status' => 'pending',
        ]);
    }

    /** @test */
    public function deduction_requires_mandatory_fields()
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/deposit-deductions', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'lease_id',
                'amount',
                'reason',
            ]);
    }

    /** @test */
    public function deduction_amount_must_be_positive()
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/deposit-deductions', [
                'lease_id' => $this->lease->id,
                'amount' => -100.00,
                'reason' => 'Test',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['amount']);
    }

    /** @test */
    public function cannot_deduct_more_than_deposit_amount()
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/deposit-deductions', [
                'lease_id' => $this->lease->id,
                'amount' => 60000.00, // More than 50000 deposit
                'reason' => 'Excessive damage',
            ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Deduction amount cannot exceed deposit amount',
            ]);
    }

    /** @test */
    public function can_calculate_remaining_deposit()
    {
        // Create first deduction
        DepositDeduction::factory()->create([
            'lease_id' => $this->lease->id,
            'amount' => 15000.00,
            'status' => 'approved',
        ]);

        // Create second deduction
        DepositDeduction::factory()->create([
            'lease_id' => $this->lease->id,
            'amount' => 10000.00,
            'status' => 'approved',
        ]);

        $totalDeductions = DepositDeduction::where('lease_id', $this->lease->id)
            ->where('status', 'approved')
            ->sum('amount');

        $remainingDeposit = $this->lease->deposit_amount - $totalDeductions;

        $this->assertEquals(25000.00, $remainingDeposit); // 50000 - 15000 - 10000
    }

    /** @test */
    public function admin_can_approve_deduction()
    {
        $deduction = DepositDeduction::factory()->create([
            'lease_id' => $this->lease->id,
            'amount' => 10000.00,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->patchJson("/api/deposit-deductions/{$deduction->id}/approve");

        $response->assertStatus(200);

        $this->assertDatabaseHas('deposit_deductions', [
            'id' => $deduction->id,
            'status' => 'approved',
            'approved_by' => $this->admin->id,
        ]);

        $deduction->refresh();
        $this->assertNotNull($deduction->approved_at);
    }

    /** @test */
    public function admin_can_reject_deduction()
    {
        $deduction = DepositDeduction::factory()->create([
            'lease_id' => $this->lease->id,
            'amount' => 10000.00,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->patchJson("/api/deposit-deductions/{$deduction->id}/reject", [
                'rejection_reason' => 'Not sufficient evidence',
            ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('deposit_deductions', [
            'id' => $deduction->id,
            'status' => 'rejected',
            'rejection_reason' => 'Not sufficient evidence',
        ]);
    }

    /** @test */
    public function can_list_deductions_for_lease()
    {
        DepositDeduction::factory()->count(3)->create([
            'lease_id' => $this->lease->id,
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson("/api/leases/{$this->lease->id}/deposit-deductions");

        $response->assertStatus(200);
        $this->assertCount(3, $response->json('data'));
    }

    /** @test */
    public function tenant_isolation_enforced()
    {
        // Create another tenant and lease
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
        $otherUnit = Unit::factory()->create([
            'tenant_id' => $otherTenant->id,
            'property_id' => $otherProperty->id,
        ]);
        $otherTenantUser = User::factory()->create([
            'tenant_id' => $otherTenant->id,
            'role' => 'tenant',
        ]);
        $otherLease = Lease::factory()->create([
            'tenant_id' => $otherTenantUser->id,
            'property_id' => $otherProperty->id,
            'unit_id' => $otherUnit->id,
            'property_owner_id' => $otherPropertyOwner->id,
        ]);

        $deduction = DepositDeduction::factory()->create([
            'lease_id' => $otherLease->id,
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson("/api/deposit-deductions/{$deduction->id}");

        $response->assertStatus(403);
    }
}
