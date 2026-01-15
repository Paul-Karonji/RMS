<?php

namespace Tests\Feature\Financial;

use Tests\TestCase;
use App\Models\User;
use App\Models\Tenant;
use App\Models\Property;
use App\Models\PropertyOwner;
use App\Models\Payment;
use App\Models\BalanceTransaction;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BalanceTransactionTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;
    private PropertyOwner $propertyOwner;
    private Payment $payment;

    protected function setUp(): void
    {
        parent::setUp();

        // Create tenant
        $this->tenant = Tenant::factory()->create();

        // Create property owner
        $this->propertyOwner = PropertyOwner::factory()->create([
            'tenant_id' => $this->tenant->id,
        ]);

        // Create payment for testing
        $this->payment = Payment::factory()->create([
            'tenant_id' => $this->tenant->id,
            'amount' => 50000.00,
            'status' => 'completed',
        ]);
    }

    /** @test */
    public function can_create_balance_transaction()
    {
        $transaction = BalanceTransaction::create([
            'tenant_id' => $this->tenant->id,
            'payment_id' => $this->payment->id,
            'property_owner_id' => $this->propertyOwner->id,
            'transaction_type' => 'rent_payment',
            'amount' => 50000.00,
            'fee_amount' => 5000.00,
            'net_amount' => 45000.00,
            'transaction_date' => now()->format('Y-m-d'),
            'description' => 'Rent payment received',
            'reference_id' => $this->payment->id,
        ]);

        $this->assertDatabaseHas('balance_transactions', [
            'tenant_id' => $this->tenant->id,
            'transaction_type' => 'rent_payment',
            'amount' => 50000.00,
        ]);

        $this->assertEquals(45000.00, $transaction->net_amount);
    }

    /** @test */
    public function transaction_requires_mandatory_fields()
    {
        $this->expectException(\Illuminate\Database\QueryException::class);

        BalanceTransaction::create([
            'tenant_id' => $this->tenant->id,
            // Missing required fields
        ]);
    }

    /** @test */
    public function can_list_transactions_for_tenant()
    {
        // Create multiple transactions
        BalanceTransaction::factory()->count(3)->create([
            'tenant_id' => $this->tenant->id,
            'property_owner_id' => $this->propertyOwner->id,
        ]);

        $transactions = BalanceTransaction::where('tenant_id', $this->tenant->id)->get();

        $this->assertCount(3, $transactions);
    }

    /** @test */
    public function can_filter_transactions_by_type()
    {
        BalanceTransaction::factory()->create([
            'tenant_id' => $this->tenant->id,
            'property_owner_id' => $this->propertyOwner->id,
            'transaction_type' => 'rent_payment',
        ]);

        BalanceTransaction::factory()->create([
            'tenant_id' => $this->tenant->id,
            'property_owner_id' => $this->propertyOwner->id,
            'transaction_type' => 'expense_deduction',
        ]);

        $rentPayments = BalanceTransaction::where('tenant_id', $this->tenant->id)
            ->where('transaction_type', 'rent_payment')
            ->get();

        $this->assertCount(1, $rentPayments);
        $this->assertEquals('rent_payment', $rentPayments->first()->transaction_type);
    }

    /** @test */
    public function can_get_transactions_for_property_owner()
    {
        // Create transactions for this owner
        BalanceTransaction::factory()->count(2)->create([
            'tenant_id' => $this->tenant->id,
            'property_owner_id' => $this->propertyOwner->id,
        ]);

        // Create transaction for another owner
        $otherOwner = PropertyOwner::factory()->create([
            'tenant_id' => $this->tenant->id,
        ]);
        BalanceTransaction::factory()->create([
            'tenant_id' => $this->tenant->id,
            'property_owner_id' => $otherOwner->id,
        ]);

        $ownerTransactions = BalanceTransaction::where('property_owner_id', $this->propertyOwner->id)
            ->get();

        $this->assertCount(2, $ownerTransactions);
    }

    /** @test */
    public function can_calculate_net_amount_correctly()
    {
        $transaction = BalanceTransaction::create([
            'tenant_id' => $this->tenant->id,
            'property_owner_id' => $this->propertyOwner->id,
            'transaction_type' => 'rent_payment',
            'amount' => 100000.00,
            'fee_amount' => 10000.00,
            'net_amount' => 90000.00,
            'transaction_date' => now()->format('Y-m-d'),
        ]);

        // Verify net amount calculation
        $expectedNet = $transaction->amount - $transaction->fee_amount;
        $this->assertEquals($expectedNet, $transaction->net_amount);
    }

    /** @test */
    public function transactions_are_tenant_scoped()
    {
        // Create transaction for this tenant
        $transaction1 = BalanceTransaction::factory()->create([
            'tenant_id' => $this->tenant->id,
            'property_owner_id' => $this->propertyOwner->id,
        ]);

        // Create another tenant and transaction
        $otherTenant = Tenant::factory()->create();
        $otherOwner = PropertyOwner::factory()->create([
            'tenant_id' => $otherTenant->id,
        ]);
        $transaction2 = BalanceTransaction::factory()->create([
            'tenant_id' => $otherTenant->id,
            'property_owner_id' => $otherOwner->id,
        ]);

        // Verify tenant isolation
        $tenantTransactions = BalanceTransaction::where('tenant_id', $this->tenant->id)->get();
        
        $this->assertCount(1, $tenantTransactions);
        $this->assertEquals($transaction1->id, $tenantTransactions->first()->id);
    }

    /** @test */
    public function can_get_transactions_by_date_range()
    {
        $startDate = now()->subDays(10);
        $endDate = now();

        // Create transaction within range
        BalanceTransaction::factory()->create([
            'tenant_id' => $this->tenant->id,
            'property_owner_id' => $this->propertyOwner->id,
            'transaction_date' => now()->subDays(5)->format('Y-m-d'),
        ]);

        // Create transaction outside range
        BalanceTransaction::factory()->create([
            'tenant_id' => $this->tenant->id,
            'property_owner_id' => $this->propertyOwner->id,
            'transaction_date' => now()->subDays(15)->format('Y-m-d'),
        ]);

        $transactions = BalanceTransaction::where('tenant_id', $this->tenant->id)
            ->whereBetween('transaction_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->get();

        $this->assertCount(1, $transactions);
    }
}
