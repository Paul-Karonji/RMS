<?php

namespace Database\Seeders;

use App\Models\SubscriptionInvoice;
use App\Models\Tenant;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class SubscriptionInvoiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tenants = Tenant::all();
        
        foreach ($tenants as $tenant) {
            // Create last month's invoice (paid)
            SubscriptionInvoice::create([
                'tenant_id' => $tenant->id,
                'invoice_number' => 'SUB-' . now()->subMonth()->format('Ym') . '-' . $tenant->id,
                'amount' => $tenant->subscription_amount,
                'due_date' => now()->subMonth()->endOfMonth(),
                'status' => 'paid',
                'paid_at' => now()->subMonth()->endOfMonth()->subDays(5),
                'payment_method' => 'bank_transfer',
                'period_start' => now()->subMonth()->startOfMonth(),
                'period_end' => now()->subMonth()->endOfMonth(),
                'created_at' => now()->subMonth()->startOfMonth(),
                'updated_at' => now()->subMonth()->endOfMonth()->subDays(5),
            ]);
            
            // Create current month's invoice (pending)
            SubscriptionInvoice::create([
                'tenant_id' => $tenant->id,
                'invoice_number' => 'SUB-' . now()->format('Ym') . '-' . $tenant->id,
                'amount' => $tenant->subscription_amount,
                'due_date' => now()->endOfMonth(),
                'status' => 'pending',
                'period_start' => now()->startOfMonth(),
                'period_end' => now()->endOfMonth(),
                'created_at' => now()->startOfMonth(),
                'updated_at' => now()->startOfMonth(),
            ]);
        }

        $this->command->info('Subscription invoices seeded successfully!');
    }
}
