<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Properties indexes
        Schema::table('properties', function (Blueprint $table) {
            $table->index(['tenant_id', 'status'], 'idx_properties_tenant_status');
            $table->index('property_owner_id', 'idx_properties_owner');
        });

        // Units indexes
        Schema::table('units', function (Blueprint $table) {
            $table->index(['property_id', 'status'], 'idx_units_property_status');
        });

        // Payments indexes
        Schema::table('payments', function (Blueprint $table) {
            $table->index(['lease_id', 'status'], 'idx_payments_lease_status');
            $table->index(['tenant_id', 'payment_date'], 'idx_payments_tenant_date');
            $table->index('created_at', 'idx_payments_created_at');
        });

        // Leases indexes
        Schema::table('leases', function (Blueprint $table) {
            $table->index(['unit_id', 'status'], 'idx_leases_unit_status');
            $table->index('tenant_id', 'idx_leases_tenant');
            $table->index(['start_date', 'end_date'], 'idx_leases_dates');
        });

        // Balance Transactions indexes
        Schema::table('balance_transactions', function (Blueprint $table) {
            $table->index(['tenant_id', 'transaction_type', 'created_at'], 'idx_balance_trans_tenant_type');
            $table->index(['property_owner_id', 'created_at'], 'idx_balance_trans_owner');
        });

        // Expenses indexes
        Schema::table('expenses', function (Blueprint $table) {
            $table->index(['tenant_id', 'status', 'expense_date'], 'idx_expenses_tenant_status');
            $table->index(['property_id', 'status'], 'idx_expenses_property');
        });

        // Maintenance Requests indexes
        Schema::table('maintenance_requests', function (Blueprint $table) {
            $table->index(['property_id', 'status'], 'idx_maintenance_property_status');
            $table->index(['tenant_user_id', 'status'], 'idx_maintenance_tenant');
        });

        // Notifications indexes
        Schema::table('notifications', function (Blueprint $table) {
            $table->index(['user_id', 'read_at'], 'idx_notifications_user_read');
            $table->index('created_at', 'idx_notifications_created');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropIndex('idx_properties_tenant_status');
            $table->dropIndex('idx_properties_owner');
        });

        Schema::table('units', function (Blueprint $table) {
            $table->dropIndex('idx_units_property_status');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex('idx_payments_lease_status');
            $table->dropIndex('idx_payments_tenant_date');
            $table->dropIndex('idx_payments_created_at');
        });

        Schema::table('leases', function (Blueprint $table) {
            $table->dropIndex('idx_leases_unit_status');
            $table->dropIndex('idx_leases_tenant');
            $table->dropIndex('idx_leases_dates');
        });

        Schema::table('balance_transactions', function (Blueprint $table) {
            $table->dropIndex('idx_balance_trans_tenant_type');
            $table->dropIndex('idx_balance_trans_owner');
        });

        Schema::table('expenses', function (Blueprint $table) {
            $table->dropIndex('idx_expenses_tenant_status');
            $table->dropIndex('idx_expenses_property');
        });

        Schema::table('maintenance_requests', function (Blueprint $table) {
            $table->dropIndex('idx_maintenance_property_status');
            $table->dropIndex('idx_maintenance_tenant');
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->dropIndex('idx_notifications_user_read');
            $table->dropIndex('idx_notifications_created');
        });
    }
};
