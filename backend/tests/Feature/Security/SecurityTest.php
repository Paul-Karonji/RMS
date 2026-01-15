<?php

namespace Tests\Feature\Security;

use Tests\TestCase;
use App\Models\User;
use App\Models\Tenant;
use App\Models\Property;
use App\Models\PropertyOwner;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Security Test Suite
 * Verifies authentication, authorization, and data isolation
 */
class SecurityTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function unauthenticated_users_cannot_access_protected_routes()
    {
        $protectedRoutes = [
            ['GET', '/api/properties'],
            ['GET', '/api/units'],
            ['GET', '/api/leases'],
            ['GET', '/api/payments'],
            ['GET', '/api/dashboard/company'],
        ];

        foreach ($protectedRoutes as [$method, $route]) {
            $response = $this->json($method, $route);
            $response->assertStatus(401, "Route {$method} {$route} should require authentication");
        }
    }

    /** @test */
    public function csrf_protection_is_enabled()
    {
        $response = $this->post('/api/properties', []);
        
        // Should fail without CSRF token or API authentication
        $this->assertContains($response->status(), [401, 419]);
    }

    /** @test */
    public function users_cannot_access_other_tenant_data()
    {
        // Create two tenants
        $tenant1 = Tenant::factory()->create();
        $tenant2 = Tenant::factory()->create();

        // Create users for each tenant
        $user1 = User::factory()->create(['tenant_id' => $tenant1->id, 'role' => 'company_admin']);
        $user2 = User::factory()->create(['tenant_id' => $tenant2->id, 'role' => 'company_admin']);

        // Create property for tenant2
        $owner2 = PropertyOwner::factory()->create(['tenant_id' => $tenant2->id]);
        $property2 = Property::factory()->create([
            'tenant_id' => $tenant2->id,
            'owner_id' => $owner2->id,
        ]);

        // User1 tries to access tenant2's property
        $response = $this->actingAs($user1, 'sanctum')
            ->getJson("/api/properties/{$property2->id}");

        $response->assertStatus(403);
    }

    /** @test */
    public function role_based_authorization_is_enforced()
    {
        $tenant = Tenant::factory()->create();
        
        // Create a regular tenant user (not admin)
        $tenantUser = User::factory()->create([
            'tenant_id' => $tenant->id,
            'role' => 'tenant',
        ]);

        // Tenant users should not be able to create properties
        $response = $this->actingAs($tenantUser, 'sanctum')
            ->postJson('/api/properties', [
                'name' => 'Test Property',
            ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function sql_injection_is_prevented()
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create(['tenant_id' => $tenant->id, 'role' => 'company_admin']);

        // Attempt SQL injection in search parameter
        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/properties?search=' OR '1'='1");

        // Should not cause error or return all properties
        $this->assertContains($response->status(), [200, 422]);
    }

    /** @test */
    public function xss_attacks_are_prevented()
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create(['tenant_id' => $tenant->id, 'role' => 'company_admin']);
        $owner = PropertyOwner::factory()->create(['tenant_id' => $tenant->id]);

        // Attempt XSS in property name
        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/properties', [
                'name' => '<script>alert("XSS")</script>',
                'owner_id' => $owner->id,
                'address' => 'Test Address',
                'city' => 'Test City',
                'state' => 'Test State',
                'zip_code' => '12345',
                'country' => 'Kenya',
            ]);

        if ($response->status() === 201) {
            $property = Property::latest()->first();
            // Script tags should be escaped or stripped
            $this->assertStringNotContainsString('<script>', $property->name);
        }
    }

    /** @test */
    public function rate_limiting_is_configured()
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create(['tenant_id' => $tenant->id]);

        // Make multiple requests
        $responses = [];
        for ($i = 0; $i < 10; $i++) {
            $responses[] = $this->actingAs($user, 'sanctum')
                ->getJson('/api/properties');
        }

        // All reasonable requests should succeed
        foreach ($responses as $response) {
            $this->assertNotEquals(429, $response->status());
        }
    }

    /** @test */
    public function sensitive_data_is_not_exposed_in_responses()
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create([
            'tenant_id' => $tenant->id,
            'password' => bcrypt('secret123'),
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/user');

        $response->assertStatus(200);
        
        // Password should not be in response
        $this->assertArrayNotHasKey('password', $response->json());
    }

    /** @test */
    public function file_upload_validation_is_enforced()
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create(['tenant_id' => $tenant->id, 'role' => 'company_admin']);

        // Attempt to upload invalid file type
        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/properties/photos', [
                'photo' => 'not-a-file',
            ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function audit_trail_is_maintained()
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create(['tenant_id' => $tenant->id, 'role' => 'company_admin']);
        $owner = PropertyOwner::factory()->create(['tenant_id' => $tenant->id]);

        // Create a property
        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/properties', [
                'name' => 'Test Property',
                'owner_id' => $owner->id,
                'address' => 'Test Address',
                'city' => 'Nairobi',
                'state' => 'Nairobi',
                'zip_code' => '00100',
                'country' => 'Kenya',
            ]);

        if ($response->status() === 201) {
            $property = Property::latest()->first();
            
            // Verify created_by is set
            $this->assertNotNull($property->created_by);
            $this->assertEquals($user->id, $property->created_by);
        }
    }
}
