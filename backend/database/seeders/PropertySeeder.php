<?php

namespace Database\Seeders;

use App\Models\Property;
use App\Models\PropertyOwner;
use App\Models\PropertyAmenity;
use App\Models\Tenant;
use App\Models\Unit;
use App\Models\UnitPhoto;
use App\Models\User;
use Illuminate\Database\Seeder;

class PropertySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tenants = Tenant::all();
        
        foreach ($tenants as $tenant) {
            $admin = User::where('tenant_id', $tenant->id)
                         ->whereHas('roles', function($q) {
                             $q->where('name', 'company_admin');
                         })
                         ->first();
            
            if (!$admin) {
                continue;
            }
            
            $owners = PropertyOwner::where('tenant_id', $tenant->id)->get();
            
            if ($owners->count() < 3) {
                continue;
            }

            $properties = [
                [
                    'tenant_id' => $tenant->id,
                    'property_owner_id' => $owners[0]->id,
                    'name' => 'Westlands Heights Apartments',
                    'property_type' => 'apartment',
                    'description' => 'Modern apartment complex in the heart of Westlands',
                    'address_line_1' => '123 Westlands Road',
                    'address_line_2' => 'Westlands',
                    'city' => 'Nairobi',
                    'state' => 'Nairobi',
                    'postal_code' => '00100',
                    'country' => 'Kenya',
                    'latitude' => -1.2654,
                    'longitude' => 36.7984,
                    'total_units' => 10,
                    'occupied_units' => 0,
                    'status' => 'active',
                    'commission_percentage' => 10.00,
                    'approved_by' => $admin->id,
                    'approved_at' => now()->subMonths(3),
                    'created_at' => now()->subMonths(3),
                    'updated_at' => now(),
                ],
                [
                    'tenant_id' => $tenant->id,
                    'property_owner_id' => $owners[1]->id,
                    'name' => 'Karen Gardens',
                    'property_type' => 'townhouse',
                    'description' => 'Luxury townhouses in Karen',
                    'address_line_1' => '456 Karen Road',
                    'address_line_2' => null,
                    'city' => 'Nairobi',
                    'state' => 'Nairobi',
                    'postal_code' => '00502',
                    'country' => 'Kenya',
                    'latitude' => -1.3118,
                    'longitude' => 36.7297,
                    'total_units' => 8,
                    'occupied_units' => 0,
                    'status' => 'active',
                    'commission_percentage' => 10.00,
                    'approved_by' => $admin->id,
                    'approved_at' => now()->subMonths(2),
                    'created_at' => now()->subMonths(2),
                    'updated_at' => now(),
                ],
                [
                    'tenant_id' => $tenant->id,
                    'property_owner_id' => $owners[2]->id,
                    'name' => 'Pending Property - Lavington',
                    'property_type' => 'apartment',
                    'description' => 'New property awaiting approval',
                    'address_line_1' => '789 Lavington Road',
                    'address_line_2' => null,
                    'city' => 'Nairobi',
                    'state' => 'Nairobi',
                    'postal_code' => '00606',
                    'country' => 'Kenya',
                    'latitude' => -1.2864,
                    'longitude' => 36.7866,
                    'total_units' => 12,
                    'occupied_units' => 0,
                    'status' => 'pending_approval',
                    'commission_percentage' => 10.00,
                    'approved_by' => null,
                    'approved_at' => null,
                    'created_at' => now()->subDays(5),
                    'updated_at' => now()->subDays(5),
                ],
                [
                    'tenant_id' => $tenant->id,
                    'property_owner_id' => $owners[0]->id,
                    'name' => 'Rejected Property - Kilimani',
                    'property_type' => 'studio',
                    'description' => 'Property rejected due to incomplete documentation',
                    'address_line_1' => '321 Kilimani Road',
                    'address_line_2' => null,
                    'city' => 'Nairobi',
                    'state' => 'Nairobi',
                    'postal_code' => '00100',
                    'country' => 'Kenya',
                    'latitude' => -1.2921,
                    'longitude' => 36.7820,
                    'total_units' => 15,
                    'occupied_units' => 0,
                    'status' => 'rejected',
                    'commission_percentage' => 10.00,
                    'rejection_reason' => 'Incomplete documentation. Please provide title deed and building permit.',
                    'approved_by' => null,
                    'approved_at' => null,
                    'created_at' => now()->subDays(10),
                    'updated_at' => now()->subDays(3),
                ],
            ];

            foreach ($properties as $propertyData) {
                $property = Property::create($propertyData);
                
                // Add amenities for approved properties
                if ($property->status === 'active') {
                    $amenities = [
                        ['amenity_type' => 'security', 'name' => '24/7 Security', 'description' => 'Round the clock security', 'quantity' => 1, 'is_available' => true],
                        ['amenity_type' => 'parking', 'name' => 'Parking', 'description' => 'Secure parking space', 'quantity' => $property->total_units, 'is_available' => true],
                        ['amenity_type' => 'water', 'name' => 'Water Supply', 'description' => 'Reliable water supply', 'quantity' => 1, 'is_available' => true],
                        ['amenity_type' => 'internet', 'name' => 'Fiber Internet', 'description' => 'High-speed internet ready', 'quantity' => 1, 'is_available' => true],
                    ];
                    
                    foreach ($amenities as $amenity) {
                        PropertyAmenity::create(array_merge(['property_id' => $property->id], $amenity));
                    }
                    
                    // Add units for approved properties
                    $unitsPerProperty = $property->total_units;
                    for ($i = 1; $i <= $unitsPerProperty; $i++) {
                        $unitNumber = chr(64 + ceil($i / 10)) . str_pad($i % 10 ?: 10, 2, '0', STR_PAD_LEFT);
                        
                        $unit = Unit::create([
                            'tenant_id' => $tenant->id,
                            'property_id' => $property->id,
                            'unit_number' => $unitNumber,
                            'unit_type' => $i % 3 == 0 ? '3BR' : ($i % 2 == 0 ? '2BR' : '1BR'),
                            'bedrooms' => $i % 3 == 0 ? 3 : ($i % 2 == 0 ? 2 : 1),
                            'bathrooms' => $i % 3 == 0 ? 2 : 1,
                            'size_sqft' => $i % 3 == 0 ? 1200 : ($i % 2 == 0 ? 900 : 650),
                            'floor_level' => ceil($i / 4),
                            'monthly_rent' => $i % 3 == 0 ? 60000 : ($i % 2 == 0 ? 45000 : 30000),
                            'deposit_amount' => $i % 3 == 0 ? 60000 : ($i % 2 == 0 ? 45000 : 30000),
                            'status' => 'available',
                            'description' => 'Well-maintained unit with modern finishes',
                            'commission_percentage' => 10.00,
                            'is_furnished' => $i % 4 == 0,
                            'allow_pets' => $i % 5 == 0,
                            'parking_available' => true,
                            'parking_spaces' => 1,
                        ]);
                        
                        // Add photos for first 3 units
                        if ($i <= 3) {
                            UnitPhoto::create([
                                'unit_id' => $unit->id,
                                'photo_url' => 'https://via.placeholder.com/800x600?text=Unit+' . $unitNumber,
                                'photo_caption' => 'Living room',
                                'sort_order' => 1,
                                'is_primary' => true,
                            ]);
                            
                            UnitPhoto::create([
                                'unit_id' => $unit->id,
                                'photo_url' => 'https://via.placeholder.com/800x600?text=Bedroom+' . $unitNumber,
                                'photo_caption' => 'Master bedroom',
                                'sort_order' => 2,
                                'is_primary' => false,
                            ]);
                        }
                    }
                }
            }
        }

        $this->command->info('Properties, amenities, units, and photos seeded successfully!');
    }
}
