<?php

namespace Database\Seeders;

use App\Models\Lease;
use App\Models\LeaseSignature;
use App\Models\User;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class LeaseSignatureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $leases = Lease::all();
        
        foreach ($leases as $lease) {
            $tenant = User::find($lease->tenant_id);
            $owner = User::where('tenant_id', $lease->property->tenant_id)
                         ->where('role', 'company_admin')
                         ->first();
            
            if (!$tenant || !$owner) {
                continue; // Skip if tenant or owner not found
            }
            
            // Create tenant signature
            LeaseSignature::create([
                'lease_id' => $lease->id,
                'user_id' => $lease->tenant_id,
                'signer_role' => 'tenant',
                'signature_data' => [
                    'type' => 'digital',
                    'coordinates' => [
                        ['x' => 100, 'y' => 200],
                        ['x' => 150, 'y' => 210],
                        ['x' => 200, 'y' => 205]
                    ]
                ],
                'signed_at' => $lease->start_date,
                'ip_address' => '192.168.1.' . rand(1, 254),
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'created_at' => $lease->start_date,
                'updated_at' => $lease->start_date,
            ]);
            
            // Create owner signature
            LeaseSignature::create([
                'lease_id' => $lease->id,
                'user_id' => $owner->id,
                'signer_role' => 'owner',
                'signature_data' => [
                    'type' => 'digital',
                    'coordinates' => [
                        ['x' => 100, 'y' => 300],
                        ['x' => 140, 'y' => 310],
                        ['x' => 180, 'y' => 305]
                    ]
                ],
                'signed_at' => $lease->start_date->copy()->addDay(),
                'ip_address' => '192.168.1.' . rand(1, 254),
                'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36',
                'created_at' => $lease->start_date->copy()->addDay(),
                'updated_at' => $lease->start_date->copy()->addDay(),
            ]);
        }

        $this->command->info('Lease signatures seeded successfully!');
    }
}
