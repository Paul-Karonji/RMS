<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\BalanceService;
use App\Models\Payment;
use App\Models\Lease;
use App\Models\Property;
use App\Models\PropertyOwner;
use App\Models\CompanyBalance;
use App\Models\OwnerBalance;
use App\Models\PlatformFee;
use App\Models\BalanceTransaction;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BalanceServiceTest extends TestCase
{
    use RefreshDatabase;

    private BalanceService $balanceService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->balanceService = new BalanceService();
    }

    /** @test */
    public function it_calculates_platform_fee_correctly()
    {
        // Create test data
        $tenant = \App\Models\Tenant::factory()->create();
        $user = \App\Models\User::factory()->create(['tenant_id' => $tenant->id]);
        $owner = PropertyOwner::factory()->create();
        $property = Property::factory()->create([
            'tenant_id' => $tenant->id,
            'property_owner_id' => $owner->id,
            'commission_percentage' => 10.00, // 10% platform fee
        ]);
        $unit = \App\Models\Unit::factory()->create([
            'property_id' => $property->id,
        ]);
        $lease = Lease::factory()->create([
            'tenant_id' => $user->id,  // tenant_id references users table
            'property_id' => $property->id,
            'property_owner_id' => $owner->id,
            'unit_id' => $unit->id,
            'start_date' => now(),
            'end_date' => now()->addYear(),
            'monthly_rent' => 50000,
            'deposit_amount' => 50000,
            'created_by' => $user->id,
        ]);
        $payment = Payment::factory()->create([
            'lease_id' => $lease->id,
            'tenant_id' => $lease->tenant_id,
            'amount' => 50000, // KES 50,000
            'payment_type' => 'rent',
            'payment_method' => 'mpesa',
            'payment_date' => now(),
            'status' => 'completed',
        ]);

        // Update balances
        $result = $this->balanceService->updateBalancesAfterPayment($payment);

        // Assert balance update was successful
        $this->assertTrue($result);

        // Assert company balance updated correctly
        $companyBalance = CompanyBalance::where('tenant_id', $tenant->id)->first();
        $this->assertNotNull($companyBalance);
        $this->assertEquals(50000, $companyBalance->total_collected);
        $this->assertEquals(5000, $companyBalance->platform_fees_collected); // 10% of 50,000
        $this->assertEquals(5000, $companyBalance->available_balance);

        // Assert owner balance updated correctly
        $ownerBalance = OwnerBalance::where('property_owner_id', $owner->id)->first();
        $this->assertNotNull($ownerBalance);
        $this->assertEquals(50000, $ownerBalance->total_rent_collected);
        $this->assertEquals(45000, $ownerBalance->amount_owed); // 50,000 - 5,000 fee

        // Assert platform fee record created
        $platformFee = PlatformFee::where('payment_id', $payment->id)->first();
        $this->assertNotNull($platformFee);
        $this->assertEquals(10.00, $platformFee->fee_percentage);
        $this->assertEquals(5000, $platformFee->fee_amount);
        $this->assertEquals(50000, $platformFee->payment_amount);

        // Assert balance transaction logged
        $transaction = BalanceTransaction::where('payment_id', $payment->id)->first();
        $this->assertNotNull($transaction);
        $this->assertEquals(50000, $transaction->amount);
        $this->assertEquals(5000, $transaction->fee_amount);
        $this->assertEquals(45000, $transaction->net_amount);
    }

    /** @test */
    public function it_handles_different_platform_fee_percentages()
    {
        // Test with 15% platform fee
        $tenant = \App\Models\Tenant::factory()->create();
        $user = \App\Models\User::factory()->create(['tenant_id' => $tenant->id]);
        $owner = PropertyOwner::factory()->create();
        $property = Property::factory()->create([
            'tenant_id' => $tenant->id,
            'property_owner_id' => $owner->id,
            'commission_percentage' => 15.00, // 15% platform fee
        ]);
        $unit = \App\Models\Unit::factory()->create([
            'property_id' => $property->id,
        ]);
        $lease = Lease::factory()->create([
            'tenant_id' => $user->id,
            'property_id' => $property->id,
            'property_owner_id' => $owner->id,
            'unit_id' => $unit->id,
            'start_date' => now(),
            'end_date' => now()->addYear(),
            'monthly_rent' => 100000,
            'deposit_amount' => 100000,
            'created_by' => $user->id,
        ]);
        $payment = Payment::factory()->create([
            'lease_id' => $lease->id,
            'tenant_id' => $lease->tenant_id,
            'amount' => 100000, // KES 100,000
            'payment_type' => 'rent',
            'payment_method' => 'mpesa',
            'payment_date' => now(),
            'status' => 'completed',
        ]);

        $result = $this->balanceService->updateBalancesAfterPayment($payment);

        $this->assertTrue($result);

        $companyBalance = CompanyBalance::where('tenant_id', $tenant->id)->first();
        $this->assertEquals(15000, $companyBalance->platform_fees_collected); // 15% of 100,000

        $ownerBalance = OwnerBalance::where('property_owner_id', $owner->id)->first();
        $this->assertEquals(85000, $ownerBalance->amount_owed); // 100,000 - 15,000
    }

    /** @test */
    public function it_accumulates_multiple_payments_correctly()
    {
        $tenant = \App\Models\Tenant::factory()->create();
        $user = \App\Models\User::factory()->create(['tenant_id' => $tenant->id]);
        $owner = PropertyOwner::factory()->create();
        $property = Property::factory()->create([
            'tenant_id' => $tenant->id,
            'property_owner_id' => $owner->id,
            'commission_percentage' => 10.00,
        ]);
        $unit = \App\Models\Unit::factory()->create([
            'property_id' => $property->id,
        ]);
        $lease = Lease::factory()->create([
            'tenant_id' => $user->id,  // tenant_id references users table
            'property_id' => $property->id,
            'property_owner_id' => $owner->id,
            'unit_id' => $unit->id,
            'start_date' => now(),
            'end_date' => now()->addYear(),
            'monthly_rent' => 50000,
            'deposit_amount' => 50000,
            'created_by' => $user->id,
        ]);

        // First payment
        $payment1 = Payment::factory()->create([
            'lease_id' => $lease->id,
            'tenant_id' => $lease->tenant_id,
            'amount' => 50000,
            'payment_type' => 'rent',
            'payment_method' => 'mpesa',
            'payment_date' => now(),
            'status' => 'completed',
        ]);
        $this->balanceService->updateBalancesAfterPayment($payment1);

        // Second payment
        $payment2 = Payment::factory()->create([
            'lease_id' => $lease->id,
            'tenant_id' => $lease->tenant_id,
            'amount' => 30000,
            'payment_type' => 'rent',
            'payment_method' => 'mpesa',
            'payment_date' => now(),
            'status' => 'completed',
        ]);
        $this->balanceService->updateBalancesAfterPayment($payment2);

        // Assert accumulated balances
        $companyBalance = CompanyBalance::where('tenant_id', $tenant->id)->first();
        $this->assertEquals(80000, $companyBalance->total_collected); // 50,000 + 30,000
        $this->assertEquals(8000, $companyBalance->platform_fees_collected); // 5,000 + 3,000

        $ownerBalance = OwnerBalance::where('property_owner_id', $owner->id)->first();
        $this->assertEquals(80000, $ownerBalance->total_rent_collected);
        $this->assertEquals(72000, $ownerBalance->amount_owed); // 45,000 + 27,000
    }
}
