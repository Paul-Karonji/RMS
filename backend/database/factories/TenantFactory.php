<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Tenant>
 */
class TenantFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'company_name' => fake()->company(),
            'pricing_model' => 'percentage',
            'admin_email' => fake()->unique()->companyEmail(),
            'admin_phone' => fake()->phoneNumber(),
            'subscription_plan' => fake()->randomElement(['basic', 'professional', 'enterprise']),
            'subscription_status' => 'active',
        ];
    }
}
