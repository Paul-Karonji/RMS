<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Purpose: Add composite indexes to improve query performance for leases, payments, properties, and units
     * These indexes target the most common query patterns identified in controllers
     */
    public function up(): void
    {
        // Leases table - Add composite indexes for common queries
        Schema::table('leases', function (Blueprint $table) {
            // Existing indexes from original migration:
            // - index(['tenant_id', 'status'])
            // - index(['unit_id', 'status'])
            
            // Add new composite indexes for performance
            $table->index(['property_id', 'status', 'created_at'], 'idx_leases_property_status_date');
            $table->index(['created_by', 'status'], 'idx_leases_creator_status');
        });

        // Payments table - Add indexes for filtering and searching
        Schema::table('payments', function (Blueprint $table) {
            // Existing index from original migration:
            // - index(['tenant_id', 'status', 'payment_date'])
            
            // Add new indexes for common queries
            $table->index(['lease_id', 'status'], 'idx_payments_lease_status');
            $table->index(['payment_method', 'status', 'payment_date'], 'idx_payments_method_status_date');
            $table->index('transaction_id', 'idx_payments_transaction');
        });

        // Properties table - Add indexes for approval workflow
        Schema::table('properties', function (Blueprint $table) {
            // Note: Column is 'status' not 'approval_status', 'manager_id' not 'property_manager_id'
            $table->index(['tenant_id', 'status', 'created_at'], 'idx_properties_tenant_status_date');
            $table->index(['property_owner_id', 'status'], 'idx_properties_owner_status');
            $table->index('manager_id', 'idx_properties_manager');
        });

        // Units table - Add indexes for availability searches
        Schema::table('units', function (Blueprint $table) {
            // Note: Column is 'monthly_rent' not 'rent_amount'
            $table->index(['property_id', 'status', 'monthly_rent'], 'idx_units_property_status_rent');
            $table->index(['status', 'is_furnished'], 'idx_units_status_furnished');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leases', function (Blueprint $table) {
            $table->dropIndex('idx_leases_property_status_date');
            $table->dropIndex('idx_leases_creator_status');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex('idx_payments_lease_status');
            $table->dropIndex('idx_payments_method_status_date');
            $table->dropIndex('idx_payments_transaction');
        });

        Schema::table('properties', function (Blueprint $table) {
            $table->dropIndex('idx_properties_tenant_status_date');
            $table->dropIndex('idx_properties_owner_status');
            $table->dropIndex('idx_properties_manager');
        });

        Schema::table('units', function (Blueprint $table) {
            $table->dropIndex('idx_units_property_status_rent');
            $table->dropIndex('idx_units_status_furnished');
        });
    }
};
