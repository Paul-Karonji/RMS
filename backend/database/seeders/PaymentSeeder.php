<?php

namespace Database\Seeders;

use App\Models\Lease;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class PaymentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $leases = Lease::all();
        
        foreach ($leases as $lease) {
            $tenant = User::find($lease->tenant_id);
            
            // Create deposit payment
            Payment::create([
                'lease_id' => $lease->id,
                'tenant_id' => $lease->tenant_id,
                'amount' => $lease->deposit_amount,
                'payment_type' => 'deposit',
                'payment_method' => 'mpesa',
                'payment_date' => $lease->start_date,
                'status' => 'completed',
                'transaction_id' => 'DEP' . uniqid(),
                'description' => 'Security deposit for ' . $lease->unit->unit_number,
                'created_at' => $lease->start_date,
                'updated_at' => $lease->start_date,
            ]);
            
            // Create rent payments for the last 3 months
            for ($i = 3; $i >= 1; $i--) {
                $paymentDate = Carbon::now()->subMonths($i)->day($lease->payment_day);
                
                Payment::create([
                    'lease_id' => $lease->id,
                    'tenant_id' => $lease->tenant_id,
                    'amount' => $lease->monthly_rent,
                    'payment_type' => 'rent',
                    'payment_method' => 'mpesa',
                    'payment_date' => $paymentDate,
                    'status' => 'completed',
                    'transaction_id' => 'RENT' . uniqid(),
                    'description' => 'Rent payment for ' . $paymentDate->format('F Y'),
                    'created_at' => $paymentDate,
                    'updated_at' => $paymentDate,
                ]);
            }
            
            // Add some late fees randomly
            if (rand(0, 2) === 1) {
                $lateDate = Carbon::now()->subMonth()->day($lease->payment_day + 10);
                
                Payment::create([
                    'lease_id' => $lease->id,
                    'tenant_id' => $lease->tenant_id,
                    'amount' => $lease->monthly_rent * 0.10, // 10% late fee
                    'payment_type' => 'late_fee',
                    'payment_method' => 'mpesa',
                    'payment_date' => $lateDate,
                    'status' => 'completed',
                    'transaction_id' => 'LATE' . uniqid(),
                    'description' => 'Late fee for ' . $lateDate->format('F Y'),
                    'created_at' => $lateDate,
                    'updated_at' => $lateDate,
                ]);
            }
        }

        $this->command->info('Payments seeded successfully!');
    }
}
