<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('units', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('property_id');
            $table->string('unit_number');
            $table->string('unit_type');
            $table->integer('bedrooms');
            $table->integer('bathrooms');
            $table->decimal('size_sqft', 8, 2);
            $table->decimal('monthly_rent', 10, 2);
            $table->decimal('deposit_amount', 10, 2);
            $table->string('status')->default('available');
            $table->text('description')->nullable();
            $table->decimal('commission_percentage', 5, 2)->nullable();
            $table->boolean('is_furnished')->default(false);
            $table->boolean('allow_pets')->default(false);
            $table->boolean('parking_available')->default(false);
            $table->integer('parking_spaces')->default(0);
            $table->string('floor_level')->nullable();
            $table->timestamps();
            
            $table->foreign('property_id')->references('id')->on('properties')->onDelete('cascade');
            $table->unique(['property_id', 'unit_number']);
            $table->index(['property_id', 'status', 'unit_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('units');
    }
};
