<?php

namespace Database\Seeders;

use App\Models\PlatformUser;
use App\Models\Tenant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TenantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $platformOwner = PlatformUser::where('role', 'platform_owner')->first();

        $tenants = [
            [
                'company_name' => 'Prime Properties Kenya',
                'pricing_model' => 'payment_processing',
                'cashout_fee_percentage' => 3.00,
                'min_cashout_amount' => 10000.00,
                'subscription_plan' => 'premium',
                'subscription_amount' => 15000.00,
                'subscription_status' => 'active',
                'subscription_started_at' => now()->subMonth(),
                'next_billing_date' => now()->addMonth(),
                'min_platform_fee_percentage' => 5.00,
                'max_platform_fee_percentage' => 15.00,
                'default_platform_fee_percentage' => 10.00,
                'admin_email' => 'admin@primeproperties.co.ke',
                'admin_phone' => '+254722123456',
                'bank_name' => 'Equity Bank Kenya',
                'bank_account_number' => '0030187654321',
                'bank_account_name' => 'Prime Properties Kenya Ltd',
                'bank_swift_code' => 'EQBLKENA',
                'mpesa_phone' => '+254722123456',
                'default_currency' => 'KES',
                'timezone' => 'Africa/Nairobi',
                'default_rent_collection_day' => 5,
                'default_lease_terms' => 'Standard residential lease agreement applies',
                'status' => 'active',
                'created_by' => $platformOwner->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'company_name' => 'Nairobi Homes Ltd',
                'pricing_model' => 'listings_only',
                'cashout_fee_percentage' => 2.50,
                'min_cashout_amount' => 5000.00,
                'subscription_plan' => 'basic',
                'subscription_amount' => 10000.00,
                'subscription_status' => 'active',
                'subscription_started_at' => now()->subMonths(2),
                'next_billing_date' => now()->addMonth(),
                'min_platform_fee_percentage' => 3.00,
                'max_platform_fee_percentage' => 10.00,
                'default_platform_fee_percentage' => 7.00,
                'admin_email' => 'info@nairobihomes.co.ke',
                'admin_phone' => '+254733987654',
                'bank_name' => 'KCB Bank Kenya',
                'bank_account_number' => '1234567890',
                'bank_account_name' => 'Nairobi Homes Ltd',
                'bank_swift_code' => 'KCBLKENA',
                'mpesa_phone' => '+254733987654',
                'default_currency' => 'KES',
                'timezone' => 'Africa/Nairobi',
                'default_rent_collection_day' => 1,
                'default_lease_terms' => 'All tenants must provide ID and proof of income',
                'status' => 'active',
                'created_by' => $platformOwner->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($tenants as $tenant) {
            Tenant::create($tenant);
        }

        $this->command->info('Tenants seeded successfully!');
    }
}
