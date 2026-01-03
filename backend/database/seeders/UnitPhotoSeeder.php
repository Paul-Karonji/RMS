<?php

namespace Database\Seeders;

use App\Models\Unit;
use App\Models\UnitPhoto;
use Illuminate\Database\Seeder;

class UnitPhotoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Only seed photos for first 50 units to avoid timeout
        $units = Unit::take(50)->get();
        
        foreach ($units as $unit) {
            // Add 2-3 photos per unit
            $photoCount = rand(2, 3);
            
            for ($i = 1; $i <= $photoCount; $i++) {
                UnitPhoto::create([
                    'unit_id' => $unit->id,
                    'photo_url' => "https://picsum.photos/seed/unit-{$unit->id}-{$i}/800/600.jpg",
                    'photo_caption' => "Photo {$i} of {$unit->unit_number} - " . ($i === 1 ? 'Living Room' : ($i === 2 ? 'Bedroom' : ($i === 3 ? 'Kitchen' : 'Bathroom'))),
                    'sort_order' => $i,
                    'is_primary' => $i === 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        $this->command->info('Unit photos seeded successfully!');
    }
}
