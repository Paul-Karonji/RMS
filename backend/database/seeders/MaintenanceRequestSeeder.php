<?php

namespace Database\Seeders;

use App\Models\MaintenanceRequest;
use App\Models\Property;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class MaintenanceRequestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $properties = Property::all();
        
        foreach ($properties as $property) {
            $occupiedUnits = Unit::where('property_id', $property->id)
                               ->where('status', 'occupied')
                               ->take(3) // Create requests for 3 units
                               ->get();
            
            $tenants = User::where('tenant_id', $property->tenant_id)
                         ->where('role', 'tenant')
                         ->take(3)
                         ->get();
            
            $manager = User::where('tenant_id', $property->tenant_id)
                          ->where('role', 'property_manager')
                          ->first();
            
            foreach ($occupiedUnits as $index => $unit) {
                if ($index >= $tenants->count()) break;
                
                $statuses = ['pending', 'assigned', 'in_progress', 'completed'];
                $categories = ['plumbing', 'electrical', 'hvac', 'appliance', 'structural'];
                $priorities = ['low', 'medium', 'high'];
                
                MaintenanceRequest::create([
                    'tenant_id' => $property->tenant_id,
                    'property_id' => $property->id,
                    'unit_id' => $unit->id,
                    'reported_by' => $tenants[$index]->id,
                    'assigned_to' => $manager->id,
                    'category' => $categories[array_rand($categories)],
                    'priority' => $priorities[array_rand($priorities)],
                    'title' => 'Fix ' . $categories[array_rand($categories)] . ' issue',
                    'description' => 'There is an issue with the ' . $categories[array_rand($categories)] . ' that needs attention',
                    'estimated_cost' => rand(1000, 10000),
                    'actual_cost' => rand(1000, 10000),
                    'status' => $statuses[array_rand($statuses)],
                    'completed_at' => rand(0, 1) ? Carbon::now()->subDays(rand(1, 30)) : null,
                    'created_at' => Carbon::now()->subDays(rand(1, 60)),
                    'updated_at' => now(),
                ]);
            }
        }

        $this->command->info('Maintenance requests seeded successfully!');
    }
}
