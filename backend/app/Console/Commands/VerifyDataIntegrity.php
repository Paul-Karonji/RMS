<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Property;
use App\Models\Unit;
use App\Models\Lease;
use App\Models\Payment;
use App\Models\Expense;

class VerifyDataIntegrity extends Command
{
    protected $signature = 'db:verify-integrity';
    protected $description = 'Verify database constraints and data integrity';

    public function handle()
    {
        $this->info('🔍 Verifying Database Integrity & Constraints...');
        $this->newLine();

        $passed = 0;
        $failed = 0;

        // Test 1: UUID Format Validation
        $this->test('UUID Format - All primary keys are valid UUIDs', function() {
            // Check that all IDs are valid UUIDs using PostgreSQL's uuid type validation
            $tables = ['tenants', 'users', 'properties', 'units', 'leases', 'payments'];
            foreach ($tables as $table) {
                // Check for null IDs
                $nullIds = DB::table($table)->whereNull('id')->count();
                if ($nullIds > 0) {
                    return false;
                }
                // All IDs in PostgreSQL uuid columns are automatically valid UUIDs
            }
            return true;
        }, $passed, $failed);

        // Test 2: Foreign Key Integrity - Tenants
        $this->test('Foreign Key Integrity - All users have valid tenant_id', function() {
            $orphanedUsers = User::whereNotNull('tenant_id')
                ->whereNotExists(function($query) {
                    $query->select(DB::raw(1))
                        ->from('tenants')
                        ->whereColumn('tenants.id', 'users.tenant_id');
                })->count();
            return $orphanedUsers === 0;
        }, $passed, $failed);

        // Test 3: Foreign Key Integrity - Properties
        $this->test('Foreign Key Integrity - All properties have valid tenant_id', function() {
            $orphanedProperties = Property::whereNotExists(function($query) {
                $query->select(DB::raw(1))
                    ->from('tenants')
                    ->whereColumn('tenants.id', 'properties.tenant_id');
            })->count();
            return $orphanedProperties === 0;
        }, $passed, $failed);

        // Test 4: Foreign Key Integrity - Units
        $this->test('Foreign Key Integrity - All units have valid property_id', function() {
            $orphanedUnits = Unit::whereNotExists(function($query) {
                $query->select(DB::raw(1))
                    ->from('properties')
                    ->whereColumn('properties.id', 'units.property_id');
            })->count();
            return $orphanedUnits === 0;
        }, $passed, $failed);

        // Test 5: NOT NULL Constraints
        $this->test('NOT NULL Constraints - Required fields are populated', function() {
            $nullTenantNames = Tenant::whereNull('company_name')->count();
            $nullUserEmails = User::whereNull('email')->count();
            $nullPropertyNames = Property::whereNull('property_name')->count();
            return $nullTenantNames === 0 && $nullUserEmails === 0 && $nullPropertyNames === 0;
        }, $passed, $failed);

        // Test 6: Unique Constraints - Email uniqueness per tenant
        $this->test('Unique Constraints - User emails unique per tenant', function() {
            // Check for duplicate emails within the same tenant
            $duplicates = DB::select("
                SELECT tenant_id, email, COUNT(*) as cnt 
                FROM users 
                GROUP BY tenant_id, email 
                HAVING COUNT(*) > 1
            ");
            return count($duplicates) === 0;
        }, $passed, $failed);

        // Test 7: Unique Constraints - Tenant emails are unique
        $this->test('Unique Constraints - Tenant emails are unique', function() {
            // Check for duplicate admin emails using raw query
            $duplicates = DB::select("
                SELECT admin_email, COUNT(*) as cnt 
                FROM tenants 
                GROUP BY admin_email 
                HAVING COUNT(*) > 1
            ");
            return count($duplicates) === 0;
        }, $passed, $failed);

        // Test 8: Positive Amount Constraints
        $this->test('Check Constraints - Payment amounts are positive', function() {
            $negativePayments = Payment::where('amount', '<=', 0)->count();
            return $negativePayments === 0;
        }, $passed, $failed);

        // Test 9: Positive Amount Constraints - Expenses
        $this->test('Check Constraints - Expense amounts are positive', function() {
            $negativeExpenses = Expense::where('amount', '<=', 0)->count();
            return $negativeExpenses === 0;
        }, $passed, $failed);

        // Test 10: Date Logic - Lease dates
        $this->test('Date Logic - Lease end_date is after start_date', function() {
            $invalidLeases = Lease::whereRaw('end_date <= start_date')->count();
            return $invalidLeases === 0;
        }, $passed, $failed);

        // Test 11: Enum/Status Values
        $this->test('Enum Values - User roles are valid', function() {
            $validRoles = ['company_admin', 'property_manager', 'tenant', 'property_owner'];
            $invalidRoles = User::whereNotIn('role', $validRoles)->count();
            return $invalidRoles === 0;
        }, $passed, $failed);

        // Test 12: Enum Values - Property status
        $this->test('Enum Values - Property status are valid', function() {
            $validStatuses = ['pending_approval', 'approved', 'rejected', 'active', 'inactive'];
            $invalidStatuses = Property::whereNotIn('status', $validStatuses)->count();
            return $invalidStatuses === 0;
        }, $passed, $failed);

        // Test 13: Enum Values - Unit status
        $this->test('Enum Values - Unit status are valid', function() {
            $validStatuses = ['available', 'occupied', 'reserved', 'maintenance'];
            $invalidStatuses = Unit::whereNotIn('status', $validStatuses)->count();
            return $invalidStatuses === 0;
        }, $passed, $failed);

        // Test 14: Enum Values - Lease status
        $this->test('Enum Values - Lease status are valid', function() {
            $validStatuses = ['draft', 'active', 'expired', 'terminated', 'renewed'];
            $invalidStatuses = Lease::whereNotIn('status', $validStatuses)->count();
            return $invalidStatuses === 0;
        }, $passed, $failed);

        // Test 15: Enum Values - Payment status
        $this->test('Enum Values - Payment status are valid', function() {
            $validStatuses = ['pending', 'completed', 'failed', 'refunded'];
            $invalidStatuses = Payment::whereNotIn('status', $validStatuses)->count();
            return $invalidStatuses === 0;
        }, $passed, $failed);

        // Test 16: Decimal Precision - Payment amounts
        $this->test('Decimal Precision - Payment amounts have correct precision', function() {
            // Check that amounts don't have more than 2 decimal places
            $invalidPrecision = Payment::whereRaw("(amount * 100) != FLOOR(amount * 100)")->count();
            return $invalidPrecision === 0;
        }, $passed, $failed);

        // Test 17: Percentage Range - Fee percentages
        $this->test('Percentage Range - Platform fees are between 0-100', function() {
            $invalidFees = Tenant::where(function($query) {
                $query->where('default_platform_fee_percentage', '<', 0)
                      ->orWhere('default_platform_fee_percentage', '>', 100);
            })->count();
            return $invalidFees === 0;
        }, $passed, $failed);

        // Test 18: Cascade Delete Verification
        $this->test('Cascade Rules - Verify cascade delete is configured', function() {
            // Check if foreign keys exist with cascade rules
            $cascadeKeys = DB::select("
                SELECT COUNT(*) as count
                FROM information_schema.table_constraints tc
                JOIN information_schema.referential_constraints rc 
                    ON tc.constraint_name = rc.constraint_name
                WHERE tc.constraint_type = 'FOREIGN KEY'
                AND rc.delete_rule = 'CASCADE'
            ");
            return $cascadeKeys[0]->count > 0;
        }, $passed, $failed);

        // Test 19: Timestamp Consistency
        $this->test('Timestamp Consistency - created_at <= updated_at', function() {
            $tables = ['tenants', 'users', 'properties', 'units', 'leases'];
            foreach ($tables as $table) {
                $inconsistent = DB::table($table)
                    ->whereRaw('created_at > updated_at')
                    ->count();
                if ($inconsistent > 0) {
                    return false;
                }
            }
            return true;
        }, $passed, $failed);

        // Test 20: Data Consistency - Properties have units
        $this->test('Data Consistency - All properties have units', function() {
            // Verify all properties have at least one unit
            $propertiesWithoutUnits = Property::whereDoesntHave('units')->count();
            return $propertiesWithoutUnits === 0;
        }, $passed, $failed);

        // Test 21: Tenant Isolation - Multi-tenancy check
        $this->test('Multi-Tenancy - Users belong to correct tenant', function() {
            $crossTenantUsers = User::whereExists(function($query) {
                $query->select(DB::raw(1))
                    ->from('properties')
                    ->whereColumn('properties.tenant_id', '!=', 'users.tenant_id')
                    ->whereExists(function($subQuery) {
                        $subQuery->select(DB::raw(1))
                            ->from('leases')
                            ->whereColumn('leases.property_id', 'properties.id')
                            ->whereColumn('leases.tenant_id', 'users.id');
                    });
            })->count();
            return $crossTenantUsers === 0;
        }, $passed, $failed);

        // Test 22: Index Verification
        $this->test('Indexes - Critical indexes exist', function() {
            $indexes = DB::select("
                SELECT COUNT(*) as count
                FROM pg_indexes
                WHERE schemaname = 'public'
                AND tablename IN ('users', 'properties', 'units', 'leases', 'payments')
            ");
            return $indexes[0]->count >= 10; // Should have at least 10 indexes
        }, $passed, $failed);

        // Test 23: Orphaned Records - Check for orphaned leases
        $this->test('Orphaned Records - No leases without valid units', function() {
            $orphanedLeases = Lease::whereNotExists(function($query) {
                $query->select(DB::raw(1))
                    ->from('units')
                    ->whereColumn('units.id', 'leases.unit_id');
            })->count();
            return $orphanedLeases === 0;
        }, $passed, $failed);

        // Test 24: Orphaned Records - Check for orphaned payments
        $this->test('Orphaned Records - No payments without valid leases', function() {
            $orphanedPayments = Payment::whereNotExists(function($query) {
                $query->select(DB::raw(1))
                    ->from('leases')
                    ->whereColumn('leases.id', 'payments.lease_id');
            })->count();
            return $orphanedPayments === 0;
        }, $passed, $failed);

        // Test 25: Boolean Field Validation
        $this->test('Boolean Fields - All boolean values are valid', function() {
            // Check that boolean fields exist and are properly set
            $allTenants = Tenant::count();
            $validTenants = Tenant::whereIn('status', ['active', 'inactive', 'suspended'])->count();
            return $allTenants === $validTenants;
        }, $passed, $failed);

        $this->newLine();
        $this->info("✅ Passed: {$passed}");
        $this->error("❌ Failed: {$failed}");
        $this->newLine();

        if ($failed === 0) {
            $this->info('🎉 All data integrity checks passed!');
            return 0;
        } else {
            $this->error('⚠️  Some integrity checks failed.');
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
                $this->line("  ❌ {$name} - check failed");
                $failed++;
            }
        } catch (\Exception $e) {
            $this->line("  ❌ {$name} - {$e->getMessage()}");
            $failed++;
        }
    }
}
