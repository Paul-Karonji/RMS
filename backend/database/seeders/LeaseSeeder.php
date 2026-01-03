<?php

namespace Database\Seeders;

use App\Models\Lease;
use App\Models\Property;
use App\Models\PropertyOwner;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class LeaseSeeder extends Seeder
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
                               ->get();
            
            $tenants = User::where('tenant_id', $property->tenant_id)
                         ->where('role', 'tenant')
                         ->take($occupiedUnits->count())
                         ->get();
            
            $owner = PropertyOwner::find($property->property_owner_id);
            $admin = User::where('tenant_id', $property->tenant_id)
                        ->where('role', 'company_admin')
                        ->first();
            
            foreach ($occupiedUnits as $index => $unit) {
                if ($index >= $tenants->count()) break;
                
                $tenant = $tenants[$index];
                $startDate = Carbon::now()->subMonths(rand(6, 24));
                $endDate = $startDate->copy()->addYear();
                
                Lease::create([
                    'property_id' => $property->id,
                    'unit_id' => $unit->id,
                    'property_owner_id' => $owner->id,
                    'tenant_id' => $tenant->id,
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'monthly_rent' => $unit->monthly_rent,
                    'deposit_amount' => $unit->deposit_amount,
                    'payment_frequency' => 'monthly',
                    'payment_day' => $property->tenant->default_rent_collection_day,
                    'late_fee_type' => 'percentage',
                    'late_fee_amount' => 10.00,
                    'grace_period_days' => 5,
                    'status' => 'active',
                    'created_by' => $admin->id,
                    'created_at' => $startDate,
                    'updated_at' => now(),
                ]);
            }
        }

        $this->command->info('Leases seeded successfully!');
    }
}
