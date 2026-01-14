<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\CashoutRequest;
use App\Services\CashoutService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CashoutApprovalController extends Controller
{
    protected CashoutService $cashoutService;
    
    public function __construct(CashoutService $cashoutService)
    {
        $this->cashoutService = $cashoutService;
    }
    
    /**
     * List all pending cashout requests
     */
    public function index(): JsonResponse
    {
        $query = CashoutRequest::with('tenant:id,company_name,email');
        
        // Filter by status if provided
        if (request()->has('status')) {
            $query->where('status', request('status'));
        } else {
            // Default to pending
            $query->where('status', 'pending');
        }
        
        $cashouts = $query->latest('created_at')->paginate(20);
        
        return response()->json([
            'success' => true,
            'data' => $cashouts->items(),
            'meta' => [
                'total' => $cashouts->total(),
                'per_page' => $cashouts->perPage(),
                'current_page' => $cashouts->currentPage(),
                'last_page' => $cashouts->lastPage(),
            ],
        ]);
    }
    
    /**
     * Approve cashout request
     */
    public function approve(Request $request, string $id): JsonResponse
    {
        try {
            $cashout = CashoutRequest::findOrFail($id);
            $cashout = $this->cashoutService->approve($cashout, auth()->id());
            
            return response()->json([
                'success' => true,
                'message' => 'Cashout request approved successfully',
                'data' => $cashout,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }
    
    /**
     * Reject cashout request
     */
    public function reject(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'reason' => 'required|string|max:500',
        ]);
        
        try {
            $cashout = CashoutRequest::findOrFail($id);
            $cashout = $this->cashoutService->reject($cashout, auth()->id(), $request->reason);
            
            return response()->json([
                'success' => true,
                'message' => 'Cashout request rejected',
                'data' => $cashout,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }
    
    /**
     * Mark cashout as processed (after manual transfer)
     */
    public function process(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'transaction_id' => 'required|string|max:255',
        ]);
        
        try {
            $cashout = CashoutRequest::findOrFail($id);
            $cashout = $this->cashoutService->process($cashout, $request->transaction_id);
            
            return response()->json([
                'success' => true,
                'message' => 'Cashout marked as processed. Company balance has been updated.',
                'data' => $cashout,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
