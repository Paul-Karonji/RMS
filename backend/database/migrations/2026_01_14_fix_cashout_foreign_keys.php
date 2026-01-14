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
        Schema::table('cashout_requests', function (Blueprint $table) {
            // Drop existing foreign keys
            $table->dropForeign(['approved_by']);
            $table->dropForeign(['rejected_by']);
            
            // Add new foreign keys pointing to platform_users
            $table->foreign('approved_by')->references('id')->on('platform_users')->onDelete('set null');
            $table->foreign('rejected_by')->references('id')->on('platform_users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cashout_requests', function (Blueprint $table) {
            // Drop platform_users foreign keys
            $table->dropForeign(['approved_by']);
            $table->dropForeign(['rejected_by']);
            
            // Restore original foreign keys to users
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('rejected_by')->references('id')->on('users')->onDelete('set null');
        });
    }
};
