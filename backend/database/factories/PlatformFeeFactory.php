<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PlatformFee>
 */
class PlatformFeeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $baseAmount = fake()->randomFloat(2, 1000, 50000);
        $feePercentage = fake()->randomFloat(2, 5, 15);
        $feeAmount = $baseAmount * ($feePercentage / 100);
        
        return [
            'tenant_id' => \App\Models\Tenant::factory(),
            'payment_id' => \App\Models\Payment::factory(),
            'property_id' => \App\Models\Property::factory(),
            'fee_percentage' => $feePercentage,
            'base_amount' => $baseAmount,
            'fee_amount' => $feeAmount,
            'status' => 'pending',
            'collected_at' => null,
        ];
    }
}
