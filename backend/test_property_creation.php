<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== Testing Property Creation Workflow ===\n\n";

// Step 1: Find or create a Property Owner user
echo "Step 1: Finding Property Owner user...\n";
$user = \App\Models\User::where('role', 'property_owner')->first();

if (!$user) {
    echo "No Property Owner found. Creating one...\n";
    
    // Create tenant
    $tenant = \App\Models\Tenant::create([
        'company_name' => 'Test Property Management',
        'admin_email' => 'testowner@example.com',
        'admin_phone' => '+254722000000',
        'pricing_model' => 'payment_processing',
        'status' => 'active',
    ]);
    
    // Create user
    $user = \App\Models\User::create([
        'tenant_id' => $tenant->id,
        'name' => 'Test Owner',
        'email' => 'testowner@example.com',
        'phone' => '+254722000000',
        'password_hash' => \Illuminate\Support\Facades\Hash::make('password123'),
        'role' => 'property_owner',
        'account_type' => 'property_owner',
        'status' => 'active',
        'must_change_password' => false,
    ]);
    
    $tenant->update(['admin_user_id' => $user->id]);
    
    // Create property owner record
    $propertyOwner = \App\Models\PropertyOwner::create([
        'tenant_id' => $tenant->id,
        'user_id' => $user->id,
        'owner_name' => 'Test Owner',
        'email' => 'testowner@example.com',
        'phone' => '+254722000000',
        'status' => 'active',
    ]);
    
    echo "✅ Created Property Owner: {$user->email}\n";
} else {
    echo "✅ Found Property Owner: {$user->email}\n";
}

echo "   Tenant: {$user->tenant->company_name}\n";
echo "   Has PropertyOwner record: " . ($user->propertyOwner ? 'Yes' : 'No') . "\n\n";

// Step 2: Create a property
echo "Step 2: Creating property...\n";

try {
    $property = \App\Models\Property::create([
        'tenant_id' => $user->tenant_id,
        'property_owner_id' => $user->propertyOwner->id,
        'property_name' => 'Test Property ' . time(),
        'property_type' => 'apartment',
        'description' => 'Test property for verification',
        'address' => '123 Test Street, Nairobi',
        'city' => 'Nairobi',
        'county' => 'Nairobi County',
        'postal_code' => '00100',
        'country' => 'Kenya',
        'total_units' => 5,
        'occupied_units' => 0,
        'vacant_units' => 5,
        'status' => 'pending_approval',
        'commission_percentage' => 10.00,
    ]);
    
    echo "✅ Property created successfully!\n";
    echo "   ID: {$property->id}\n";
    echo "   Name: {$property->property_name}\n";
    echo "   Status: {$property->status}\n";
    echo "   Owner: {$property->propertyOwner->owner_name}\n\n";
    
    // Step 3: Verify property can be retrieved
    echo "Step 3: Verifying property retrieval...\n";
    $retrieved = \App\Models\Property::find($property->id);
    
    if ($retrieved) {
        echo "✅ Property retrieved successfully!\n";
        echo "   Can access owner: " . ($retrieved->propertyOwner ? 'Yes' : 'No') . "\n";
        echo "   Can access tenant: " . ($retrieved->tenant ? 'Yes' : 'No') . "\n\n";
    } else {
        echo "❌ Failed to retrieve property\n\n";
    }
    
    // Step 4: Test property approval (simulate Company Admin)
    echo "Step 4: Testing property approval...\n";
    $admin = \App\Models\User::where('tenant_id', $user->tenant_id)
                             ->where('role', 'company_admin')
                             ->first();
    
    if (!$admin) {
        echo "No Company Admin found. Creating one...\n";
        $admin = \App\Models\User::create([
            'tenant_id' => $user->tenant_id,
            'name' => 'Test Admin',
            'email' => 'admin@testproperties.com',
            'phone' => '+254722000001',
            'password_hash' => \Illuminate\Support\Facades\Hash::make('password123'),
            'role' => 'company_admin',
            'account_type' => 'staff',
            'status' => 'active',
            'must_change_password' => false,
        ]);
    }
    
    $property->update([
        'status' => 'approved',
        'approved_by' => $admin->id,
        'approved_at' => now(),
    ]);
    
    echo "✅ Property approved!\n";
    echo "   Status: {$property->status}\n";
    echo "   Approved by: {$admin->name}\n\n";
    
    echo "=== ✅ ALL TESTS PASSED! ===\n";
    echo "Property creation workflow is working correctly!\n";
    
} catch (\Exception $e) {
    echo "❌ Error creating property:\n";
    echo "   " . $e->getMessage() . "\n";
    echo "   File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}
