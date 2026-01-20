<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\BalanceTransaction>
 */
class BalanceTransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $balanceBefore = fake()->randomFloat(2, 0, 100000);
        $amount = fake()->randomFloat(2, 100, 10000);
        
        return [
            'tenant_id' => \App\Models\Tenant::factory(),
            'property_owner_id' => \App\Models\PropertyOwner::factory(),
            'transaction_type' => fake()->randomElement(['payment_received', 'expense_deducted', 'fee_deducted', 'payout_made']),
            'amount' => $amount,
            'balance_before' => $balanceBefore,
            'balance_after' => $balanceBefore + $amount,
            'description' => fake()->sentence(),
            'reference_type' => null,
            'reference_id' => null,
        ];
    }
}
