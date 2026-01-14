<?php

namespace Tests\Feature\Payout;

use Tests\TestCase;
use App\Models\Tenant;
use App\Models\User;
use App\Models\CompanyBalance;
use App\Models\CashoutRequest;
use App\Models\PlatformUser;
use App\Services\CashoutService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

class CashoutServiceTest extends TestCase
{
    use RefreshDatabase;

    protected CashoutService $cashoutService;
    protected Tenant $tenant;
    protected User $admin;
    protected CompanyBalance $companyBalance;
    protected PlatformUser $platformOwner;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->cashoutService = new CashoutService();
        
        // Create platform owner
        $this->platformOwner = PlatformUser::create([
            'id' => Str::uuid(),
            'name' => 'Platform Admin',
            'email' => 'platform@test.com',
            'password_hash' => bcrypt('password'),
            'role' => 'platform_owner',
        ]);
        
        // Create tenant
        $this->tenant = Tenant::create([
            'id' => Str::uuid(),
            'company_name' => 'Test Company',
            'admin_email' => 'admin@test.com',
            'pricing_model' => 'percentage',
            'cashout_fee_percentage' => 3.00,
            'min_cashout_amount' => 5000.00,
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
        
        // Create company balance
        $this->companyBalance = CompanyBalance::create([
            'id' => Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'available_balance' => 100000.00,
            'total_collected' => 100000.00,
        ]);
    }

    /** @test */
    public function can_create_cashout_request_with_correct_fee_calculation()
    {
        $cashout = $this->cashoutService->createRequest([
            'tenant_id' => $this->tenant->id,
            'amount' => 50000.00,
            'payment_method' => 'bank_transfer',
            'payment_details' => [
                'account_number' => '1234567890',
                'bank_name' => 'Test Bank',
                'account_name' => 'Test Company',
            ],
        ]);

        $this->assertEquals(50000.00, $cashout->amount);
        $this->assertEquals(1500.00, $cashout->fee_amount); // 3% of 50,000
        $this->assertEquals(48500.00, $cashout->net_amount); // 50,000 - 1,500
        $this->assertEquals('pending', $cashout->status);
        $this->assertEquals('bank_transfer', $cashout->payment_method);
    }

    /** @test */
    public function cannot_create_cashout_with_insufficient_balance()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Insufficient balance');

        $this->cashoutService->createRequest([
            'tenant_id' => $this->tenant->id,
            'amount' => 150000.00, // More than available balance
            'payment_method' => 'bank_transfer',
        ]);
    }

    /** @test */
    public function cannot_create_cashout_below_minimum_amount()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Minimum cashout amount');

        $this->cashoutService->createRequest([
            'tenant_id' => $this->tenant->id,
            'amount' => 1000.00, // Less than min_cashout_amount (5000)
            'payment_method' => 'mpesa',
        ]);
    }

    /** @test */
    public function can_approve_pending_cashout_request()
    {
        $cashout = CashoutRequest::create([
            'id' => Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'amount' => 50000.00,
            'fee_amount' => 1500.00,
            'net_amount' => 48500.00,
            'status' => 'pending',
            'payment_method' => 'bank_transfer',
        ]);

        $approved = $this->cashoutService->approve($cashout, $this->platformOwner->id);

        $this->assertEquals('approved', $approved->status);
        $this->assertEquals($this->platformOwner->id, $approved->approved_by);
        $this->assertNotNull($approved->approved_at);
    }

    /** @test */
    public function cannot_approve_non_pending_cashout()
    {
        $cashout = CashoutRequest::create([
            'id' => Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'amount' => 50000.00,
            'fee_amount' => 1500.00,
            'net_amount' => 48500.00,
            'status' => 'approved',
            'payment_method' => 'bank_transfer',
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Only pending requests can be approved');

        $this->cashoutService->approve($cashout, $this->platformOwner->id);
    }

    /** @test */
    public function processing_cashout_updates_company_balance()
    {
        $cashout = CashoutRequest::create([
            'id' => Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'amount' => 50000.00,
            'fee_amount' => 1500.00,
            'net_amount' => 48500.00,
            'status' => 'approved',
            'payment_method' => 'bank_transfer',
            'approved_by' => $this->platformOwner->id,
            'approved_at' => now(),
        ]);

        $processed = $this->cashoutService->process($cashout, 'TRANS123456');

        $this->assertEquals('processed', $processed->status);
        $this->assertEquals('TRANS123456', $processed->transaction_id);
        $this->assertNotNull($processed->processed_at);

        // Check company balance updated
        $this->companyBalance->refresh();
        $this->assertEquals(50000.00, $this->companyBalance->available_balance); // 100,000 - 50,000
        $this->assertEquals(48500.00, $this->companyBalance->total_cashed_out);
        $this->assertEquals(1500.00, $this->companyBalance->total_platform_fees_paid);
        $this->assertNotNull($this->companyBalance->last_cashout_at);
        $this->assertEquals(48500.00, $this->companyBalance->last_cashout_amount);
    }

    /** @test */
    public function cannot_process_non_approved_cashout()
    {
        $cashout = CashoutRequest::create([
            'id' => Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'amount' => 50000.00,
            'fee_amount' => 1500.00,
            'net_amount' => 48500.00,
            'status' => 'pending',
            'payment_method' => 'bank_transfer',
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Only approved requests can be processed');

        $this->cashoutService->process($cashout, 'TRANS123456');
    }

    /** @test */
    public function can_reject_pending_cashout_with_reason()
    {
        $cashout = CashoutRequest::create([
            'id' => Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'amount' => 50000.00,
            'fee_amount' => 1500.00,
            'net_amount' => 48500.00,
            'status' => 'pending',
            'payment_method' => 'bank_transfer',
        ]);

        $rejected = $this->cashoutService->reject(
            $cashout,
            $this->platformOwner->id,
            'Invalid bank details'
        );

        $this->assertEquals('rejected', $rejected->status);
        $this->assertEquals($this->platformOwner->id, $rejected->rejected_by);
        $this->assertEquals('Invalid bank details', $rejected->rejection_reason);
        $this->assertNotNull($rejected->rejected_at);
    }

    /** @test */
    public function get_statistics_returns_correct_data()
    {
        // Create various cashout requests
        CashoutRequest::create([
            'id' => Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'amount' => 30000.00,
            'fee_amount' => 900.00,
            'net_amount' => 29100.00,
            'status' => 'pending',
            'payment_method' => 'bank_transfer',
        ]);

        CashoutRequest::create([
            'id' => Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'amount' => 20000.00,
            'fee_amount' => 600.00,
            'net_amount' => 19400.00,
            'status' => 'approved',
            'payment_method' => 'mpesa',
        ]);

        CashoutRequest::create([
            'id' => Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'amount' => 50000.00,
            'fee_amount' => 1500.00,
            'net_amount' => 48500.00,
            'status' => 'processed',
            'payment_method' => 'bank_transfer',
        ]);

        $stats = $this->cashoutService->getStatistics($this->tenant->id);

        $this->assertEquals(30000.00, $stats['pending_amount']);
        $this->assertEquals(19400.00, $stats['approved_amount']);
        $this->assertEquals(48500.00, $stats['total_cashed_out']);
        $this->assertEquals(2100.00, $stats['total_fees_paid']); // 600 + 1500
    }
}
