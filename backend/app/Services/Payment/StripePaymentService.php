<?php

namespace App\Services\Payment;

use Stripe\Stripe;
use Stripe\PaymentIntent;
use Stripe\Customer;
use Stripe\Exception\ApiErrorException;
use App\Models\Payment;
use Illuminate\Support\Facades\Log;

class StripePaymentService
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    /**
     * Create a payment intent for card payment
     *
     * @param Payment $payment
     * @param array $metadata
     * @return array
     */
    public function createPaymentIntent(Payment $payment, array $metadata = []): array
    {
        try {
            $paymentIntent = PaymentIntent::create([
                'amount' => $this->convertToStripeAmount($payment->amount),
                'currency' => config('services.stripe.currency', 'kes'),
                'metadata' => array_merge([
                    'payment_id' => $payment->id,
                    'lease_id' => $payment->lease_id,
                    'tenant_id' => $payment->tenant_id,
                    'payment_type' => $payment->payment_type,
                ], $metadata),
                'description' => $this->getPaymentDescription($payment),
            ]);

            // Update payment record with Stripe payment intent ID
            $payment->update([
                'stripe_payment_intent_id' => $paymentIntent->id,
                'gateway_response' => json_encode([
                    'client_secret' => $paymentIntent->client_secret,
                    'status' => $paymentIntent->status,
                ]),
            ]);

            Log::info('Stripe payment intent created', [
                'payment_id' => $payment->id,
                'intent_id' => $paymentIntent->id,
                'amount' => $payment->amount,
            ]);

            return [
                'success' => true,
                'payment_intent_id' => $paymentIntent->id,
                'client_secret' => $paymentIntent->client_secret,
                'status' => $paymentIntent->status,
            ];

        } catch (ApiErrorException $e) {
            Log::error('Stripe payment intent creation failed', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
            ]);

            $payment->update([
                'status' => 'failed',
                'gateway_response' => json_encode([
                    'error' => $e->getMessage(),
                ]),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Retrieve payment intent status
     *
     * @param string $paymentIntentId
     * @return array
     */
    public function getPaymentIntentStatus(string $paymentIntentId): array
    {
        try {
            $paymentIntent = PaymentIntent::retrieve($paymentIntentId);

            return [
                'success' => true,
                'status' => $paymentIntent->status,
                'amount' => $this->convertFromStripeAmount($paymentIntent->amount),
                'payment_intent' => $paymentIntent,
            ];

        } catch (ApiErrorException $e) {
            Log::error('Failed to retrieve payment intent', [
                'intent_id' => $paymentIntentId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Create or retrieve Stripe customer
     *
     * @param \App\Models\User $user
     * @return string|null
     */
    public function createOrGetCustomer($user): ?string
    {
        try {
            // Check if user already has a Stripe customer ID
            if ($user->stripe_customer_id) {
                return $user->stripe_customer_id;
            }

            // Create new customer
            $customer = Customer::create([
                'email' => $user->email,
                'name' => $user->name,
                'phone' => $user->phone,
                'metadata' => [
                    'user_id' => $user->id,
                    'tenant_id' => $user->tenant_id,
                ],
            ]);

            // Save customer ID to user
            $user->update(['stripe_customer_id' => $customer->id]);

            Log::info('Stripe customer created', [
                'user_id' => $user->id,
                'customer_id' => $customer->id,
            ]);

            return $customer->id;

        } catch (ApiErrorException $e) {
            Log::error('Failed to create Stripe customer', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Convert amount to Stripe format (smallest currency unit)
     * For KES, 1 KES = 100 cents
     *
     * @param float $amount
     * @return int
     */
    private function convertToStripeAmount(float $amount): int
    {
        return (int) ($amount * 100);
    }

    /**
     * Convert amount from Stripe format to decimal
     *
     * @param int $amount
     * @return float
     */
    private function convertFromStripeAmount(int $amount): float
    {
        return $amount / 100;
    }

    /**
     * Generate payment description
     *
     * @param Payment $payment
     * @return string
     */
    private function getPaymentDescription(Payment $payment): string
    {
        $type = ucfirst($payment->payment_type);
        $leaseNumber = $payment->lease->lease_number ?? 'N/A';
        
        return "{$type} payment for lease {$leaseNumber}";
    }

    /**
     * Verify webhook signature
     *
     * @param string $payload
     * @param string $signature
     * @return bool
     */
    public function verifyWebhookSignature(string $payload, string $signature): bool
    {
        try {
            $webhookSecret = config('services.stripe.webhook_secret');
            
            if (empty($webhookSecret)) {
                Log::warning('Stripe webhook secret not configured');
                return true; // Allow in development
            }

            \Stripe\Webhook::constructEvent(
                $payload,
                $signature,
                $webhookSecret
            );

            return true;

        } catch (\Exception $e) {
            Log::error('Stripe webhook signature verification failed', [
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
