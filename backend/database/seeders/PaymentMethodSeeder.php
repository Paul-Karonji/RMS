<?php

namespace Database\Seeders;

use App\Models\PaymentMethod;
use App\Models\User;
use Illuminate\Database\Seeder;

class PaymentMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tenants = User::where('role', 'tenant')->get();
        
        foreach ($tenants as $tenant) {
            // Add M-Pesa payment method
            PaymentMethod::create([
                'tenant_id' => $tenant->tenant_id,
                'user_id' => $tenant->id,
                'method_type' => 'mobile_money',
                'provider' => 'mpesa',
                'account_number' => $tenant->phone,
                'account_name' => $tenant->name,
                'is_default' => true,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            // Add bank payment method for some tenants
            if (rand(0, 1) === 1) {
                PaymentMethod::create([
                    'tenant_id' => $tenant->tenant_id,
                    'user_id' => $tenant->id,
                    'method_type' => 'bank_account',
                    'provider' => 'Equity Bank',
                    'account_number' => '0030' . rand(100000, 999999),
                    'account_name' => $tenant->name,
                    'is_default' => false,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        $this->command->info('Payment methods seeded successfully!');
    }
}
