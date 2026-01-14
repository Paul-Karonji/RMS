<?php

namespace Tests\Feature\Reports;

use Tests\TestCase;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Property;
use App\Models\Unit;
use App\Models\PropertyOwner;
use App\Models\OwnerBalance;
use App\Models\Lease;
use App\Models\Payment;
use App\Models\Expense;
use App\Models\OwnerPayment;
use App\Models\BalanceTransaction;
use App\Services\ReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ReportServiceTest extends TestCase
{
    use RefreshDatabase;

    protected ReportService $reportService;
    protected Tenant $tenant;
    protected User $admin;
    protected PropertyOwner $owner;
    protected Property $property;
    protected Unit $unit;
    protected User $tenantRenter;
    protected Lease $lease;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->reportService = new ReportService();
        
        // Create tenant (company)
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
        
        // Create property
        $this->property = Property::create([
            'id' => Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'property_owner_id' => $this->owner->id,
            'property_name' => 'Test Property',
            'address' => '123 Test St',
            'city' => 'Nairobi',
            'property_type' => 'apartment',
            'total_units' => 2,
            'occupied_units' => 1,
            'vacant_units' => 1,
            'status' => 'approved',
        ]);
        
        // Create units
        $this->unit = Unit::create([
            'id' => Str::uuid(),
            'property_id' => $this->property->id,
            'unit_number' => '101',
            'unit_type' => '2BR',
            'bedrooms' => 2,
            'bathrooms' => 1,
            'size_sqft' => 800.00,
            'monthly_rent' => 50000.00,
            'deposit_amount' => 50000.00,
            'status' => 'occupied',
        ]);
        
        Unit::create([
            'id' => Str::uuid(),
            'property_id' => $this->property->id,
            'unit_number' => '102',
            'unit_type' => '1BR',
            'bedrooms' => 1,
            'bathrooms' => 1,
            'size_sqft' => 600.00,
            'monthly_rent' => 35000.00,
            'deposit_amount' => 35000.00,
            'status' => 'available',
        ]);
        
        // Create tenant renter
        $this->tenantRenter = User::create([
            'id' => Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'name' => 'Tenant Renter',
            'email' => 'renter@test.com',
            'password_hash' => bcrypt('password'),
            'role' => 'tenant',
        ]);
        
        // Create lease
        $this->lease = Lease::create([
            'id' => Str::uuid(),
            'tenant_id' => $this->tenantRenter->id,
            'property_id' => $this->property->id,
            'unit_id' => $this->unit->id,
            'property_owner_id' => $this->owner->id,
            'start_date' => now()->subMonths(3),
            'end_date' => now()->addMonths(9),
            'monthly_rent' => 50000.00,
            'deposit_amount' => 50000.00,
            'status' => 'active',
            'created_by' => $this->admin->id,
        ]);
        
        // Create owner balance
        OwnerBalance::create([
            'id' => Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'property_owner_id' => $this->owner->id,
            'amount_owed' => 30000.00,
            'amount_paid' => 20000.00,
            'total_rent_collected' => 150000.00,
            'total_platform_fees' => 4500.00,
            'total_expenses' => 15000.00,
            'total_earned' => 130500.00,
            'total_paid' => 100500.00,
        ]);
    }

    /** @test */
    public function can_generate_financial_report()
    {
        // Create payment
        Payment::create([
            'id' => Str::uuid(),
            'tenant_id' => $this->tenantRenter->id,
            'lease_id' => $this->lease->id,
            'payment_type' => 'rent',
            'amount' => 50000.00,
            'payment_method' => 'mpesa',
            'status' => 'completed',
            'payment_date' => now(),
        ]);
        
        // Create expense
        Expense::create([
            'id' => Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'property_id' => $this->property->id,
            'category' => 'maintenance',
            'description' => 'Repair work',
            'amount' => 5000.00,
            'expense_date' => now(),
            'created_by' => $this->admin->id,
        ]);
        
        $report = $this->reportService->generateFinancialReport(
            $this->tenant->id,
            now()->startOfMonth(),
            now()->endOfMonth()
        );
        
        $this->assertArrayHasKey('summary', $report);
        $this->assertArrayHasKey('total_revenue', $report['summary']);
        $this->assertArrayHasKey('total_expenses', $report['summary']);
        $this->assertEquals(50000.00, $report['summary']['total_revenue']);
        $this->assertEquals(5000.00, $report['summary']['total_expenses']);
    }

    /** @test */
    public function can_generate_occupancy_report()
    {
        $report = $this->reportService->generateOccupancyReport(
            $this->tenant->id,
            now()->startOfMonth(),
            now()->endOfMonth()
        );
        
        $this->assertArrayHasKey('summary', $report);
        $this->assertArrayHasKey('vacant_units', $report);
        $this->assertArrayHasKey('occupied_units', $report);
        $this->assertEquals(2, $report['summary']['total_units']);
        $this->assertEquals(1, $report['summary']['occupied_units']);
        $this->assertEquals(1, $report['summary']['vacant_units']);
        $this->assertEquals(50.00, $report['summary']['occupancy_rate']);
    }

    /** @test */
    public function can_generate_payment_report()
    {
        // Create payments
        Payment::create([
            'id' => Str::uuid(),
            'tenant_id' => $this->tenantRenter->id,
            'lease_id' => $this->lease->id,
            'payment_type' => 'rent',
            'amount' => 50000.00,
            'payment_method' => 'mpesa',
            'status' => 'completed',
            'payment_date' => now(),
        ]);
        
        Payment::create([
            'id' => Str::uuid(),
            'tenant_id' => $this->tenantRenter->id,
            'lease_id' => $this->lease->id,
            'payment_type' => 'rent',
            'amount' => 50000.00,
            'payment_method' => 'bank_transfer',
            'status' => 'pending',
            'payment_date' => now()->addDays(5),
        ]);
        
        $report = $this->reportService->generatePaymentReport(
            $this->tenant->id,
            now()->startOfMonth(),
            now()->endOfMonth()
        );
        
        $this->assertArrayHasKey('summary', $report);
        $this->assertArrayHasKey('payments_by_method', $report);
        $this->assertEquals(2, $report['summary']['total_payments']);
        $this->assertEquals(1, $report['summary']['completed_payments']);
        $this->assertEquals(1, $report['summary']['pending_payments']);
        $this->assertEquals(50.00, $report['summary']['payment_success_rate']);
    }

    /** @test */
    public function can_generate_owner_statement()
    {
        // Create owner payment
        OwnerPayment::create([
            'id' => Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'property_owner_id' => $this->owner->id,
            'amount' => 20000.00,
            'payment_date' => now(),
            'payment_method' => 'bank_transfer',
            'created_by' => $this->admin->id,
        ]);
        
        // Create balance transaction
        BalanceTransaction::create([
            'id' => Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'property_owner_id' => $this->owner->id,
            'transaction_type' => 'rent_collection',
            'amount' => 50000.00,
            'fee_amount' => 1500.00,
            'net_amount' => 48500.00,
            'transaction_date' => now(),
        ]);
        
        $report = $this->reportService->generateOwnerStatement(
            $this->owner->id,
            now()->startOfMonth(),
            now()->endOfMonth()
        );
        
        $this->assertArrayHasKey('summary', $report);
        $this->assertArrayHasKey('lifetime_totals', $report);
        $this->assertArrayHasKey('payments_received', $report);
        $this->assertEquals(30000.00, $report['summary']['outstanding_balance']);
        $this->assertEquals(130500.00, $report['lifetime_totals']['total_earned']);
    }
}
