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
        Schema::create('platform_revenue', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->string('revenue_source', 50); // 'cashout_fee', 'subscription'
            $table->uuid('cashout_request_id')->nullable();
            $table->uuid('subscription_invoice_id')->nullable();
            $table->date('period_start')->nullable();
            $table->date('period_end')->nullable();
            $table->decimal('company_gross_revenue', 12, 2)->default(0);
            $table->decimal('platform_revenue_percentage', 5, 2)->default(0);
            $table->decimal('platform_revenue_amount', 12, 2)->default(0);
            $table->string('status', 20)->default('completed');
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('cashout_request_id')->references('id')->on('cashout_requests')->onDelete('set null');
            $table->foreign('subscription_invoice_id')->references('id')->on('subscription_invoices')->onDelete('set null');
            
            $table->index('tenant_id');
            $table->index('revenue_source');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('platform_revenue');
    }
};
