<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Expense>
 */
class ExpenseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenant_id' => \App\Models\Tenant::factory(),
            'property_id' => \App\Models\Property::factory(),
            'unit_id' => null,
            'maintenance_request_id' => null,
            'category' => fake()->randomElement(['maintenance', 'utilities', 'repairs', 'cleaning', 'other']),
            'description' => fake()->sentence(),
            'amount' => fake()->randomFloat(2, 100, 10000),
            'expense_date' => now(),
            'owner_share' => 0.00,
            'platform_share' => 0.00,
            'status' => 'pending',
            'created_by' => \App\Models\User::factory(),
            'approved_by' => null,
            'approved_at' => null,
            'rejected_by' => null,
            'rejected_at' => null,
            'rejection_reason' => null,
        ];
    }
}
