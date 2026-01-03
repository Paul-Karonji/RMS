<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Tenant;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

class ResetPasswordTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Create a test user with tenant.
     */
    private function createTestUser(): User
    {
        $tenant = Tenant::create([
            'company_name' => 'Test Company',
            'admin_email' => 'admin@company.com',
            'contact_email' => 'test@company.com',
            'contact_phone' => '+254712345678',
            'pricing_model' => 'payment_processing',
            'status' => 'active',
        ]);

        return User::create([
            'tenant_id' => $tenant->id,
            'name' => 'Test User',
            'email' => 'test@example.com',
            'phone' => '+254712345678',
            'password_hash' => Hash::make('oldpassword123'),
            'role' => 'company_admin',
            'account_type' => 'company_admin',
            'status' => 'active',
        ]);
    }

    /** @test */
    public function user_can_reset_password_with_valid_token()
    {
        $user = $this->createTestUser();
        $token = Password::createToken($user);

        $response = $this->postJson('/api/auth/reset-password', [
            'token' => $token,
            'email' => 'test@example.com',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Your password has been reset successfully. Please login with your new password.',
            ]);

        // Verify password was changed
        $user->refresh();
        $this->assertTrue(Hash::check('newpassword123', $user->password_hash));
    }

    /** @test */
    public function reset_password_requires_all_fields()
    {
        $response = $this->postJson('/api/auth/reset-password', []);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'success',
                'message',
                'errors' => [
                    'token',
                    'email',
                    'password',
                ],
            ]);
    }

    /** @test */
    public function reset_password_fails_with_invalid_token()
    {
        $user = $this->createTestUser();

        $response = $this->postJson('/api/auth/reset-password', [
            'token' => 'invalid-token',
            'email' => 'test@example.com',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
            ]);
    }

    /** @test */
    public function reset_password_requires_password_confirmation()
    {
        $user = $this->createTestUser();
        $token = Password::createToken($user);

        $response = $this->postJson('/api/auth/reset-password', [
            'token' => $token,
            'email' => 'test@example.com',
            'password' => 'newpassword123',
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
    public function reset_password_requires_minimum_password_length()
    {
        $user = $this->createTestUser();
        $token = Password::createToken($user);

        $response = $this->postJson('/api/auth/reset-password', [
            'token' => $token,
            'email' => 'test@example.com',
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
    public function reset_password_revokes_all_tokens()
    {
        $user = $this->createTestUser();
        
        // Create some tokens
        $user->createToken('token-1');
        $user->createToken('token-2');

        $token = Password::createToken($user);

        $response = $this->postJson('/api/auth/reset-password', [
            'token' => $token,
            'email' => 'test@example.com',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertStatus(200);

        // Verify all tokens are revoked
        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $user->id,
        ]);
    }

    /** @test */
    public function user_can_login_with_new_password_after_reset()
    {
        $user = $this->createTestUser();
        $token = Password::createToken($user);

        // Reset password
        $this->postJson('/api/auth/reset-password', [
            'token' => $token,
            'email' => 'test@example.com',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        // Try to login with new password
        $response = $this->postJson('/api/auth/login', [
            'email' => 'test@example.com',
            'password' => 'newpassword123',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Login successful',
            ]);
    }
}
