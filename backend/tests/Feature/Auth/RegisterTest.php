<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Tenant;
use App\Models\PropertyOwner;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function property_owner_can_register()
    {
        $response = $this->postJson('/api/auth/register', [
            'company_name' => 'ABC Properties',
            'name' => 'John Doe',
            'email' => 'john@abcproperties.com',
            'phone' => '+254712345678',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        if ($response->status() !== 201) {
            dump($response->json());
        }
        
        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'user' => [
                        'id',
                        'name',
                        'email',
                        'role',
                    ],
                    'token',
                    'token_type',
                ],
            ])
            ->assertJson([
                'success' => true,
                'message' => 'Registration successful. Welcome to RMS!',
            ]);

        // Verify tenant was created
        $this->assertDatabaseHas('tenants', [
            'company_name' => 'ABC Properties',
            'admin_email' => 'john@abcproperties.com',
        ]);

        // Verify user was created
        $this->assertDatabaseHas('users', [
            'email' => 'john@abcproperties.com',
            'role' => 'property_owner',
        ]);

        // Verify property owner record was created
        $this->assertDatabaseHas('property_owners', [
            'email' => 'john@abcproperties.com',
        ]);

        // Verify company balance was created
        $tenant = Tenant::where('company_name', 'ABC Properties')->first();
        $this->assertDatabaseHas('company_balances', [
            'tenant_id' => $tenant->id,
        ]);
    }

    /** @test */
    public function registration_requires_all_fields()
    {
        $response = $this->postJson('/api/auth/register', []);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'success',
                'message',
                'errors' => [
                    'company_name',
                    'name',
                    'email',
                    'phone',
                    'password',
                ],
            ]);
    }

    /** @test */
    public function registration_requires_unique_email()
    {
        // Create existing user
        $tenant = Tenant::create([
            'company_name' => 'Existing Company',
            'admin_email' => 'admin@existing.com',
            'contact_email' => 'existing@example.com',
            'contact_phone' => '+254712345678',
            'pricing_model' => 'payment_processing',
            'status' => 'active',
        ]);

        User::create([
            'tenant_id' => $tenant->id,
            'name' => 'Existing User',
            'email' => 'existing@example.com',
            'phone' => '+254712345678',
            'password_hash' => bcrypt('password123'),
            'role' => 'property_owner',
            'account_type' => 'property_owner',
            'status' => 'active',
        ]);

        $response = $this->postJson('/api/auth/register', [
            'company_name' => 'New Company',
            'name' => 'New User',
            'email' => 'existing@example.com',
            'phone' => '+254700000000',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'success',
                'message',
                'errors' => [
                    'email',
                ],
            ]);
    }

    /** @test */
    public function registration_requires_password_confirmation()
    {
        $response = $this->postJson('/api/auth/register', [
            'company_name' => 'ABC Properties',
            'name' => 'John Doe',
            'email' => 'john@abcproperties.com',
            'phone' => '+254712345678',
            'password' => 'password123',
            'password_confirmation' => 'differentpassword',
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'success',
                'message',
                'errors' => [
                    'password',
                ],
            ]);
    }

    /** @test */
    public function registration_requires_minimum_password_length()
    {
        $response = $this->postJson('/api/auth/register', [
            'company_name' => 'ABC Properties',
            'name' => 'John Doe',
            'email' => 'john@abcproperties.com',
            'phone' => '+254712345678',
            'password' => 'short',
            'password_confirmation' => 'short',
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'success',
                'message',
                'errors' => [
                    'password',
                ],
            ]);
    }

    /** @test */
    public function registered_user_receives_valid_token()
    {
        $response = $this->postJson('/api/auth/register', [
            'company_name' => 'ABC Properties',
            'name' => 'John Doe',
            'email' => 'john@abcproperties.com',
            'phone' => '+254712345678',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(201);

        $token = $response->json('data.token');
        $this->assertNotEmpty($token);

        // Verify token works
        $userResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/auth/user');

        $userResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);
    }
}
