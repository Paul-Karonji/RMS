<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->command->info('Starting database seeding...');
        
        // Platform level data
        $this->call(PlatformUserSeeder::class);
        
        // Company level data
        $this->call(TenantSeeder::class);
        $this->call(CompanyBalanceSeeder::class);
        
        // Users
        $this->call(UserSeeder::class);
        $this->call(PropertyOwnerSeeder::class);
        $this->call(OwnerBalanceSeeder::class);
        
        // Properties and units
        $this->call(PropertySeeder::class);
        $this->call(UnitSeeder::class);
        $this->call(PropertyAmenitySeeder::class);
        $this->call(UnitPhotoSeeder::class);
        
        // Leases and tenants
        $this->call(RentalInquirySeeder::class);
        $this->call(ReservationSeeder::class);
        $this->call(LeaseSeeder::class);
        $this->call(LeaseSignatureSeeder::class);
        
        // Payments
        $this->call(PaymentMethodSeeder::class);
        $this->call(PaymentSeeder::class);
        $this->call(BalanceTransactionSeeder::class);
        
        // Operations
        $this->call(ExpenseSeeder::class);
        $this->call(MaintenanceRequestSeeder::class);
        $this->call(MaintenanceUpdateSeeder::class);
        
        // System
        $this->call(NotificationSeeder::class);
        $this->call(AuditLogSeeder::class);
        $this->call(CashoutRequestSeeder::class);
        $this->call(OwnerPaymentSeeder::class);
        
        $this->command->info('Database seeding completed!');
    }
}
