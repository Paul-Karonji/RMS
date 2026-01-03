<?php

namespace Database\Seeders;

use App\Models\CashoutRequest;
use App\Models\Tenant;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class CashoutRequestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tenants = Tenant::all();
        
        foreach ($tenants as $tenant) {
            // Create processed cashout request
            CashoutRequest::create([
                'tenant_id' => $tenant->id,
                'amount' => 500000,
                'fee_amount' => 15000, // 3% fee
                'net_amount' => 485000,
                'status' => 'processed',
                'payment_method' => 'bank_transfer',
                'payment_details' => json_encode([
                    'account_name' => $tenant->bank_account_name,
                    'account_number' => $tenant->bank_account_number,
                    'bank_name' => $tenant->bank_name,
                ]),
                'approved_at' => Carbon::now()->subDays(35),
                'processed_at' => Carbon::now()->subDays(30),
                'transaction_id' => 'TXN' . uniqid(),
                'created_at' => Carbon::now()->subDays(40),
                'updated_at' => Carbon::now()->subDays(30),
            ]);
            
            // Create pending cashout request
            if (rand(0, 1) === 1) {
                CashoutRequest::create([
                    'tenant_id' => $tenant->id,
                    'amount' => 300000,
                    'fee_amount' => 9000, // 3% fee
                    'net_amount' => 291000,
                    'status' => 'pending',
                    'payment_method' => 'bank_transfer',
                    'payment_details' => json_encode([
                        'account_name' => $tenant->bank_account_name,
                        'account_number' => $tenant->bank_account_number,
                        'bank_name' => $tenant->bank_name,
                    ]),
                    'created_at' => Carbon::now()->subDays(5),
                    'updated_at' => Carbon::now()->subDays(5),
                ]);
            }
        }

        $this->command->info('Cashout requests seeded successfully!');
    }
}
