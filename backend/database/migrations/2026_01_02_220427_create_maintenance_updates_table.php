<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('maintenance_updates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('maintenance_request_id');
            $table->uuid('updated_by');
            $table->string('update_type');
            $table->text('description')->nullable();
            $table->string('status_before')->nullable();
            $table->string('status_after')->nullable();
            $table->uuid('assigned_to')->nullable();
            $table->timestamps();
            
            $table->foreign('maintenance_request_id')->references('id')->on('maintenance_requests')->onDelete('cascade');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('assigned_to')->references('id')->on('users')->onDelete('set null');
            $table->index(['maintenance_request_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenance_updates');
    }
};
