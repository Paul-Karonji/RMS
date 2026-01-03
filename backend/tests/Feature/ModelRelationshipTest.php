<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use App\Models\Property;
use App\Models\PropertyOwner;
use App\Models\Unit;
use App\Models\Lease;
use App\Models\Payment;
use App\Models\Expense;
use App\Models\MaintenanceRequest;
use App\Models\CompanyBalance;
use App\Models\OwnerBalance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModelRelationshipTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed');
    }

    public function test_tenant_has_users_relationship()
    {
        $tenant = Tenant::first();
        
        $this->assertNotNull($tenant);
        $this->assertTrue($tenant->users()->exists());
        $this->assertInstanceOf(User::class, $tenant->users()->first());
    }

    public function test_tenant_has_properties_relationship()
    {
        $tenant = Tenant::first();
        
        $this->assertTrue($tenant->properties()->exists());
        $this->assertInstanceOf(Property::class, $tenant->properties()->first());
    }

    public function test_tenant_has_company_balance_relationship()
    {
        $tenant = Tenant::first();
        
        $this->assertNotNull($tenant->companyBalance);
        $this->assertInstanceOf(CompanyBalance::class, $tenant->companyBalance);
    }

    public function test_property_belongs_to_tenant()
    {
        $property = Property::first();
        
        $this->assertNotNull($property->tenant);
        $this->assertInstanceOf(Tenant::class, $property->tenant);
    }

    public function test_property_belongs_to_owner()
    {
        $property = Property::first();
        
        $this->assertNotNull($property->owner);
        $this->assertInstanceOf(PropertyOwner::class, $property->owner);
    }

    public function test_property_has_units_relationship()
    {
        $property = Property::first();
        
        $this->assertTrue($property->units()->exists());
        $this->assertInstanceOf(Unit::class, $property->units()->first());
    }

    public function test_property_has_manager_relationship()
    {
        $property = Property::whereNotNull('property_manager_id')->first();
        
        if ($property) {
            $this->assertNotNull($property->manager);
            $this->assertInstanceOf(User::class, $property->manager);
        } else {
            $this->markTestSkipped('No property with manager found');
        }
    }

    public function test_unit_belongs_to_property()
    {
        $unit = Unit::first();
        
        $this->assertNotNull($unit->property);
        $this->assertInstanceOf(Property::class, $unit->property);
    }

    public function test_unit_has_leases_relationship()
    {
        $unit = Unit::whereHas('leases')->first();
        
        if ($unit) {
            $this->assertTrue($unit->leases()->exists());
            $this->assertInstanceOf(Lease::class, $unit->leases()->first());
        } else {
            $this->markTestSkipped('No unit with leases found');
        }
    }

    public function test_lease_belongs_to_property()
    {
        $lease = Lease::first();
        
        $this->assertNotNull($lease->property);
        $this->assertInstanceOf(Property::class, $lease->property);
    }

    public function test_lease_belongs_to_unit()
    {
        $lease = Lease::first();
        
        $this->assertNotNull($lease->unit);
        $this->assertInstanceOf(Unit::class, $lease->unit);
    }

    public function test_lease_belongs_to_tenant()
    {
        $lease = Lease::first();
        
        $this->assertNotNull($lease->tenant);
        $this->assertInstanceOf(User::class, $lease->tenant);
    }

    public function test_lease_has_payments_relationship()
    {
        $lease = Lease::whereHas('payments')->first();
        
        if ($lease) {
            $this->assertTrue($lease->payments()->exists());
            $this->assertInstanceOf(Payment::class, $lease->payments()->first());
        } else {
            $this->markTestSkipped('No lease with payments found');
        }
    }

    public function test_payment_belongs_to_lease()
    {
        $payment = Payment::first();
        
        $this->assertNotNull($payment->lease);
        $this->assertInstanceOf(Lease::class, $payment->lease);
    }

    public function test_payment_belongs_to_tenant()
    {
        $payment = Payment::first();
        
        $this->assertNotNull($payment->tenant);
        $this->assertInstanceOf(User::class, $payment->tenant);
    }

    public function test_expense_belongs_to_property()
    {
        $expense = Expense::first();
        
        $this->assertNotNull($expense->property);
        $this->assertInstanceOf(Property::class, $expense->property);
    }

    public function test_expense_belongs_to_tenant()
    {
        $expense = Expense::first();
        
        $this->assertNotNull($expense->tenant);
        $this->assertInstanceOf(Tenant::class, $expense->tenant);
    }

    public function test_maintenance_request_belongs_to_property()
    {
        $request = MaintenanceRequest::first();
        
        $this->assertNotNull($request->property);
        $this->assertInstanceOf(Property::class, $request->property);
    }

    public function test_maintenance_request_belongs_to_unit()
    {
        $request = MaintenanceRequest::first();
        
        $this->assertNotNull($request->unit);
        $this->assertInstanceOf(Unit::class, $request->unit);
    }

    public function test_maintenance_request_has_reporter()
    {
        $request = MaintenanceRequest::first();
        
        $this->assertNotNull($request->reporter);
        $this->assertInstanceOf(User::class, $request->reporter);
    }

    public function test_property_owner_has_balance_relationship()
    {
        $owner = PropertyOwner::first();
        
        $this->assertNotNull($owner->balance);
        $this->assertInstanceOf(OwnerBalance::class, $owner->balance);
    }

    public function test_property_owner_has_properties_relationship()
    {
        $owner = PropertyOwner::first();
        
        $this->assertTrue($owner->properties()->exists());
        $this->assertInstanceOf(Property::class, $owner->properties()->first());
    }

    public function test_user_belongs_to_tenant()
    {
        $user = User::where('role', 'tenant')->first();
        
        $this->assertNotNull($user->tenant);
        $this->assertInstanceOf(Tenant::class, $user->tenant);
    }

    public function test_cascade_relationships()
    {
        $tenant = Tenant::factory()->create();
        $property = Property::factory()->create(['tenant_id' => $tenant->id]);
        $unit = Unit::factory()->create(['property_id' => $property->id]);
        
        $propertyId = $property->id;
        $unitId = $unit->id;
        
        $tenant->delete();
        
        $this->assertDatabaseMissing('properties', ['id' => $propertyId]);
        $this->assertDatabaseMissing('units', ['id' => $unitId]);
    }

    public function test_eager_loading_relationships()
    {
        $properties = Property::with(['tenant', 'owner', 'units', 'manager'])->get();
        
        $this->assertGreaterThan(0, $properties->count());
        
        foreach ($properties as $property) {
            $this->assertNotNull($property->tenant);
            $this->assertNotNull($property->owner);
        }
    }

    public function test_relationship_counts()
    {
        $tenant = Tenant::withCount(['users', 'properties'])->first();
        
        $this->assertGreaterThan(0, $tenant->users_count);
        $this->assertGreaterThan(0, $tenant->properties_count);
    }

    public function test_has_many_through_relationship()
    {
        $tenant = Tenant::first();
        $property = $tenant->properties()->first();
        
        if ($property) {
            $units = $property->units;
            $this->assertGreaterThan(0, $units->count());
        }
    }
}
