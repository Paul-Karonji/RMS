<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ChangeRequest>
 */
class ChangeRequestFactory extends Factory
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
            'property_owner_id' => \App\Models\PropertyOwner::factory(),
            'property_id' => \App\Models\Property::factory(),
            'unit_id' => \App\Models\Unit::factory(),
            'request_type' => fake()->randomElement(['unit_price', 'property_details', 'unit_details']),
            'current_value' => fake()->numberBetween(5000, 50000),
            'requested_value' => fake()->numberBetween(5000, 50000),
            'reason' => fake()->sentence(),
            'status' => 'pending',
            'affects_existing_leases' => false,
            'reviewed_by' => null,
            'reviewed_at' => null,
            'review_notes' => null,
        ];
    }
}
