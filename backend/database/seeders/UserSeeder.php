<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tenants = Tenant::all();
        
        foreach ($tenants as $tenant) {
            // Company Admin
            $admin = User::create([
                'tenant_id' => $tenant->id,
                'name' => $tenant->company_name . ' Admin',
                'email' => 'admin@' . strtolower(str_replace(' ', '', $tenant->company_name)) . '.com',
                'phone' => '+2547' . rand(10000000, 99999999),
                'password_hash' => Hash::make('password123'),
                'role' => 'company_admin',
                'account_type' => 'staff',
                'must_change_password' => false,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Update tenant with admin_user_id (handling circular reference)
            $tenant->update(['admin_user_id' => $admin->id]);

            // Property Managers
            for ($i = 1; $i <= 2; $i++) {
                User::create([
                    'tenant_id' => $tenant->id,
                    'name' => "Property Manager {$i}",
                    'email' => "manager{$i}@" . strtolower(str_replace(' ', '', $tenant->company_name)) . '.com',
                    'phone' => '+2547' . rand(10000000, 99999999),
                    'password_hash' => Hash::make('password123'),
                    'role' => 'property_manager',
                    'account_type' => 'staff',
                    'must_change_password' => false,
                    'status' => 'active',
                    'created_by' => $admin->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Test Tenants (same email can exist across different companies)
            for ($i = 1; $i <= 5; $i++) {
                User::create([
                    'tenant_id' => $tenant->id,
                    'name' => "Test Tenant {$i}",
                    'email' => "tenant{$i}@example.com",
                    'phone' => '+2547' . rand(10000000, 99999999),
                    'password_hash' => Hash::make('password123'),
                    'role' => 'tenant',
                    'account_type' => 'tenant',
                    'must_change_password' => true,
                    'status' => 'active',
                    'created_by' => $admin->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        $this->command->info('Users seeded successfully!');
    }
}
