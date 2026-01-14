<?php

namespace Tests\Feature\Dashboard;

use Tests\TestCase;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Property;
use App\Models\Unit;
use App\Models\PropertyOwner;
use App\Models\CompanyBalance;
use App\Models\OwnerBalance;
use App\Models\Lease;
use App\Models\Payment;
use App\Models\CashoutRequest;
use App\Models\PlatformFee;
use App\Services\DashboardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Carbon\Carbon;

class DashboardServiceTest extends TestCase
{
    use RefreshDatabase;

    protected DashboardService $dashboardService;
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
        
        $this->dashboardService = new DashboardService();
        
        // Create tenant (company) - Schema: tenants
        $this->tenant = Tenant::create([
            'id' => Str::uuid(),
            'company_name' => 'Test Company',
            'admin_email' => 'admin@test.com',
            'pricing_model' => 'percentage',
        ]);
        
        // Create admin user - Schema: users (tenant_id → tenants)
        $this->admin = User::create([
            'id' => Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'password_hash' => bcrypt('password'),
            'role' => 'company_admin',
        ]);
        
        // Create property owner - Schema: property_owners
        $this->owner = PropertyOwner::create([
            'id' => Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'owner_name' => 'Test Owner',
            'email' => 'owner@test.com',
            'phone' => '+254700000000',
        ]);
        
        // Create property - Schema: properties
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
        
        // Create units - Schema: units
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
        
        // Create tenant renter - Schema: users (for lease.tenant_id)
        $this->tenantRenter = User::create([
            'id' => Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'name' => 'Tenant Renter',
            'email' => 'renter@test.com',
            'password_hash' => bcrypt('password'),
            'role' => 'tenant',
        ]);
        
        // Create lease - Schema: leases (tenant_id → users.id)
        $this->lease = Lease::create([
            'id' => Str::uuid(),
            'tenant_id' => $this->tenantRenter->id, // References users table
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
        
        // Create company balance - Schema: company_balances
        CompanyBalance::create([
            'id' => Str::uuid(),
            'tenant_id' => $this->tenant->id,
            'available_balance' => 100000.00,
            'pending_balance' => 50000.00,
            'total_collected' => 150000.00,
            'total_earned' => 140000.00,
            'total_cashed_out' => 50000.00,
            'total_platform_fees_paid' => 10000.00,
        ]);
        
        // Create owner balance - Schema: owner_balances
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
            'last_payment_date' => now()->subDays(5),
            'last_payment_amount' => 20000.00,
        ]);
    }

    /** @test */
    public function can_get_company_metrics()
    {
        // Create payments - Schema: payments (amount, status, payment_date)
        Payment::create([
            'id' => Str::uuid(),
            'tenant_id' => $this->tenantRenter->id, // References users table
            'lease_id' => $this->lease->id,
            'payment_type' => 'rent',
            'amount' => 50000.00,
            'payment_method' => 'mpesa',
            'status' => 'completed',
            'payment_date' => now(),
        ]);
        
        $metrics = $this->dashboardService->getCompanyMetrics(
            $this->tenant->id,
            now()->startOfMonth(),
            now()->endOfMonth()
        );
        
        $this->assertArrayHasKey('financial_overview', $metrics);
        $this->assertArrayHasKey('this_month_revenue', $metrics['financial_overview']);
        $this->assertEquals(50000.00, $metrics['financial_overview']['this_month_revenue']);
        $this->assertEquals(100000.00, $metrics['financial_overview']['available_balance']);
    }

    /** @test */
    public function can_get_occupancy_metrics()
    {
        $metrics = $this->dashboardService->getOccupancyMetrics($this->tenant->id);
        
        $this->assertArrayHasKey('property_metrics', $metrics);
        $this->assertEquals(1, $metrics['property_metrics']['total_properties']);
        $this->assertEquals(2, $metrics['property_metrics']['total_units']);
        $this->assertEquals(1, $metrics['property_metrics']['occupied_units']);
        $this->assertEquals(1, $metrics['property_metrics']['vacant_units']);
        $this->assertEquals(50.00, $metrics['property_metrics']['occupancy_rate']);
    }

    /** @test */
    public function can_get_payment_metrics()
    {
        // Create completed payment
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
        
        // Create pending payment
        Payment::create([
            'id' => Str::uuid(),
            'tenant_id' => $this->tenantRenter->id,
            'lease_id' => $this->lease->id,
            'payment_type' => 'rent',
            'amount' => 50000.00,
            'payment_method' => 'mpesa',
            'status' => 'pending',
            'payment_date' => now()->addDays(5),
        ]);
        
        $metrics = $this->dashboardService->getPaymentMetrics(
            $this->tenant->id,
            now()->startOfMonth(),
            now()->endOfMonth()
        );
        
        $this->assertArrayHasKey('payment_metrics', $metrics);
        $this->assertEquals(1, $metrics['payment_metrics']['payments_received']);
        $this->assertEquals(1, $metrics['payment_metrics']['pending_payments']);
    }

    /** @test */
    public function can_get_owner_metrics()
    {
        $metrics = $this->dashboardService->getOwnerMetrics($this->tenant->id);
        
        $this->assertArrayHasKey('owner_metrics', $metrics);
        $this->assertEquals(1, $metrics['owner_metrics']['total_owners']);
        $this->assertEquals(30000.00, $metrics['owner_metrics']['amount_owed_to_owners']);
        $this->assertEquals(100500.00, $metrics['owner_metrics']['payments_made_to_owners']);
    }

    /** @test */
    public function can_get_owner_dashboard_metrics()
    {
        $metrics = $this->dashboardService->getOwnerDashboardMetrics($this->owner->id);
        
        $this->assertArrayHasKey('financial_summary', $metrics);
        $this->assertArrayHasKey('property_overview', $metrics);
        $this->assertEquals(130500.00, $metrics['financial_summary']['total_earned']);
        $this->assertEquals(30000.00, $metrics['financial_summary']['amount_owed']);
        $this->assertEquals(1, $metrics['property_overview']['total_properties']);
    }

    /** @test */
    public function can_get_tenant_lease_info()
    {
        $info = $this->dashboardService->getTenantLeaseInfo($this->tenantRenter->id);
        
        $this->assertArrayHasKey('lease_info', $info);
        $this->assertNotNull($info['lease_info']);
        $this->assertEquals($this->lease->id, $info['lease_info']['lease_id']);
        $this->assertEquals(50000.00, $info['lease_info']['monthly_rent']);
        $this->assertEquals('101', $info['lease_info']['unit']['unit_number']);
    }

    /** @test */
    public function can_get_tenant_payment_summary()
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
            'payment_date' => now()->subDays(10),
        ]);
        
        $summary = $this->dashboardService->getTenantPaymentSummary($this->tenantRenter->id);
        
        $this->assertArrayHasKey('payment_summary', $summary);
        $this->assertEquals(50000.00, $summary['payment_summary']['total_paid']);
    }
}
