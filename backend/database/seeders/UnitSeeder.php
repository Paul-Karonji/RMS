<?php

namespace Database\Seeders;

use App\Models\Property;
use App\Models\Unit;
use Illuminate\Database\Seeder;

class UnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $properties = Property::all();
        
        foreach ($properties as $property) {
            // Generate units based on property type
            if ($property->property_type === 'apartment') {
                $this->createApartmentUnits($property);
            } elseif ($property->property_type === 'townhouse') {
                $this->createTownhouseUnits($property);
            } elseif ($property->property_type === 'commercial') {
                $this->createCommercialUnits($property);
            } elseif ($property->property_type === 'mixed_use') {
                $this->createMixedUseUnits($property);
            }
        }

        $this->command->info('Units seeded successfully!');
    }

    private function createApartmentUnits(Property $property)
    {
        $units = [];
        $floorCount = $property->total_units / 4; // 4 units per floor
        
        for ($floor = 1; $floor <= $floorCount; $floor++) {
            for ($unit = 1; $unit <= 4; $unit++) {
                $unitNumber = $floor . '0' . $unit;
                $bedrooms = $unit <= 2 ? 1 : 2;
                $status = ($floor * 4 + $unit) <= $property->occupied_units ? 'occupied' : 'available';
                
                $units[] = [
                    'property_id' => $property->id,
                    'unit_number' => $unitNumber,
                    'unit_type' => $bedrooms === 1 ? '1BR Apartment' : '2BR Apartment',
                    'bedrooms' => $bedrooms,
                    'bathrooms' => $bedrooms === 1 ? 1 : 2,
                    'size_sqft' => $bedrooms === 1 ? 450 : 650,
                    'monthly_rent' => $bedrooms === 1 ? 25000 : 35000,
                    'deposit_amount' => $bedrooms === 1 ? 50000 : 70000,
                    'status' => $status,
                    'description' => "Modern {$bedrooms}-bedroom apartment on floor {$floor}",
                    'commission_percentage' => 10.00,
                    'is_furnished' => $unit % 2 === 0,
                    'allow_pets' => true,
                    'parking_available' => true,
                    'parking_spaces' => 1,
                    'floor_level' => $floor,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        foreach ($units as $unit) {
            Unit::create($unit);
        }
    }

    private function createTownhouseUnits(Property $property)
    {
        for ($i = 1; $i <= $property->total_units; $i++) {
            $status = $i <= $property->occupied_units ? 'occupied' : 'available';
            
            Unit::create([
                'property_id' => $property->id,
                'unit_number' => 'TH' . str_pad($i, 2, '0', STR_PAD_LEFT),
                'unit_type' => '3BR Townhouse',
                'bedrooms' => 3,
                'bathrooms' => 3,
                'size_sqft' => 1200,
                'monthly_rent' => 80000,
                'deposit_amount' => 160000,
                'status' => $status,
                'description' => "Spacious 3-bedroom townhouse with private garden",
                'commission_percentage' => 8.00,
                'is_furnished' => false,
                'allow_pets' => true,
                'parking_available' => true,
                'parking_spaces' => 2,
                'floor_level' => 'Ground + 2',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function createCommercialUnits(Property $property)
    {
        for ($i = 1; $i <= $property->total_units; $i++) {
            $status = $i <= $property->occupied_units ? 'occupied' : 'available';
            $size = $i <= 5 ? 500 : ($i <= 10 ? 800 : 1200);
            $rent = $size === 500 ? 60000 : ($size === 800 ? 90000 : 120000);
            
            Unit::create([
                'property_id' => $property->id,
                'unit_number' => 'B' . str_pad($i, 2, '0', STR_PAD_LEFT),
                'unit_type' => 'Commercial Space',
                'bedrooms' => 0,
                'bathrooms' => 2,
                'size_sqft' => $size,
                'monthly_rent' => $rent,
                'deposit_amount' => $rent * 2,
                'status' => $status,
                'description' => "Commercial office space of {$size} sqft",
                'commission_percentage' => 12.00,
                'is_furnished' => false,
                'allow_pets' => false,
                'parking_available' => true,
                'parking_spaces' => 3,
                'floor_level' => 'Ground',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function createMixedUseUnits(Property $property)
    {
        $commercialUnits = 10;
        $residentialUnits = $property->total_units - $commercialUnits;
        
        // Create commercial units
        for ($i = 1; $i <= $commercialUnits; $i++) {
            $status = $i <= 8 ? 'occupied' : 'available';
            
            Unit::create([
                'property_id' => $property->id,
                'unit_number' => 'C' . str_pad($i, 2, '0', STR_PAD_LEFT),
                'unit_type' => 'Commercial Shop',
                'bedrooms' => 0,
                'bathrooms' => 1,
                'size_sqft' => 400,
                'monthly_rent' => 50000,
                'deposit_amount' => 100000,
                'status' => $status,
                'description' => "Ground floor commercial shop space",
                'commission_percentage' => 15.00,
                'is_furnished' => false,
                'allow_pets' => false,
                'parking_available' => true,
                'parking_spaces' => 2,
                'floor_level' => 'Ground',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        
        // Create residential units
        for ($i = 1; $i <= $residentialUnits; $i++) {
            $status = $i <= 17 ? 'occupied' : 'available';
            $bedrooms = $i <= 10 ? 1 : 2;
            
            Unit::create([
                'property_id' => $property->id,
                'unit_number' => 'R' . str_pad($i, 2, '0', STR_PAD_LEFT),
                'unit_type' => $bedrooms === 1 ? '1BR Flat' : '2BR Flat',
                'bedrooms' => $bedrooms,
                'bathrooms' => $bedrooms === 1 ? 1 : 2,
                'size_sqft' => $bedrooms === 1 ? 400 : 600,
                'monthly_rent' => $bedrooms === 1 ? 30000 : 45000,
                'deposit_amount' => $bedrooms === 1 ? 60000 : 90000,
                'status' => $status,
                'description' => "Residential flat above commercial area",
                'commission_percentage' => 10.00,
                'is_furnished' => $i % 3 === 0,
                'allow_pets' => true,
                'parking_available' => true,
                'parking_spaces' => 1,
                'floor_level' => ($i - 1) % 4 + 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
