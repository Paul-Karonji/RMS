<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SubscriptionInvoice>
 */
class SubscriptionInvoiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $periodStart = now()->startOfMonth();
        $periodEnd = now()->endOfMonth();
        
        return [
            'tenant_id' => \App\Models\Tenant::factory(),
            'invoice_number' => 'INV-' . now()->format('Ymd') . '-' . fake()->unique()->numberBetween(1000, 9999),
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
            'amount' => fake()->randomFloat(2, 1000, 5000),
            'due_date' => $periodEnd->copy()->addDays(7),
            'status' => fake()->randomElement(['pending', 'paid', 'overdue', 'cancelled']),
            'payment_method' => null,
            'transaction_id' => null,
        ];
    }
}
