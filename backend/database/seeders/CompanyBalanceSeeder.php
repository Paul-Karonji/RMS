<?php

namespace Database\Seeders;

use App\Models\CompanyBalance;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class CompanyBalanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tenants = Tenant::all();
        
        foreach ($tenants as $tenant) {
            // Calculate total collected from properties
            $totalCollected = $tenant->properties()->sum('monthly_rental_income') * 3; // 3 months of rent
            
            // Calculate some withdrawals
            $totalWithdrawn = rand(500000, 1500000);
            
            // Available balance after platform fees
            $platformFeePercentage = $tenant->default_platform_fee_percentage / 100;
            $availableBalance = ($totalCollected * (1 - $platformFeePercentage)) - $totalWithdrawn;
            
            // Pending balance from current month
            $pendingBalance = $tenant->properties()->sum('monthly_rental_income') * 0.7; // 70% of current month collected
            
            CompanyBalance::create([
                'tenant_id' => $tenant->id,
                'available_balance' => max(0, $availableBalance),
                'pending_balance' => $pendingBalance,
                'total_collected' => $totalCollected,
                'total_withdrawn' => $totalWithdrawn,
                'last_cashout_at' => now()->subDays(rand(15, 45)),
                'last_cashout_amount' => rand(100000, 500000),
                'created_at' => now()->subMonths(3),
                'updated_at' => now(),
            ]);
        }

        $this->command->info('Company balances seeded successfully!');
    }
}
