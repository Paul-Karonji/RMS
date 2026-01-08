<?php

namespace App\Services;

use App\Models\User;
use App\Models\RentalInquiry;
use App\Notifications\TenantAccountCreated;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TenantService
{
    /**
     * Create tenant account from approved inquiry
     *
     * @param RentalInquiry $inquiry
     * @return array
     */
    public function createFromInquiry(RentalInquiry $inquiry): array
    {
        // Generate temporary password
        $temporaryPassword = $this->generateTemporaryPassword();
        
        // Create user account
        $tenant = User::create([
            'tenant_id' => auth()->user()->tenant_id,
            'name' => $inquiry->name,
            'email' => $inquiry->email,
            'phone' => $inquiry->phone,
            'password' => Hash::make($temporaryPassword),
            'role' => 'tenant',
            'must_change_password' => true,
        ]);

        // Send welcome email with credentials
        $tenant->notify(new TenantAccountCreated($temporaryPassword));

        return [
            'tenant' => $tenant,
            'credentials' => [
                'email' => $tenant->email,
                'temporary_password' => $temporaryPassword,
            ],
        ];
    }

    /**
     * Create tenant account directly (not from inquiry)
     *
     * @param array $data
     * @return array
     */
    public function createDirect(array $data): array
    {
        // Generate temporary password
        $temporaryPassword = $this->generateTemporaryPassword();
        
        // Create user account
        $tenant = User::create([
            'tenant_id' => auth()->user()->tenant_id,
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'password' => Hash::make($temporaryPassword),
            'role' => 'tenant',
            'id_number' => $data['id_number'] ?? null,
            'emergency_contact_name' => $data['emergency_contact_name'] ?? null,
            'emergency_contact_phone' => $data['emergency_contact_phone'] ?? null,
            'occupation' => $data['occupation'] ?? null,
            'employer' => $data['employer'] ?? null,
            'must_change_password' => true,
        ]);

        // Send welcome email with credentials
        $tenant->notify(new TenantAccountCreated($temporaryPassword));

        return [
            'tenant' => $tenant,
            'credentials' => [
                'email' => $tenant->email,
                'temporary_password' => $temporaryPassword,
            ],
        ];
    }

    /**
     * Generate a secure temporary password
     *
     * @return string
     */
    private function generateTemporaryPassword(): string
    {
        // Generate 12-character password with mixed case, numbers, and symbols
        $uppercase = 'ABCDEFGHJKLMNPQRSTUVWXYZ';
        $lowercase = 'abcdefghjkmnpqrstuvwxyz';
        $numbers = '23456789';
        $symbols = '!@#$%&*';

        $password = '';
        $password .= $uppercase[random_int(0, strlen($uppercase) - 1)];
        $password .= $uppercase[random_int(0, strlen($uppercase) - 1)];
        $password .= $lowercase[random_int(0, strlen($lowercase) - 1)];
        $password .= $lowercase[random_int(0, strlen($lowercase) - 1)];
        $password .= $numbers[random_int(0, strlen($numbers) - 1)];
        $password .= $numbers[random_int(0, strlen($numbers) - 1)];
        $password .= $symbols[random_int(0, strlen($symbols) - 1)];
        
        // Fill remaining characters
        $allChars = $uppercase . $lowercase . $numbers . $symbols;
        for ($i = 0; $i < 5; $i++) {
            $password .= $allChars[random_int(0, strlen($allChars) - 1)];
        }

        // Shuffle the password
        return str_shuffle($password);
    }
}
