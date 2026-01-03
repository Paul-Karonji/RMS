<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('property_amenities', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('property_id');
            $table->string('amenity_type');
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('quantity')->nullable();
            $table->boolean('is_available')->default(true);
            $table->timestamps();
            
            $table->foreign('property_id')->references('id')->on('properties')->onDelete('cascade');
            $table->index(['property_id', 'amenity_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('property_amenities');
    }
};
