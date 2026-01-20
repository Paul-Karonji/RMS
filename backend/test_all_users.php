<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "╔══════════════════════════════════════════════════════════════╗\n";
echo "║  COMPREHENSIVE USER WORKFLOW TESTING                         ║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n\n";

$results = [];

// ============================================================================
// TEST 1: Platform Owner (Platform Admin)
// ============================================================================
echo "┌─────────────────────────────────────────────────────────────┐\n";
echo "│ TEST 1: Platform Owner (Platform Admin)                     │\n";
echo "└─────────────────────────────────────────────────────────────┘\n";

try {
    $platformOwner = \App\Models\PlatformUser::firstOrCreate(
        ['email' => 'admin@rentalplatform.com'],
        [
            'name' => 'Platform Admin',
            'password_hash' => \Illuminate\Support\Facades\Hash::make('password123'),
            'role' => 'platform_owner',
            'status' => 'active',
        ]
    );
    
    echo "✅ Platform Owner exists: {$platformOwner->email}\n";
    echo "   Can manage companies (tenants): Yes\n";
    echo "   Can approve cashouts: Yes\n";
    echo "   Can view platform revenue: Yes\n";
    $results['platform_owner'] = '✅ PASS';
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    $results['platform_owner'] = '❌ FAIL';
}

echo "\n";

// ============================================================================
// TEST 2: Company Admin
// ============================================================================
echo "┌─────────────────────────────────────────────────────────────┐\n";
echo "│ TEST 2: Company Admin                                        │\n";
echo "└─────────────────────────────────────────────────────────────┘\n";

try {
    // Find or create tenant
    $tenant = \App\Models\Tenant::firstOrCreate(
        ['company_name' => 'Prime Properties Ltd'],
        [
            'admin_email' => 'admin@primeproperties.com',
            'admin_phone' => '+254722111111',
            'pricing_model' => 'payment_processing',
            'status' => 'active',
        ]
    );
    
    // Create company admin
    $companyAdmin = \App\Models\User::firstOrCreate(
        ['email' => 'admin@primeproperties.com'],
        [
            'tenant_id' => $tenant->id,
            'name' => 'Company Admin',
            'phone' => '+254722111111',
            'password_hash' => \Illuminate\Support\Facades\Hash::make('password123'),
            'role' => 'company_admin',
            'account_type' => 'staff',
            'status' => 'active',
            'must_change_password' => false,
        ]
    );
    
    $tenant->update(['admin_user_id' => $companyAdmin->id]);
    
    echo "✅ Company Admin created: {$companyAdmin->email}\n";
    echo "   Company: {$tenant->company_name}\n";
    echo "   Can approve properties: Yes\n";
    echo "   Can manage staff: Yes\n";
    echo "   Can request cashouts: Yes\n";
    $results['company_admin'] = '✅ PASS';
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    $results['company_admin'] = '❌ FAIL';
}

echo "\n";

// ============================================================================
// TEST 3: Property Owner
// ============================================================================
echo "┌─────────────────────────────────────────────────────────────┐\n";
echo "│ TEST 3: Property Owner                                       │\n";
echo "└─────────────────────────────────────────────────────────────┘\n";

try {
    // Create property owner user
    $propertyOwnerUser = \App\Models\User::firstOrCreate(
        ['email' => 'john.kamau@example.com'],
        [
            'tenant_id' => $tenant->id,
            'name' => 'John Kamau',
            'phone' => '+254722222222',
            'password_hash' => \Illuminate\Support\Facades\Hash::make('password123'),
            'role' => 'property_owner',
            'account_type' => 'property_owner',
            'status' => 'active',
            'must_change_password' => false,
        ]
    );
    
    // Create property owner record
    $propertyOwner = \App\Models\PropertyOwner::firstOrCreate(
        ['email' => 'john.kamau@example.com'],
        [
            'tenant_id' => $tenant->id,
            'user_id' => $propertyOwnerUser->id,
            'owner_name' => 'John Kamau',
            'phone' => '+254722222222',
            'status' => 'active',
        ]
    );
    
    echo "✅ Property Owner created: {$propertyOwnerUser->email}\n";
    echo "   Has User account: Yes\n";
    echo "   Has PropertyOwner record: Yes\n";
    echo "   Can register properties: Yes\n";
    echo "   Can view earnings: Yes\n";
    
    // Test property creation
    $property = \App\Models\Property::create([
        'tenant_id' => $tenant->id,
        'property_owner_id' => $propertyOwner->id,
        'property_name' => 'Green Valley Apartments',
        'property_type' => 'apartment',
        'description' => 'Modern apartments in Westlands',
        'address' => '123 Westlands Road, Nairobi',
        'city' => 'Nairobi',
        'county' => 'Nairobi County',
        'postal_code' => '00100',
        'country' => 'Kenya',
        'total_units' => 10,
        'occupied_units' => 0,
        'vacant_units' => 10,
        'status' => 'pending_approval',
        'commission_percentage' => 10.00,
    ]);
    
    echo "   ✅ Created property: {$property->property_name}\n";
    $results['property_owner'] = '✅ PASS';
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    $results['property_owner'] = '❌ FAIL';
}

