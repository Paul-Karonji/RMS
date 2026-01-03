<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('properties', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('property_owner_id');
            $table->string('property_name');
            $table->text('description')->nullable();
            $table->string('address');
            $table->string('city');
            $table->string('county')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('country')->default('Kenya');
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('property_type');
            $table->integer('total_units');
            $table->integer('occupied_units')->default(0);
            $table->integer('vacant_units')->default(0);
            $table->decimal('monthly_rental_income', 15, 2)->default(0);
            $table->string('status')->default('pending_approval');
            $table->uuid('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->uuid('manager_id')->nullable();
            $table->decimal('commission_percentage', 5, 2)->nullable();
            $table->timestamps();
            
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('property_owner_id')->references('id')->on('property_owners')->onDelete('cascade');
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('manager_id')->references('id')->on('users')->onDelete('set null');
            $table->index(['tenant_id', 'status', 'property_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('properties');
    }
};
