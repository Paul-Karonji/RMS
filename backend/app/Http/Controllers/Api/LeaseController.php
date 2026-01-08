<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Lease\LeaseStoreRequest;
use App\Http\Requests\Lease\LeaseUpdateRequest;
use App\Http\Requests\Lease\LeaseTerminationRequest;
use App\Http\Requests\Lease\LeaseRenewalRequest;
use App\Http\Resources\LeaseResource;
use App\Http\Resources\LeaseDetailResource;
use App\Models\Lease;
use App\Services\LeaseService;
use Illuminate\Http\JsonResponse;

class LeaseController extends Controller
{
    protected LeaseService $leaseService;

    public function __construct(LeaseService $leaseService)
    {
        $this->leaseService = $leaseService;
    }

    /**
     * Display a listing of leases
     */
    public function index(): JsonResponse
    {
        $query = Lease::where('tenant_id', auth()->user()->tenant_id)
            ->with(['property', 'unit', 'tenant']);

        // Filter by status if provided
        if (request()->has('status')) {
            $query->where('status', request('status'));
        }

        $leases = $query->latest()->paginate(20);

        return response()->json([
            'success' => true,
            'data' => LeaseResource::collection($leases),
            'meta' => [
                'total' => $leases->total(),
                'page' => $leases->currentPage(),
                'per_page' => $leases->perPage(),
            ],
        ]);
    }

    /**
     * Store a newly created lease
     */
    public function store(LeaseStoreRequest $request): JsonResponse
    {
        try {
            $result = $this->leaseService->create($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Lease created successfully',
                'data' => [
                    'lease' => new LeaseDetailResource($result['lease']),
                    'first_payment' => $result['first_payment'],
                ],
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Display the specified lease
     */
    public function show(string $id): JsonResponse
    {
        $lease = Lease::where('tenant_id', auth()->user()->tenant_id)
            ->with(['property', 'unit', 'tenant'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => new LeaseDetailResource($lease),
        ]);
    }

    /**
     * Update the specified lease
     */
    public function update(LeaseUpdateRequest $request, string $id): JsonResponse
    {
        $lease = Lease::where('tenant_id', auth()->user()->tenant_id)
            ->findOrFail($id);

        $lease->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Lease updated successfully',
            'data' => new LeaseDetailResource($lease->load(['property', 'unit', 'tenant'])),
        ]);
    }

    /**
     * Terminate the specified lease
     */
    public function terminate(LeaseTerminationRequest $request, string $id): JsonResponse
    {
        $lease = Lease::where('tenant_id', auth()->user()->tenant_id)
            ->findOrFail($id);

        try {
            $terminatedLease = $this->leaseService->terminate($lease, $request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Lease terminated successfully',
                'data' => new LeaseDetailResource($terminatedLease),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Renew the specified lease
     */
    public function renew(LeaseRenewalRequest $request, string $id): JsonResponse
    {
        $lease = Lease::where('tenant_id', auth()->user()->tenant_id)
            ->findOrFail($id);

        try {
            $newLease = $this->leaseService->renew($lease, $request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Lease renewed successfully',
                'data' => new LeaseDetailResource($newLease),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
