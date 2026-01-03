<?php

namespace Database\Seeders;

use App\Models\OwnerBalance;
use App\Models\PropertyOwner;
use Illuminate\Database\Seeder;

class OwnerBalanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $owners = PropertyOwner::all();
        
        foreach ($owners as $owner) {
            // Generate realistic balance values without complex queries
            $totalEarned = rand(500000, 1500000); // Total earned from properties
            $commissionDeductions = $totalEarned * ($owner->commission_percentage / 100);
            $amountPaid = rand(200000, 800000); // Amount already paid to owner
            $amountOwed = max(0, $totalEarned - $commissionDeductions - $amountPaid);
            
            OwnerBalance::create([
                'tenant_id' => $owner->tenant_id,
                'property_owner_id' => $owner->id,
                'amount_owed' => $amountOwed,
                'amount_paid' => $amountPaid,
                'total_rent_collected' => $totalEarned,
                'total_platform_fees' => $commissionDeductions,
                'total_expenses' => rand(50000, 200000),
                'total_earned' => $totalEarned,
                'total_paid' => $amountPaid,
                'last_payment_date' => now()->subDays(rand(15, 30)),
                'last_payment_amount' => rand(50000, 200000),
                'next_expected_payment_date' => now()->addDays(15),
                'created_at' => now()->subMonths(3),
                'updated_at' => now(),
            ]);
        }

        $this->command->info('Owner balances seeded successfully!');
    }
}
