<?php

namespace Tests\Feature\Expense;

use Tests\TestCase;
use App\Models\User;
use App\Models\Tenant;
use App\Models\Property;
use App\Models\PropertyOwner;
use App\Models\Unit;
use App\Models\Expense;
use App\Models\MaintenanceRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ExpenseTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $manager;
    private Tenant $tenant;
    private Property $property;
    private Unit $unit;
    private MaintenanceRequest $maintenanceRequest;

    protected function setUp(): void
    {
        parent::setUp();

        // Create tenant (company)
        $this->tenant = Tenant::factory()->create([
            'pricing_model' => 'payment_processing',
        ]);

        // Create admin user
        $this->admin = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'role' => 'company_admin',
        ]);

        // Create manager user
        $this->manager = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'role' => 'property_manager',
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
            'expense_sharing_enabled' => true,
            'owner_expense_percentage' => 70.00,
            'platform_expense_percentage' => 30.00,
        ]);

        // Create unit
        $this->unit = Unit::factory()->create([
            'tenant_id' => $this->tenant->id,
            'property_id' => $this->property->id,
            'status' => 'occupied',
        ]);

        // Create maintenance request
        $this->maintenanceRequest = MaintenanceRequest::factory()->create([
            'tenant_id' => $this->tenant->id,
            'property_id' => $this->property->id,
            'unit_id' => $this->unit->id,
            'status' => 'completed',
        ]);
    }

    /** @test */
    public function manager_can_create_expense()
    {
        $expenseData = [
            'property_id' => $this->property->id,
            'unit_id' => $this->unit->id,
            'maintenance_request_id' => $this->maintenanceRequest->id,
            'category' => 'maintenance',
            'description' => 'Fixed broken water heater',
            'amount' => 15000.00,
            'expense_date' => now()->format('Y-m-d'),
            'invoice_number' => 'INV-2026-001',
            'receipt_url' => 'https://example.com/receipt.pdf',
        ];

        $response = $this->actingAs($this->manager, 'sanctum')
            ->postJson('/api/expenses', $expenseData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'status',
                    'owner_share',
                    'platform_share',
                ],
            ]);

        $this->assertDatabaseHas('expenses', [
            'tenant_id' => $this->tenant->id,
            'property_id' => $this->property->id,
            'category' => 'maintenance',
            'amount' => 15000.00,
            'status' => 'pending',
        ]);

        // Verify cost sharing calculation
        $expense = Expense::latest()->first();
        $this->assertEquals(10500.00, $expense->owner_share); // 70% of 15000
        $this->assertEquals(4500.00, $expense->platform_share); // 30% of 15000
    }

    /** @test */
    public function expense_requires_all_mandatory_fields()
    {
        $response = $this->actingAs($this->manager, 'sanctum')
            ->postJson('/api/expenses', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'property_id',
                'category',
                'description',
                'amount',
                'expense_date',
                'receipt_url',
            ]);
    }

    /** @test */
    public function expense_amount_must_be_positive()
    {
        $expenseData = [
            'property_id' => $this->property->id,
            'maintenance_request_id' => $this->maintenanceRequest->id,
            'category' => 'maintenance',
            'description' => 'Test expense',
            'amount' => -100.00,
            'expense_date' => now()->format('Y-m-d'),
            'receipt_url' => 'https://example.com/receipt.pdf',
        ];

        $response = $this->actingAs($this->manager, 'sanctum')
            ->postJson('/api/expenses', $expenseData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['amount']);
    }

    /** @test */
    public function user_can_only_create_expense_for_own_tenant()
    {
        // Create another tenant and property
        $otherTenant = Tenant::factory()->create();
        $otherPropertyOwner = PropertyOwner::factory()->create([
            'tenant_id' => $otherTenant->id,
        ]);
        $otherProperty = Property::factory()->create([
            'tenant_id' => $otherTenant->id,
            'owner_id' => $otherPropertyOwner->id,
        ]);

        $expenseData = [
            'property_id' => $otherProperty->id,
            'category' => 'maintenance',
            'description' => 'Test expense',
            'amount' => 1000.00,
            'expense_date' => now()->format('Y-m-d'),
            'receipt_url' => 'https://example.com/receipt.pdf',
        ];

        $response = $this->actingAs($this->manager, 'sanctum')
            ->postJson('/api/expenses', $expenseData);

        $response->assertStatus(403);
    }

    /** @test */
    public function can_list_expenses_for_tenant()
    {
        // Create multiple expenses
        Expense::factory()->count(3)->create([
            'tenant_id' => $this->tenant->id,
            'property_id' => $this->property->id,
            'maintenance_request_id' => $this->maintenanceRequest->id,
            'created_by' => $this->manager->id,
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/expenses');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'id',
                        'category',
                        'description',
                        'amount',
                        'status',
                        'owner_share',
                        'platform_share',
                    ],
                ],
            ]);

        $this->assertCount(3, $response->json('data'));
    }

    /** @test */
    public function can_filter_expenses_by_status()
    {
        // Create expenses with different statuses
        Expense::factory()->create([
            'tenant_id' => $this->tenant->id,
            'property_id' => $this->property->id,
            'maintenance_request_id' => $this->maintenanceRequest->id,
            'status' => 'pending',
            'created_by' => $this->manager->id,
        ]);

        Expense::factory()->create([
            'tenant_id' => $this->tenant->id,
            'property_id' => $this->property->id,
            'maintenance_request_id' => $this->maintenanceRequest->id,
            'status' => 'approved',
            'created_by' => $this->manager->id,
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/expenses?status=pending');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals('pending', $response->json('data.0.status'));
    }

    /** @test */
    public function can_view_single_expense()
    {
        $expense = Expense::factory()->create([
            'tenant_id' => $this->tenant->id,
            'property_id' => $this->property->id,
            'maintenance_request_id' => $this->maintenanceRequest->id,
            'created_by' => $this->manager->id,
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson("/api/expenses/{$expense->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'category',
                    'description',
                    'amount',
                    'status',
                    'property',
                    'maintenance_request',
                ],
            ]);
    }

    /** @test */
    public function unauthenticated_user_cannot_access_expenses()
    {
        $response = $this->getJson('/api/expenses');
        $response->assertStatus(401);
    }
}
