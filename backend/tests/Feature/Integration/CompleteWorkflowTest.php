<?php

namespace Tests\Feature\Integration;

use Tests\TestCase;
use App\Models\User;
use App\Models\Tenant;
use App\Models\Property;
use App\Models\PropertyOwner;
use App\Models\Unit;
use App\Models\TenantUser;
use App\Models\Lease;
use App\Models\Payment;
use App\Models\CompanyBalance;
use App\Models\OwnerBalance;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Integration tests for complete workflows
 * Tests end-to-end scenarios across multiple models
 */
class CompleteWorkflowTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function complete_property_registration_to_approval_workflow()
    {
        // Step 1: Create tenant (company)
        $tenant = Tenant::factory()->create([
            'pricing_model' => 'payment_processing',
        ]);

        // Step 2: Create property owner user
        $ownerUser = User::factory()->create([
            'tenant_id' => $tenant->id,
            'role' => 'property_owner',
        ]);

        $propertyOwner = PropertyOwner::factory()->create([
            'tenant_id' => $tenant->id,
            'user_id' => $ownerUser->id,
        ]);

        // Step 3: Create admin user
        $admin = User::factory()->create([
            'tenant_id' => $tenant->id,
            'role' => 'company_admin',
        ]);

        // Step 4: Owner submits property
        $property = Property::factory()->create([
            'tenant_id' => $tenant->id,
            'owner_id' => $propertyOwner->id,
            'status' => 'pending_approval',
        ]);

        $this->assertEquals('pending_approval', $property->status);

        // Step 5: Admin approves property
        $property->update([
            'status' => 'active',
            'approved_by' => $admin->id,
            'approved_at' => now(),
        ]);

        $this->assertEquals('active', $property->fresh()->status);
        $this->assertNotNull($property->approved_at);
    }

    /** @test */
    public function complete_tenant_onboarding_to_lease_creation()
    {
        // Setup
        $tenant = Tenant::factory()->create();
        $admin = User::factory()->create([
            'tenant_id' => $tenant->id,
            'role' => 'company_admin',
        ]);
        $propertyOwner = PropertyOwner::factory()->create([
            'tenant_id' => $tenant->id,
        ]);
        $property = Property::factory()->create([
            'tenant_id' => $tenant->id,
            'owner_id' => $propertyOwner->id,
            'status' => 'active',
        ]);
        $unit = Unit::factory()->create([
            'tenant_id' => $tenant->id,
            'property_id' => $property->id,
            'status' => 'vacant',
        ]);

        // Step 1: Create tenant renter user
        $tenantUser = User::factory()->create([
            'tenant_id' => $tenant->id,
            'role' => 'tenant',
        ]);

        $tenantRenter = TenantUser::factory()->create([
            'tenant_id' => $tenant->id,
            'user_id' => $tenantUser->id,
        ]);

        // Step 2: Create lease
        $lease = Lease::factory()->create([
            'tenant_id' => $tenantUser->id, // References users.id
            'property_id' => $property->id,
            'unit_id' => $unit->id,
            'property_owner_id' => $propertyOwner->id,
            'monthly_rent' => 50000.00,
            'deposit_amount' => 50000.00,
            'status' => 'active',
            'created_by' => $admin->id,
        ]);

        // Step 3: Update unit status
        $unit->update(['status' => 'occupied']);

        // Verify workflow
        $this->assertEquals('active', $lease->status);
        $this->assertEquals('occupied', $unit->fresh()->status);
        $this->assertEquals(50000.00, $lease->monthly_rent);
    }

    /** @test */
    public function complete_payment_to_balance_update_workflow()
    {
        // Setup
        $tenant = Tenant::factory()->create([
            'pricing_model' => 'payment_processing',
        ]);
        
        CompanyBalance::factory()->create([
            'tenant_id' => $tenant->id,
            'available_balance' => 0.00,
        ]);

        $propertyOwner = PropertyOwner::factory()->create([
            'tenant_id' => $tenant->id,
        ]);

        OwnerBalance::factory()->create([
            'tenant_id' => $tenant->id,
            'property_owner_id' => $propertyOwner->id,
            'amount_owed' => 0.00,
        ]);

        $property = Property::factory()->create([
            'tenant_id' => $tenant->id,
            'owner_id' => $propertyOwner->id,
            'fee_type' => 'percentage',
            'fee_value' => 10.00,
        ]);

        $unit = Unit::factory()->create([
            'tenant_id' => $tenant->id,
            'property_id' => $property->id,
        ]);

        $tenantUser = User::factory()->create([
            'tenant_id' => $tenant->id,
            'role' => 'tenant',
        ]);

        $lease = Lease::factory()->create([
            'tenant_id' => $tenantUser->id,
            'property_id' => $property->id,
            'unit_id' => $unit->id,
            'property_owner_id' => $propertyOwner->id,
            'monthly_rent' => 50000.00,
        ]);

        // Step 1: Create payment
        $payment = Payment::factory()->create([
            'tenant_id' => $tenantUser->id, // References users.id
            'lease_id' => $lease->id,
            'property_id' => $property->id,
            'unit_id' => $unit->id,
            'amount' => 50000.00,
            'payment_type' => 'rent',
            'payment_method' => 'mpesa',
            'status' => 'completed',
            'payment_date' => now(),
        ]);

        // Step 2: Calculate platform fee (10%)
        $platformFee = 5000.00;
        $ownerShare = 45000.00;

        // Step 3: Update company balance
        $companyBalance = CompanyBalance::where('tenant_id', $tenant->id)->first();
        $companyBalance->update([
            'available_balance' => $companyBalance->available_balance + $ownerShare,
            'platform_fees_collected' => $companyBalance->platform_fees_collected + $platformFee,
            'total_earned' => $companyBalance->total_earned + $payment->amount,
        ]);

        // Step 4: Update owner balance
        $ownerBalance = OwnerBalance::where('property_owner_id', $propertyOwner->id)->first();
        $ownerBalance->update([
            'amount_owed' => $ownerBalance->amount_owed + $ownerShare,
            'total_rent_collected' => $ownerBalance->total_rent_collected + $payment->amount,
            'total_platform_fees' => $ownerBalance->total_platform_fees + $platformFee,
        ]);

        // Verify workflow
        $this->assertEquals('completed', $payment->status);
        $this->assertEquals(45000.00, $companyBalance->fresh()->available_balance);
        $this->assertEquals(5000.00, $companyBalance->fresh()->platform_fees_collected);
        $this->assertEquals(45000.00, $ownerBalance->fresh()->amount_owed);
    }

    /** @test */
    public function complete_expense_approval_to_owner_balance_deduction()
    {
        // Setup
        $tenant = Tenant::factory()->create();
        $admin = User::factory()->create([
            'tenant_id' => $tenant->id,
            'role' => 'company_admin',
        ]);
        $propertyOwner = PropertyOwner::factory()->create([
            'tenant_id' => $tenant->id,
        ]);
        
        $ownerBalance = OwnerBalance::factory()->create([
            'tenant_id' => $tenant->id,
            'property_owner_id' => $propertyOwner->id,
            'amount_owed' => 50000.00,
        ]);

        $property = Property::factory()->create([
            'tenant_id' => $tenant->id,
            'owner_id' => $propertyOwner->id,
            'expense_sharing_enabled' => true,
            'owner_expense_percentage' => 70.00,
        ]);

        // Step 1: Create expense
        $expense = \App\Models\Expense::factory()->create([
            'tenant_id' => $tenant->id,
            'property_id' => $property->id,
            'amount' => 10000.00,
            'owner_share' => 7000.00,
            'platform_share' => 3000.00,
            'status' => 'pending',
            'created_by' => $admin->id,
        ]);

        $this->assertEquals('pending', $expense->status);

        // Step 2: Admin approves expense
        $expense->update([
            'status' => 'approved',
            'approved_by' => $admin->id,
            'approved_at' => now(),
        ]);

        // Step 3: Deduct from owner balance
        $ownerBalance->update([
            'amount_owed' => $ownerBalance->amount_owed - $expense->owner_share,
            'total_expenses' => $ownerBalance->total_expenses + $expense->owner_share,
        ]);

        // Verify workflow
        $this->assertEquals('approved', $expense->fresh()->status);
        $this->assertEquals(43000.00, $ownerBalance->fresh()->amount_owed); // 50000 - 7000
        $this->assertEquals(7000.00, $ownerBalance->fresh()->total_expenses);
    }
}
