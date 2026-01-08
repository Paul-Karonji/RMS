<?php

namespace App\Services;

use App\Models\Lease;
use App\Models\Unit;
use App\Notifications\LeaseCreated;
use App\Notifications\LeaseTerminated;
use Carbon\Carbon;

class LeaseService
{
    protected ProRatedRentCalculator $rentCalculator;

    public function __construct(ProRatedRentCalculator $rentCalculator)
    {
        $this->rentCalculator = $rentCalculator;
    }

    /**
     * Create a new lease
     *
     * @param array $data
     * @return array
     * @throws \Exception
     */
    public function create(array $data): array
    {
        // Validate unit is available
        $unit = Unit::findOrFail($data['unit_id']);
        
        if (!in_array($unit->status, ['vacant', 'available'])) {
            throw new \Exception('Unit is not available for lease');
        }

        // Calculate pro-rated rent for first payment
        $firstPayment = $this->rentCalculator->calculateFirstPayment(
            $data['start_date'],
            $data['rent_amount'],
            $data['deposit_amount']
        );

        // Create lease
        $lease = Lease::create([
            'tenant_id' => auth()->user()->tenant_id,
            'property_id' => $data['property_id'],
            'unit_id' => $data['unit_id'],
            'tenant_user_id' => $data['tenant_user_id'],
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'rent_amount' => $data['rent_amount'],
            'deposit_amount' => $data['deposit_amount'],
            'payment_frequency' => $data['payment_frequency'],
            'payment_day' => $data['payment_day'],
            'payment_type' => $data['payment_type'],
            'late_fee_type' => $data['late_fee_type'] ?? null,
            'late_fee_amount' => $data['late_fee_amount'] ?? null,
            'late_fee_grace_period_days' => $data['late_fee_grace_period_days'] ?? 3,
            'terms_source' => $data['terms_source'] ?? 'property',
            'status' => 'active',
        ]);

        // Update unit status to occupied
        $unit->update(['status' => 'occupied']);

        // Send notification to tenant
        $lease->tenant->notify(new LeaseCreated($lease, $firstPayment));

        return [
            'lease' => $lease->load(['property', 'unit', 'tenant']),
            'first_payment' => $firstPayment,
        ];
    }

    /**
     * Terminate a lease
     *
     * @param Lease $lease
     * @param array $data
     * @return Lease
     * @throws \Exception
     */
    public function terminate(Lease $lease, array $data): Lease
    {
        // Validate lease is active
        if ($lease->status !== 'active') {
            throw new \Exception('Only active leases can be terminated');
        }

        // Validate termination date
        $terminationDate = Carbon::parse($data['termination_date']);
        $startDate = Carbon::parse($lease->start_date);
        
        if ($terminationDate->lt($startDate)) {
            throw new \Exception('Termination date cannot be before lease start date');
        }

        // Update lease
        $lease->update([
            'status' => 'terminated',
            'terminated_at' => $terminationDate,
            'termination_reason' => $data['termination_reason'],
        ]);

        // Update unit status to vacant
        $lease->unit->update(['status' => 'vacant']);

        // Send notification to tenant
        $lease->tenant->notify(new LeaseTerminated($lease));

        return $lease->fresh(['property', 'unit', 'tenant']);
    }

    /**
     * Renew a lease
     *
     * @param Lease $lease
     * @param array $data
     * @return Lease
     * @throws \Exception
     */
    public function renew(Lease $lease, array $data): Lease
    {
        // Validate lease can be renewed
        if (!in_array($lease->status, ['active', 'expiring'])) {
            throw new \Exception('Only active or expiring leases can be renewed');
        }

        // Validate new end date
        $newEndDate = Carbon::parse($data['new_end_date']);
        $currentEndDate = Carbon::parse($lease->end_date);
        
        if ($newEndDate->lte($currentEndDate)) {
            throw new \Exception('New end date must be after current end date');
        }

        // Create new lease record (keep history)
        $newLease = $lease->replicate();
        $newLease->start_date = $currentEndDate->addDay()->toDateString();
        $newLease->end_date = $newEndDate->toDateString();
        $newLease->rent_amount = $data['new_rent_amount'] ?? $lease->rent_amount;
        $newLease->status = 'active';
        $newLease->save();

        // Mark old lease as expired
        $lease->update(['status' => 'expired']);

        return $newLease->load(['property', 'unit', 'tenant']);
    }
}
