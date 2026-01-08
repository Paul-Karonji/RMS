<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\User;
use App\Models\Lease;
use App\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PaymentControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private string $token;

    protected function setUp(): void
    {
        parent::setUp();

        // Create authenticated user
        $this->user = User::factory()->create([
            'role' => 'tenant',
        ]);
        $this->token = $this->user->createToken('test-token')->plainTextToken;
    }

    /** @test */
    public function it_can_list_payments()
    {
        // Create test payments
        $lease = Lease::factory()->create([
            'tenant_id' => $this->user->tenant_id,
        ]);
        
        Payment::factory()->count(3)->create([
            'lease_id' => $lease->id,
            'tenant_id' => $this->user->tenant_id,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/payments');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'id',
                        'amount',
                        'status',
                        'payment_type',
                        'payment_method',
                    ],
                ],
                'meta',
            ]);
    }

    /** @test */
    public function it_can_initiate_stripe_payment()
    {
        $lease = Lease::factory()->create([
            'tenant_id' => $this->user->tenant_id,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/payments', [
            'lease_id' => $lease->id,
            'amount' => 50000,
            'payment_type' => 'rent',
            'payment_method' => 'stripe',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'payment',
                    'gateway_data',
                ],
            ]);

        $this->assertDatabaseHas('payments', [
            'lease_id' => $lease->id,
            'amount' => 50000,
            'payment_type' => 'rent',
            'payment_method' => 'stripe',
            'status' => 'pending',
        ]);
    }

    /** @test */
    public function it_validates_payment_initiation_request()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/payments', [
            // Missing required fields
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['lease_id', 'amount', 'payment_type', 'payment_method']);
    }

    /** @test */
    public function it_requires_mpesa_phone_for_mpesa_payments()
    {
        $lease = Lease::factory()->create([
            'tenant_id' => $this->user->tenant_id,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/payments', [
            'lease_id' => $lease->id,
            'amount' => 50000,
            'payment_type' => 'rent',
            'payment_method' => 'mpesa',
            // Missing mpesa_phone
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['mpesa_phone']);
    }

    /** @test */
    public function it_can_get_payment_details()
    {
        $lease = Lease::factory()->create([
            'tenant_id' => $this->user->tenant_id,
        ]);
        
        $payment = Payment::factory()->create([
            'lease_id' => $lease->id,
            'tenant_id' => $this->user->tenant_id,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson("/api/payments/{$payment->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonPath('data.id', $payment->id);
    }

    /** @test */
    public function it_prevents_viewing_other_tenant_payments()
    {
        $otherUser = User::factory()->create();
        $otherLease = Lease::factory()->create([
            'tenant_id' => $otherUser->tenant_id,
        ]);
        
        $payment = Payment::factory()->create([
            'lease_id' => $otherLease->id,
            'tenant_id' => $otherUser->tenant_id,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson("/api/payments/{$payment->id}");

        $response->assertStatus(403);
    }

    /** @test */
    public function it_can_check_payment_status()
    {
        $lease = Lease::factory()->create([
            'tenant_id' => $this->user->tenant_id,
        ]);
        
        $payment = Payment::factory()->create([
            'lease_id' => $lease->id,
            'tenant_id' => $this->user->tenant_id,
            'status' => 'completed',
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson("/api/payments/{$payment->id}/status");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'payment_id',
                    'status',
                    'gateway_status',
                ],
            ]);
    }
}
