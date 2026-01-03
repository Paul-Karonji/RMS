<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Tenant;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Auth\Notifications\ResetPassword;

class ForgotPasswordTest extends TestCase
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
            'password_hash' => Hash::make('password123'),
            'role' => 'company_admin',
            'account_type' => 'company_admin',
            'status' => 'active',
        ]);
    }

    /** @test */
    public function user_can_request_password_reset_link()
    {
        Notification::fake();

        $user = $this->createTestUser();

        $response = $this->postJson('/api/auth/forgot-password', [
            'email' => 'test@example.com',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Password reset link has been sent to your email address.',
            ]);

        Notification::assertSentTo($user, ResetPassword::class);
    }

    /** @test */
    public function forgot_password_requires_email()
    {
        $response = $this->postJson('/api/auth/forgot-password', []);

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
    public function forgot_password_requires_valid_email_format()
    {
        $response = $this->postJson('/api/auth/forgot-password', [
            'email' => 'invalid-email',
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
    public function forgot_password_requires_existing_email()
    {
        $response = $this->postJson('/api/auth/forgot-password', [
            'email' => 'nonexistent@example.com',
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
}
