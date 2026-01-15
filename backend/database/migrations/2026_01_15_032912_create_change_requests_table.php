<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('change_requests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('property_owner_id');
            $table->uuid('property_id')->nullable();
            $table->uuid('unit_id')->nullable();
            $table->string('request_type'); // unit_price, unit_condition, fee_structure, manager_change, property_details
            $table->text('current_value');
            $table->text('requested_value');
            $table->text('reason');
            $table->boolean('affects_existing_leases')->default(false);
            $table->date('effective_from')->nullable();
            $table->string('status')->default('pending'); // pending, approved, rejected
            $table->uuid('reviewed_by')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_notes')->nullable();
            $table->timestamps();
            
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('property_owner_id')->references('id')->on('property_owners')->onDelete('cascade');
            $table->foreign('property_id')->references('id')->on('properties')->onDelete('cascade');
            $table->foreign('unit_id')->references('id')->on('units')->onDelete('cascade');
            $table->foreign('reviewed_by')->references('id')->on('users')->onDelete('set null');
            $table->index(['tenant_id', 'status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('change_requests');
    }
};
