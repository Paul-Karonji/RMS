<?php

namespace Database\Seeders;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class NotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        
        foreach ($users as $user) {
            // Create different types of notifications based on user role
            if ($user->role === 'tenant') {
                // Tenant notifications
                Notification::create([
                    'user_id' => $user->id,
                    'type' => 'rent_due',
                    'title' => 'Rent Payment Due',
                    'message' => 'Your rent payment for this month is due. Please ensure timely payment to avoid late fees.',
                    'data' => ['due_date' => now()->addDays(5)->toDateString()],
                    'created_at' => now()->subDays(3),
                    'updated_at' => now(),
                ]);
                
                Notification::create([
                    'user_id' => $user->id,
                    'type' => 'maintenance_update',
                    'title' => 'Maintenance Update',
                    'message' => 'Your maintenance request has been updated.',
                    'data' => ['request_id' => 'REQ-' . rand(100, 999)],
                    'read_at' => now()->subDay(),
                    'created_at' => now()->subDays(2),
                    'updated_at' => now(),
                ]);
            } elseif ($user->role === 'property_manager') {
                // Manager notifications
                Notification::create([
                    'user_id' => $user->id,
                    'type' => 'new_maintenance_request',
                    'title' => 'New Maintenance Request',
                    'message' => 'A new maintenance request has been submitted and requires your attention.',
                    'data' => ['priority' => 'medium'],
                    'created_at' => now()->subHours(6),
                    'updated_at' => now(),
                ]);
                
                Notification::create([
                    'user_id' => $user->id,
                    'type' => 'new_inquiry',
                    'title' => 'New Rental Inquiry',
                    'message' => 'A prospective tenant has inquired about one of your properties.',
                    'data' => ['property_name' => 'Westlands Heights'],
                    'read_at' => now()->subHours(2),
                    'created_at' => now()->subHours(4),
                    'updated_at' => now(),
                ]);
            } elseif ($user->role === 'company_admin') {
                // Admin notifications
                Notification::create([
                    'user_id' => $user->id,
                    'type' => 'payment_received',
                    'title' => 'Payment Received',
                    'message' => 'A new payment has been received from a tenant.',
                    'data' => ['amount' => 'KES 35,000'],
                    'created_at' => now()->subHours(12),
                    'updated_at' => now(),
                ]);
            }
        }

        $this->command->info('Notifications seeded successfully!');
    }
}
