<?php

namespace Database\Seeders;

use App\Models\Property;
use App\Models\PropertyOwner;
use App\Models\Tenant;
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
                         ->where('role', 'company_admin')
                         ->first();
            
            $owners = PropertyOwner::where('tenant_id', $tenant->id)->get();

            $properties = [
                [
                    'tenant_id' => $tenant->id,
                    'property_owner_id' => $owners[0]->id,
                    'property_name' => 'Westlands Heights Apartments',
                    'property_type' => 'apartment',
                    'address' => '123 Westlands Road, Nairobi',
                    'city' => 'Nairobi',
                    'county' => 'Nairobi',
                    'postal_code' => '00100',
                    'latitude' => -1.2654,
                    'longitude' => 36.7984,
                    'total_units' => 20,
                    'occupied_units' => 15,
                    'vacant_units' => 5,
                    'monthly_rental_income' => 750000.00,
                    'status' => 'active',
                    'approved_by' => $admin->id,
                    'approved_at' => now()->subMonths(3),
                    'created_at' => now()->subMonths(3),
                    'updated_at' => now(),
                ],
                [
                    'tenant_id' => $tenant->id,
                    'property_owner_id' => $owners[1]->id,
                    'property_name' => 'Karen Gardens',
                    'property_type' => 'townhouse',
                    'address' => '456 Karen Road, Nairobi',
                    'city' => 'Nairobi',
                    'county' => 'Nairobi',
                    'postal_code' => '00502',
                    'latitude' => -1.3118,
                    'longitude' => 36.7297,
                    'total_units' => 10,
                    'occupied_units' => 8,
                    'vacant_units' => 2,
                    'monthly_rental_income' => 800000.00,
                    'status' => 'active',
                    'approved_by' => $admin->id,
                    'approved_at' => now()->subMonths(2),
                    'created_at' => now()->subMonths(2),
                    'updated_at' => now(),
                ],
                [
                    'tenant_id' => $tenant->id,
                    'property_owner_id' => $owners[2]->id,
                    'property_name' => 'Lavington Plaza',
                    'property_type' => 'commercial',
                    'address' => '789 Lavington Road, Nairobi',
                    'city' => 'Nairobi',
                    'county' => 'Nairobi',
                    'postal_code' => '00606',
                    'latitude' => -1.2864,
                    'longitude' => 36.7866,
                    'total_units' => 15,
                    'occupied_units' => 12,
                    'vacant_units' => 3,
                    'monthly_rental_income' => 1200000.00,
                    'status' => 'active',
                    'approved_by' => $admin->id,
                    'approved_at' => now()->subMonths(1),
                    'created_at' => now()->subMonths(1),
                    'updated_at' => now(),
                ],
                [
                    'tenant_id' => $tenant->id,
                    'property_owner_id' => $owners[0]->id,
                    'property_name' => 'Kilimani Suites',
                    'property_type' => 'apartment',
                    'address' => '321 Kilimani Road, Nairobi',
                    'city' => 'Nairobi',
                    'county' => 'Nairobi',
                    'postal_code' => '00100',
                    'latitude' => -1.2921,
                    'longitude' => 36.7820,
                    'total_units' => 25,
                    'occupied_units' => 20,
                    'vacant_units' => 5,
                    'monthly_rental_income' => 900000.00,
                    'status' => 'active',
                    'approved_by' => $admin->id,
                    'approved_at' => now()->subWeeks(2),
                    'created_at' => now()->subWeeks(2),
                    'updated_at' => now(),
                ],
                [
                    'tenant_id' => $tenant->id,
                    'property_owner_id' => $owners[1]->id,
                    'property_name' => 'Riverside Complex',
                    'property_type' => 'mixed_use',
                    'address' => '555 Riverside Drive, Nairobi',
                    'city' => 'Nairobi',
                    'county' => 'Nairobi',
                    'postal_code' => '00100',
                    'latitude' => -1.2749,
                    'longitude' => 36.8190,
                    'total_units' => 30,
                    'occupied_units' => 25,
                    'vacant_units' => 5,
                    'monthly_rental_income' => 1500000.00,
                    'status' => 'active',
                    'approved_by' => $admin->id,
                    'approved_at' => now()->subWeek(),
                    'created_at' => now()->subWeek(),
                    'updated_at' => now(),
                ],
            ];

            foreach ($properties as $property) {
                Property::create($property);
            }
        }

        $this->command->info('Properties seeded successfully!');
    }
}
