<?php

namespace Database\Seeders;

use App\Models\MaintenanceRequest;
use App\Models\MaintenanceUpdate;
use App\Models\User;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class MaintenanceUpdateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $requests = MaintenanceRequest::where('status', '!=', 'pending')->take(10)->get();
        
        foreach ($requests as $request) {
            $manager = User::find($request->assigned_to);
            
            // Add initial update
            MaintenanceUpdate::create([
                'maintenance_request_id' => $request->id,
                'updated_by' => $manager->id,
                'update_type' => 'status_change',
                'description' => 'Request has been assigned to maintenance team',
                'created_at' => $request->created_at->addHours(2),
                'updated_at' => $request->created_at->addHours(2),
            ]);
            
            // Add progress update if in progress or completed
            if (in_array($request->status, ['in_progress', 'completed'])) {
                MaintenanceUpdate::create([
                    'maintenance_request_id' => $request->id,
                    'updated_by' => $manager->id,
                    'update_type' => 'progress',
                    'description' => 'Work has begun on the repair. Parts have been ordered.',
                    'created_at' => $request->created_at->addDay(),
                    'updated_at' => $request->created_at->addDay(),
                ]);
            }
            
            // Add completion update if completed
            if ($request->status === 'completed') {
                MaintenanceUpdate::create([
                    'maintenance_request_id' => $request->id,
                    'updated_by' => $manager->id,
                    'update_type' => 'completion',
                    'description' => 'Repair has been completed successfully. Unit is now fully functional.',
                    'created_at' => $request->completed_at,
                    'updated_at' => $request->completed_at,
                ]);
            }
        }

        $this->command->info('Maintenance updates seeded successfully!');
    }
}
