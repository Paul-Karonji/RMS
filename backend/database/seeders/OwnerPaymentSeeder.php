<?php

namespace Database\Seeders;

use App\Models\OwnerPayment;
use App\Models\PropertyOwner;
use App\Models\User;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class OwnerPaymentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $owners = PropertyOwner::all();
        
        foreach ($owners as $owner) {
            $admin = User::where('tenant_id', $owner->tenant_id)
                        ->where('role', 'company_admin')
                        ->first();
            
            // Create recent payment
            OwnerPayment::create([
                'property_owner_id' => $owner->id,
                'amount' => 250000,
                'payment_date' => Carbon::now()->subDays(15),
                'payment_method' => 'bank_transfer',
                'transaction_id' => 'PAY' . uniqid(),
                'notes' => 'Monthly rental income payment for December 2025',
                'created_by' => $admin->id,
                'created_at' => Carbon::now()->subDays(15),
                'updated_at' => Carbon::now()->subDays(15),
            ]);
            
            // Create previous payment
            OwnerPayment::create([
                'property_owner_id' => $owner->id,
                'amount' => 275000,
                'payment_date' => Carbon::now()->subDays(45),
                'payment_method' => 'bank_transfer',
                'transaction_id' => 'PAY' . uniqid(),
                'notes' => 'Monthly rental income payment for November 2025',
                'created_by' => $admin->id,
                'created_at' => Carbon::now()->subDays(45),
                'updated_at' => Carbon::now()->subDays(45),
            ]);
        }

        $this->command->info('Owner payments seeded successfully!');
    }
}
