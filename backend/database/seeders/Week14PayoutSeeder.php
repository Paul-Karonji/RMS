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
use App\Models\CashoutRequest;
use App\Models\OwnerPayment;
use App\Models\PlatformUser;
use Illuminate\Support\Str;

class Week14PayoutSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        echo "\n🚀 Starting Week 14 Payout System Seeder...\n\n";

        // ========================================
        // 1. CREATE PLATFORM OWNER
        // ========================================
        
        $platformOwner = PlatformUser::create([
            'id' => Str::uuid(),
            'name' => 'Platform Admin',
            'email' => 'platform@rms.com',
            'password_hash' => bcrypt('password123'),
            'role' => 'platform_owner',
        ]);
        
        echo "✅ Platform Owner created\n";

        // ========================================
        // 2. CREATE TENANT COMPANY
        // ========================================
        
        $tenant = Tenant::create([
            'id' => Str::uuid(),
            'company_name' => 'Nairobi Property Management',
            'admin_email' => 'info@nairobipm.com',
            'admin_phone' => '+254722000000',
            'pricing_model' => 'percentage',
            'status' => 'active',
            'cashout_fee_percentage' => 3.00,
            'min_cashout_amount' => 5000.00,
        ]);
        
        echo "✅ Tenant Company created: {$tenant->company_name}\n";

        // ========================================
        // 3. CREATE USERS
        // ========================================
        
        $admin = User::create([
            'id' => Str::uuid(),
            'tenant_id' => $tenant->id,
            'name' => 'Admin User',
            'email' => 'admin@nairobipm.com',
            'password' => bcrypt('password123'),
            'role' => 'company_admin',
        ]);
        
        echo "✅ Admin User created\n";

        // ========================================
        // 4. CREATE PROPERTY OWNERS
        // ========================================
        
        $owner1 = PropertyOwner::create([
            'id' => Str::uuid(),
            'tenant_id' => $tenant->id,
            'owner_name' => 'David Mwangi',
            'email' => 'david@example.com',
            'phone' => '+254733111111',
            'status' => 'active',
        ]);
        
        $owner2 = PropertyOwner::create([
            'id' => Str::uuid(),
            'tenant_id' => $tenant->id,
            'owner_name' => 'Sarah Njeri',
            'email' => 'sarah@example.com',
            'phone' => '+254733222222',
            'status' => 'active',
        ]);
        
        echo "✅ 2 Property Owners created\n";

        // ========================================
        // 5. CREATE PROPERTIES
        // ========================================
        
        $property1 = Property::create([
            'id' => Str::uuid(),
            'tenant_id' => $tenant->id,
            'property_owner_id' => $owner1->id,
            'property_name' => 'Kilimani Heights',
            'property_type' => 'apartment',
            'address' => '789 Kilimani Road',
            'city' => 'Nairobi',
            'total_units' => 2,
            'status' => 'approved',
            'approved_by' => $admin->id,
            'approved_at' => now()->subDays(60),
        ]);
        
        $property2 = Property::create([
            'id' => Str::uuid(),
            'tenant_id' => $tenant->id,
            'property_owner_id' => $owner2->id,
            'property_name' => 'Westlands Plaza',
            'property_type' => 'apartment',
            'address' => '456 Westlands Avenue',
            'city' => 'Nairobi',
            'total_units' => 1,
            'status' => 'approved',
            'approved_by' => $admin->id,
            'approved_at' => now()->subDays(50),
        ]);
        
        echo "✅ 2 Properties created\n";

        // ========================================
        // 6. CREATE UNITS
        // ========================================
        
        $unit1 = Unit::create([
            'id' => Str::uuid(),
            'property_id' => $property1->id,
            'unit_number' => 'K101',
            'unit_type' => '2BR',
            'bedrooms' => 2,
            'bathrooms' => 2,
            'size_sqft' => 900,
            'monthly_rent' => 60000.00,
            'deposit_amount' => 60000.00,
            'status' => 'occupied',
        ]);
        
        $unit2 = Unit::create([
            'id' => Str::uuid(),
            'property_id' => $property1->id,
            'unit_number' => 'K102',
            'unit_type' => '3BR',
            'bedrooms' => 3,
            'bathrooms' => 2,
            'size_sqft' => 1200,
            'monthly_rent' => 80000.00,
            'deposit_amount' => 80000.00,
            'status' => 'occupied',
        ]);
        
        $unit3 = Unit::create([
            'id' => Str::uuid(),
            'property_id' => $property2->id,
            'unit_number' => 'W201',
            'unit_type' => '2BR',
            'bedrooms' => 2,
            'bathrooms' => 2,
            'size_sqft' => 850,
            'monthly_rent' => 55000.00,
            'deposit_amount' => 55000.00,
            'status' => 'occupied',
        ]);
        
        echo "✅ 3 Units created\n";

        // ========================================
        // 7. CREATE TENANTS (RENTERS) & LEASES
        // ========================================
        
        $tenant1 = User::create([
            'id' => Str::uuid(),
            'tenant_id' => $tenant->id,
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('password123'),
            'role' => 'tenant',
        ]);
        
        $lease1 = Lease::create([
            'id' => Str::uuid(),
            'tenant_id' => $tenant1->id,
            'property_id' => $property1->id,
            'unit_id' => $unit1->id,
            'property_owner_id' => $owner1->id,
            'start_date' => now()->subMonths(4),
            'end_date' => now()->addMonths(8),
            'monthly_rent' => 60000.00,
            'deposit_amount' => 60000.00,
            'status' => 'active',
            'created_by' => $admin->id,
        ]);
        
        $tenant2 = User::create([
            'id' => Str::uuid(),
            'tenant_id' => $tenant->id,
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
            'password' => bcrypt('password123'),
            'role' => 'tenant',
        ]);
        
        $lease2 = Lease::create([
            'id' => Str::uuid(),
            'tenant_id' => $tenant2->id,
            'property_id' => $property1->id,
            'unit_id' => $unit2->id,
            'property_owner_id' => $owner1->id,
            'start_date' => now()->subMonths(3),
            'end_date' => now()->addMonths(9),
            'monthly_rent' => 80000.00,
            'deposit_amount' => 80000.00,
            'status' => 'active',
            'created_by' => $admin->id,
        ]);
        
        $tenant3 = User::create([
            'id' => Str::uuid(),
            'tenant_id' => $tenant->id,
            'name' => 'Mike Johnson',
            'email' => 'mike@example.com',
            'password' => bcrypt('password123'),
            'role' => 'tenant',
        ]);
        
        $lease3 = Lease::create([
            'id' => Str::uuid(),
            'tenant_id' => $tenant3->id,
            'property_id' => $property2->id,
            'unit_id' => $unit3->id,
            'property_owner_id' => $owner2->id,
            'start_date' => now()->subMonths(2),
            'end_date' => now()->addMonths(10),
            'monthly_rent' => 55000.00,
            'deposit_amount' => 55000.00,
            'status' => 'active',
            'created_by' => $admin->id,
        ]);
        
        echo "✅ 3 Tenants and 3 Leases created\n";

        // ========================================
        // 8. CREATE PAYMENTS (Rent collected)
        // ========================================
        
        $totalCollected = 0;
        $platformFeesCollected = 0;
        
        // Tenant 1 - 4 months of rent
        for ($i = 4; $i >= 1; $i--) {
            $payment = Payment::create([
                'id' => Str::uuid(),
                'tenant_id' => $tenant1->id,
                'lease_id' => $lease1->id,
                'payment_type' => 'rent',
                'amount' => 60000.00,
                'payment_method' => 'mpesa',
                'status' => 'completed',
                'payment_date' => now()->subMonths($i),
                'transaction_id' => 'MPESA' . strtoupper(Str::random(10)),
            ]);
            $totalCollected += 60000.00;
            $platformFeesCollected += 60000.00 * 0.03; // 3% platform fee
        }
        
        // Tenant 2 - 3 months of rent
        for ($i = 3; $i >= 1; $i--) {
            $payment = Payment::create([
                'id' => Str::uuid(),
                'tenant_id' => $tenant2->id,
                'lease_id' => $lease2->id,
                'payment_type' => 'rent',
                'amount' => 80000.00,
                'payment_method' => 'mpesa',
                'status' => 'completed',
                'payment_date' => now()->subMonths($i),
                'transaction_id' => 'MPESA' . strtoupper(Str::random(10)),
            ]);
            $totalCollected += 80000.00;
            $platformFeesCollected += 80000.00 * 0.03;
        }
        
        // Tenant 3 - 2 months of rent
        for ($i = 2; $i >= 1; $i--) {
            $payment = Payment::create([
                'id' => Str::uuid(),
                'tenant_id' => $tenant3->id,
                'lease_id' => $lease3->id,
                'payment_type' => 'rent',
                'amount' => 55000.00,
                'payment_method' => 'bank_transfer',
                'status' => 'completed',
                'payment_date' => now()->subMonths($i),
                'transaction_id' => 'BANK' . strtoupper(Str::random(10)),
            ]);
            $totalCollected += 55000.00;
            $platformFeesCollected += 55000.00 * 0.03;
        }
        
        echo "✅ 9 Payments created (Total: KES " . number_format($totalCollected, 2) . ")\n";

        // ========================================
        // 9. CREATE COMPANY BALANCE
        // ========================================
        
        $availableBalance = $totalCollected - $platformFeesCollected;
        
        $companyBalance = CompanyBalance::create([
            'id' => Str::uuid(),
            'tenant_id' => $tenant->id,
            'available_balance' => $availableBalance,
            'platform_fees_collected' => $platformFeesCollected,
            'total_collected' => $totalCollected,
            'total_cashed_out' => 0.00,
            'total_platform_fees_paid' => 0.00,
        ]);
        
        echo "✅ Company Balance created (Available: KES " . number_format($availableBalance, 2) . ")\n";

        // ========================================
        // 10. CREATE OWNER BALANCES
        // ========================================
        
        // Owner 1: 2 units, 7 months total rent
        $owner1TotalRent = (60000 * 4) + (80000 * 3); // 480,000
        $owner1Balance = OwnerBalance::create([
            'id' => Str::uuid(),
            'tenant_id' => $tenant->id,
            'property_owner_id' => $owner1->id,
            'amount_owed' => $owner1TotalRent,
            'amount_paid' => 0.00,
            'total_rent_collected' => $owner1TotalRent,
            'total_paid' => 0.00,
        ]);
        
        // Owner 2: 1 unit, 2 months rent
        $owner2TotalRent = 55000 * 2; // 110,000
        $owner2Balance = OwnerBalance::create([
            'id' => Str::uuid(),
            'tenant_id' => $tenant->id,
            'property_owner_id' => $owner2->id,
            'amount_owed' => $owner2TotalRent,
            'amount_paid' => 0.00,
            'total_rent_collected' => $owner2TotalRent,
            'total_paid' => 0.00,
        ]);
        
        echo "✅ 2 Owner Balances created\n";
        echo "   - Owner 1 owed: KES " . number_format($owner1TotalRent, 2) . "\n";
        echo "   - Owner 2 owed: KES " . number_format($owner2TotalRent, 2) . "\n";

        // ========================================
        // 11. CREATE CASHOUT REQUESTS
        // ========================================
        
        // Pending cashout request
        $cashout1 = CashoutRequest::create([
            'id' => Str::uuid(),
            'tenant_id' => $tenant->id,
            'amount' => 100000.00,
            'fee_amount' => 3000.00, // 3%
            'net_amount' => 97000.00,
            'status' => 'pending',
            'payment_method' => 'bank_transfer',
            'payment_details' => json_encode([
                'account_number' => '1234567890',
                'bank_name' => 'Equity Bank',
                'account_name' => 'Nairobi Property Management',
            ]),
        ]);
        
        // Approved cashout request
        $cashout2 = CashoutRequest::create([
            'id' => Str::uuid(),
            'tenant_id' => $tenant->id,
            'amount' => 50000.00,
            'fee_amount' => 1500.00,
            'net_amount' => 48500.00,
            'status' => 'approved',
            'payment_method' => 'mpesa',
            'payment_details' => json_encode([
                'phone_number' => '254722000000',
            ]),
            'approved_by' => $platformOwner->id,
            'approved_at' => now()->subDays(2),
        ]);
        
        // Processed (completed) cashout request
        $cashout3 = CashoutRequest::create([
            'id' => Str::uuid(),
            'tenant_id' => $tenant->id,
            'amount' => 75000.00,
            'fee_amount' => 2250.00,
            'net_amount' => 72750.00,
            'status' => 'processed',
            'payment_method' => 'bank_transfer',
            'payment_details' => json_encode([
                'account_number' => '1234567890',
                'bank_name' => 'Equity Bank',
                'account_name' => 'Nairobi Property Management',
            ]),
            'approved_by' => $platformOwner->id,
            'approved_at' => now()->subDays(10),
            'transaction_id' => 'CASHOUT' . strtoupper(Str::random(10)),
            'processed_at' => now()->subDays(9),
        ]);
        
        // Rejected cashout request
        $cashout4 = CashoutRequest::create([
            'id' => Str::uuid(),
            'tenant_id' => $tenant->id,
            'amount' => 200000.00,
            'fee_amount' => 6000.00,
            'net_amount' => 194000.00,
            'status' => 'rejected',
            'payment_method' => 'bank_transfer',
            'payment_details' => json_encode([
                'account_number' => '9999999999',
                'bank_name' => 'Unknown Bank',
                'account_name' => 'Test Account',
            ]),
            'rejected_by' => $platformOwner->id,
            'rejected_at' => now()->subDays(5),
            'rejection_reason' => 'Invalid bank account details provided',
        ]);
        
        echo "✅ 4 Cashout Requests created (pending, approved, processed, rejected)\n";

        // ========================================
        // 12. CREATE OWNER PAYMENTS
        // ========================================
        
        // Payment to Owner 1
        $ownerPayment1 = OwnerPayment::create([
            'id' => Str::uuid(),
            'tenant_id' => $tenant->id,
            'property_owner_id' => $owner1->id,
            'amount' => 200000.00,
            'payment_date' => now()->subDays(15),
            'payment_method' => 'bank_transfer',
            'transaction_id' => 'OWNERPAY' . strtoupper(Str::random(10)),
            'notes' => 'Partial payment for rent collected',
            'created_by' => $admin->id,
        ]);
        
        // Update owner 1 balance
        $owner1Balance->update([
            'amount_paid' => 200000.00,
            'amount_owed' => $owner1TotalRent - 200000.00,
            'total_paid' => 200000.00,
            'last_payment_date' => now()->subDays(15),
            'last_payment_amount' => 200000.00,
        ]);
        
        // Payment to Owner 2
        $ownerPayment2 = OwnerPayment::create([
            'id' => Str::uuid(),
            'tenant_id' => $tenant->id,
            'property_owner_id' => $owner2->id,
            'amount' => 55000.00,
            'payment_date' => now()->subDays(10),
            'payment_method' => 'mpesa',
            'transaction_id' => 'MPESA' . strtoupper(Str::random(10)),
            'notes' => 'Payment for first month rent',
            'created_by' => $admin->id,
        ]);
        
        // Update owner 2 balance
        $owner2Balance->update([
            'amount_paid' => 55000.00,
            'amount_owed' => $owner2TotalRent - 55000.00,
            'total_paid' => 55000.00,
            'last_payment_date' => now()->subDays(10),
            'last_payment_amount' => 55000.00,
        ]);
        
        echo "✅ 2 Owner Payments created\n";
        echo "   - Owner 1 paid: KES 200,000 (Owed: KES " . number_format($owner1TotalRent - 200000, 2) . ")\n";
        echo "   - Owner 2 paid: KES 55,000 (Owed: KES " . number_format($owner2TotalRent - 55000, 2) . ")\n";

        // ========================================
        // SUMMARY
        // ========================================
        
        echo "\n";
        echo "========================================\n";
        echo "✅ Week 14 Payout Seeder Complete!\n";
        echo "========================================\n\n";
        
        echo "📊 Summary:\n";
        echo "  - Platform Owner: 1\n";
        echo "  - Tenant Company: 1 ({$tenant->company_name})\n";
        echo "  - Users: 4 (1 admin, 3 tenants)\n";
        echo "  - Property Owners: 2\n";
        echo "  - Properties: 2\n";
        echo "  - Units: 3\n";
        echo "  - Leases: 3\n";
        echo "  - Payments: 9 (Total: KES " . number_format($totalCollected, 2) . ")\n";
        echo "  - Company Balance: KES " . number_format($availableBalance, 2) . "\n";
        echo "  - Cashout Requests: 4 (1 pending, 1 approved, 1 processed, 1 rejected)\n";
        echo "  - Owner Payments: 2 (Total: KES 255,000)\n\n";
        
        echo "🔑 Test Credentials:\n";
        echo "  Platform: platform@rms.com / password123\n";
        echo "  Admin: admin@nairobipm.com / password123\n";
        echo "  Tenant 1: john@example.com / password123\n";
        echo "  Tenant 2: jane@example.com / password123\n";
        echo "  Tenant 3: mike@example.com / password123\n\n";
    }
}
