<?php

namespace Database\Seeders;

use App\Models\Property;
use App\Models\PropertyAmenity;
use Illuminate\Database\Seeder;

class PropertyAmenitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $properties = Property::all();
        
        foreach ($properties as $property) {
            // Add common amenities based on property type
            if ($property->property_type === 'apartment') {
                $amenities = [
                    ['amenity_type' => 'parking', 'name' => 'Covered Parking', 'description' => 'Secure covered parking spaces', 'quantity' => $property->total_units, 'is_available' => true],
                    ['amenity_type' => 'security', 'name' => '24/7 Security', 'description' => 'Round-the-clock security surveillance', 'quantity' => 1, 'is_available' => true],
                    ['amenity_type' => 'facility', 'name' => 'Elevator', 'description' => 'High-speed elevator service', 'quantity' => 2, 'is_available' => true],
                    ['amenity_type' => 'facility', 'name' => 'Gym', 'description' => 'Fully equipped fitness center', 'quantity' => 1, 'is_available' => true],
                    ['amenity_type' => 'utility', 'name' => 'Water Tank', 'description' => 'Backup water supply', 'quantity' => 2, 'is_available' => true],
                ];
            } elseif ($property->property_type === 'townhouse') {
                $amenities = [
                    ['amenity_type' => 'parking', 'name' => 'Private Parking', 'description' => 'Dedicated parking for each unit', 'quantity' => $property->total_units * 2, 'is_available' => true],
                    ['amenity_type' => 'outdoor', 'name' => 'Private Garden', 'description' => 'Individual garden spaces', 'quantity' => $property->total_units, 'is_available' => true],
                    ['amenity_type' => 'security', 'name' => 'Gated Community', 'description' => 'Secure gated entrance', 'quantity' => 1, 'is_available' => true],
                    ['amenity_type' => 'facility', 'name' => 'Playground', 'description' => 'Children play area', 'quantity' => 1, 'is_available' => true],
                ];
            } elseif ($property->property_type === 'commercial') {
                $amenities = [
                    ['amenity_type' => 'parking', 'name' => 'Customer Parking', 'description' => 'Dedicated customer parking', 'quantity' => 50, 'is_available' => true],
                    ['amenity_type' => 'facility', 'name' => 'Conference Room', 'description' => 'Shared conference facilities', 'quantity' => 2, 'is_available' => true],
                    ['amenity_type' => 'utility', 'name' => 'Backup Generator', 'description' => 'Full power backup', 'quantity' => 1, 'is_available' => true],
                    ['amenity_type' => 'security', 'name' => 'CCTV Surveillance', 'description' => '24/7 camera monitoring', 'quantity' => 1, 'is_available' => true],
                ];
            } else { // mixed_use
                $amenities = [
                    ['amenity_type' => 'parking', 'name' => 'Mixed Parking', 'description' => 'Parking for residential and commercial', 'quantity' => 40, 'is_available' => true],
                    ['amenity_type' => 'facility', 'name' => 'Rooftop Terrace', 'description' => 'Shared rooftop space', 'quantity' => 1, 'is_available' => true],
                    ['amenity_type' => 'security', 'name' => 'Access Control', 'description' => 'Electronic access system', 'quantity' => 1, 'is_available' => true],
                    ['amenity_type' => 'retail', 'name' => 'Retail Spaces', 'description' => 'Ground floor retail units', 'quantity' => 10, 'is_available' => true],
                ];
            }
            
            foreach ($amenities as $amenity) {
                PropertyAmenity::create([
                    'property_id' => $property->id,
                    'amenity_type' => $amenity['amenity_type'],
                    'name' => $amenity['name'],
                    'description' => $amenity['description'],
                    'quantity' => $amenity['quantity'],
                    'is_available' => $amenity['is_available'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        $this->command->info('Property amenities seeded successfully!');
    }
}
