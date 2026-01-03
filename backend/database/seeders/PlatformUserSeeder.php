<?php

namespace Database\Seeders;

use App\Models\PlatformUser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class PlatformUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $platformUsers = [
            [
                'name' => 'Platform Owner',
                'email' => 'owner@rentalplatform.com',
                'password_hash' => Hash::make('password123'),
                'role' => 'platform_owner',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Platform Admin',
                'email' => 'admin@rentalplatform.com',
                'password_hash' => Hash::make('password123'),
                'role' => 'platform_admin',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($platformUsers as $user) {
            PlatformUser::create($user);
        }

        $this->command->info('Platform users seeded successfully!');
    }
}
