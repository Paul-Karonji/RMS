<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('company_name');
            $table->string('pricing_model');
            $table->decimal('cashout_fee_percentage', 5, 2)->nullable();
            $table->decimal('min_cashout_amount', 10, 2)->default(1000.00);
            $table->string('subscription_plan')->nullable();
            $table->decimal('subscription_amount', 10, 2)->nullable();
            $table->string('subscription_status')->default('active');
            $table->timestamp('subscription_started_at')->nullable();
            $table->date('next_billing_date')->nullable();
            $table->decimal('min_platform_fee_percentage', 5, 2)->default(5.00);
            $table->decimal('max_platform_fee_percentage', 5, 2)->default(15.00);
            $table->decimal('default_platform_fee_percentage', 5, 2)->default(10.00);
            $table->uuid('admin_user_id')->nullable();
            $table->string('admin_email');
            $table->string('admin_phone')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('bank_account_number')->nullable();
            $table->string('bank_account_name')->nullable();
            $table->string('bank_swift_code')->nullable();
            $table->string('mpesa_phone')->nullable();
            $table->string('default_currency', 3)->default('KES');
            $table->string('timezone')->default('Africa/Nairobi');
            $table->integer('default_rent_collection_day')->default(5);
            $table->text('default_lease_terms')->nullable();
            $table->text('logo_url')->nullable();
            $table->string('status')->default('active');
            $table->uuid('created_by')->nullable();
            $table->timestamps();
            
            $table->foreign('created_by')->references('id')->on('platform_users')->onDelete('set null');
            $table->index(['status', 'pricing_model']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};
