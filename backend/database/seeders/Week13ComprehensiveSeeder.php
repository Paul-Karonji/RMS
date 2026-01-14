<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tenant;
use App\Models\User;
use App\Models\PropertyOwner;
use App\Models\Property;
use App\Models\Unit;
use App\Models\Lease;
use App\Models\Payment;
use App\Models\CompanyBalance;
use App\Models\OwnerBalance;
use App\Models\PropertyAmenity;
use App\Models\UnitPhoto;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class Week13ComprehensiveSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Creates comprehensive test data with proper relationships:
     * - 1 Tenant Company (Prime Properties Kenya)
     * - 3 Users (Admin, Manager, Tenant users)
     * - 2 Property Owners
     * - 2 Properties (both approved)
     * - 6 Units (3 occupied, 2 vacant, 1 under maintenance)
     * - 3 Active Leases
     * - 9 Payments (various statuses)
     * - Balances for company and owners
     */
    public function run(): void
    {
        // ========================================
        // 1. CREATE TENANT COMPANY
        // ========================================
        $tenant = Tenant::create([
            'id' => Str::uuid(),
            'company_name' => 'Prime Properties Kenya',
            'admin_email' => 'admin@primepropertieskenya.com',
            'admin_phone' => '+254712345678',
            'pricing_model' => 'payment_processing',
            'cashout_fee_percentage' => 3.00,
            'min_platform_fee_percentage' => 5.00,
            'max_platform_fee_percentage' => 15.00,
            'default_platform_fee_percentage' => 10.00,
            'status' => 'active',
            'subscription_status' => 'active',
        ]);

        // ========================================
        // 2. CREATE USERS
        // ========================================
        
        // Company Admin
        $admin = User::create([
            'id' => Str::uuid(),
            'tenant_id' => $tenant->id,
            'name' => 'Admin User',
            'email' => 'admin@primepropertieskenya.com',
            'phone' => '+254712345678',
            'password_hash' => Hash::make('password123'),
            'role' => 'company_admin',
            'status' => 'active',
        ]);

        // Property Manager
        $manager = User::create([
            'id' => Str::uuid(),
            'tenant_id' => $tenant->id,
            'name' => 'Manager One',
            'email' => 'manager1@primepropertieskenya.com',
            'phone' => '+254712345679',
            'password_hash' => Hash::make('password123'),
            'role' => 'company_staff',
            'status' => 'active',
        ]);

        // Tenant Users (renters)
        $tenant1 = User::create([
            'id' => Str::uuid(),
            'tenant_id' => $tenant->id,
            'name' => 'Tenant One',
            'email' => 'tenant1@example.com',
            'phone' => '+254722111111',
            'password_hash' => Hash::make('password123'),
            'role' => 'tenant',
            'status' => 'active',
        ]);

        $tenant2 = User::create([
            'id' => Str::uuid(),
            'tenant_id' => $tenant->id,
            'name' => 'Tenant Two',
            'email' => 'tenant2@example.com',
            'phone' => '+254722222222',
            'password_hash' => Hash::make('password123'),
            'role' => 'tenant',
            'status' => 'active',
        ]);

        $tenant3 = User::create([
            'id' => Str::uuid(),
            'tenant_id' => $tenant->id,
            'name' => 'Tenant Three',
            'email' => 'tenant3@example.com',
            'phone' => '+254722333333',
            'password_hash' => Hash::make('password123'),
            'role' => 'tenant',
            'status' => 'active',
        ]);

        // ========================================
        // 3. CREATE PROPERTY OWNERS
        // ========================================
        
        $owner1 = PropertyOwner::create([
            'id' => Str::uuid(),
            'tenant_id' => $tenant->id,
            'owner_name' => 'John Kamau',
            'email' => 'owner1@example.com',
            'phone' => '+254733111111',
            'id_number' => 'ID12345678',
            'status' => 'active',
        ]);

        $owner2 = PropertyOwner::create([
            'id' => Str::uuid(),
            'tenant_id' => $tenant->id,
            'owner_name' => 'Jane Wanjiru',
            'email' => 'owner2@example.com',
            'phone' => '+254733222222',
            'id_number' => 'ID87654321',
            'status' => 'active',
        ]);

        // ========================================
        // 4. CREATE PROPERTIES
        // ========================================
        
        $property1 = Property::create([
            'id' => Str::uuid(),
            'tenant_id' => $tenant->id,
            'property_owner_id' => $owner1->id,
            'manager_id' => $manager->id,
            'property_name' => 'Green Valley Apartments',
            'property_type' => 'apartment',
            'address' => '123 Moi Avenue, Westlands',
            'city' => 'Nairobi',
            'county' => 'Nairobi',
            'description' => 'Modern apartments in the heart of Westlands',
            'total_units' => 4,
            'status' => 'approved',
            'approved_by' => $admin->id,
            'approved_at' => now()->subDays(30),
        ]);

        $property2 = Property::create([
            'id' => Str::uuid(),
            'tenant_id' => $tenant->id,
            'property_owner_id' => $owner2->id,
            'manager_id' => $manager->id,
            'property_name' => 'Westlands Towers',
            'property_type' => 'apartment',
            'address' => '456 Waiyaki Way, Westlands',
            'city' => 'Nairobi',
            'county' => 'Nairobi',
            'description' => 'Luxury high-rise apartments with city views',
            'total_units' => 2,
            'status' => 'approved',
            'approved_by' => $admin->id,
            'approved_at' => now()->subDays(25),
        ]);

        // ========================================
        // 5. CREATE PROPERTY AMENITIES
        // ========================================
        
        PropertyAmenity::create([
            'id' => Str::uuid(),
            'property_id' => $property1->id,
            'name' => 'Parking',
            'amenity_type' => 'facility',
        ]);

        PropertyAmenity::create([
            'id' => Str::uuid(),
            'property_id' => $property1->id,
            'name' => 'WiFi',
            'amenity_type' => 'utility',
        ]);

        PropertyAmenity::create([
            'id' => Str::uuid(),
            'property_id' => $property2->id,
            'name' => 'Swimming Pool',
            'amenity_type' => 'facility',
        ]);

        PropertyAmenity::create([
            'id' => Str::uuid(),
            'property_id' => $property2->id,
            'name' => 'Gym',
            'amenity_type' => 'facility',
        ]);

        // ========================================
        // 6. CREATE UNITS
        // ========================================
        
        // Property 1 Units
        $unitA101 = Unit::create([
            'id' => Str::uuid(),
            'property_id' => $property1->id,
            'unit_number' => 'A101',
            'unit_type' => '2BR',
            'bedrooms' => 2,
            'bathrooms' => 2,
            'size_sqft' => 850,
            'floor_level' => '1',
            'monthly_rent' => 50000.00,
            'deposit_amount' => 50000.00,
            'status' => 'occupied',
            'description' => 'Spacious 2BR with balcony',
            'is_furnished' => true,
        ]);

        $unitA102 = Unit::create([
            'id' => Str::uuid(),
            'property_id' => $property1->id,
            'unit_number' => 'A102',
            'unit_type' => '1BR',
            'bedrooms' => 1,
            'bathrooms' => 1,
            'size_sqft' => 600,
            'floor_level' => '1',
            'monthly_rent' => 35000.00,
            'deposit_amount' => 35000.00,
            'status' => 'occupied',
            'description' => 'Cozy 1BR unit',
            'is_furnished' => false,
        ]);

        $unitA103 = Unit::create([
            'id' => Str::uuid(),
            'property_id' => $property1->id,
            'unit_number' => 'A103',
            'unit_type' => '2BR',
            'bedrooms' => 2,
            'bathrooms' => 2,
            'size_sqft' => 850,
            'floor_level' => '1',
            'monthly_rent' => 50000.00,
            'deposit_amount' => 50000.00,
            'status' => 'available',
            'description' => 'Available 2BR unit',
            'is_furnished' => false,
        ]);

        $unitA104 = Unit::create([
            'id' => Str::uuid(),
            'property_id' => $property1->id,
            'unit_number' => 'A104',
            'unit_type' => '3BR',
            'bedrooms' => 3,
            'bathrooms' => 2,
            'size_sqft' => 1200,
            'floor_level' => '1',
            'monthly_rent' => 70000.00,
            'deposit_amount' => 70000.00,
            'status' => 'under_maintenance',
            'description' => 'Large 3BR - under renovation',
            'is_furnished' => false,
        ]);

        // Property 2 Units
        $unitB201 = Unit::create([
            'id' => Str::uuid(),
            'property_id' => $property2->id,
            'unit_number' => 'B201',
            'unit_type' => '2BR',
            'bedrooms' => 2,
            'bathrooms' => 2,
            'size_sqft' => 900,
            'floor_level' => '2',
            'monthly_rent' => 60000.00,
            'deposit_amount' => 60000.00,
            'status' => 'occupied',
            'description' => 'Luxury 2BR with city view',
            'is_furnished' => true,
        ]);

        $unitB202 = Unit::create([
            'id' => Str::uuid(),
            'property_id' => $property2->id,
            'unit_number' => 'B202',
            'unit_type' => '3BR',
            'bedrooms' => 3,
            'bathrooms' => 3,
            'size_sqft' => 1400,
            'floor_level' => '2',
            'monthly_rent' => 85000.00,
            'deposit_amount' => 85000.00,
            'status' => 'available',
            'description' => 'Premium 3BR penthouse',
            'is_furnished' => true,
        ]);

        // ========================================
        // 7. CREATE UNIT PHOTOS
        // ========================================
        
        UnitPhoto::create([
            'id' => Str::uuid(),
            'unit_id' => $unitA101->id,
            'photo_url' => 'https://via.placeholder.com/800x600?text=A101+Living+Room',
            'photo_caption' => 'Living Room',
            'sort_order' => 1,
            'is_primary' => true,
        ]);

        UnitPhoto::create([
            'id' => Str::uuid(),
            'unit_id' => $unitB201->id,
            'photo_url' => 'https://via.placeholder.com/800x600?text=B201+City+View',
            'photo_caption' => 'City View',
            'sort_order' => 1,
            'is_primary' => true,
        ]);

        // ========================================
        // 8. CREATE LEASES
        // ========================================
        
        // Lease 1: Unit A101 - Started 3 months ago
        $lease1 = Lease::create([
            'id' => Str::uuid(),
            'tenant_id' => $tenant1->id,
            'property_id' => $property1->id,
            'unit_id' => $unitA101->id,
            'property_owner_id' => $owner1->id,
            'start_date' => now()->subMonths(3)->startOfMonth(),
            'end_date' => now()->addMonths(9)->endOfMonth(),
            'monthly_rent' => 50000.00,
            'deposit_amount' => 50000.00,
            'first_month_rent' => 50000.00,
            'is_prorated' => false,
            'payment_type' => 'manual',
            'payment_frequency' => 'monthly',
            'payment_day' => 1,
            'late_fee_type' => 'flat',
            'late_fee_amount' => 2000.00,
            'grace_period_days' => 3,
            'status' => 'active',
            'created_by' => $admin->id,
        ]);

        // Lease 2: Unit A102 - Started 2 months ago
        $lease2 = Lease::create([
            'id' => Str::uuid(),
            'tenant_id' => $tenant2->id,
            'property_id' => $property1->id,
            'unit_id' => $unitA102->id,
            'property_owner_id' => $owner1->id,
            'start_date' => now()->subMonths(2)->startOfMonth(),
            'end_date' => now()->addMonths(10)->endOfMonth(),
            'monthly_rent' => 35000.00,
            'deposit_amount' => 35000.00,
            'first_month_rent' => 35000.00,
            'is_prorated' => false,
            'payment_type' => 'manual',
            'payment_frequency' => 'monthly',
            'payment_day' => 1,
            'late_fee_type' => 'percentage',
            'late_fee_amount' => 5.00,
            'grace_period_days' => 3,
            'status' => 'active',
            'created_by' => $admin->id,
        ]);

        // Lease 3: Unit B201 - Started 4 months ago
        $lease3 = Lease::create([
            'id' => Str::uuid(),
            'tenant_id' => $tenant3->id,
            'property_id' => $property2->id,
            'unit_id' => $unitB201->id,
            'property_owner_id' => $owner2->id,
            'start_date' => now()->subMonths(4)->startOfMonth(),
            'end_date' => now()->addMonths(8)->endOfMonth(),
            'monthly_rent' => 60000.00,
            'deposit_amount' => 60000.00,
            'first_month_rent' => 60000.00,
            'is_prorated' => false,
            'payment_type' => 'manual',
            'payment_frequency' => 'monthly',
            'payment_day' => 1,
            'late_fee_type' => 'flat',
            'late_fee_amount' => 3000.00,
            'grace_period_days' => 5,
            'status' => 'active',
            'created_by' => $manager->id,
        ]);

        // ========================================
        // 9. CREATE PAYMENTS
        // ========================================
        
        // Lease 1 Payments (Unit A101 - KES 50,000/month)
        // Deposit payment
        Payment::create([
            'id' => Str::uuid(),
            'tenant_id' => $tenant1->id,
            'lease_id' => $lease1->id,
            'payment_type' => 'deposit',
            'amount' => 50000.00,
            'payment_method' => 'mpesa',
            'status' => 'completed',
            'payment_date' => now()->subMonths(3),
            'transaction_id' => 'MPESA' . strtoupper(Str::random(10)),
            'processed_at' => now()->subMonths(3),
        ]);

        // Month 1 rent
        Payment::create([
            'id' => Str::uuid(),
            'tenant_id' => $tenant1->id,
            'lease_id' => $lease1->id,
            'payment_type' => 'rent',
            'amount' => 50000.00,
            'payment_method' => 'mpesa',
            'status' => 'completed',
            'payment_date' => now()->subMonths(3),
            'transaction_id' => 'MPESA' . strtoupper(Str::random(10)),
            'processed_at' => now()->subMonths(3),
        ]);

        // Month 2 rent
        Payment::create([
            'id' => Str::uuid(),
            'tenant_id' => $tenant1->id,
            'lease_id' => $lease1->id,
            'payment_type' => 'rent',
            'amount' => 50000.00,
            'payment_method' => 'bank_transfer',
            'status' => 'completed',
            'payment_date' => now()->subMonths(2),
            'transaction_id' => 'BANK' . strtoupper(Str::random(10)),
            'processed_at' => now()->subMonths(2),
        ]);

        // Month 3 rent (current month - pending)
        Payment::create([
            'id' => Str::uuid(),
            'tenant_id' => $tenant1->id,
            'lease_id' => $lease1->id,
            'payment_type' => 'rent',
            'amount' => 50000.00,
            'payment_method' => 'mpesa',
            'status' => 'pending',
            'payment_date' => now(),
            'transaction_id' => 'MPESA' . strtoupper(Str::random(10)),
        ]);

        // Lease 2 Payments (Unit A102 - KES 35,000/month)
        // Deposit payment
        Payment::create([
            'id' => Str::uuid(),
            'tenant_id' => $tenant2->id,
            'lease_id' => $lease2->id,
            'payment_type' => 'deposit',
            'amount' => 35000.00,
            'payment_method' => 'mpesa',
            'status' => 'completed',
            'payment_date' => now()->subMonths(2),
            'transaction_id' => 'MPESA' . strtoupper(Str::random(10)),
            'processed_at' => now()->subMonths(2),
        ]);

        // Month 1 rent
        Payment::create([
            'id' => Str::uuid(),
            'tenant_id' => $tenant2->id,
            'lease_id' => $lease2->id,
            'payment_type' => 'rent',
            'amount' => 35000.00,
            'payment_method' => 'mpesa',
            'status' => 'completed',
            'payment_date' => now()->subMonths(2),
            'transaction_id' => 'MPESA' . strtoupper(Str::random(10)),
            'processed_at' => now()->subMonths(2),
        ]);

        // Month 2 rent
        Payment::create([
            'id' => Str::uuid(),
            'tenant_id' => $tenant2->id,
            'lease_id' => $lease2->id,
            'payment_type' => 'rent',
            'amount' => 35000.00,
            'payment_method' => 'bank_transfer',
            'status' => 'completed',
            'payment_date' => now()->subMonths(1),
            'transaction_id' => 'BANK' . strtoupper(Str::random(10)),
            'processed_at' => now()->subMonths(1),
        ]);

        // Lease 3 Payments (Unit B201 - KES 60,000/month)
        // Deposit payment
        Payment::create([
            'id' => Str::uuid(),
            'tenant_id' => $tenant3->id,
            'lease_id' => $lease3->id,
            'payment_type' => 'deposit',
            'amount' => 60000.00,
            'payment_method' => 'mpesa',
            'status' => 'completed',
            'payment_date' => now()->subMonths(4),
            'transaction_id' => 'MPESA' . strtoupper(Str::random(10)),
            'processed_at' => now()->subMonths(4),
        ]);

        // Month 1-4 rent (all completed)
        for ($i = 4; $i >= 1; $i--) {
            Payment::create([
                'id' => Str::uuid(),
                'tenant_id' => $tenant3->id,
                'lease_id' => $lease3->id,
                'payment_type' => 'rent',
                'amount' => 60000.00,
                'payment_method' => $i % 2 === 0 ? 'mpesa' : 'bank_transfer',
                'status' => 'completed',
                'payment_date' => now()->subMonths($i),
                'transaction_id' => ($i % 2 === 0 ? 'MPESA' : 'BANK') . strtoupper(Str::random(10)),
                'processed_at' => now()->subMonths($i),
            ]);
        }

        // ========================================
        // 10. CREATE BALANCES
        // ========================================
        
        // Company Balance
        // Total collected: 3 deposits + 9 rent payments = (50k + 35k + 60k) + (50k*3 + 35k*2 + 60k*4)
        // = 145,000 + (150,000 + 70,000 + 240,000) = 145,000 + 460,000 = 605,000
        // Platform fees (10%): 60,500
        // Available balance: 605,000 - 60,500 = 544,500
        
        CompanyBalance::create([
            'id' => Str::uuid(),
            'tenant_id' => $tenant->id,
            'available_balance' => 544500.00,
            'pending_balance' => 50000.00, // One pending payment
            'platform_fees_collected' => 60500.00,
            'deposits_held' => 145000.00,
            'reservations_collected' => 0.00,
            'total_collected' => 605000.00,
            'total_withdrawn' => 0.00,
            'total_earned' => 605000.00,
            'total_cashed_out' => 0.00,
            'total_platform_fees_paid' => 60500.00,
        ]);

        // Owner 1 Balance (John Kamau - Green Valley Apartments)
        // Units A101 + A102: (50k*3 + 35k*2) = 220,000
        // Platform fees (10%): 22,000
        // Amount owed: 220,000 - 22,000 = 198,000
        
        OwnerBalance::create([
            'id' => Str::uuid(),
            'tenant_id' => $tenant->id,
            'property_owner_id' => $owner1->id,
            'amount_owed' => 198000.00,
            'amount_paid' => 0.00,
            'total_rent_collected' => 220000.00,
            'total_platform_fees' => 22000.00,
            'total_expenses' => 0.00,
            'total_earned' => 198000.00,
            'total_paid' => 0.00,
        ]);

        // Owner 2 Balance (Jane Wanjiru - Westlands Towers)
        // Unit B201: 60k*4 = 240,000
        // Platform fees (10%): 24,000
        // Amount owed: 240,000 - 24,000 = 216,000
        
        OwnerBalance::create([
            'id' => Str::uuid(),
            'tenant_id' => $tenant->id,
            'property_owner_id' => $owner2->id,
            'amount_owed' => 216000.00,
            'amount_paid' => 0.00,
            'total_rent_collected' => 240000.00,
            'total_platform_fees' => 24000.00,
            'total_expenses' => 0.00,
            'total_earned' => 216000.00,
            'total_paid' => 0.00,
        ]);

        $this->command->info('✅ Week 13 Comprehensive Test Data Created Successfully!');
        $this->command->info('');
        $this->command->info('📊 Summary:');
        $this->command->info('  - Tenant Company: Prime Properties Kenya');
        $this->command->info('  - Users: 5 (1 admin, 1 manager, 3 tenants)');
        $this->command->info('  - Property Owners: 2');
        $this->command->info('  - Properties: 2 (both approved)');
        $this->command->info('  - Units: 6 (3 occupied, 2 vacant, 1 under maintenance)');
        $this->command->info('  - Leases: 3 (all active)');
        $this->command->info('  - Payments: 13 total (12 completed, 1 pending)');
        $this->command->info('  - Company Balance: KES 544,500 available');
        $this->command->info('  - Owner Balances: KES 414,000 total owed');
        $this->command->info('');
        $this->command->info('🔑 Test Credentials:');
        $this->command->info('  Admin: admin@primepropertieskenya.com / password123');
        $this->command->info('  Manager: manager1@primepropertieskenya.com / password123');
        $this->command->info('  Tenant 1: tenant1@example.com / password123');
        $this->command->info('  Tenant 2: tenant2@example.com / password123');
        $this->command->info('  Tenant 3: tenant3@example.com / password123');
    }
}
