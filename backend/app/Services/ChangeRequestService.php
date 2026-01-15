<?php

namespace App\Services;

use App\Models\ChangeRequest;
use App\Models\PropertyOwner;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class ChangeRequestService
{
    public function __construct(
        private NotificationService $notificationService
    ) {}

    /**
     * Create a new change request.
     */
    public function create(PropertyOwner $owner, array $data): ChangeRequest
    {
        $changeRequest = ChangeRequest::create([
            'tenant_id' => $owner->tenant_id,
            'property_owner_id' => $owner->id,
            'property_id' => $data['property_id'] ?? null,
            'unit_id' => $data['unit_id'] ?? null,
            'request_type' => $data['request_type'],
            'current_value' => $data['current_value'],
            'requested_value' => $data['requested_value'],
            'reason' => $data['reason'],
            'affects_existing_leases' => $data['affects_existing_leases'] ?? false,
            'effective_from' => $data['effective_from'] ?? null,
            'status' => 'pending',
        ]);

        // Notify company admins
        $this->notifyAdmins($changeRequest);

        return $changeRequest;
    }

    /**
     * Get paginated change requests.
     */
    public function getChangeRequests(
        User $user,
        ?string $status = null,
        ?string $type = null,
        int $perPage = 20
    ): LengthAwarePaginator {
        $query = ChangeRequest::with(['propertyOwner', 'property', 'unit', 'reviewer'])
            ->latest('created_at');

        // Filter by status
        if ($status) {
            $query->where('status', $status);
        }

        // Filter by type
        if ($type) {
            $query->where('request_type', $type);
        }

        // Role-based filtering
        if ($user->hasRole('property_owner')) {
            $query->where('property_owner_id', $user->property_owner_id);
        }

        return $query->paginate($perPage);
    }

    /**
     * Approve a change request.
     */
    public function approve(ChangeRequest $request, User $approver, ?string $notes = null): ChangeRequest
    {
        $request->update([
            'status' => 'approved',
            'reviewed_by' => $approver->id,
            'reviewed_at' => now(),
            'review_notes' => $notes,
        ]);

        // Apply the changes
        $this->applyChanges($request);

        // Notify owner
        $this->notificationService->create(
            user: $request->propertyOwner->user,
            type: 'change_request_approved',
            title: 'Change Request Approved',
            message: "Your change request for {$request->request_type} has been approved.",
            data: ['change_request_id' => $request->id]
        );

        return $request->fresh();
    }

    /**
     * Reject a change request.
     */
    public function reject(ChangeRequest $request, User $rejector, string $reason): ChangeRequest
    {
        $request->update([
            'status' => 'rejected',
            'reviewed_by' => $rejector->id,
            'reviewed_at' => now(),
            'review_notes' => $reason,
        ]);

        // Notify owner
        $this->notificationService->create(
            user: $request->propertyOwner->user,
            type: 'change_request_rejected',
            title: 'Change Request Rejected',
            message: "Your change request for {$request->request_type} was rejected. Reason: {$reason}",
            data: ['change_request_id' => $request->id]
        );

        return $request->fresh();
    }

    /**
     * Apply approved changes to the system.
     */
    protected function applyChanges(ChangeRequest $request): void
    {
        switch ($request->request_type) {
            case 'unit_price':
                if ($request->unit) {
                    $request->unit->update([
                        'monthly_rent' => $request->requested_value,
                    ]);
                }
                break;

            case 'unit_condition':
                if ($request->unit) {
                    $request->unit->update([
                        'condition' => $request->requested_value,
                    ]);
                }
                break;

            case 'property_details':
                if ($request->property) {
                    $details = json_decode($request->requested_value, true);
                    $request->property->update($details);
                }
                break;

            // Add more cases as needed
        }
    }

    /**
     * Notify company admins about new change request.
     */
    protected function notifyAdmins(ChangeRequest $request): void
    {
        $admins = User::where('tenant_id', $request->tenant_id)
            ->whereHas('roles', function ($query) {
                $query->where('name', 'company_admin');
            })
            ->get();

        foreach ($admins as $admin) {
            $this->notificationService->create(
                user: $admin,
                type: 'change_request_submitted',
                title: 'New Change Request',
                message: "A new change request has been submitted by {$request->propertyOwner->owner_name}.",
                data: ['change_request_id' => $request->id]
            );
        }
    }
}
