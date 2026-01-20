<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Notification>
 */
class NotificationFactory extends Factory
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
            'user_id' => \App\Models\User::factory(),
            'type' => fake()->randomElement(['info', 'warning', 'success', 'error']),
            'title' => fake()->sentence(3),
            'message' => fake()->paragraph(),
            'status' => 'unread',
            'data' => null,
            'read_at' => null,
        ];
    }
}
