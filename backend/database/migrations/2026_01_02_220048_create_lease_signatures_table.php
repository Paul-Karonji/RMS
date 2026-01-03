<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lease_signatures', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('lease_id');
            $table->uuid('user_id');
            $table->string('signer_role');
            $table->json('signature_data');
            $table->timestamp('signed_at');
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();
            
            $table->foreign('lease_id')->references('id')->on('leases')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unique(['lease_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lease_signatures');
    }
};
