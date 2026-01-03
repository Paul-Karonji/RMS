<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leases', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('property_id');
            $table->uuid('unit_id');
            $table->uuid('property_owner_id');
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('monthly_rent', 10, 2);
            $table->decimal('deposit_amount', 10, 2);
            $table->decimal('first_month_rent', 10, 2)->nullable();
            $table->boolean('is_prorated')->default(false);
            $table->text('prorated_note')->nullable();
            $table->string('payment_type')->default('manual');
            $table->string('payment_frequency')->default('monthly');
            $table->integer('payment_day')->default(1);
            $table->string('late_fee_type')->nullable();
            $table->decimal('late_fee_amount', 10, 2)->nullable();
            $table->integer('grace_period_days')->default(0);
            $table->string('status')->default('active');
            $table->date('move_in_date')->nullable();
            $table->date('move_out_date')->nullable();
            $table->text('terms')->nullable();
            $table->uuid('created_by');
            $table->timestamps();
            
            $table->foreign('tenant_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('property_id')->references('id')->on('properties')->onDelete('cascade');
            $table->foreign('unit_id')->references('id')->on('units')->onDelete('cascade');
            $table->foreign('property_owner_id')->references('id')->on('property_owners')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            $table->index(['tenant_id', 'status']);
            $table->index(['unit_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leases');
    }
};
