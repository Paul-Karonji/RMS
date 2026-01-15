<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ChangeRequestService;
use App\Http\Requests\ChangeRequest\StoreChangeRequestRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\ChangeRequest;

class ChangeRequestController extends Controller
{
    public function __construct(
        private ChangeRequestService $changeRequestService
    ) {}

    /**
     * Get paginated change requests.
     */
    public function index(Request $request): JsonResponse
    {
        $status = $request->query('status');
        $type = $request->query('request_type');
        $perPage = min($request->query('per_page', 20), 100);

        $changeRequests = $this->changeRequestService->getChangeRequests(
            user: $request->user(),
            status: $status,
            type: $type,
            perPage: $perPage
        );

        return response()->json([
            'success' => true,
            'data' => $changeRequests->items(),
            'meta' => [
                'current_page' => $changeRequests->currentPage(),
                'last_page' => $changeRequests->lastPage(),
                'per_page' => $changeRequests->perPage(),
                'total' => $changeRequests->total(),
            ],
        ]);
    }

    /**
     * Create a new change request.
     */
    public function store(StoreChangeRequestRequest $request): JsonResponse
    {
        $user = $request->user();
        
        if (!$user->property_owner_id) {
            return response()->json([
                'success' => false,
                'message' => 'Only property owners can create change requests',
            ], 403);
        }

        $changeRequest = $this->changeRequestService->create(
            owner: $user->propertyOwner,
            data: $request->validated()
        );

        return response()->json([
            'success' => true,
            'message' => 'Change request submitted successfully',
            'data' => $changeRequest->load(['property', 'unit']),
        ], 201);
    }

    /**
     * Get a specific change request.
     */
    public function show(Request $request, string $id): JsonResponse
    {
        $changeRequest = ChangeRequest::with(['propertyOwner', 'property', 'unit', 'reviewer'])
            ->findOrFail($id);

        // Authorization check
        $user = $request->user();
        if (!$user->hasRole('company_admin') && $changeRequest->property_owner_id !== $user->property_owner_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $changeRequest,
        ]);
    }

    /**
     * Approve a change request.
     */
    public function approve(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'notes' => 'nullable|string|max:500',
        ]);

        $changeRequest = ChangeRequest::findOrFail($id);

        if (!$changeRequest->isPending()) {
            return response()->json([
                'success' => false,
                'message' => 'Only pending requests can be approved',
            ], 400);
        }

        $updatedRequest = $this->changeRequestService->approve(
            request: $changeRequest,
            approver: $request->user(),
            notes: $request->input('notes')
        );

        return response()->json([
            'success' => true,
            'message' => 'Change request approved successfully',
            'data' => $updatedRequest,
        ]);
    }

    /**
     * Reject a change request.
     */
    public function reject(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'reason' => 'required|string|min:10|max:500',
        ]);

        $changeRequest = ChangeRequest::findOrFail($id);

        if (!$changeRequest->isPending()) {
            return response()->json([
                'success' => false,
                'message' => 'Only pending requests can be rejected',
            ], 400);
        }

        $updatedRequest = $this->changeRequestService->reject(
            request: $changeRequest,
            rejector: $request->user(),
            reason: $request->input('reason')
        );

        return response()->json([
            'success' => true,
            'message' => 'Change request rejected',
            'data' => $updatedRequest,
        ]);
    }
}
