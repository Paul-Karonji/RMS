<?php

namespace Database\Seeders;

use App\Models\Expense;
use App\Models\MaintenanceRequest;
use App\Models\Property;
use App\Models\User;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class ExpenseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $properties = Property::all();
        
        foreach ($properties as $property) {
            $admin = User::where('tenant_id', $property->tenant_id)
                        ->where('role', 'company_admin')
                        ->first();
            
            // Create general expenses
            $categories = ['utilities', 'insurance', 'taxes', 'repairs', 'cleaning', 'security'];
            
            for ($i = 0; $i < 3; $i++) {
                Expense::create([
                    'tenant_id' => $property->tenant_id,
                    'property_id' => $property->id,
                    'category' => $categories[array_rand($categories)],
                    'description' => 'Monthly ' . $categories[array_rand($categories)] . ' payment',
                    'amount' => rand(5000, 50000),
                    'expense_date' => Carbon::now()->subMonths($i + 1),
                    'owner_share' => 0.7,
                    'platform_share' => 0.3,
                    'status' => 'approved',
                    'approved_by' => $admin->id,
                    'approved_at' => Carbon::now()->subMonths($i + 1),
                    'created_by' => $admin->id,
                    'created_at' => Carbon::now()->subMonths($i + 1),
                    'updated_at' => now(),
                ]);
            }
            
            // Create expenses linked to maintenance requests
            $maintenanceRequests = MaintenanceRequest::where('property_id', $property->id)
                                                  ->where('status', 'completed')
                                                  ->take(2)
                                                  ->get();
            
            foreach ($maintenanceRequests as $request) {
                Expense::create([
                    'tenant_id' => $property->tenant_id,
                    'property_id' => $property->id,
                    'unit_id' => $request->unit_id,
                    'maintenance_request_id' => $request->id,
                    'category' => 'repairs',
                    'description' => 'Repair cost for ' . $request->title,
                    'amount' => $request->actual_cost,
                    'expense_date' => $request->completed_at ?? now(),
                    'owner_share' => 0.8,
                    'platform_share' => 0.2,
                    'status' => 'approved',
                    'approved_by' => $admin->id,
                    'approved_at' => $request->completed_at ?? now(),
                    'created_by' => $admin->id,
                    'created_at' => $request->created_at,
                    'updated_at' => now(),
                ]);
            }
        }

        $this->command->info('Expenses seeded successfully!');
    }
}
