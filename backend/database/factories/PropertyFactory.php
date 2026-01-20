<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Property>
 */
class PropertyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $propertyTypes = ['apartment', 'villa', 'townhouse', 'studio', 'commercial', 'mixed_use'];
        $totalUnits = fake()->numberBetween(5, 50);
        
        return [
            'tenant_id' => \App\Models\Tenant::factory(),
            'property_owner_id' => \App\Models\PropertyOwner::factory(),
            'property_name' => fake()->words(3, true) . ' ' . fake()->randomElement(['Apartments', 'Villas', 'Residences', 'Heights', 'Gardens']),
            'property_type' => fake()->randomElement($propertyTypes),
            'description' => fake()->paragraph(),
            'address' => fake()->streetAddress() . ', ' . fake()->secondaryAddress(),
            'city' => fake()->city(),
            'county' => fake()->state(),
            'postal_code' => fake()->postcode(),
            'country' => 'Kenya',
            'latitude' => fake()->latitude(-1.5, -1.0),
            'longitude' => fake()->longitude(36.5, 37.0),
            'total_units' => $totalUnits,
            'occupied_units' => 0,
            'vacant_units' => $totalUnits,
            'status' => 'active',
            'commission_percentage' => 10.00,
        ];
    }
}
