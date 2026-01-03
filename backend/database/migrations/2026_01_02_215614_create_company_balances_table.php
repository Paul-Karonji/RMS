<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_balances', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->unique();
            $table->decimal('available_balance', 12, 2)->default(0.00);
            $table->decimal('pending_balance', 12, 2)->default(0.00);
            $table->decimal('platform_fees_collected', 12, 2)->default(0.00);
            $table->decimal('deposits_held', 12, 2)->default(0.00);
            $table->decimal('reservations_collected', 12, 2)->default(0.00);
            $table->decimal('total_collected', 12, 2)->default(0.00);
            $table->decimal('total_withdrawn', 12, 2)->default(0.00);
            $table->decimal('total_earned', 12, 2)->default(0.00);
            $table->decimal('total_cashed_out', 12, 2)->default(0.00);
            $table->decimal('total_platform_fees_paid', 12, 2)->default(0.00);
            $table->timestamp('last_cashout_at')->nullable();
            $table->decimal('last_cashout_amount', 12, 2)->nullable();
            $table->timestamps();
            
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_balances');
    }
};