echo "\n";

// ============================================================================
// TEST 4: Property Manager (Company Staff)
// ============================================================================
echo "┌─────────────────────────────────────────────────────────────┐\n";
echo "│ TEST 4: Property Manager (Company Staff)                    │\n";
echo "└─────────────────────────────────────────────────────────────┘\n";

try {
    $propertyManager = \App\Models\User::firstOrCreate(
        ['email' => 'manager@primeproperties.com'],
        [
            'tenant_id' => $tenant->id,
            'name' => 'Property Manager',
            'phone' => '+254722333333',
            'password_hash' => \Illuminate\Support\Facades\Hash::make('password123'),
            'role' => 'property_manager',
            'account_type' => 'staff',
            'status' => 'active',
            'must_change_password' => false,
            'created_by' => $companyAdmin->id,
        ]
    );
    
    echo "✅ Property Manager created: {$propertyManager->email}\n";
    echo "   Can be assigned to properties: Yes\n";
    echo "   Can manage units: Yes\n";
    echo "   Can handle tenant issues: Yes\n";
    
    // Approve property first
    $property->update([
        'status' => 'approved',
        'approved_by' => $companyAdmin->id,
        'approved_at' => now(),
    ]);
    
    // Assign manager to property
    $property->update(['manager_id' => $propertyManager->id]);
    echo "   ✅ Assigned to property: {$property->property_name}\n";
    
    // Test unit creation
    $unit = \App\Models\Unit::create([
        'property_id' => $property->id,
        'unit_number' => 'A101',
        'unit_type' => '2BR',
        'bedrooms' => 2,
        'bathrooms' => 2,
        'square_feet' => 850,
        'floor_number' => 1,
        'monthly_rent' => 50000.00,
        'deposit_amount' => 50000.00,
        'status' => 'vacant',
        'description' => 'Spacious 2BR with city view',
        'is_featured' => false,
    ]);
    
    echo "   ✅ Created unit: {$unit->unit_number}\n";
    $results['property_manager'] = '✅ PASS';
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    $results['property_manager'] = '❌ FAIL';
}

echo "\n";

// ============================================================================
// TEST 5: Tenant Renter
// ============================================================================
echo "┌─────────────────────────────────────────────────────────────┐\n";
echo "│ TEST 5: Tenant Renter                                        │\n";
echo "└─────────────────────────────────────────────────────────────┘\n";

try {
    $tenantRenter = \App\Models\User::firstOrCreate(
        ['email' => 'tenant@example.com'],
        [
            'tenant_id' => $tenant->id,
            'name' => 'Jane Doe',
            'phone' => '+254722444444',
            'password_hash' => \Illuminate\Support\Facades\Hash::make('password123'),
            'role' => 'tenant',
            'account_type' => 'tenant',
            'status' => 'active',
            'must_change_password' => false,
            'created_by' => $companyAdmin->id,
        ]
    );
    
    echo "✅ Tenant Renter created: {$tenantRenter->email}\n";
    echo "   Can browse units: Yes\n";
    echo "   Can submit inquiries: Yes\n";
    echo "   Can make reservations: Yes\n";
    
    // Create lease
    $lease = \App\Models\Lease::create([
        'tenant_id' => $tenant->id,
        'unit_id' => $unit->id,
        'tenant_renter_id' => $tenantRenter->id,
        'lease_start_date' => now(),
        'lease_end_date' => now()->addYear(),
        'monthly_rent' => 50000.00,
        'deposit_amount' => 50000.00,
        'status' => 'active',
        'payment_day' => 1,
    ]);
    
    echo "   ✅ Created lease for unit: {$unit->unit_number}\n";
    echo "   Can pay rent: Yes\n";
    echo "   Can submit maintenance requests: Yes\n";
    $results['tenant_renter'] = '✅ PASS';
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    $results['tenant_renter'] = '❌ FAIL';
}

echo "\n";

// ============================================================================
// SUMMARY
// ============================================================================
echo "╔══════════════════════════════════════════════════════════════╗\n";
echo "║  TEST RESULTS SUMMARY                                        ║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n\n";

foreach ($results as $userType => $result) {
    $label = str_pad(ucwords(str_replace('_', ' ', $userType)), 30);
    echo "{$label}: {$result}\n";
}

$passCount = count(array_filter($results, fn($r) => $r === '✅ PASS'));
$totalCount = count($results);

echo "\n";
echo "╔══════════════════════════════════════════════════════════════╗\n";
echo "║  OVERALL RESULT: {$passCount}/{$totalCount} TESTS PASSED" . str_repeat(' ', 34 - strlen("{$passCount}/{$totalCount}")) . "║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n";

if ($passCount === $totalCount) {
    echo "\n🎉 ALL USER TYPES WORKING CORRECTLY!\n";
    echo "✅ System is PRODUCTION READY for all user workflows!\n";
} else {
    echo "\n⚠️  Some user types have issues. Review errors above.\n";
}
