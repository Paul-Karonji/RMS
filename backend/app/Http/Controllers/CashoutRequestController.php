<?php

namespace App\Http\Controllers;

use App\Models\CashoutRequest;
use App\Http\Requests\StoreCashoutRequestRequest;
use App\Http\Requests\UpdateCashoutRequestRequest;
use App\Services\CashoutService;
use Illuminate\Http\JsonResponse;

class CashoutRequestController extends Controller
{
    protected CashoutService $cashoutService;
    
    public function __construct(CashoutService $cashoutService)
    {
        $this->cashoutService = $cashoutService;
    }
    
    /**
     * Display a listing of cashout requests
     */
    public function index(): JsonResponse
    {
        $user = auth()->user();
        
        $query = CashoutRequest::where('tenant_id', $user->tenant_id);
        
        // Filter by status if provided
        if (request()->has('status')) {
            $query->where('status', request('status'));
        }
        
        $cashouts = $query->latest('created_at')->paginate(20);
        
        // Get statistics
        $statistics = $this->cashoutService->getStatistics($user->tenant_id);
        
        return response()->json([
            'success' => true,
            'data' => $cashouts->items(),
            'meta' => [
                'total' => $cashouts->total(),
                'per_page' => $cashouts->perPage(),
                'current_page' => $cashouts->currentPage(),
                'last_page' => $cashouts->lastPage(),
            ],
            'statistics' => $statistics,
        ]);
    }

    /**
     * Store a newly created cashout request
     */
    public function store(StoreCashoutRequestRequest $request): JsonResponse
    {
        try {
            $cashout = $this->cashoutService->createRequest([
                'tenant_id' => auth()->user()->tenant_id,
                'amount' => $request->amount,
                'payment_method' => $request->payment_method,
                'payment_details' => $request->payment_details,
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Cashout request created successfully. Your request is pending approval.',
                'data' => $cashout,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Display the specified cashout request
     */
    public function show(CashoutRequest $cashoutRequest): JsonResponse
    {
        // Ensure user can only view their own requests
        if ($cashoutRequest->tenant_id !== auth()->user()->tenant_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }
        
        return response()->json([
            'success' => true,
            'data' => $cashoutRequest,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CashoutRequest $cashoutRequest)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCashoutRequestRequest $request, CashoutRequest $cashoutRequest)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CashoutRequest $cashoutRequest)
    {
        //
    }
}
