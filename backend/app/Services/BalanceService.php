<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\CompanyBalance;
use App\Models\OwnerBalance;
use App\Models\PlatformFee;
use App\Models\BalanceTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BalanceService
{
    /**
     * Update balances after successful payment
     *
     * @param Payment $payment
     * @return bool
     */
    public function updateBalancesAfterPayment(Payment $payment): bool
    {
        try {
            DB::beginTransaction();

            $lease = $payment->lease;
            $property = $lease->property;
            $owner = $property->propertyOwner;

            // Calculate platform fee
            $platformFeePercentage = $property->commission_percentage ?? config('services.platform.fee_percentage', 10.00);
            $platformFeeAmount = $this->calculatePlatformFee($payment->amount, $platformFeePercentage);
            $ownerAmount = $payment->amount - $platformFeeAmount;

            // Update company balance
            $this->updateCompanyBalance($lease->tenant_id, $platformFeeAmount, $payment);

            // Update owner balance
            $this->updateOwnerBalance($owner->id, $payment->amount, $platformFeeAmount, $payment);

            // Create platform fee record
            $this->createPlatformFeeRecord($payment, $property, $platformFeePercentage, $platformFeeAmount);

            // Log balance transaction
            $this->logBalanceTransaction($payment, $platformFeeAmount, $ownerAmount);

            DB::commit();

            Log::info('Balances updated successfully', [
                'payment_id' => $payment->id,
                'platform_fee' => $platformFeeAmount,
                'owner_amount' => $ownerAmount,
            ]);

            return true;

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Failed to update balances', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Calculate platform fee
     *
     * @param float $amount
     * @param float $percentage
     * @return float
     */
    private function calculatePlatformFee(float $amount, float $percentage): float
    {
        return round(($amount * $percentage) / 100, 2);
    }

    /**
     * Update company balance
     *
     * @param string $tenantId
     * @param float $platformFee
     * @param Payment $payment
     * @return void
     */
    private function updateCompanyBalance(string $tenantId, float $platformFee, Payment $payment): void
    {
        $companyBalance = CompanyBalance::firstOrCreate(
            ['tenant_id' => $tenantId],
            [
                'total_rent_collected' => 0,
                'platform_fees_collected' => 0,
                'total_expenses' => 0,
                'pending_cashout' => 0,
                'available_balance' => 0,
            ]
        );

        $companyBalance->increment('total_rent_collected', $payment->amount);
        $companyBalance->increment('platform_fees_collected', $platformFee);
        $companyBalance->increment('available_balance', $platformFee);

        Log::info('Company balance updated', [
            'tenant_id' => $tenantId,
            'platform_fee' => $platformFee,
            'new_balance' => $companyBalance->available_balance,
        ]);
    }

    /**
     * Update owner balance
     *
     * @param string $ownerId
     * @param float $rentAmount
     * @param float $platformFee
     * @param Payment $payment
     * @return void
     */
    private function updateOwnerBalance(string $ownerId, float $rentAmount, float $platformFee, Payment $payment): void
    {
        $ownerBalance = OwnerBalance::firstOrCreate(
            ['property_owner_id' => $ownerId],
            [
                'total_rent_collected' => 0,
                'total_expenses' => 0,
                'total_paid_out' => 0,
                'pending_balance' => 0,
            ]
        );

        $ownerBalance->increment('total_rent_collected', $rentAmount);
        $ownerBalance->increment('pending_balance', $rentAmount - $platformFee);

        Log::info('Owner balance updated', [
            'owner_id' => $ownerId,
            'rent_amount' => $rentAmount,
            'platform_fee' => $platformFee,
            'new_pending' => $ownerBalance->pending_balance,
        ]);
    }

    /**
     * Create platform fee record
     *
     * @param Payment $payment
     * @param \App\Models\Property $property
     * @param float $feePercentage
     * @param float $feeAmount
     * @return void
     */
    private function createPlatformFeeRecord(Payment $payment, $property, float $feePercentage, float $feeAmount): void
    {
        PlatformFee::create([
            'tenant_id' => $payment->tenant_id,
            'payment_id' => $payment->id,
            'property_id' => $property->id,
            'fee_type' => $payment->payment_type,
            'fee_percentage' => $feePercentage,
            'fee_amount' => $feeAmount,
            'base_amount' => $payment->amount,
        ]);

        Log::info('Platform fee record created', [
            'payment_id' => $payment->id,
            'fee_amount' => $feeAmount,
        ]);
    }

    /**
     * Log balance transaction
     *
     * @param Payment $payment
     * @param float $platformFee
     * @param float $ownerAmount
     * @return void
     */
    private function logBalanceTransaction(Payment $payment, float $platformFee, float $ownerAmount): void
    {
        BalanceTransaction::create([
            'tenant_id' => $payment->tenant_id,
            'payment_id' => $payment->id,
            'property_owner_id' => $payment->lease->property->property_owner_id,
            'transaction_type' => $payment->payment_type . '_payment',
            'amount' => $payment->amount,
            'fee_amount' => $platformFee,
            'net_amount' => $ownerAmount,
            'transaction_date' => now(),
            'description' => $this->getTransactionDescription($payment),
        ]);

        Log::info('Balance transaction logged', [
            'payment_id' => $payment->id,
        ]);
    }

    /**
     * Get transaction description
     *
     * @param Payment $payment
     * @return string
     */
    private function getTransactionDescription(Payment $payment): string
    {
        $type = ucfirst($payment->payment_type);
        $leaseNumber = $payment->lease->lease_number ?? 'N/A';
        
        return "{$type} payment for lease {$leaseNumber} - Amount: KES " . number_format($payment->amount, 2);
    }
}
