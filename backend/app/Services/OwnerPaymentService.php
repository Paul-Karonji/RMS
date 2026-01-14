<?php

namespace App\Services;

use App\Models\OwnerPayment;
use App\Models\OwnerBalance;
use Illuminate\Support\Facades\DB;

class OwnerPaymentService
{
    /**
     * Mark payment to owner
     * 
     * @param array $data
     * @return OwnerPayment
     * @throws \Exception
     */
    public function markPayment(array $data): OwnerPayment
    {
        return DB::transaction(function () use ($data) {
            // Validate owner balance exists
            $ownerBalance = OwnerBalance::where('property_owner_id', $data['property_owner_id'])->first();
            
            if (!$ownerBalance) {
                throw new \Exception('Owner balance not found');
            }
            
            // Validate payment amount doesn't exceed amount owed
            if ($data['amount'] > $ownerBalance->amount_owed) {
                throw new \Exception('Payment amount (' . number_format($data['amount'], 2) . ') exceeds amount owed (' . number_format($ownerBalance->amount_owed, 2) . ')');
            }
            
            // Create owner payment record
            $payment = OwnerPayment::create([
                'id' => \Illuminate\Support\Str::uuid(),
                'tenant_id' => $data['tenant_id'],
                'property_owner_id' => $data['property_owner_id'],
                'amount' => $data['amount'],
                'payment_date' => $data['payment_date'],
                'payment_method' => $data['payment_method'],
                'transaction_id' => $data['transaction_id'] ?? null,
                'notes' => $data['notes'] ?? null,
                'created_by' => $data['created_by'],
            ]);
            
            // Update owner balance
            $ownerBalance->update([
                'amount_paid' => $ownerBalance->amount_paid + $data['amount'],
                'amount_owed' => $ownerBalance->amount_owed - $data['amount'],
                'total_paid' => $ownerBalance->total_paid + $data['amount'],
                'last_payment_date' => $data['payment_date'],
                'last_payment_amount' => $data['amount'],
            ]);
            
            return $payment->fresh(['propertyOwner']);
        });
    }
    
    /**
     * Get payment history for an owner
     * 
     * @param string $propertyOwnerId
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getOwnerPaymentHistory(string $propertyOwnerId, int $limit = 10)
    {
        return OwnerPayment::where('property_owner_id', $propertyOwnerId)
            ->with('createdBy:id,name')
            ->latest('payment_date')
            ->limit($limit)
            ->get();
    }
    
    /**
     * Get payment statistics for a tenant
     * 
     * @param string $tenantId
     * @return array
     */
    public function getStatistics(string $tenantId): array
    {
        $totalPaid = OwnerPayment::where('tenant_id', $tenantId)->sum('amount');
        
        $thisMonth = OwnerPayment::where('tenant_id', $tenantId)
            ->whereYear('payment_date', now()->year)
            ->whereMonth('payment_date', now()->month)
            ->sum('amount');
            
        $lastMonth = OwnerPayment::where('tenant_id', $tenantId)
            ->whereYear('payment_date', now()->subMonth()->year)
            ->whereMonth('payment_date', now()->subMonth()->month)
            ->sum('amount');
        
        return [
            'total_paid' => $totalPaid,
            'this_month' => $thisMonth,
            'last_month' => $lastMonth,
        ];
    }
}
