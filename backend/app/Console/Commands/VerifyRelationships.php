<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
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

class VerifyRelationships extends Command
{
    protected $signature = 'db:verify-relationships';
    protected $description = 'Verify all model relationships are working correctly';

    public function handle()
    {
        $this->info('🔍 Verifying Model Relationships...');
        $this->newLine();

        $passed = 0;
        $failed = 0;

        // Test 1: Tenant → Users
        $this->test('Tenant → Users', function() {
            $tenant = Tenant::first();
            return $tenant && $tenant->users()->exists();
        }, $passed, $failed);

        // Test 2: Tenant → Properties
        $this->test('Tenant → Properties', function() {
            $tenant = Tenant::first();
            return $tenant && $tenant->properties()->exists();
        }, $passed, $failed);

        // Test 3: Tenant → CompanyBalance (1:1)
        $this->test('Tenant → CompanyBalance (1:1)', function() {
            $tenant = Tenant::first();
            return $tenant && $tenant->companyBalance !== null;
        }, $passed, $failed);

        // Test 4: Property → Tenant
        $this->test('Property → Tenant', function() {
            $property = Property::first();
            return $property && $property->tenant !== null;
        }, $passed, $failed);

        // Test 5: Property → Owner
        $this->test('Property → Owner', function() {
            $property = Property::first();
            return $property && $property->owner !== null;
        }, $passed, $failed);

        // Test 6: Property → Units
        $this->test('Property → Units', function() {
            $property = Property::first();
            return $property && $property->units()->exists();
        }, $passed, $failed);

        // Test 7: Property → Manager (nullable)
        $this->test('Property → Manager (nullable)', function() {
            $property = Property::whereNotNull('manager_id')->first();
            return !$property || $property->manager !== null;
        }, $passed, $failed);

        // Test 8: Unit → Property
        $this->test('Unit → Property', function() {
            $unit = Unit::first();
            return $unit && $unit->property !== null;
        }, $passed, $failed);

        // Test 9: Unit → Leases
        $this->test('Unit → Leases', function() {
            $unit = Unit::whereHas('leases')->first();
            return !$unit || $unit->leases()->exists();
        }, $passed, $failed);

        // Test 10: Lease → Property
        $this->test('Lease → Property', function() {
            $lease = Lease::first();
            return $lease && $lease->property !== null;
        }, $passed, $failed);

        // Test 11: Lease → Unit
        $this->test('Lease → Unit', function() {
            $lease = Lease::first();
            return $lease && $lease->unit !== null;
        }, $passed, $failed);

        // Test 12: Lease → Tenant (User)
        $this->test('Lease → Tenant (User)', function() {
            $lease = Lease::first();
            return $lease && $lease->tenant !== null;
        }, $passed, $failed);

        // Test 13: Lease → Payments
        $this->test('Lease → Payments', function() {
            $lease = Lease::whereHas('payments')->first();
            return !$lease || $lease->payments()->exists();
        }, $passed, $failed);

        // Test 14: Payment → Lease
        $this->test('Payment → Lease', function() {
            $payment = Payment::first();
            return $payment && $payment->lease !== null;
        }, $passed, $failed);

        // Test 15: Payment → Tenant (User)
        $this->test('Payment → Tenant (User)', function() {
            $payment = Payment::first();
            return $payment && $payment->tenant !== null;
        }, $passed, $failed);

        // Test 16: Expense → Property
        $this->test('Expense → Property', function() {
            $expense = Expense::first();
            return $expense && $expense->property !== null;
        }, $passed, $failed);

        // Test 17: Expense → Tenant
        $this->test('Expense → Tenant', function() {
            $expense = Expense::first();
            return $expense && $expense->tenant !== null;
        }, $passed, $failed);

        // Test 18: MaintenanceRequest → Property
        $this->test('MaintenanceRequest → Property', function() {
            $request = MaintenanceRequest::first();
            return $request && $request->property !== null;
        }, $passed, $failed);

        // Test 19: MaintenanceRequest → Unit
        $this->test('MaintenanceRequest → Unit', function() {
            $request = MaintenanceRequest::first();
            return $request && $request->unit !== null;
        }, $passed, $failed);

        // Test 20: MaintenanceRequest → Reporter (User)
        $this->test('MaintenanceRequest → Reporter', function() {
            $request = MaintenanceRequest::first();
            return $request && $request->reporter !== null;
        }, $passed, $failed);

        // Test 21: PropertyOwner → Balance (1:1)
        $this->test('PropertyOwner → Balance (1:1)', function() {
            $owner = PropertyOwner::first();
            return $owner && $owner->balance !== null;
        }, $passed, $failed);

        // Test 22: PropertyOwner → Properties
        $this->test('PropertyOwner → Properties', function() {
            $owner = PropertyOwner::first();
            return $owner && $owner->properties()->exists();
        }, $passed, $failed);

        // Test 23: User → Tenant
        $this->test('User → Tenant', function() {
            $user = User::first();
            return $user && $user->tenant !== null;
        }, $passed, $failed);

        // Test 24: Eager Loading
        $this->test('Eager Loading (Property with relations)', function() {
            $property = Property::with(['tenant', 'owner', 'units'])->first();
            return $property && 
                   $property->relationLoaded('tenant') && 
                   $property->relationLoaded('owner') && 
                   $property->relationLoaded('units');
        }, $passed, $failed);

        // Test 25: Relationship Counts
        $this->test('Relationship Counts (Tenant)', function() {
            $tenant = Tenant::withCount(['users', 'properties'])->first();
            return $tenant && 
                   isset($tenant->users_count) && 
                   isset($tenant->properties_count);
        }, $passed, $failed);

        $this->newLine();
        $this->info("✅ Passed: {$passed}");
        $this->error("❌ Failed: {$failed}");
        $this->newLine();

        if ($failed === 0) {
            $this->info('🎉 All relationships verified successfully!');
            return 0;
        } else {
            $this->error('⚠️  Some relationships failed verification.');
            return 1;
        }
    }

    private function test($name, $callback, &$passed, &$failed)
    {
        try {
            $result = $callback();
            if ($result) {
                $this->line("  ✅ {$name}");
                $passed++;
            } else {
                $this->line("  ❌ {$name} - returned false");
                $failed++;
            }
        } catch (\Exception $e) {
            $this->line("  ❌ {$name} - {$e->getMessage()}");
            $failed++;
        }
    }
}
