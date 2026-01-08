<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Http\Requests\Payment\InitiatePaymentRequest;
use App\Http\Requests\StorePaymentRequest;
use App\Http\Requests\UpdatePaymentRequest;
use App\Services\Payment\PaymentService;
use App\Http\Resources\PaymentResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    private PaymentService $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * Display a listing of payments
     */
    public function index(Request $request): JsonResponse
    {
        $query = Payment::with(['lease.unit', 'lease.property'])
            ->where('tenant_id', $request->user()->tenant_id);

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by payment type
        if ($request->has('payment_type')) {
            $query->where('payment_type', $request->payment_type);
        }

        // Filter by date range
        if ($request->has('start_date')) {
            $query->whereDate('payment_date', '>=', $request->start_date);
        }
        if ($request->has('end_date')) {
            $query->whereDate('payment_date', '<=', $request->end_date);
        }

        $payments = $query->latest('payment_date')->paginate(20);

        return response()->json([
            'success' => true,
            'data' => PaymentResource::collection($payments),
            'meta' => [
                'total' => $payments->total(),
                'per_page' => $payments->perPage(),
                'current_page' => $payments->currentPage(),
                'last_page' => $payments->lastPage(),
            ],
        ]);
    }

    /**
     * Initiate a new payment
     */
    public function store(InitiatePaymentRequest $request): JsonResponse
    {
        $result = $this->paymentService->initiatePayment($request->validated());

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => 'Payment initiated successfully',
                'data' => [
                    'payment' => new PaymentResource($result['payment']),
                    'gateway_data' => $result['gateway_data'],
                ],
            ], 201);
        }

        return response()->json([
            'success' => false,
            'message' => 'Payment initiation failed',
            'error' => $result['error'] ?? 'Unknown error',
        ], 400);
    }

    /**
     * Display the specified payment
     */
    public function show(Payment $payment): JsonResponse
    {
        // Ensure user can only view their own payments
        if ($payment->tenant_id !== auth()->user()->tenant_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => new PaymentResource($payment->load(['lease.unit', 'lease.property'])),
        ]);
    }

    /**
     * Get payment status
     */
    public function status(Payment $payment): JsonResponse
    {
        // Ensure user can only check their own payments
        if ($payment->tenant_id !== auth()->user()->tenant_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $status = $this->paymentService->getPaymentStatus($payment);

        return response()->json([
            'success' => true,
            'data' => [
                'payment_id' => $payment->id,
                'status' => $payment->status,
                'gateway_status' => $status,
            ],
        ]);
    }

    /**
     * Update the specified payment (admin only)
     */
    public function update(UpdatePaymentRequest $request, Payment $payment): JsonResponse
    {
        $payment->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Payment updated successfully',
            'data' => new PaymentResource($payment),
        ]);
    }

    /**
     * Remove the specified payment (admin only)
     */
    public function destroy(Payment $payment): JsonResponse
    {
        // Only allow deletion of pending/failed payments
        if (!in_array($payment->status, ['pending', 'failed'])) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete completed payments',
            ], 400);
        }

        $payment->delete();

        return response()->json([
            'success' => true,
            'message' => 'Payment deleted successfully',
        ]);
    }
}

