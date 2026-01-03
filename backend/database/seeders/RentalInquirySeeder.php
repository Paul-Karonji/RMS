<?php

namespace Database\Seeders;

use App\Models\RentalInquiry;
use App\Models\Unit;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class RentalInquirySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $units = Unit::where('status', 'available')->take(10)->get();
        
        foreach ($units as $unit) {
            $statuses = ['pending', 'approved', 'rejected', 'converted'];
            $status = $statuses[array_rand($statuses)];
            
            $inquiryDate = Carbon::now()->subDays(rand(1, 60));
            
            RentalInquiry::create([
                'unit_id' => $unit->id,
                'name' => 'Prospective Tenant ' . rand(1, 100),
                'email' => 'inquiry' . rand(1, 100) . '@example.com',
                'phone' => '+2547' . rand(10000000, 99999999),
                'message' => 'I am interested in renting this unit. Please provide more information about availability and viewing schedule.',
                'preferred_move_in_date' => Carbon::now()->addDays(rand(15, 60)),
                'status' => $status,
                'notes' => 'Inquiry for ' . $unit->unit_number . ' at ' . $unit->property->property_name,
                'follow_up_date' => $status === 'pending' ? Carbon::now()->addDays(3) : null,
                'created_at' => $inquiryDate,
                'updated_at' => $status === 'pending' ? $inquiryDate : now(),
            ]);
        }

        $this->command->info('Rental inquiries seeded successfully!');
    }
}
