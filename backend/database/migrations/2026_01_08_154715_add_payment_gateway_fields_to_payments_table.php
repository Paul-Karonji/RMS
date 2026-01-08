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
        Schema::table('payments', function (Blueprint $table) {
            $table->string('stripe_payment_intent_id')->nullable()->after('payment_method');
            $table->string('mpesa_checkout_request_id')->nullable()->after('stripe_payment_intent_id');
            $table->string('mpesa_merchant_request_id')->nullable()->after('mpesa_checkout_request_id');
            $table->json('gateway_response')->nullable()->after('mpesa_merchant_request_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            //
        });
    }
};
