<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\Payment\StripePaymentService;
use App\Services\Payment\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class StripeWebhookController extends Controller
{
    private StripePaymentService $stripeService;
    private PaymentService $paymentService;

    public function __construct(
        StripePaymentService $stripeService,
        PaymentService $paymentService
    ) {
        $this->stripeService = $stripeService;
        $this->paymentService = $paymentService;
    }

    /**
     * Handle Stripe webhook
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function handle(Request $request): JsonResponse
    {
        $payload = $request->getContent();
        $signature = $request->header('Stripe-Signature');

        // Verify webhook signature
        if (!$this->stripeService->verifyWebhookSignature($payload, $signature)) {
            Log::warning('Stripe webhook signature verification failed');
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        $event = json_decode($payload, true);

        Log::info('Stripe webhook received', [
            'type' => $event['type'] ?? 'unknown',
            'id' => $event['id'] ?? 'unknown',
        ]);

        // Handle different event types
        try {
            match ($event['type']) {
                'payment_intent.succeeded' => $this->handlePaymentIntentSucceeded($event),
                'payment_intent.payment_failed' => $this->handlePaymentIntentFailed($event),
                'charge.succeeded' => $this->handleChargeSucceeded($event),
                'charge.failed' => $this->handleChargeFailed($event),
                default => Log::info('Unhandled Stripe event type', ['type' => $event['type']]),
            };

            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            Log::error('Stripe webhook processing failed', [
                'error' => $e->getMessage(),
                'event_type' => $event['type'] ?? 'unknown',
            ]);

            return response()->json(['error' => 'Webhook processing failed'], 500);
        }
    }

    /**
     * Handle payment_intent.succeeded event
     *
     * @param array $event
     * @return void
     */
    private function handlePaymentIntentSucceeded(array $event): void
    {
        $paymentIntent = $event['data']['object'];
        $paymentIntentId = $paymentIntent['id'];

        // Find payment by Stripe payment intent ID
        $payment = Payment::where('stripe_payment_intent_id', $paymentIntentId)->first();

        if (!$payment) {
            Log::warning('Payment not found for Stripe payment intent', [
                'payment_intent_id' => $paymentIntentId,
            ]);
            return;
        }

        // Update payment with Stripe receipt
        $payment->update([
            'gateway_response' => json_encode([
                'payment_intent' => $paymentIntent,
                'receipt_url' => $paymentIntent['charges']['data'][0]['receipt_url'] ?? null,
            ]),
        ]);

        // Handle successful payment
        $this->paymentService->handleSuccessfulPayment($payment);

        Log::info('Stripe payment succeeded', [
            'payment_id' => $payment->id,
            'amount' => $payment->amount,
        ]);
    }

    /**
     * Handle payment_intent.payment_failed event
     *
     * @param array $event
     * @return void
     */
    private function handlePaymentIntentFailed(array $event): void
    {
        $paymentIntent = $event['data']['object'];
        $paymentIntentId = $paymentIntent['id'];

        $payment = Payment::where('stripe_payment_intent_id', $paymentIntentId)->first();

        if (!$payment) {
            Log::warning('Payment not found for failed Stripe payment intent', [
                'payment_intent_id' => $paymentIntentId,
            ]);
            return;
        }

        $failureReason = $paymentIntent['last_payment_error']['message'] ?? 'Payment failed';

        $this->paymentService->handleFailedPayment($payment, $failureReason);

        Log::info('Stripe payment failed', [
            'payment_id' => $payment->id,
            'reason' => $failureReason,
        ]);
    }

    /**
     * Handle charge.succeeded event
     *
     * @param array $event
     * @return void
     */
    private function handleChargeSucceeded(array $event): void
    {
        $charge = $event['data']['object'];
        
        Log::info('Stripe charge succeeded', [
            'charge_id' => $charge['id'],
            'amount' => $charge['amount'] / 100,
        ]);
    }

    /**
     * Handle charge.failed event
     *
     * @param array $event
     * @return void
     */
    private function handleChargeFailed(array $event): void
    {
        $charge = $event['data']['object'];
        
        Log::info('Stripe charge failed', [
            'charge_id' => $charge['id'],
            'failure_message' => $charge['failure_message'] ?? 'Unknown',
        ]);
    }
}
