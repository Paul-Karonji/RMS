<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Unit>
 */
class UnitFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $bedrooms = fake()->numberBetween(1, 4);
        $unitTypes = ['Studio', '1BR', '2BR', '3BR', '4BR'];
        $rent = $bedrooms * 25000;
        
        return [
            'property_id' => \App\Models\Property::factory(),
            'unit_number' => strtoupper(fake()->unique()->bothify('??###')),
            'unit_type' => $unitTypes[$bedrooms - 1] ?? 'Studio',
            'bedrooms' => $bedrooms,
            'bathrooms' => fake()->numberBetween(1, $bedrooms),
            'size_sqft' => fake()->numberBetween(400, 2000),
            'floor_level' => (string) fake()->numberBetween(1, 10),
            'monthly_rent' => $rent,
            'deposit_amount' => $rent,
            'status' => 'available',
            'description' => fake()->sentence(),
            'commission_percentage' => 10.00,
            'is_furnished' => fake()->boolean(30),
            'allow_pets' => fake()->boolean(20),
            'parking_available' => fake()->boolean(70),
            'parking_spaces' => fake()->numberBetween(0, 2),
            'is_publicly_listed' => true,
        ];
    }
}
