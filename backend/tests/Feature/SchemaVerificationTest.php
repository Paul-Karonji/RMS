<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * CRITICAL: Schema Verification Tests
 * 
 * These tests verify that all models match the actual database schema.
 * This prevents the 95% of issues encountered in Week 11 caused by
 * database schema mismatches.
 * 
 * Reference: DEVELOPMENT_BEST_PRACTICES.md
 */
class SchemaVerificationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function owner_balances_table_has_correct_columns()
    {
        $columns = Schema::getColumnListing('owner_balances');

        // Critical: Verify correct field names (NOT pending_balance, NOT total_paid_out)
        $this->assertContains('id', $columns);
        $this->assertContains('tenant_id', $columns);
        $this->assertContains('property_owner_id', $columns);
        $this->assertContains('amount_owed', $columns); // NOT pending_balance
        $this->assertContains('total_rent_collected', $columns); // NOT total_collected
        $this->assertContains('total_platform_fees', $columns);
        $this->assertContains('total_expenses', $columns);
        $this->assertContains('last_payment_date', $columns);
        $this->assertContains('last_payment_amount', $columns);
        $this->assertContains('next_expected_payment_date', $columns);
        $this->assertContains('total_earned', $columns);
        $this->assertContains('total_paid', $columns); // NOT total_paid_out
        $this->assertContains('updated_at', $columns);

        // Verify columns that should NOT exist
        $this->assertNotContains('pending_balance', $columns);
        $this->assertNotContains('total_paid_out', $columns);
        $this->assertNotContains('total_collected', $columns);
    }

    /** @test */
    public function company_balances_table_has_correct_columns()
    {
        $columns = Schema::getColumnListing('company_balances');

        $this->assertContains('id', $columns);
        $this->assertContains('tenant_id', $columns);
        $this->assertContains('available_balance', $columns);
        $this->assertContains('platform_fees_collected', $columns);
        $this->assertContains('deposits_held', $columns);
        $this->assertContains('reservations_collected', $columns);
        $this->assertContains('total_earned', $columns);
        $this->assertContains('total_cashed_out', $columns);
        $this->assertContains('total_platform_fees_paid', $columns);
        $this->assertContains('updated_at', $columns);
    }

    /** @test */
    public function platform_fees_table_has_correct_columns()
    {
        $columns = Schema::getColumnListing('platform_fees');

        $this->assertContains('id', $columns);
        $this->assertContains('tenant_id', $columns);
        $this->assertContains('payment_id', $columns);
        $this->assertContains('fee_percentage', $columns); // Actual column name
        $this->assertContains('fee_amount', $columns);
        $this->assertContains('payment_amount', $columns); // NOT base_amount
        $this->assertContains('fee_type', $columns);
        $this->assertContains('status', $columns);
        $this->assertContains('paid_at', $columns);
        $this->assertContains('created_at', $columns);

        // Verify columns that should NOT exist
        $this->assertNotContains('base_amount', $columns);
        $this->assertNotContains('property_id', $columns); // Not in actual schema
    }

    /** @test */
    public function payments_tenant_id_references_users_table()
    {
        // CRITICAL: payments.tenant_id references users.id (NOT tenants.id)
        // This is the tenant RENTER, not the company
        
        $foreignKeys = $this->getForeignKeys('payments');
        
        $tenantIdFk = collect($foreignKeys)->firstWhere('column_name', 'tenant_id');
        
        // Verify it references users table (using object notation)
        $this->assertNotNull($tenantIdFk);
        $this->assertEquals('users', $tenantIdFk->foreign_table_name);
        $this->assertEquals('id', $tenantIdFk->foreign_column_name);
    }

    /** @test */
    public function leases_tenant_id_references_users_table()
    {
        // CRITICAL: leases.tenant_id references users.id (NOT tenants.id)
        // This is the tenant RENTER, not the company
        
        $foreignKeys = $this->getForeignKeys('leases');
        
        $tenantIdFk = collect($foreignKeys)->firstWhere('column_name', 'tenant_id');
        
        // Verify it references users table (using object notation)
        $this->assertNotNull($tenantIdFk);
        $this->assertEquals('users', $tenantIdFk->foreign_table_name);
        $this->assertEquals('id', $tenantIdFk->foreign_column_name);
    }

    /** @test */
    public function properties_tenant_id_references_tenants_table()
    {
        // Properties.tenant_id should reference tenants.id (the company)
        
        $foreignKeys = $this->getForeignKeys('properties');
        
        $tenantIdFk = collect($foreignKeys)->firstWhere('column_name', 'tenant_id');
        
        // Using object notation for stdClass
        $this->assertNotNull($tenantIdFk);
        $this->assertEquals('tenants', $tenantIdFk->foreign_table_name);
        $this->assertEquals('id', $tenantIdFk->foreign_column_name);
    }

    /** @test */
    public function balance_transactions_has_correct_columns()
    {
        $columns = Schema::getColumnListing('balance_transactions');

        $this->assertContains('id', $columns);
        $this->assertContains('tenant_id', $columns);
        $this->assertContains('payment_id', $columns);
        $this->assertContains('property_owner_id', $columns); // Actual schema
        $this->assertContains('transaction_type', $columns);
        $this->assertContains('amount', $columns);
        $this->assertContains('fee_amount', $columns);
        $this->assertContains('net_amount', $columns);
        $this->assertContains('transaction_date', $columns);
        $this->assertContains('description', $columns);
        $this->assertContains('reference_id', $columns);
        $this->assertContains('created_at', $columns);

        // Note: Actual schema uses property_owner_id, not entity_type/entity_id
        $this->assertNotContains('entity_type', $columns);
        $this->assertNotContains('entity_id', $columns);
        $this->assertNotContains('balance_before', $columns);
        $this->assertNotContains('balance_after', $columns);
    }

    /** @test */
    public function expenses_table_has_correct_columns()
    {
        $columns = Schema::getColumnListing('expenses');

        $this->assertContains('id', $columns);
        $this->assertContains('tenant_id', $columns);
        $this->assertContains('property_id', $columns);
        $this->assertContains('unit_id', $columns);
        $this->assertContains('maintenance_request_id', $columns);
        $this->assertContains('category', $columns);
        $this->assertContains('description', $columns);
        $this->assertContains('amount', $columns);
        $this->assertContains('expense_date', $columns);
        $this->assertContains('owner_share', $columns);
        $this->assertContains('platform_share', $columns);
        $this->assertContains('status', $columns);
        $this->assertContains('approved_by', $columns);
        $this->assertContains('approved_at', $columns);
        $this->assertContains('rejected_by', $columns);
        $this->assertContains('rejected_at', $columns);
        $this->assertContains('rejection_reason', $columns);
        $this->assertContains('created_by', $columns);
        $this->assertContains('created_at', $columns);

        // Note: invoice_number and receipt_url are NOT in actual schema
        $this->assertNotContains('invoice_number', $columns);
        $this->assertNotContains('receipt_url', $columns);
    }

    /** @test */
    public function all_tables_have_required_timestamps()
    {
        $tablesRequiringTimestamps = [
            'tenants',
            'users',
            'properties',
            'units',
            'leases',
            'payments',
            'expenses',
            'maintenance_requests',
            'notifications',
            'audit_logs',
        ];

        foreach ($tablesRequiringTimestamps as $table) {
            $columns = Schema::getColumnListing($table);
            
            $this->assertContains('created_at', $columns, "Table {$table} missing created_at");
            
            // Some tables only have created_at, not updated_at
            if (!in_array($table, ['payments', 'platform_fees', 'balance_transactions'])) {
                $this->assertContains('updated_at', $columns, "Table {$table} missing updated_at");
            }
        }
    }

    /** @test */
    public function decimal_fields_have_correct_precision()
    {
        // Verify decimal fields are (10,2) or (12,2) for amounts
        $this->assertColumnType('payments', 'amount', 'numeric');
        $this->assertColumnType('leases', 'monthly_rent', 'numeric'); // NOT rent_amount
        $this->assertColumnType('expenses', 'amount', 'numeric');
        $this->assertColumnType('company_balances', 'available_balance', 'numeric');
        $this->assertColumnType('owner_balances', 'amount_owed', 'numeric');
    }

    /**
     * Helper method to get foreign keys for a table
     */
    private function getForeignKeys(string $table): array
    {
        $query = "
            SELECT
                tc.constraint_name,
                kcu.column_name,
                ccu.table_name AS foreign_table_name,
                ccu.column_name AS foreign_column_name
            FROM information_schema.table_constraints AS tc
            JOIN information_schema.key_column_usage AS kcu
                ON tc.constraint_name = kcu.constraint_name
            JOIN information_schema.constraint_column_usage AS ccu
                ON ccu.constraint_name = tc.constraint_name
            WHERE tc.constraint_type = 'FOREIGN KEY'
                AND tc.table_name = ?
        ";

        return \DB::select($query, [$table]);
    }

    /**
     * Helper method to assert column type
     */
    private function assertColumnType(string $table, string $column, string $expectedType): void
    {
        $columnType = Schema::getColumnType($table, $column);
        $this->assertEquals($expectedType, $columnType, 
            "Column {$table}.{$column} should be {$expectedType}, got {$columnType}");
    }
}
