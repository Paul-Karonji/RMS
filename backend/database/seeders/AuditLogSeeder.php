<?php

namespace Database\Seeders;

use App\Models\AuditLog;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class AuditLogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tenants = Tenant::all();
        
        foreach ($tenants as $tenant) {
            $admin = User::where('tenant_id', $tenant->id)
                        ->where('role', 'company_admin')
                        ->first();
            
            // Create various audit logs
            $activities = [
                [
                    'user_id' => $admin->id,
                    'action' => 'property_created',
                    'model_type' => 'Property',
                    'details' => 'Created new property: Westlands Heights Apartments',
                    'ip_address' => '192.168.1.' . rand(1, 254),
                    'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
                    'created_at' => Carbon::now()->subMonths(3),
                ],
                [
                    'user_id' => $admin->id,
                    'action' => 'lease_signed',
                    'model_type' => 'Lease',
                    'details' => 'New lease signed for unit 101',
                    'ip_address' => '192.168.1.' . rand(1, 254),
                    'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
                    'created_at' => Carbon::now()->subMonths(2),
                ],
                [
                    'user_id' => $admin->id,
                    'action' => 'payment_received',
                    'model_type' => 'Payment',
                    'details' => 'Received rent payment of KES 35,000',
                    'ip_address' => '192.168.1.' . rand(1, 254),
                    'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
                    'created_at' => Carbon::now()->subMonth(),
                ],
                [
                    'user_id' => $admin->id,
                    'action' => 'maintenance_request_created',
                    'model_type' => 'MaintenanceRequest',
                    'details' => 'New maintenance request for plumbing issue',
                    'ip_address' => '192.168.1.' . rand(1, 254),
                    'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
                    'created_at' => Carbon::now()->subDays(15),
                ],
                [
                    'user_id' => $admin->id,
                    'action' => 'cashout_processed',
                    'model_type' => 'CashoutRequest',
                    'details' => 'Processed cashout request of KES 500,000',
                    'ip_address' => '192.168.1.' . rand(1, 254),
                    'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
                    'created_at' => Carbon::now()->subDays(30),
                ],
            ];
            
            foreach ($activities as $activity) {
                AuditLog::create(array_merge($activity, [
                    'tenant_id' => $tenant->id,
                    'updated_at' => $activity['created_at'],
                ]));
            }
        }

        $this->command->info('Audit logs seeded successfully!');
    }
}
