<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MaintenanceRequest>
 */
class MaintenanceRequestFactory extends Factory
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
            'unit_id' => \App\Models\Unit::factory(),
            'reported_by' => \App\Models\User::factory(),
            'category' => fake()->randomElement(['plumbing', 'electrical', 'hvac', 'appliance', 'structural', 'other']),
            'priority' => fake()->randomElement(['low', 'medium', 'high', 'urgent']),
            'title' => fake()->sentence(4),
            'description' => fake()->paragraph(),
            'status' => 'pending',
            'assigned_to' => null,
            'scheduled_date' => null,
            'completed_at' => null,
            'notes' => null,
        ];
    }
}
