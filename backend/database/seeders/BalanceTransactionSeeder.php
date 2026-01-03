<?php

namespace Database\Seeders;

use App\Models\BalanceTransaction;
use App\Models\Payment;
use App\Models\Tenant;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class BalanceTransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tenants = Tenant::all();
        
        foreach ($tenants as $tenant) {
            $payments = Payment::whereHas('lease.unit.property', function($query) use ($tenant) {
                $query->where('tenant_id', $tenant->id);
            })->take(5)->get();
            
            foreach ($payments as $payment) {
                // Create balance transaction for payment
                BalanceTransaction::create([
                    'tenant_id' => $tenant->id,
                    'payment_id' => $payment->id,
                    'transaction_type' => 'payment_received',
                    'amount' => $payment->amount,
                    'fee_amount' => $payment->amount * ($tenant->default_platform_fee_percentage / 100),
                    'net_amount' => $payment->amount * (1 - $tenant->default_platform_fee_percentage / 100),
                    'transaction_date' => $payment->payment_date,
                    'description' => 'Payment received for ' . $payment->payment_type,
                    'reference_id' => $payment->transaction_id,
                    'created_at' => $payment->payment_date,
                    'updated_at' => $payment->payment_date,
                ]);
            }
            
            // Create cashout transaction
            BalanceTransaction::create([
                'tenant_id' => $tenant->id,
                'transaction_type' => 'cashout',
                'amount' => 500000,
                'fee_amount' => 15000, // 3% cashout fee
                'net_amount' => 485000,
                'transaction_date' => now()->subDays(30),
                'description' => 'Monthly cashout to owner',
                'reference_id' => 'CASHOUT-' . uniqid(),
                'created_at' => now()->subDays(30),
                'updated_at' => now()->subDays(30),
            ]);
        }

        $this->command->info('Balance transactions seeded successfully!');
    }
}
