<?php

namespace Database\Seeders;

use App\Models\PropertyOwner;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;

class PropertyOwnerSeeder extends Seeder
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

            $owners = [
                [
                    'tenant_id' => $tenant->id,
                    'owner_name' => 'John Kamau',
                    'email' => 'john.kamau@example.com',
                    'phone' => '+254722123001',
                    'address' => '123 Westlands Road, Nairobi',
                    'id_number' => '12345678',
                    'kra_pin' => 'A001234567P',
                    'bank_name' => 'Equity Bank Kenya',
                    'bank_account_number' => '0030123456789',
                    'bank_account_name' => 'John Kamau',
                    'bank_branch' => 'Westlands Branch',
                    'bank_swift_code' => 'EQBLKENA',
                    'mpesa_phone' => '+254722123001',
                    'commission_percentage' => 10.00,
                    'status' => 'active',
                    'added_by' => $admin->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'tenant_id' => $tenant->id,
                    'owner_name' => 'Mary Wanjiru',
                    'email' => 'mary.wanjiru@example.com',
                    'phone' => '+254733123002',
                    'address' => '456 Karen Road, Nairobi',
                    'id_number' => '23456789',
                    'kra_pin' => 'A002345678P',
                    'bank_name' => 'KCB Bank Kenya',
                    'bank_account_number' => '1234567890123',
                    'bank_account_name' => 'Mary Wanjiru',
                    'bank_branch' => 'Karen Branch',
                    'bank_swift_code' => 'KCBLKENA',
                    'mpesa_phone' => '+254733123002',
                    'commission_percentage' => 8.00,
                    'status' => 'active',
                    'added_by' => $admin->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'tenant_id' => $tenant->id,
                    'owner_name' => 'David Ochieng',
                    'email' => 'david.ochieng@example.com',
                    'phone' => '+254744123003',
                    'address' => '789 Lavington Road, Nairobi',
                    'id_number' => '34567890',
                    'kra_pin' => 'A003456789P',
                    'bank_name' => 'Co-operative Bank',
                    'bank_account_number' => '011234567890',
                    'bank_account_name' => 'David Ochieng',
                    'bank_branch' => 'Lavington Branch',
                    'bank_swift_code' => 'KCBLKENA',
                    'mpesa_phone' => '+254744123003',
                    'commission_percentage' => 12.00,
                    'status' => 'active',
                    'added_by' => $admin->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ];

            foreach ($owners as $owner) {
                PropertyOwner::create($owner);
            }
        }

        $this->command->info('Property owners seeded successfully!');
    }
}
