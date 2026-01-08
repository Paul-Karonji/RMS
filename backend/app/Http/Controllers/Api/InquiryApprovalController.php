<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Inquiry\InquiryApprovalRequest;
use App\Http\Resources\RentalInquiryResource;
use App\Models\RentalInquiry;
use App\Services\TenantService;
use App\Notifications\InquiryApproved;
use App\Notifications\InquiryRejected;
use Illuminate\Http\JsonResponse;

class InquiryApprovalController extends Controller
{
    protected TenantService $tenantService;

    public function __construct(TenantService $tenantService)
    {
        $this->tenantService = $tenantService;
    }

    /**
     * List all rental inquiries with filtering
     */
    public function index(): JsonResponse
    {
        $query = RentalInquiry::where('tenant_id', auth()->user()->tenant_id)
            ->with(['unit.property']);

        // Filter by status
        if (request('status')) {
            $query->where('status', request('status'));
        }

        // Filter by unit
        if (request('unit_id')) {
            $query->where('unit_id', request('unit_id'));
        }

        // Search by name, email, or phone
        if (request('search')) {
            $search = request('search');
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Order by most recent
        $query->orderBy('created_at', 'desc');

        // Paginate
        $perPage = request('per_page', 15);
        $inquiries = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => RentalInquiryResource::collection($inquiries),
            'meta' => [
                'total' => $inquiries->total(),
                'page' => $inquiries->currentPage(),
                'per_page' => $inquiries->perPage(),
                'last_page' => $inquiries->lastPage(),
            ],
        ]);
    }

    /**
     * Approve inquiry and create tenant account
     */
    public function approve(string $id): JsonResponse
    {
        $inquiry = RentalInquiry::where('tenant_id', auth()->user()->tenant_id)
            ->findOrFail($id);

        // Validate inquiry is pending
        if ($inquiry->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Only pending inquiries can be approved',
            ], 422);
        }

        // Create tenant account
        $result = $this->tenantService->createFromInquiry($inquiry);

        // Update inquiry status
        $inquiry->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        // Send approval notification
        $inquiry->notify(new InquiryApproved($result['tenant'], $result['credentials']));

        return response()->json([
            'success' => true,
            'message' => 'Inquiry approved. Tenant account created.',
            'data' => [
                'inquiry' => new RentalInquiryResource($inquiry),
                'tenant' => $result['tenant'],
                'credentials' => $result['credentials'],
            ],
        ]);
    }

    /**
     * Reject inquiry
     */
    public function reject(InquiryApprovalRequest $request, string $id): JsonResponse
    {
        $inquiry = RentalInquiry::where('tenant_id', auth()->user()->tenant_id)
            ->findOrFail($id);

        // Validate inquiry is pending
        if ($inquiry->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Only pending inquiries can be rejected',
            ], 422);
        }

        // Update inquiry status
        $inquiry->update([
            'status' => 'rejected',
            'rejection_reason' => $request->rejection_reason,
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        // Send rejection notification
        $inquiry->notify(new InquiryRejected($inquiry));

        return response()->json([
            'success' => true,
            'message' => 'Inquiry rejected',
            'data' => new RentalInquiryResource($inquiry),
        ]);
    }
}
