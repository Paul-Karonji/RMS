<?php

namespace App\Services\Payment;

use App\Models\Payment;
use App\Models\Lease;
use App\Services\BalanceService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    private StripePaymentService $stripeService;
    private MpesaPaymentService $mpesaService;
    private BalanceService $balanceService;

    public function __construct(
        StripePaymentService $stripeService,
        MpesaPaymentService $mpesaService,
        BalanceService $balanceService
    ) {
        $this->stripeService = $stripeService;
        $this->mpesaService = $mpesaService;
        $this->balanceService = $balanceService;
    }

    /**
     * Initiate payment
     *
     * @param array $data
     * @return array
     */
    public function initiatePayment(array $data): array
    {
        try {
            DB::beginTransaction();

            // Validate lease
            $lease = Lease::findOrFail($data['lease_id']);

            // Create payment record
            $payment = Payment::create([
                'lease_id' => $lease->id,
                'tenant_id' => $lease->tenant_id,
                'payment_type' => $data['payment_type'],
                'amount' => $data['amount'],
                'payment_method' => $data['payment_method'],
                'payment_date' => now(),
                'due_date' => $data['due_date'] ?? now(),
                'status' => 'pending',
            ]);

            // Route to appropriate payment gateway
            $result = match ($data['payment_method']) {
                'stripe' => $this->processStripePayment($payment),
                'mpesa' => $this->processMpesaPayment($payment, $data['mpesa_phone']),
                default => ['success' => false, 'error' => 'Invalid payment method'],
            };

            if ($result['success']) {
                DB::commit();
                
                Log::info('Payment initiated successfully', [
                    'payment_id' => $payment->id,
                    'method' => $data['payment_method'],
                ]);

                return [
                    'success' => true,
                    'payment' => $payment->fresh(),
                    'gateway_data' => $result,
                ];
            }

            DB::rollBack();
            return $result;

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Payment initiation failed', [
                'error' => $e->getMessage(),
                'data' => $data,
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Process Stripe payment
     *
     * @param Payment $payment
     * @return array
     */
    private function processStripePayment(Payment $payment): array
    {
        return $this->stripeService->createPaymentIntent($payment);
    }

    /**
     * Process M-Pesa payment
     *
     * @param Payment $payment
     * @param string $phoneNumber
     * @return array
     */
    private function processMpesaPayment(Payment $payment, string $phoneNumber): array
    {
        return $this->mpesaService->initiateStkPush($payment, $phoneNumber);
    }

    /**
     * Handle successful payment
     *
     * @param Payment $payment
     * @return bool
     */
    public function handleSuccessfulPayment(Payment $payment): bool
    {
        try {
            DB::beginTransaction();

            // Update payment status
            $payment->update([
                'status' => 'completed',
                'payment_date' => now(),
            ]);

            // Update balances
            $this->balanceService->updateBalancesAfterPayment($payment);

            // Update unit status if this is first payment (deposit)
            if ($payment->payment_type === 'deposit') {
                $payment->lease->unit->update(['status' => 'occupied']);
            }

            DB::commit();

            Log::info('Payment processed successfully', [
                'payment_id' => $payment->id,
                'amount' => $payment->amount,
            ]);

            return true;

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Failed to process successful payment', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Handle failed payment
     *
     * @param Payment $payment
     * @param string $reason
     * @return bool
     */
    public function handleFailedPayment(Payment $payment, string $reason): bool
    {
        try {
            $payment->update([
                'status' => 'failed',
                'notes' => $reason,
            ]);

            Log::info('Payment marked as failed', [
                'payment_id' => $payment->id,
                'reason' => $reason,
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error('Failed to mark payment as failed', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Get payment status
     *
     * @param Payment $payment
     * @return array
     */
    public function getPaymentStatus(Payment $payment): array
    {
        if ($payment->payment_method === 'stripe' && $payment->stripe_payment_intent_id) {
            return $this->stripeService->getPaymentIntentStatus($payment->stripe_payment_intent_id);
        }

        if ($payment->payment_method === 'mpesa' && $payment->mpesa_checkout_request_id) {
            return $this->mpesaService->queryTransactionStatus($payment->mpesa_checkout_request_id);
        }

        return [
            'success' => true,
            'status' => $payment->status,
        ];
    }
}
