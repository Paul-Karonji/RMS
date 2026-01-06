<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions for Property Management
        $propertyPermissions = [
            'view properties',
            'create properties',
            'update properties',
            'delete properties',
            'approve properties',
            'reject properties',
            'assign property manager',
        ];

        // Create permissions for Unit Management
        $unitPermissions = [
            'view units',
            'create units',
            'update units',
            'delete units',
        ];

        // Create permissions for Lease Management
        $leasePermissions = [
            'view leases',
            'create leases',
            'update leases',
            'delete leases',
            'approve leases',
        ];

        // Create permissions for Payment Management
        $paymentPermissions = [
            'view payments',
            'record payments',
            'process refunds',
        ];

        // Create permissions for Maintenance Management
        $maintenancePermissions = [
            'view maintenance',
            'create maintenance',
            'update maintenance',
            'assign maintenance',
        ];

        // Create permissions for Reports
        $reportPermissions = [
            'view reports',
            'export reports',
        ];

        // Create permissions for User Management
        $userPermissions = [
            'view users',
            'create users',
            'update users',
            'delete users',
        ];

        // Combine all permissions
        $allPermissions = array_merge(
            $propertyPermissions,
            $unitPermissions,
            $leasePermissions,
            $paymentPermissions,
            $maintenancePermissions,
            $reportPermissions,
            $userPermissions
        );

        // Create all permissions
        foreach ($allPermissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create roles and assign permissions

        // 1. Company Admin - Full access to everything within their tenant
        $companyAdmin = Role::create(['name' => 'company_admin']);
        $companyAdmin->givePermissionTo($allPermissions);

        // 2. Company Staff - Limited access (no user management, no approvals)
        $companyStaff = Role::create(['name' => 'company_staff']);
        $companyStaff->givePermissionTo([
            'view properties',
            'view units',
            'create units',
            'update units',
            'view leases',
            'create leases',
            'update leases',
            'view payments',
            'record payments',
            'view maintenance',
            'create maintenance',
            'update maintenance',
            'view reports',
        ]);

        // 3. Property Owner - View-only access to their properties
        $propertyOwner = Role::create(['name' => 'property_owner']);
        $propertyOwner->givePermissionTo([
            'view properties',
            'create properties',
            'update properties',
            'view units',
            'view leases',
            'view payments',
            'view maintenance',
            'view reports',
        ]);

        // 4. Property Manager - Manage assigned properties
        $propertyManager = Role::create(['name' => 'property_manager']);
        $propertyManager->givePermissionTo([
            'view properties',
            'view units',
            'create units',
            'update units',
            'view leases',
            'create leases',
            'update leases',
            'view payments',
            'record payments',
            'view maintenance',
            'create maintenance',
            'update maintenance',
            'assign maintenance',
            'view reports',
        ]);

        $this->command->info('Roles and permissions created successfully!');
        $this->command->info('Created roles: company_admin, company_staff, property_owner, property_manager');
        $this->command->info('Created ' . count($allPermissions) . ' permissions');
    }
}
