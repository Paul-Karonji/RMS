<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PropertyOwner>
 */
class PropertyOwnerFactory extends Factory
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
            'owner_name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'address' => fake()->address(),
            'id_number' => fake()->numerify('########'),
            'bank_name' => fake()->randomElement(['Equity Bank', 'KCB', 'Cooperative Bank', 'NCBA']),
            'bank_account_number' => fake()->numerify('##########'),
            'bank_account_name' => fake()->name(),
            'status' => 'active',
        ];
    }
}
