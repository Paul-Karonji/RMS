<?php

namespace Tests\Feature\Payout;

use Tests\TestCase;
use App\Models\Tenant;
use App\Models\User;
use App\Models\PropertyOwner;
use App\Models\OwnerBalance;
use App\Models\OwnerPayment;
use App\Services\OwnerPaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

class OwnerPaymentServiceTest extends TestCase
{
    use RefreshDatabase;

    protected OwnerPaymentService $ownerPaymentService;
    protected Tenant $tenant;
    protected User $admin;
    protected PropertyOwner $owner;
    protected OwnerBalance $ownerBalance;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->ownerPaymentService = new OwnerPaymentService();
        
        // Create tenant
        $this->tenant = Tenant::create([
            'id' => Str::uuid(),
            'company_name' => 'Test Company',
            'admin_email' => 'admin@test.com',
            'pricing_model' => 'percentage',
        ]);
        
        // Create admin user
        $this->admin = User::create([
            'id' => Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'password_hash' => bcrypt('password'),
            'role' => 'company_admin',
        ]);
        
        // Create property owner
        $this->owner = PropertyOwner::create([
            'id' => Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'owner_name' => 'Test Owner',
            'email' => 'owner@test.com',
            'phone' => '+254700000000',
        ]);
        
        // Create owner balance
        $this->ownerBalance = OwnerBalance::create([
            'id' => Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'property_owner_id' => $this->owner->id,
            'amount_owed' => 100000.00,
            'amount_paid' => 0.00,
            'total_rent_collected' => 100000.00,
            'total_paid' => 0.00,
        ]);
    }

    /** @test */
    public function can_mark_payment_to_owner()
    {
        $payment = $this->ownerPaymentService->markPayment([
            'tenant_id' => $this->tenant->id,
            'property_owner_id' => $this->owner->id,
            'amount' => 50000.00,
            'payment_date' => now()->toDateString(),
            'payment_method' => 'bank_transfer',
            'transaction_id' => 'BANK123456',
            'notes' => 'Test payment',
            'created_by' => $this->admin->id,
        ]);

        $this->assertEquals($this->owner->id, $payment->property_owner_id);
        $this->assertEquals(50000.00, $payment->amount);
        $this->assertEquals('bank_transfer', $payment->payment_method);
        $this->assertEquals('BANK123456', $payment->transaction_id);
        $this->assertEquals('Test payment', $payment->notes);
    }

    /** @test */
    public function marking_payment_updates_owner_balance()
    {
        $this->ownerPaymentService->markPayment([
            'tenant_id' => $this->tenant->id,
            'property_owner_id' => $this->owner->id,
            'amount' => 50000.00,
            'payment_date' => now()->toDateString(),
            'payment_method' => 'bank_transfer',
            'created_by' => $this->admin->id,
        ]);

        $this->ownerBalance->refresh();

        $this->assertEquals(50000.00, $this->ownerBalance->amount_paid);
        $this->assertEquals(50000.00, $this->ownerBalance->amount_owed); // 100,000 - 50,000
        $this->assertEquals(50000.00, $this->ownerBalance->total_paid);
        $this->assertNotNull($this->ownerBalance->last_payment_date);
        $this->assertEquals(50000.00, $this->ownerBalance->last_payment_amount);
    }

    /** @test */
    public function cannot_pay_more_than_amount_owed()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('exceeds amount owed');

        $this->ownerPaymentService->markPayment([
            'tenant_id' => $this->tenant->id,
            'property_owner_id' => $this->owner->id,
            'amount' => 150000.00, // More than amount owed (100,000)
            'payment_date' => now()->toDateString(),
            'payment_method' => 'bank_transfer',
            'created_by' => $this->admin->id,
        ]);
    }

    /** @test */
    public function multiple_payments_accumulate_correctly()
    {
        // First payment
        $this->ownerPaymentService->markPayment([
            'tenant_id' => $this->tenant->id,
            'property_owner_id' => $this->owner->id,
            'amount' => 30000.00,
            'payment_date' => now()->subDays(10)->toDateString(),
            'payment_method' => 'bank_transfer',
            'created_by' => $this->admin->id,
        ]);

        // Second payment
        $this->ownerPaymentService->markPayment([
            'tenant_id' => $this->tenant->id,
            'property_owner_id' => $this->owner->id,
            'amount' => 20000.00,
            'payment_date' => now()->toDateString(),
            'payment_method' => 'mpesa',
            'created_by' => $this->admin->id,
        ]);

        $this->ownerBalance->refresh();

        $this->assertEquals(50000.00, $this->ownerBalance->amount_paid);
        $this->assertEquals(50000.00, $this->ownerBalance->amount_owed); // 100,000 - 50,000
        $this->assertEquals(50000.00, $this->ownerBalance->total_paid);
        $this->assertEquals(20000.00, $this->ownerBalance->last_payment_amount);
    }

    /** @test */
    public function get_statistics_returns_correct_data()
    {
        // Create payments in different months
        OwnerPayment::create([
            'id' => Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'property_owner_id' => $this->owner->id,
            'amount' => 30000.00,
            'payment_date' => now()->toDateString(),
            'payment_method' => 'bank_transfer',
            'created_by' => $this->admin->id,
        ]);

        OwnerPayment::create([
            'id' => Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'property_owner_id' => $this->owner->id,
            'amount' => 20000.00,
            'payment_date' => now()->toDateString(),
            'payment_method' => 'mpesa',
            'created_by' => $this->admin->id,
        ]);

        OwnerPayment::create([
            'id' => Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'property_owner_id' => $this->owner->id,
            'amount' => 15000.00,
            'payment_date' => now()->subMonth()->toDateString(),
            'payment_method' => 'bank_transfer',
            'created_by' => $this->admin->id,
        ]);

        $stats = $this->ownerPaymentService->getStatistics($this->tenant->id);

        $this->assertEquals(65000.00, $stats['total_paid']);
        $this->assertEquals(50000.00, $stats['this_month']); // 30,000 + 20,000
        $this->assertEquals(15000.00, $stats['last_month']);
    }

    /** @test */
    public function throws_exception_if_owner_balance_not_found()
    {
        // Create another owner without balance
        $newOwner = PropertyOwner::create([
            'id' => Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'owner_name' => 'New Owner',
            'email' => 'newowner@test.com',
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Owner balance not found');

        $this->ownerPaymentService->markPayment([
            'tenant_id' => $this->tenant->id,
            'property_owner_id' => $newOwner->id,
            'amount' => 10000.00,
            'payment_date' => now()->toDateString(),
            'payment_method' => 'cash',
            'created_by' => $this->admin->id,
        ]);
    }
}
