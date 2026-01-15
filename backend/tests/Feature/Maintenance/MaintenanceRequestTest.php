<?php

namespace Tests\Feature\Maintenance;

use Tests\TestCase;
use App\Models\User;
use App\Models\Tenant;
use App\Models\Property;
use App\Models\PropertyOwner;
use App\Models\Unit;
use App\Models\TenantUser;
use App\Models\MaintenanceRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MaintenanceRequestTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $manager;
    private User $tenantUser;
    private Tenant $tenant;
    private Property $property;
    private Unit $unit;
    private TenantUser $tenantRenter;

    protected function setUp(): void
    {
        parent::setUp();

        // Create tenant (company)
        $this->tenant = Tenant::factory()->create();

        // Create admin
        $this->admin = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'role' => 'company_admin',
        ]);

        // Create manager
        $this->manager = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'role' => 'property_manager',
        ]);

        // Create tenant renter user
        $this->tenantUser = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'role' => 'tenant',
        ]);

        // Create tenant renter record
        $this->tenantRenter = TenantUser::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->tenantUser->id,
        ]);

        // Create property owner
        $propertyOwner = PropertyOwner::factory()->create([
            'tenant_id' => $this->tenant->id,
        ]);

        // Create property
        $this->property = Property::factory()->create([
            'tenant_id' => $this->tenant->id,
            'owner_id' => $propertyOwner->id,
            'status' => 'active',
        ]);

        // Create unit
        $this->unit = Unit::factory()->create([
            'tenant_id' => $this->tenant->id,
            'property_id' => $this->property->id,
            'status' => 'occupied',
        ]);
    }

    /** @test */
    public function tenant_can_create_maintenance_request()
    {
        $requestData = [
            'unit_id' => $this->unit->id,
            'category' => 'plumbing',
            'priority' => 'high',
            'title' => 'Kitchen sink leaking',
            'description' => 'Water dripping from pipe under sink',
            'photos' => ['https://example.com/photo1.jpg', 'https://example.com/photo2.jpg'],
        ];

        $response = $this->actingAs($this->tenantUser, 'sanctum')
            ->postJson('/api/maintenance', $requestData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'status',
                    'category',
                    'priority',
                ],
            ]);

        $this->assertDatabaseHas('maintenance_requests', [
            'tenant_id' => $this->tenant->id,
            'property_id' => $this->property->id,
            'unit_id' => $this->unit->id,
            'tenant_user_id' => $this->tenantRenter->id,
            'category' => 'plumbing',
            'title' => 'Kitchen sink leaking',
            'status' => 'pending',
        ]);
    }

    /** @test */
    public function maintenance_request_requires_mandatory_fields()
    {
        $response = $this->actingAs($this->tenantUser, 'sanctum')
            ->postJson('/api/maintenance', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'unit_id',
                'category',
                'priority',
                'title',
                'description',
            ]);
    }

    /** @test */
    public function can_list_maintenance_requests()
    {
        // Create multiple requests
        MaintenanceRequest::factory()->count(3)->create([
            'tenant_id' => $this->tenant->id,
            'property_id' => $this->property->id,
            'unit_id' => $this->unit->id,
            'tenant_user_id' => $this->tenantRenter->id,
        ]);

        $response = $this->actingAs($this->manager, 'sanctum')
            ->getJson('/api/maintenance');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'id',
                        'category',
                        'priority',
                        'title',
                        'status',
                    ],
                ],
            ]);

        $this->assertCount(3, $response->json('data'));
    }

    /** @test */
    public function can_filter_maintenance_requests_by_status()
    {
        MaintenanceRequest::factory()->create([
            'tenant_id' => $this->tenant->id,
            'property_id' => $this->property->id,
            'unit_id' => $this->unit->id,
            'tenant_user_id' => $this->tenantRenter->id,
            'status' => 'pending',
        ]);

        MaintenanceRequest::factory()->create([
            'tenant_id' => $this->tenant->id,
            'property_id' => $this->property->id,
            'unit_id' => $this->unit->id,
            'tenant_user_id' => $this->tenantRenter->id,
            'status' => 'completed',
        ]);

        $response = $this->actingAs($this->manager, 'sanctum')
            ->getJson('/api/maintenance?status=pending');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals('pending', $response->json('data.0.status'));
    }

    /** @test */
    public function can_filter_by_priority()
    {
        MaintenanceRequest::factory()->create([
            'tenant_id' => $this->tenant->id,
            'property_id' => $this->property->id,
            'unit_id' => $this->unit->id,
            'tenant_user_id' => $this->tenantRenter->id,
            'priority' => 'urgent',
        ]);

        MaintenanceRequest::factory()->create([
            'tenant_id' => $this->tenant->id,
            'property_id' => $this->property->id,
            'unit_id' => $this->unit->id,
            'tenant_user_id' => $this->tenantRenter->id,
            'priority' => 'low',
        ]);

        $response = $this->actingAs($this->manager, 'sanctum')
            ->getJson('/api/maintenance?priority=urgent');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals('urgent', $response->json('data.0.priority'));
    }

    /** @test */
    public function can_view_single_maintenance_request()
    {
        $request = MaintenanceRequest::factory()->create([
            'tenant_id' => $this->tenant->id,
            'property_id' => $this->property->id,
            'unit_id' => $this->unit->id,
            'tenant_user_id' => $this->tenantRenter->id,
        ]);

        $response = $this->actingAs($this->manager, 'sanctum')
            ->getJson("/api/maintenance/{$request->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'category',
                    'priority',
                    'title',
                    'description',
                    'status',
                    'property',
                    'unit',
                ],
            ]);
    }

    /** @test */
    public function tenant_can_only_see_own_maintenance_requests()
    {
        // Create request for this tenant
        $ownRequest = MaintenanceRequest::factory()->create([
            'tenant_id' => $this->tenant->id,
            'property_id' => $this->property->id,
            'unit_id' => $this->unit->id,
            'tenant_user_id' => $this->tenantRenter->id,
        ]);

        // Create another tenant and request
        $otherTenant = Tenant::factory()->create();
        $otherUser = User::factory()->create([
            'tenant_id' => $otherTenant->id,
            'role' => 'tenant',
        ]);
        $otherPropertyOwner = PropertyOwner::factory()->create([
            'tenant_id' => $otherTenant->id,
        ]);
        $otherProperty = Property::factory()->create([
            'tenant_id' => $otherTenant->id,
            'owner_id' => $otherPropertyOwner->id,
        ]);
        $otherUnit = Unit::factory()->create([
            'tenant_id' => $otherTenant->id,
            'property_id' => $otherProperty->id,
        ]);
        $otherTenantRenter = TenantUser::factory()->create([
            'tenant_id' => $otherTenant->id,
            'user_id' => $otherUser->id,
        ]);
        $otherRequest = MaintenanceRequest::factory()->create([
            'tenant_id' => $otherTenant->id,
            'property_id' => $otherProperty->id,
            'unit_id' => $otherUnit->id,
            'tenant_user_id' => $otherTenantRenter->id,
        ]);

        $response = $this->actingAs($this->tenantUser, 'sanctum')
            ->getJson('/api/maintenance');

        $response->assertStatus(200);
        $requestIds = collect($response->json('data'))->pluck('id')->toArray();
        
        $this->assertContains($ownRequest->id, $requestIds);
        $this->assertNotContains($otherRequest->id, $requestIds);
    }

    /** @test */
    public function unauthenticated_user_cannot_access_maintenance_requests()
    {
        $response = $this->getJson('/api/maintenance');
        $response->assertStatus(401);
    }
}
