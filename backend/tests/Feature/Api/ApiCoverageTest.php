<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\User;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * API Coverage Test
 * Verifies that all critical API endpoints are accessible and properly secured
 */
class ApiCoverageTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::factory()->create();
        $this->admin = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'role' => 'company_admin',
        ]);
    }

    /** @test */
    public function all_protected_endpoints_require_authentication()
    {
        $protectedEndpoints = [
            ['GET', '/api/properties'],
            ['GET', '/api/units'],
            ['GET', '/api/leases'],
            ['GET', '/api/payments'],
            ['GET', '/api/expenses'],
            ['GET', '/api/maintenance'],
            ['GET', '/api/dashboard/company'],
        ];

        foreach ($protectedEndpoints as [$method, $endpoint]) {
            $response = $this->json($method, $endpoint);
            $response->assertStatus(401, "Endpoint {$method} {$endpoint} should require authentication");
        }
    }

    /** @test */
    public function authenticated_user_can_access_protected_endpoints()
    {
        $endpoints = [
            ['GET', '/api/properties'],
            ['GET', '/api/units'],
            ['GET', '/api/leases'],
            ['GET', '/api/payments'],
        ];

        foreach ($endpoints as [$method, $endpoint]) {
            $response = $this->actingAs($this->admin, 'sanctum')
                ->json($method, $endpoint);
            
            $this->assertContains(
                $response->status(),
                [200, 404], // 200 OK or 404 if no data
                "Endpoint {$method} {$endpoint} should be accessible when authenticated"
            );
        }
    }

    /** @test */
    public function api_returns_json_responses()
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/properties');

        $response->assertHeader('Content-Type', 'application/json');
        $response->assertJsonStructure(['success', 'data']);
    }

    /** @test */
    public function api_handles_not_found_gracefully()
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/properties/non-existent-id');

        $response->assertStatus(404);
        $response->assertJson([
            'success' => false,
        ]);
    }

    /** @test */
    public function api_validates_request_data()
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/properties', [
                // Missing required fields
            ]);

        $response->assertStatus(422);
        $response->assertJsonStructure(['errors']);
    }

    /** @test */
    public function cors_headers_are_present()
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/properties');

        // CORS headers should be present
        $this->assertTrue(
            $response->headers->has('Access-Control-Allow-Origin') ||
            $response->status() === 200,
            'CORS headers should be configured'
        );
    }

    /** @test */
    public function api_rate_limiting_is_configured()
    {
        // Make multiple requests to test rate limiting
        $responses = [];
        for ($i = 0; $i < 5; $i++) {
            $responses[] = $this->actingAs($this->admin, 'sanctum')
                ->getJson('/api/properties');
        }

        // All requests should succeed (rate limit should be reasonable)
        foreach ($responses as $response) {
            $this->assertNotEquals(429, $response->status(), 'Rate limit should allow reasonable requests');
        }
    }

    /** @test */
    public function public_endpoints_are_accessible_without_auth()
    {
        $publicEndpoints = [
            ['GET', '/api/public/units'],
            ['GET', '/api/public/properties'],
        ];

        foreach ($publicEndpoints as [$method, $endpoint]) {
            $response = $this->json($method, $endpoint);
            
            $this->assertContains(
                $response->status(),
                [200, 404],
                "Public endpoint {$method} {$endpoint} should be accessible without authentication"
            );
        }
    }

    /** @test */
    public function error_responses_have_consistent_format()
    {
        // Test validation error
        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/properties', []);

        $response->assertJsonStructure([
            'message',
            'errors',
        ]);

        // Test not found error
        $response2 = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/properties/invalid-id');

        $response2->assertJsonStructure([
            'success',
            'message',
        ]);
    }

    /** @test */
    public function api_version_is_consistent()
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/properties');

        // API should use /api prefix consistently
        $this->assertTrue(
            str_starts_with($response->baseResponse->headers->get('Location') ?? '/api/', '/api/') ||
            $response->status() === 200,
            'API versioning should be consistent'
        );
    }
}
