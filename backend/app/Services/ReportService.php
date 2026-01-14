<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Expense;
use App\Models\PlatformFee;
use App\Models\OwnerPayment;
use App\Models\Unit;
use App\Models\Lease;
use App\Models\OwnerBalance;
use App\Models\BalanceTransaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportService
{
    /**
     * Generate financial report
     * Schema verified: payments, expenses, platform_fees, owner_payments
     */
    public function generateFinancialReport(string $tenantId, Carbon $startDate, Carbon $endDate, ?string $propertyId = null): array
    {
        // Total revenue - Schema: payments (amount, status, payment_date, payment_type)
        $revenueQuery = Payment::where('status', 'completed')
            ->whereHas('lease', function($q) use ($tenantId, $propertyId) {
                $q->whereHas('property', function($q2) use ($tenantId, $propertyId) {
                    $q2->where('tenant_id', $tenantId);
                    if ($propertyId) {
                        $q2->where('id', $propertyId);
                    }
                });
            })
            ->whereBetween('payment_date', [$startDate, $endDate]);
        
        $totalRevenue = $revenueQuery->sum('amount');
        
        // Revenue by property
        $revenueByProperty = Payment::where('status', 'completed')
            ->whereHas('lease', function($q) use ($tenantId) {
                $q->whereHas('property', function($q2) use ($tenantId) {
                    $q2->where('tenant_id', $tenantId);
                });
            })
            ->whereBetween('payment_date', [$startDate, $endDate])
            ->join('leases', 'payments.lease_id', '=', 'leases.id')
            ->join('properties', 'leases.property_id', '=', 'properties.id')
            ->select('properties.id', 'properties.property_name', DB::raw('SUM(payments.amount) as revenue'))
            ->groupBy('properties.id', 'properties.property_name')
            ->get();
        
        // Total expenses - Schema: expenses (amount, expense_date, category, property_id)
        $expenseQuery = Expense::where('tenant_id', $tenantId)
            ->whereBetween('expense_date', [$startDate, $endDate]);
        
        if ($propertyId) {
            $expenseQuery->where('property_id', $propertyId);
        }
        
        $totalExpenses = $expenseQuery->sum('amount');
        
        // Expenses by category - Schema: expenses (category, amount)
        $expensesByCategory = Expense::where('tenant_id', $tenantId)
            ->whereBetween('expense_date', [$startDate, $endDate])
            ->when($propertyId, function($q) use ($propertyId) {
                $q->where('property_id', $propertyId);
            })
            ->select('category', DB::raw('SUM(amount) as total'))
            ->groupBy('category')
            ->get();
        
        // Platform fees paid - Schema: platform_fees (fee_amount, paid_at)
        $platformFees = PlatformFee::where('tenant_id', $tenantId)
            ->where('status', 'paid')
            ->whereBetween('paid_at', [$startDate, $endDate])
            ->sum('fee_amount');
        
        // Owner payments made - Schema: owner_payments (amount, payment_date)
        $ownerPayments = OwnerPayment::where('tenant_id', $tenantId)
            ->whereBetween('payment_date', [$startDate, $endDate])
            ->sum('amount');
        
        // Calculate net income
        $netIncome = $totalRevenue - $totalExpenses - $platformFees - $ownerPayments;
        
        return [
            'period' => [
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString(),
            ],
            'summary' => [
                'total_revenue' => (float) $totalRevenue,
                'total_expenses' => (float) $totalExpenses,
                'platform_fees_paid' => (float) $platformFees,
                'owner_payments_made' => (float) $ownerPayments,
                'net_income' => (float) $netIncome,
            ],
            'revenue_by_property' => $revenueByProperty->map(function($item) {
                return [
                    'property_id' => $item->id,
                    'property_name' => $item->property_name,
                    'revenue' => (float) $item->revenue,
                ];
            }),
            'expenses_by_category' => $expensesByCategory->map(function($item) {
                return [
                    'category' => $item->category,
                    'total' => (float) $item->total,
                ];
            }),
        ];
    }
    
    /**
     * Generate occupancy report
     * Schema verified: units, leases
     */
    public function generateOccupancyReport(string $tenantId, Carbon $startDate, Carbon $endDate): array
    {
        // Get all units for tenant - Schema: units (status, property_id, monthly_rent)
        $units = Unit::whereHas('property', function($q) use ($tenantId) {
                $q->where('tenant_id', $tenantId);
            })
            ->with('property')
            ->get();
        
        $totalUnits = $units->count();
        $occupiedUnits = $units->where('status', 'occupied')->count();
        $vacantUnits = $units->where('status', 'available')->count();
        $maintenanceUnits = $units->where('status', 'maintenance')->count();
        
        $occupancyRate = $totalUnits > 0 ? ($occupiedUnits / $totalUnits) * 100 : 0;
        
        // Vacant units list - Schema: units (unit_number, unit_type, monthly_rent)
        $vacantUnitsList = $units->where('status', 'available')->map(function($unit) {
            return [
                'unit_id' => $unit->id,
                'unit_number' => $unit->unit_number,
                'unit_type' => $unit->unit_type,
                'bedrooms' => $unit->bedrooms,
                'monthly_rent' => (float) $unit->monthly_rent,
                'property_name' => $unit->property->property_name,
            ];
        })->values();
        
        // Occupied units list - Schema: units (unit_number, unit_type)
        $occupiedUnitsList = $units->where('status', 'occupied')->map(function($unit) {
            return [
                'unit_id' => $unit->id,
                'unit_number' => $unit->unit_number,
                'unit_type' => $unit->unit_type,
                'bedrooms' => $unit->bedrooms,
                'monthly_rent' => (float) $unit->monthly_rent,
                'property_name' => $unit->property->property_name,
            ];
        })->values();
        
        // Calculate average occupancy duration - Schema: leases (start_date, end_date, status, unit_id)
        $completedLeases = Lease::whereHas('property', function($q) use ($tenantId) {
                $q->where('tenant_id', $tenantId);
            })
            ->where('status', 'expired')
            ->whereBetween('end_date', [$startDate, $endDate])
            ->get();
        
        $avgDuration = $completedLeases->count() > 0 
            ? $completedLeases->avg(function($lease) {
                return Carbon::parse($lease->start_date)->diffInDays($lease->end_date);
            })
            : 0;
        
        // Calculate turnover rate (leases ended / total units)
        $leasesEnded = $completedLeases->count();
        $turnoverRate = $totalUnits > 0 ? ($leasesEnded / $totalUnits) * 100 : 0;
        
        return [
            'period' => [
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString(),
            ],
            'summary' => [
                'total_units' => $totalUnits,
                'occupied_units' => $occupiedUnits,
                'vacant_units' => $vacantUnits,
                'maintenance_units' => $maintenanceUnits,
                'occupancy_rate' => round($occupancyRate, 2),
                'average_occupancy_duration_days' => round($avgDuration, 0),
                'turnover_rate' => round($turnoverRate, 2),
            ],
            'vacant_units' => $vacantUnitsList,
            'occupied_units' => $occupiedUnitsList,
        ];
    }
    
    /**
     * Generate payment report
     * Schema verified: payments, leases
     */
    public function generatePaymentReport(string $tenantId, Carbon $startDate, Carbon $endDate, ?string $status = null): array
    {
        // Get all payments in period - Schema: payments (amount, status, payment_date, payment_method, transaction_id)
        $paymentsQuery = Payment::with(['lease.unit', 'lease.property', 'lease.tenant'])
            ->whereHas('lease', function($q) use ($tenantId) {
                $q->whereHas('property', function($q2) use ($tenantId) {
                    $q2->where('tenant_id', $tenantId);
                });
            })
            ->whereBetween('payment_date', [$startDate, $endDate]);
        
        if ($status) {
            $paymentsQuery->where('status', $status);
        }
        
        $payments = $paymentsQuery->get();
        
        // Calculate metrics
        $totalPayments = $payments->count();
        $completedPayments = $payments->where('status', 'completed')->count();
        $pendingPayments = $payments->where('status', 'pending')->count();
        $failedPayments = $payments->where('status', 'failed')->count();
        
        $successRate = $totalPayments > 0 ? ($completedPayments / $totalPayments) * 100 : 0;
        
        // Late payments (pending and past due)
        $latePayments = $payments->filter(function($payment) {
            return $payment->status === 'pending' && Carbon::parse($payment->payment_date)->isPast();
        })->count();
        
        // Payment methods breakdown - Schema: payments (payment_method, amount)
        $paymentsByMethod = $payments->groupBy('payment_method')->map(function($group, $method) {
            return [
                'payment_method' => $method,
                'count' => $group->count(),
                'total_amount' => (float) $group->sum('amount'),
            ];
        })->values();
        
        // Outstanding payments - Schema: payments (amount, status)
        $outstandingAmount = $payments->where('status', 'pending')->sum('amount');
        
        // Payment list
        $paymentList = $payments->map(function($payment) {
            return [
                'id' => $payment->id,
                'amount' => (float) $payment->amount,
                'status' => $payment->status,
                'payment_date' => $payment->payment_date,
                'payment_method' => $payment->payment_method,
                'transaction_id' => $payment->transaction_id,
                'tenant_name' => $payment->lease->tenant->name ?? 'N/A',
                'unit_number' => $payment->lease->unit->unit_number ?? 'N/A',
                'property_name' => $payment->lease->property->property_name ?? 'N/A',
            ];
        });
        
        return [
            'period' => [
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString(),
            ],
            'summary' => [
                'total_payments' => $totalPayments,
                'completed_payments' => $completedPayments,
                'pending_payments' => $pendingPayments,
                'failed_payments' => $failedPayments,
                'late_payments' => $latePayments,
                'payment_success_rate' => round($successRate, 2),
                'total_amount_collected' => (float) $payments->where('status', 'completed')->sum('amount'),
                'outstanding_amount' => (float) $outstandingAmount,
            ],
            'payments_by_method' => $paymentsByMethod,
            'payments' => $paymentList,
        ];
    }
    
    /**
     * Generate owner statement
     * Schema verified: owner_balances, owner_payments, balance_transactions
     */
    public function generateOwnerStatement(string $ownerId, Carbon $startDate, Carbon $endDate): array
    {
        // Get owner balance - Schema: owner_balances (total_rent_collected, total_expenses, total_platform_fees, amount_owed, total_paid)
        $ownerBalance = OwnerBalance::where('property_owner_id', $ownerId)->first();
        
        // Revenue generated in period - Calculate from payments
        $revenueGenerated = Payment::where('status', 'completed')
            ->whereHas('lease', function($q) use ($ownerId) {
                $q->where('property_owner_id', $ownerId);
            })
            ->whereBetween('payment_date', [$startDate, $endDate])
            ->sum('amount');
        
        // Expenses incurred - Schema: expenses (property_id, amount, expense_date)
        $expensesIncurred = Expense::whereHas('property', function($q) use ($ownerId) {
                $q->where('property_owner_id', $ownerId);
            })
            ->whereBetween('expense_date', [$startDate, $endDate])
            ->sum('amount');
        
        // Payments received - Schema: owner_payments (amount, payment_date, payment_method, transaction_id)
        $paymentsReceived = OwnerPayment::where('property_owner_id', $ownerId)
            ->whereBetween('payment_date', [$startDate, $endDate])
            ->get()
            ->map(function($payment) {
                return [
                    'id' => $payment->id,
                    'amount' => (float) $payment->amount,
                    'payment_date' => $payment->payment_date,
                    'payment_method' => $payment->payment_method,
                    'transaction_id' => $payment->transaction_id,
                    'notes' => $payment->notes,
                ];
            });
        
        $totalPaymentsReceived = $paymentsReceived->sum('amount');
        
        // Transaction history - Schema: balance_transactions (property_owner_id, amount, transaction_type, transaction_date, description)
        $transactions = BalanceTransaction::where('property_owner_id', $ownerId)
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->orderBy('transaction_date', 'desc')
            ->get()
            ->map(function($transaction) {
                return [
                    'id' => $transaction->id,
                    'transaction_type' => $transaction->transaction_type,
                    'amount' => (float) $transaction->amount,
                    'fee_amount' => (float) $transaction->fee_amount,
                    'net_amount' => (float) $transaction->net_amount,
                    'transaction_date' => $transaction->transaction_date,
                    'description' => $transaction->description,
                ];
            });
        
        // Calculate net amount for period
        $netAmount = $revenueGenerated - $expensesIncurred;
        
        return [
            'period' => [
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString(),
            ],
            'summary' => [
                'revenue_generated' => (float) $revenueGenerated,
                'expenses_incurred' => (float) $expensesIncurred,
                'net_amount' => (float) $netAmount,
                'payments_received' => (float) $totalPaymentsReceived,
                'outstanding_balance' => (float) ($ownerBalance->amount_owed ?? 0),
            ],
            'lifetime_totals' => [
                'total_rent_collected' => (float) ($ownerBalance->total_rent_collected ?? 0),
                'total_expenses' => (float) ($ownerBalance->total_expenses ?? 0),
                'total_platform_fees' => (float) ($ownerBalance->total_platform_fees ?? 0),
                'total_earned' => (float) ($ownerBalance->total_earned ?? 0),
                'total_paid' => (float) ($ownerBalance->total_paid ?? 0),
            ],
            'payments_received' => $paymentsReceived,
            'transaction_history' => $transactions,
        ];
    }
}
