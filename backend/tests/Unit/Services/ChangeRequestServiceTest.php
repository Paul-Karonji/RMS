<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Models\User;
use App\Models\PropertyOwner;
use App\Models\Property;
use App\Models\Unit;
use App\Models\ChangeRequest;
use App\Services\ChangeRequestService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ChangeRequestServiceTest extends TestCase
{
    use RefreshDatabase;

    protected ChangeRequestService $changeRequestService;
    protected User $user;
    protected PropertyOwner $owner;
    protected Property $property;
    protected Unit $unit;

    protected function setUp(): void
    {
        parent::setUp();
        $this->changeRequestService = app(ChangeRequestService::class);
        
        // Create test data
        $tenant = \App\Models\Tenant::factory()->create();
        
        $this->owner = PropertyOwner::factory()->create([
            'tenant_id' => $tenant->id,
        ]);
        
        $this->user = User::factory()->create([
            'tenant_id' => $tenant->id,
            'property_owner_id' => $this->owner->id,
        ]);
        
        $this->property = Property::factory()->create([
            'tenant_id' => $tenant->id,
            'property_owner_id' => $this->owner->id,
        ]);
        
        $this->unit = Unit::factory()->create([
            'property_id' => $this->property->id,
            'monthly_rent' => 10000,
        ]);
    }

    /** @test */
    public function it_can_create_change_request()
    {
        $data = [
            'property_id' => $this->property->id,
            'unit_id' => $this->unit->id,
            'request_type' => 'unit_price',
            'current_value' => '10000',
            'requested_value' => '12000',
            'reason' => 'Market rate increase due to property improvements',
            'affects_existing_leases' => false,
        ];

        $changeRequest = $this->changeRequestService->create($this->owner, $data);

        $this->assertInstanceOf(ChangeRequest::class, $changeRequest);
        $this->assertEquals($this->owner->id, $changeRequest->property_owner_id);
        $this->assertEquals('unit_price', $changeRequest->request_type);
        $this->assertEquals('10000', $changeRequest->current_value);
        $this->assertEquals('12000', $changeRequest->requested_value);
        $this->assertEquals('pending', $changeRequest->status);
    }

    /** @test */
    public function it_can_approve_change_request()
    {
        $changeRequest = ChangeRequest::factory()->create([
            'tenant_id' => $this->user->tenant_id,
            'property_owner_id' => $this->owner->id,
            'unit_id' => $this->unit->id,
            'request_type' => 'unit_price',
            'current_value' => '10000',
            'requested_value' => '12000',
            'status' => 'pending',
        ]);

        $admin = User::factory()->create([
            'tenant_id' => $this->user->tenant_id,
        ]);
        $admin->assignRole('company_admin');

        $updated = $this->changeRequestService->approve(
            request: $changeRequest,
            approver: $admin,
            notes: 'Approved due to market conditions'
        );

        $this->assertEquals('approved', $updated->status);
        $this->assertEquals($admin->id, $updated->reviewed_by);
        $this->assertNotNull($updated->reviewed_at);
        $this->assertEquals('Approved due to market conditions', $updated->review_notes);
        
        // Verify changes were applied
        $this->unit->refresh();
        $this->assertEquals(12000, $this->unit->monthly_rent);
    }

    /** @test */
    public function it_can_reject_change_request()
    {
        $changeRequest = ChangeRequest::factory()->create([
            'tenant_id' => $this->user->tenant_id,
            'property_owner_id' => $this->owner->id,
            'status' => 'pending',
        ]);

        $admin = User::factory()->create([
            'tenant_id' => $this->user->tenant_id,
        ]);
        $admin->assignRole('company_admin');

        $updated = $this->changeRequestService->reject(
            request: $changeRequest,
            rejector: $admin,
            reason: 'Requested increase is too high'
        );

        $this->assertEquals('rejected', $updated->status);
        $this->assertEquals($admin->id, $updated->reviewed_by);
        $this->assertNotNull($updated->reviewed_at);
        $this->assertEquals('Requested increase is too high', $updated->review_notes);
    }

    /** @test */
    public function it_applies_unit_price_changes_when_approved()
    {
        $changeRequest = ChangeRequest::factory()->create([
            'tenant_id' => $this->user->tenant_id,
            'property_owner_id' => $this->owner->id,
            'unit_id' => $this->unit->id,
            'request_type' => 'unit_price',
            'current_value' => '10000',
            'requested_value' => '15000',
            'status' => 'pending',
        ]);

        $admin = User::factory()->create(['tenant_id' => $this->user->tenant_id]);
        $admin->assignRole('company_admin');

        $this->changeRequestService->approve($changeRequest, $admin);

        $this->unit->refresh();
        $this->assertEquals(15000, $this->unit->monthly_rent);
    }
}
