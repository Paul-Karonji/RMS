<?php

namespace App\Http\Controllers;

use App\Models\OwnerPayment;
use App\Http\Requests\StoreOwnerPaymentRequest;
use App\Http\Requests\UpdateOwnerPaymentRequest;
use App\Services\OwnerPaymentService;
use Illuminate\Http\JsonResponse;

class OwnerPaymentController extends Controller
{
    protected OwnerPaymentService $ownerPaymentService;
    
    public function __construct(OwnerPaymentService $ownerPaymentService)
    {
        $this->ownerPaymentService = $ownerPaymentService;
    }
    
    /**
     * Display a listing of owner payments
     */
    public function index(): JsonResponse
    {
        $user = auth()->user();
        
        $query = OwnerPayment::where('tenant_id', $user->tenant_id)
            ->with(['propertyOwner:id,owner_name,email', 'createdBy:id,name']);
        
        // Filter by property owner if provided
        if (request()->has('property_owner_id')) {
            $query->where('property_owner_id', request('property_owner_id'));
        }
        
        // Filter by payment method if provided
        if (request()->has('payment_method')) {
            $query->where('payment_method', request('payment_method'));
        }
        
        $payments = $query->latest('payment_date')->paginate(20);
        
        // Get statistics
        $statistics = $this->ownerPaymentService->getStatistics($user->tenant_id);
        
        return response()->json([
            'success' => true,
            'data' => $payments->items(),
            'meta' => [
                'total' => $payments->total(),
                'per_page' => $payments->perPage(),
                'current_page' => $payments->currentPage(),
                'last_page' => $payments->lastPage(),
            ],
            'statistics' => $statistics,
        ]);
    }

    /**
     * Store a newly created owner payment
     */
    public function store(StoreOwnerPaymentRequest $request): JsonResponse
    {
        try {
            $payment = $this->ownerPaymentService->markPayment([
                'tenant_id' => auth()->user()->tenant_id,
                'property_owner_id' => $request->property_owner_id,
                'amount' => $request->amount,
                'payment_date' => $request->payment_date,
                'payment_method' => $request->payment_method,
                'transaction_id' => $request->transaction_id,
                'notes' => $request->notes,
                'created_by' => auth()->id(),
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Owner payment marked successfully',
                'data' => $payment,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(OwnerPayment $ownerPayment)
    {
        //
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
    public function edit(OwnerPayment $ownerPayment)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateOwnerPaymentRequest $request, OwnerPayment $ownerPayment)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(OwnerPayment $ownerPayment)
    {
        //
    }
}
