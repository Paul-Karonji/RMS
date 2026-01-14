<?php

namespace App\Services;

use App\Models\CashoutRequest;
use App\Models\CompanyBalance;
use App\Models\Tenant;
use Illuminate\Support\Facades\DB;

class CashoutService
{
    /**
     * Create a cashout request
     * 
     * @param array $data
     * @return CashoutRequest
     * @throws \Exception
     */
    public function createRequest(array $data): CashoutRequest
    {
        $tenant = Tenant::findOrFail($data['tenant_id']);
        $companyBalance = CompanyBalance::where('tenant_id', $tenant->id)->firstOrFail();
        
        // Validate sufficient balance
        if ($companyBalance->available_balance < $data['amount']) {
            throw new \Exception('Insufficient balance. Available: ' . number_format($companyBalance->available_balance, 2));
        }
        
        // Validate minimum cashout amount
        $minAmount = $tenant->min_cashout_amount ?? 1000.00;
        if ($data['amount'] < $minAmount) {
            throw new \Exception('Minimum cashout amount is ' . number_format($minAmount, 2));
        }
        
        // Calculate fee (3% from tenant settings or default)
        $feePercentage = $tenant->cashout_fee_percentage ?? 3.00;
        $feeAmount = ($data['amount'] * $feePercentage) / 100;
        $netAmount = $data['amount'] - $feeAmount;
        
        return DB::transaction(function () use ($data, $feeAmount, $netAmount) {
            // Create cashout request
            $cashout = CashoutRequest::create([
                'id' => \Illuminate\Support\Str::uuid(),
                'tenant_id' => $data['tenant_id'],
                'amount' => $data['amount'],
                'fee_amount' => $feeAmount,
                'net_amount' => $netAmount,
                'payment_method' => $data['payment_method'],
                'payment_details' => $data['payment_details'] ?? null,
                'status' => 'pending',
            ]);
            
            return $cashout;
        });
    }
    
    /**
     * Approve cashout request
     * 
     * @param CashoutRequest $cashout
     * @param string $platformUserId
     * @return CashoutRequest
     * @throws \Exception
     */
    public function approve(CashoutRequest $cashout, string $platformUserId): CashoutRequest
    {
        if ($cashout->status !== 'pending') {
            throw new \Exception('Only pending requests can be approved');
        }
        
        return DB::transaction(function () use ($cashout, $platformUserId) {
            $cashout->update([
                'status' => 'approved',
                'approved_by' => $platformUserId,
                'approved_at' => now(),
            ]);
            
            return $cashout->fresh();
        });
    }
    
    /**
     * Process approved cashout (mark as completed)
     * 
     * @param CashoutRequest $cashout
     * @param string $transactionId
     * @return CashoutRequest
     * @throws \Exception
     */
    public function process(CashoutRequest $cashout, string $transactionId): CashoutRequest
    {
        if ($cashout->status !== 'approved') {
            throw new \Exception('Only approved requests can be processed');
        }
        
        return DB::transaction(function () use ($cashout, $transactionId) {
            $companyBalance = CompanyBalance::where('tenant_id', $cashout->tenant_id)->firstOrFail();
            
            // Validate balance still sufficient
            if ($companyBalance->available_balance < $cashout->amount) {
                throw new \Exception('Insufficient balance to process cashout');
            }
            
            // Deduct from company balance
            $companyBalance->update([
                'available_balance' => $companyBalance->available_balance - $cashout->amount,
                'total_cashed_out' => $companyBalance->total_cashed_out + $cashout->net_amount,
                'total_platform_fees_paid' => $companyBalance->total_platform_fees_paid + $cashout->fee_amount,
                'last_cashout_at' => now(),
                'last_cashout_amount' => $cashout->net_amount,
            ]);
            
            // Update cashout request
            $cashout->update([
                'status' => 'processed',
                'transaction_id' => $transactionId,
                'processed_at' => now(),
            ]);
            
            return $cashout->fresh();
        });
    }
    
    /**
     * Reject cashout request
     * 
     * @param CashoutRequest $cashout
     * @param string $platformUserId
     * @param string $reason
     * @return CashoutRequest
     * @throws \Exception
     */
    public function reject(CashoutRequest $cashout, string $platformUserId, string $reason): CashoutRequest
    {
        if ($cashout->status !== 'pending') {
            throw new \Exception('Only pending requests can be rejected');
        }
        
        $cashout->update([
            'status' => 'rejected',
            'rejected_by' => $platformUserId,
            'rejected_at' => now(),
            'rejection_reason' => $reason,
        ]);
        
        return $cashout->fresh();
    }
    
    /**
     * Get cashout statistics for a tenant
     * 
     * @param string $tenantId
     * @return array
     */
    public function getStatistics(string $tenantId): array
    {
        $pending = CashoutRequest::where('tenant_id', $tenantId)
            ->where('status', 'pending')
            ->sum('amount');
            
        $approved = CashoutRequest::where('tenant_id', $tenantId)
            ->where('status', 'approved')
            ->sum('net_amount');
            
        $processed = CashoutRequest::where('tenant_id', $tenantId)
            ->where('status', 'processed')
            ->sum('net_amount');
            
        $totalFees = CashoutRequest::where('tenant_id', $tenantId)
            ->whereIn('status', ['approved', 'processed'])
            ->sum('fee_amount');
        
        return [
            'pending_amount' => $pending,
            'approved_amount' => $approved,
            'total_cashed_out' => $processed,
            'total_fees_paid' => $totalFees,
        ];
    }
}
