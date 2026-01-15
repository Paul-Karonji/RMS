<?php

namespace Tests\Feature\Subscription;

use Tests\TestCase;
use App\Models\User;
use App\Models\Tenant;
use App\Models\SubscriptionInvoice;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SubscriptionInvoiceTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();

        // Create tenant with subscription
        $this->tenant = Tenant::factory()->create([
            'subscription_plan' => 'professional',
            'subscription_status' => 'active',
        ]);

        // Create admin
        $this->admin = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'role' => 'company_admin',
        ]);
    }

    /** @test */
    public function can_create_monthly_subscription_invoice()
    {
        $invoice = SubscriptionInvoice::create([
            'tenant_id' => $this->tenant->id,
            'amount' => 5000.00,
            'billing_period_start' => now()->startOfMonth(),
            'billing_period_end' => now()->endOfMonth(),
            'due_date' => now()->addDays(7),
            'status' => 'pending',
        ]);

        $this->assertDatabaseHas('subscription_invoices', [
            'tenant_id' => $this->tenant->id,
            'amount' => 5000.00,
            'status' => 'pending',
        ]);
    }

    /** @test */
    public function invoice_amount_calculated_correctly_for_plan()
    {
        $planPrices = [
            'basic' => 2000.00,
            'professional' => 5000.00,
            'enterprise' => 10000.00,
        ];

        $expectedAmount = $planPrices[$this->tenant->subscription_plan];

        $invoice = SubscriptionInvoice::create([
            'tenant_id' => $this->tenant->id,
            'amount' => $expectedAmount,
            'billing_period_start' => now()->startOfMonth(),
            'billing_period_end' => now()->endOfMonth(),
            'due_date' => now()->addDays(7),
            'status' => 'pending',
        ]);

        $this->assertEquals(5000.00, $invoice->amount);
    }

    /** @test */
    public function can_mark_invoice_as_paid()
    {
        $invoice = SubscriptionInvoice::factory()->create([
            'tenant_id' => $this->tenant->id,
            'status' => 'pending',
        ]);

        $invoice->update([
            'status' => 'paid',
            'paid_at' => now(),
            'payment_method' => 'mpesa',
        ]);

        $this->assertDatabaseHas('subscription_invoices', [
            'id' => $invoice->id,
            'status' => 'paid',
        ]);

        $this->assertNotNull($invoice->fresh()->paid_at);
    }

    /** @test */
    public function can_identify_overdue_invoices()
    {
        // Create overdue invoice
        $overdueInvoice = SubscriptionInvoice::factory()->create([
            'tenant_id' => $this->tenant->id,
            'due_date' => now()->subDays(5),
            'status' => 'pending',
        ]);

        // Create current invoice
        $currentInvoice = SubscriptionInvoice::factory()->create([
            'tenant_id' => $this->tenant->id,
            'due_date' => now()->addDays(5),
            'status' => 'pending',
        ]);

        $overdueInvoices = SubscriptionInvoice::where('tenant_id', $this->tenant->id)
            ->where('status', 'pending')
            ->where('due_date', '<', now())
            ->get();

        $this->assertCount(1, $overdueInvoices);
        $this->assertEquals($overdueInvoice->id, $overdueInvoices->first()->id);
    }

    /** @test */
    public function can_list_invoices_for_tenant()
    {
        SubscriptionInvoice::factory()->count(3)->create([
            'tenant_id' => $this->tenant->id,
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/subscription-invoices');

        $response->assertStatus(200);
        $this->assertCount(3, $response->json('data'));
    }

    /** @test */
    public function can_filter_invoices_by_status()
    {
        SubscriptionInvoice::factory()->create([
            'tenant_id' => $this->tenant->id,
            'status' => 'pending',
        ]);

        SubscriptionInvoice::factory()->create([
            'tenant_id' => $this->tenant->id,
            'status' => 'paid',
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/subscription-invoices?status=pending');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
    }

    /** @test */
    public function can_view_single_invoice()
    {
        $invoice = SubscriptionInvoice::factory()->create([
            'tenant_id' => $this->tenant->id,
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson("/api/subscription-invoices/{$invoice->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'amount',
                    'status',
                    'due_date',
                    'billing_period_start',
                    'billing_period_end',
                ],
            ]);
    }

    /** @test */
    public function tenant_isolation_enforced()
    {
        // Create another tenant and invoice
        $otherTenant = Tenant::factory()->create();
        $otherInvoice = SubscriptionInvoice::factory()->create([
            'tenant_id' => $otherTenant->id,
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson("/api/subscription-invoices/{$otherInvoice->id}");

        $response->assertStatus(403);
    }
}
