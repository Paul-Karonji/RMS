<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\Payment\MpesaPaymentService;
use App\Services\Payment\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class MpesaWebhookController extends Controller
{
    private MpesaPaymentService $mpesaService;
    private PaymentService $paymentService;

    public function __construct(
        MpesaPaymentService $mpesaService,
        PaymentService $paymentService
    ) {
        $this->mpesaService = $mpesaService;
        $this->paymentService = $paymentService;
    }

    /**
     * Handle M-Pesa callback
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function handle(Request $request): JsonResponse
    {
        $callbackData = $request->all();

        Log::info('M-Pesa callback received', [
            'data' => $callbackData,
        ]);

        // Validate callback
        if (!$this->mpesaService->validateCallback($callbackData)) {
            Log::warning('Invalid M-Pesa callback received');
            return response()->json([
                'ResultCode' => 1,
                'ResultDesc' => 'Invalid callback data',
            ]);
        }

        try {
            // Process callback
            $result = $this->mpesaService->processCallback($callbackData);

            // Find payment by checkout request ID
            $payment = Payment::where('mpesa_checkout_request_id', $result['checkout_request_id'])->first();

            if (!$payment) {
                Log::warning('Payment not found for M-Pesa callback', [
                    'checkout_request_id' => $result['checkout_request_id'],
                ]);
                
                return response()->json([
                    'ResultCode' => 1,
                    'ResultDesc' => 'Payment not found',
                ]);
            }

            // Check result code (0 = success)
            if ($result['result_code'] == 0) {
                // Update payment with M-Pesa receipt
                $payment->update([
                    'gateway_response' => json_encode([
                        'mpesa_receipt' => $result['mpesa_receipt'],
                        'transaction_date' => $result['transaction_date'],
                        'phone_number' => $result['phone_number'],
                        'amount' => $result['amount'],
                    ]),
                ]);

                // Handle successful payment
                $this->paymentService->handleSuccessfulPayment($payment);

                Log::info('M-Pesa payment succeeded', [
                    'payment_id' => $payment->id,
                    'mpesa_receipt' => $result['mpesa_receipt'],
                ]);

                return response()->json([
                    'ResultCode' => 0,
                    'ResultDesc' => 'Success',
                ]);
            }

            // Payment failed
            $this->paymentService->handleFailedPayment($payment, $result['result_desc']);

            Log::info('M-Pesa payment failed', [
                'payment_id' => $payment->id,
                'reason' => $result['result_desc'],
            ]);

            return response()->json([
                'ResultCode' => 0,
                'ResultDesc' => 'Acknowledged',
            ]);

        } catch (\Exception $e) {
            Log::error('M-Pesa callback processing failed', [
                'error' => $e->getMessage(),
                'callback' => $callbackData,
            ]);

            return response()->json([
                'ResultCode' => 1,
                'ResultDesc' => 'Processing failed',
            ]);
        }
    }

    /**
     * Handle M-Pesa result callback
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function result(Request $request): JsonResponse
    {
        Log::info('M-Pesa result callback received', [
            'data' => $request->all(),
        ]);

        return response()->json([
            'ResultCode' => 0,
            'ResultDesc' => 'Success',
        ]);
    }

    /**
     * Handle M-Pesa timeout callback
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function timeout(Request $request): JsonResponse
    {
        $callbackData = $request->all();

        Log::warning('M-Pesa timeout callback received', [
            'data' => $callbackData,
        ]);

        // Try to find and mark payment as failed
        if (isset($callbackData['CheckoutRequestID'])) {
            $payment = Payment::where('mpesa_checkout_request_id', $callbackData['CheckoutRequestID'])->first();
            
            if ($payment) {
                $this->paymentService->handleFailedPayment($payment, 'Transaction timeout');
            }
        }

        return response()->json([
            'ResultCode' => 0,
            'ResultDesc' => 'Timeout acknowledged',
        ]);
    }
}
