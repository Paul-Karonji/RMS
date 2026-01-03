<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('owner_balances', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('property_owner_id')->unique();
            $table->decimal('amount_owed', 12, 2)->default(0.00);
            $table->decimal('amount_paid', 12, 2)->default(0.00);
            $table->decimal('total_rent_collected', 12, 2)->default(0.00);
            $table->decimal('total_platform_fees', 12, 2)->default(0.00);
            $table->decimal('total_expenses', 12, 2)->default(0.00);
            $table->decimal('total_earned', 12, 2)->default(0.00);
            $table->decimal('total_paid', 12, 2)->default(0.00);
            $table->date('last_payment_date')->nullable();
            $table->decimal('last_payment_amount', 12, 2)->nullable();
            $table->date('next_expected_payment_date')->nullable();
            $table->timestamps();
            
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('property_owner_id')->references('id')->on('property_owners')->onDelete('cascade');
            $table->index(['tenant_id', 'property_owner_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('owner_balances');
    }
};
