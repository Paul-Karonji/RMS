<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\TenantStoreRequest;
use App\Http\Requests\Tenant\TenantUpdateRequest;
use App\Http\Resources\TenantResource;
use App\Http\Resources\TenantDetailResource;
use App\Models\User;
use App\Services\TenantService;
use Illuminate\Http\JsonResponse;

class TenantController extends Controller
{
    protected TenantService $tenantService;

    public function __construct(TenantService $tenantService)
    {
        $this->tenantService = $tenantService;
    }

    /**
     * Display a listing of tenants
     */
    public function index(): JsonResponse
    {
        $tenants = User::where('tenant_id', auth()->user()->tenant_id)
            ->where('role', 'tenant')
            ->latest()
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => TenantResource::collection($tenants),
            'meta' => [
                'total' => $tenants->total(),
                'page' => $tenants->currentPage(),
                'per_page' => $tenants->perPage(),
            ],
        ]);
    }

    /**
     * Store a newly created tenant
     */
    public function store(TenantStoreRequest $request): JsonResponse
    {
        $result = $this->tenantService->createDirect($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Tenant added successfully',
            'data' => [
                'tenant' => new TenantDetailResource($result['tenant']),
                'credentials' => $result['credentials'],
            ],
        ], 201);
    }

    /**
     * Display the specified tenant
     */
    public function show(string $id): JsonResponse
    {
        $tenant = User::where('tenant_id', auth()->user()->tenant_id)
            ->where('role', 'tenant')
            ->with('activeLeases')
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => new TenantDetailResource($tenant),
        ]);
    }

    /**
     * Update the specified tenant
     */
    public function update(TenantUpdateRequest $request, string $id): JsonResponse
    {
        $tenant = User::where('tenant_id', auth()->user()->tenant_id)
            ->where('role', 'tenant')
            ->findOrFail($id);

        $tenant->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Tenant updated successfully',
            'data' => new TenantDetailResource($tenant),
        ]);
    }

    /**
     * Remove the specified tenant
     */
    public function destroy(string $id): JsonResponse
    {
        $tenant = User::where('tenant_id', auth()->user()->tenant_id)
            ->where('role', 'tenant')
            ->findOrFail($id);

        // Check for active leases
        if ($tenant->activeLeases()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete tenant with active leases',
            ], 422);
        }

        $tenant->delete();

        return response()->json([
            'success' => true,
            'message' => 'Tenant deleted successfully',
        ]);
    }
}
