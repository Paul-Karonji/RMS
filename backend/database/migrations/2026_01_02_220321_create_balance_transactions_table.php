<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('balance_transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('payment_id')->nullable();
            $table->uuid('property_owner_id')->nullable();
            $table->string('transaction_type');
            $table->decimal('amount', 12, 2);
            $table->decimal('fee_amount', 12, 2)->default(0.00);
            $table->decimal('net_amount', 12, 2);
            $table->date('transaction_date');
            $table->text('description')->nullable();
            $table->string('reference_id')->nullable();
            $table->timestamps();
            
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('property_owner_id')->references('id')->on('property_owners')->onDelete('cascade');
            $table->index(['tenant_id', 'transaction_type', 'created_at']);
            $table->index(['property_owner_id', 'transaction_type', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('balance_transactions');
    }
};
