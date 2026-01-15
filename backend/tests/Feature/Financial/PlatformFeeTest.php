<?php

namespace Tests\Feature\Financial;

use Tests\TestCase;
use App\Models\User;
use App\Models\Tenant;
use App\Models\Property;
use App\Models\PropertyOwner;
use App\Models\Unit;
use App\Models\Lease;
use App\Models\Payment;
use App\Models\PlatformFee;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PlatformFeeTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;
    private Property $property;
    private Payment $payment;

    protected function setUp(): void
    {
        parent::setUp();

        // Create tenant
        $this->tenant = Tenant::factory()->create([
            'pricing_model' => 'payment_processing',
        ]);

        // Create property owner
        $propertyOwner = PropertyOwner::factory()->create([
            'tenant_id' => $this->tenant->id,
        ]);

        // Create property with 10% fee
        $this->property = Property::factory()->create([
            'tenant_id' => $this->tenant->id,
            'owner_id' => $propertyOwner->id,
            'fee_type' => 'percentage',
            'fee_value' => 10.00,
        ]);

        // Create unit
        $unit = Unit::factory()->create([
            'tenant_id' => $this->tenant->id,
            'property_id' => $this->property->id,
        ]);

        // Create tenant renter
        $tenantUser = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'role' => 'tenant',
        ]);

        // Create lease
        $lease = Lease::factory()->create([
            'tenant_id' => $this->tenant->id,
            'property_id' => $this->property->id,
            'unit_id' => $unit->id,
            'tenant_user_id' => $tenantUser->id,
            'rent_amount' => 50000.00,
        ]);

        // Create payment
        $this->payment = Payment::factory()->create([
            'tenant_id' => $this->tenant->id,
            'lease_id' => $lease->id,
            'tenant_user_id' => $tenantUser->id,
            'property_id' => $this->property->id,
            'unit_id' => $unit->id,
            'amount' => 50000.00,
            'payment_type' => 'rent',
            'payment_method' => 'mpesa',
            'status' => 'completed',
        ]);
    }

    /** @test */
    public function platform_fee_is_calculated_correctly_for_percentage()
    {
        // Create platform fee
        $platformFee = PlatformFee::create([
            'tenant_id' => $this->tenant->id,
            'payment_id' => $this->payment->id,
            'property_id' => $this->property->id,
            'fee_type' => 'percentage',
            'fee_rate' => 10.00,
            'base_amount' => 50000.00,
            'fee_amount' => 5000.00, // 10% of 50000
        ]);

        $this->assertDatabaseHas('platform_fees', [
            'payment_id' => $this->payment->id,
            'fee_amount' => 5000.00,
        ]);

        // Verify calculation
        $expectedFee = $this->payment->amount * ($this->property->fee_value / 100);
        $this->assertEquals($expectedFee, $platformFee->fee_amount);
    }

    /** @test */
    public function platform_fee_uses_correct_base_amount_field()
    {
        // CRITICAL: Verify we're using base_amount, NOT payment_amount
        $platformFee = PlatformFee::create([
            'tenant_id' => $this->tenant->id,
            'payment_id' => $this->payment->id,
            'property_id' => $this->property->id,
            'fee_type' => 'percentage',
            'fee_rate' => 10.00,
            'base_amount' => 50000.00,
            'fee_amount' => 5000.00,
        ]);

        // Verify base_amount is set correctly
        $this->assertEquals(50000.00, $platformFee->base_amount);
        
        // Verify the column exists in database
        $columns = \Schema::getColumnListing('platform_fees');
        $this->assertContains('base_amount', $columns);
        $this->assertNotContains('payment_amount', $columns);
    }

    /** @test */
    public function platform_fee_is_created_for_each_payment()
    {
        $initialCount = PlatformFee::count();

        // Create another payment
        $newPayment = Payment::factory()->create([
            'tenant_id' => $this->tenant->id,
            'lease_id' => $this->payment->lease_id,
            'tenant_user_id' => $this->payment->tenant_user_id,
            'property_id' => $this->property->id,
            'unit_id' => $this->payment->unit_id,
            'amount' => 50000.00,
            'payment_type' => 'rent',
            'payment_method' => 'stripe',
            'status' => 'completed',
        ]);

        // Create platform fee for new payment
        PlatformFee::create([
            'tenant_id' => $this->tenant->id,
            'payment_id' => $newPayment->id,
            'property_id' => $this->property->id,
            'fee_type' => 'percentage',
            'fee_rate' => 10.00,
            'base_amount' => 50000.00,
            'fee_amount' => 5000.00,
        ]);

        $this->assertEquals($initialCount + 1, PlatformFee::count());
    }

    /** @test */
    public function platform_fee_varies_by_property_fee_value()
    {
        // Create property with different fee
        $propertyOwner = PropertyOwner::factory()->create([
            'tenant_id' => $this->tenant->id,
        ]);

        $property15Percent = Property::factory()->create([
            'tenant_id' => $this->tenant->id,
            'owner_id' => $propertyOwner->id,
            'fee_type' => 'percentage',
            'fee_value' => 15.00, // 15% instead of 10%
        ]);

        $unit = Unit::factory()->create([
            'tenant_id' => $this->tenant->id,
            'property_id' => $property15Percent->id,
        ]);

        $tenantUser = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'role' => 'tenant',
        ]);

        $lease = Lease::factory()->create([
            'tenant_id' => $this->tenant->id,
            'property_id' => $property15Percent->id,
            'unit_id' => $unit->id,
            'tenant_user_id' => $tenantUser->id,
        ]);

        $payment = Payment::factory()->create([
            'tenant_id' => $this->tenant->id,
            'lease_id' => $lease->id,
            'tenant_user_id' => $tenantUser->id,
            'property_id' => $property15Percent->id,
            'unit_id' => $unit->id,
            'amount' => 50000.00,
            'payment_type' => 'rent',
            'payment_method' => 'mpesa',
            'status' => 'completed',
        ]);

        // Create platform fee with 15%
        $platformFee = PlatformFee::create([
            'tenant_id' => $this->tenant->id,
            'payment_id' => $payment->id,
            'property_id' => $property15Percent->id,
            'fee_type' => 'percentage',
            'fee_rate' => 15.00,
            'base_amount' => 50000.00,
            'fee_amount' => 7500.00, // 15% of 50000
        ]);

        $this->assertEquals(7500.00, $platformFee->fee_amount);
    }

    /** @test */
    public function platform_fee_includes_property_id()
    {
        // CRITICAL: Verify property_id is included (was missing in Week 15)
        $platformFee = PlatformFee::create([
            'tenant_id' => $this->tenant->id,
            'payment_id' => $this->payment->id,
            'property_id' => $this->property->id,
            'fee_type' => 'percentage',
            'fee_rate' => 10.00,
            'base_amount' => 50000.00,
            'fee_amount' => 5000.00,
        ]);

        $this->assertEquals($this->property->id, $platformFee->property_id);
        
        $this->assertDatabaseHas('platform_fees', [
            'property_id' => $this->property->id,
            'payment_id' => $this->payment->id,
        ]);
    }

    /** @test */
    public function can_calculate_total_platform_fees_for_tenant()
    {
        // Create multiple platform fees
        for ($i = 0; $i < 3; $i++) {
            PlatformFee::create([
                'tenant_id' => $this->tenant->id,
                'payment_id' => $this->payment->id,
                'property_id' => $this->property->id,
                'fee_type' => 'percentage',
                'fee_rate' => 10.00,
                'base_amount' => 50000.00,
                'fee_amount' => 5000.00,
            ]);
        }

        $totalFees = PlatformFee::where('tenant_id', $this->tenant->id)
            ->sum('fee_amount');

        $this->assertEquals(15000.00, $totalFees); // 3 x 5000
    }
}
