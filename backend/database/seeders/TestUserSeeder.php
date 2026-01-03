<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\Tenant;
use App\Models\User;
use App\Models\PropertyOwner;
use App\Models\CompanyBalance;

class TestUserSeeder extends Seeder
{
    public function run(): void
    {
        // Create a test tenant
        $tenant = Tenant::create([
            'company_name' => 'Test Company',
            'admin_email' => 'admin@test.com',
            'admin_phone' => '+254712345678',
            'pricing_model' => 'payment_processing',
            'status' => 'active',
        ]);

        // Create a test user (property owner)
        $user = User::create([
            'tenant_id' => $tenant->id,
            'name' => 'John Doe',
            'email' => 'john@test.com',
            'phone' => '+254712345678',
            'password_hash' => Hash::make('password123'),
            'role' => 'property_owner',
            'status' => 'active',
        ]);

        // Create property owner record
        $propertyOwner = new PropertyOwner([
            'user_id' => $user->id,
            'owner_name' => 'John Doe',
            'email' => 'john@test.com',
            'phone' => '+254712345678',
            'status' => 'active',
        ]);
        $propertyOwner->tenant_id = $tenant->id;
        $propertyOwner->save();

        // Create company balance
        $companyBalance = new CompanyBalance([
            'available_balance' => 0,
            'pending_balance' => 0,
            'total_collected' => 0,
        ]);
        $companyBalance->tenant_id = $tenant->id;
        $companyBalance->save();

        $this->command->info('Test user created successfully!');
        $this->command->info('Email: john@test.com');
        $this->command->info('Password: password123');
    }
}
