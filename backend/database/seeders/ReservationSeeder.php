<?php

namespace Database\Seeders;

use App\Models\Reservation;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class ReservationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $properties = Unit::where('status', 'available')->take(5)->get();
        
        foreach ($properties as $unit) {
            $tenant = User::where('tenant_id', $unit->property->tenant_id)
                         ->where('role', 'tenant')
                         ->inRandomOrder()
                         ->first();
            
            $statuses = ['active', 'expired', 'cancelled'];
            $status = $statuses[array_rand($statuses)];
            
            $reservationDate = Carbon::now()->subDays(rand(1, 30));
            $expiryDate = $reservationDate->copy()->addDays(7);
            
            Reservation::create([
                'unit_id' => $unit->id,
                'tenant_id' => $tenant->id,
                'reservation_date' => $reservationDate,
                'expiry_date' => $expiryDate,
                'deposit_amount' => $unit->deposit_amount * 0.1, // 10% of full deposit
                'status' => $status,
                'notes' => 'Reservation for ' . $unit->unit_number,
                'created_at' => $reservationDate,
                'updated_at' => $status === 'active' ? now() : $reservationDate,
            ]);
        }

        $this->command->info('Reservations seeded successfully!');
    }
}
