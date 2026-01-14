<?php

namespace App\Services;

use App\Models\CompanyBalance;
use App\Models\Payment;
use App\Models\Property;
use App\Models\Unit;
use App\Models\OwnerBalance;
use App\Models\PropertyOwner;
use App\Models\Lease;
use App\Models\MaintenanceRequest;
use App\Models\RentalInquiry;
use App\Models\CashoutRequest;
use App\Models\PlatformFee;
use App\Models\Expense;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    /**
     * Get company dashboard metrics
     * Schema verified: company_balances, payments, platform_fees, cashout_requests
     */
    public function getCompanyMetrics(string $tenantId, ?Carbon $startDate = null, ?Carbon $endDate = null): array
    {
        $startDate = $startDate ?? now()->startOfMonth();
        $endDate = $endDate ?? now()->endOfMonth();
        
        // Get company balance - Schema: company_balances
        $companyBalance = CompanyBalance::where('tenant_id', $tenantId)->first();
        
        // Calculate revenue - Schema: payments (amount, status, payment_date)
        // Note: payments.tenant_id references users (tenant renter), so we need to join through leases
        $thisMonthRevenue = Payment::where('status', 'completed')
            ->whereHas('lease', function($q) use ($tenantId) {
                $q->whereHas('property', function($q2) use ($tenantId) {
                    $q2->where('tenant_id', $tenantId);
                });
            })
            ->whereBetween('payment_date', [$startDate, $endDate])
            ->sum('amount');
            
        $lastMonthStart = now()->subMonth()->startOfMonth();
        $lastMonthEnd = now()->subMonth()->endOfMonth();
        
        $lastMonthRevenue = Payment::where('status', 'completed')
            ->whereHas('lease', function($q) use ($tenantId) {
                $q->whereHas('property', function($q2) use ($tenantId) {
                    $q2->where('tenant_id', $tenantId);
                });
            })
            ->whereBetween('payment_date', [$lastMonthStart, $lastMonthEnd])
            ->sum('amount');
            
        $ytdRevenue = Payment::where('status', 'completed')
            ->whereHas('lease', function($q) use ($tenantId) {
                $q->whereHas('property', function($q2) use ($tenantId) {
                    $q2->where('tenant_id', $tenantId);
                });
            })
            ->whereBetween('payment_date', [now()->startOfYear(), now()])
            ->sum('amount');
        
        // Calculate outstanding rent - Schema: payments (amount, status)
        $outstandingRent = Payment::where('status', 'pending')
            ->whereHas('lease', function($q) use ($tenantId) {
                $q->whereHas('property', function($q2) use ($tenantId) {
                    $q2->where('tenant_id', $tenantId);
                });
            })
            ->sum('amount');
        
        // Get platform fees paid - Schema: platform_fees (fee_amount, status)
        $platformFeesPaid = PlatformFee::where('tenant_id', $tenantId)
            ->where('status', 'paid')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('fee_amount');
        
        // Get pending cashouts - Schema: cashout_requests (amount, status)
        $pendingCashouts = CashoutRequest::where('tenant_id', $tenantId)
            ->where('status', 'pending')
            ->sum('amount');
        
        return [
            'financial_overview' => [
                'this_month_revenue' => (float) $thisMonthRevenue,
                'last_month_revenue' => (float) $lastMonthRevenue,
                'ytd_revenue' => (float) $ytdRevenue,
                'revenue_growth' => $lastMonthRevenue > 0 
                    ? (($thisMonthRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100 
                    : 0,
                'outstanding_rent' => (float) $outstandingRent,
                'platform_fees_paid' => (float) $platformFeesPaid,
                'available_balance' => (float) ($companyBalance->available_balance ?? 0),
                'pending_cashouts' => (float) $pendingCashouts,
            ],
        ];
    }
    
    /**
     * Get occupancy metrics
     * Schema verified: properties, units
     */
    public function getOccupancyMetrics(string $tenantId): array
    {
        // Get property counts - Schema: properties (tenant_id, total_units, occupied_units, vacant_units)
        $totalProperties = Property::where('tenant_id', $tenantId)
            ->where('status', 'approved')
            ->count();
        
        // Get unit statistics - Schema: units (property_id, status)
        $unitStats = Unit::whereHas('property', function($q) use ($tenantId) {
                $q->where('tenant_id', $tenantId);
            })
            ->selectRaw('
                COUNT(*) as total_units,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as occupied_units,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as vacant_units
            ', ['occupied', 'available'])
            ->first();
        
        $totalUnits = $unitStats->total_units ?? 0;
        $occupiedUnits = $unitStats->occupied_units ?? 0;
        $vacantUnits = $unitStats->vacant_units ?? 0;
        
        $occupancyRate = $totalUnits > 0 ? ($occupiedUnits / $totalUnits) * 100 : 0;
        
        return [
            'property_metrics' => [
                'total_properties' => $totalProperties,
                'total_units' => $totalUnits,
                'occupied_units' => $occupiedUnits,
                'vacant_units' => $vacantUnits,
                'occupancy_rate' => round($occupancyRate, 2),
            ],
        ];
    }
    
    /**
     * Get payment metrics
     * Schema verified: payments, leases
     */
    public function getPaymentMetrics(string $tenantId, Carbon $startDate, Carbon $endDate): array
    {
        // Payments received this month - Schema: payments (amount, status, payment_date)
        $paymentsReceived = Payment::where('status', 'completed')
            ->whereHas('lease', function($q) use ($tenantId) {
                $q->whereHas('property', function($q2) use ($tenantId) {
                    $q2->where('tenant_id', $tenantId);
                });
            })
            ->whereBetween('payment_date', [$startDate, $endDate])
            ->count();
        
        // Pending payments - Schema: payments (status)
        $pendingPayments = Payment::where('status', 'pending')
            ->whereHas('lease', function($q) use ($tenantId) {
                $q->whereHas('property', function($q2) use ($tenantId) {
                    $q2->where('tenant_id', $tenantId);
                });
            })
            ->count();
        
        // Late payments (pending and past due date)
        $latePayments = Payment::where('status', 'pending')
            ->where('payment_date', '<', now())
            ->whereHas('lease', function($q) use ($tenantId) {
                $q->whereHas('property', function($q2) use ($tenantId) {
                    $q2->where('tenant_id', $tenantId);
                });
            })
            ->count();
        
        // Payment success rate
        $totalPayments = Payment::whereHas('lease', function($q) use ($tenantId) {
                $q->whereHas('property', function($q2) use ($tenantId) {
                    $q2->where('tenant_id', $tenantId);
                });
            })
            ->whereBetween('payment_date', [$startDate, $endDate])
            ->count();
        
        $successRate = $totalPayments > 0 ? ($paymentsReceived / $totalPayments) * 100 : 0;
        
        return [
            'payment_metrics' => [
                'payments_received' => $paymentsReceived,
                'pending_payments' => $pendingPayments,
                'late_payments' => $latePayments,
                'payment_success_rate' => round($successRate, 2),
            ],
        ];
    }
    
    /**
     * Get owner metrics for company dashboard
     * Schema verified: owner_balances, property_owners
     */
    public function getOwnerMetrics(string $tenantId): array
    {
        // Total owners - Schema: property_owners (tenant_id)
        $totalOwners = PropertyOwner::where('tenant_id', $tenantId)->count();
        
        // Amount owed to owners - Schema: owner_balances (amount_owed)
        $amountOwed = OwnerBalance::where('tenant_id', $tenantId)
            ->sum('amount_owed');
        
        // Payments made to owners - Schema: owner_balances (amount_paid, total_paid)
        $paymentsMade = OwnerBalance::where('tenant_id', $tenantId)
            ->sum('total_paid');
        
        return [
            'owner_metrics' => [
                'total_owners' => $totalOwners,
                'amount_owed_to_owners' => (float) $amountOwed,
                'payments_made_to_owners' => (float) $paymentsMade,
            ],
        ];
    }
    
    /**
     * Get recent activity
     * Schema verified: payments, maintenance_requests, rental_inquiries, leases
     */
    public function getRecentActivity(string $tenantId, int $limit = 10): array
    {
        // Recent payments - Schema: payments (created_at, amount, payment_method, status)
        $recentPayments = Payment::with(['lease.unit', 'lease.tenant'])
            ->whereHas('lease', function($q) use ($tenantId) {
                $q->whereHas('property', function($q2) use ($tenantId) {
                    $q2->where('tenant_id', $tenantId);
                });
            })
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(function($payment) {
                return [
                    'id' => $payment->id,
                    'type' => 'payment',
                    'amount' => $payment->amount,
                    'payment_method' => $payment->payment_method,
                    'status' => $payment->status,
                    'date' => $payment->created_at,
                ];
            });
        
        // Recent maintenance requests - Schema: maintenance_requests (created_at, category, priority, status)
        $recentMaintenance = MaintenanceRequest::with(['unit', 'property'])
            ->where('tenant_id', $tenantId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(function($request) {
                return [
                    'id' => $request->id,
                    'type' => 'maintenance',
                    'category' => $request->category,
                    'priority' => $request->priority,
                    'status' => $request->status,
                    'date' => $request->created_at,
                ];
            });
        
        // Recent inquiries - Schema: rental_inquiries (created_at, status)
        $recentInquiries = RentalInquiry::with('unit')
            ->where('tenant_id', $tenantId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(function($inquiry) {
                return [
                    'id' => $inquiry->id,
                    'type' => 'inquiry',
                    'status' => $inquiry->status,
                    'date' => $inquiry->created_at,
                ];
            });
        
        // Upcoming lease expirations - Schema: leases (end_date, status)
        $upcomingExpirations = Lease::with(['unit', 'tenant'])
            ->whereHas('property', function($q) use ($tenantId) {
                $q->where('tenant_id', $tenantId);
            })
            ->where('status', 'active')
            ->where('end_date', '>=', now())
            ->where('end_date', '<=', now()->addDays(30))
            ->orderBy('end_date', 'asc')
            ->limit($limit)
            ->get()
            ->map(function($lease) {
                return [
                    'id' => $lease->id,
                    'type' => 'lease_expiration',
                    'end_date' => $lease->end_date,
                    'days_remaining' => now()->diffInDays($lease->end_date),
                ];
            });
        
        return [
            'recent_activity' => [
                'payments' => $recentPayments,
                'maintenance' => $recentMaintenance,
                'inquiries' => $recentInquiries,
                'upcoming_expirations' => $upcomingExpirations,
            ],
        ];
    }
    
    /**
     * Get owner metrics for owner dashboard
     * Schema verified: owner_balances, properties
     */
    public function getOwnerDashboardMetrics(string $ownerId): array
    {
        // Get owner balance - Schema: owner_balances (total_earned, amount_owed, amount_paid, total_paid, last_payment_date, last_payment_amount)
        $ownerBalance = OwnerBalance::where('property_owner_id', $ownerId)->first();
        
        // Get property statistics - Schema: properties (property_owner_id, total_units, occupied_units, vacant_units)
        $propertyStats = Property::where('property_owner_id', $ownerId)
            ->selectRaw('
                COUNT(*) as total_properties,
                SUM(total_units) as total_units,
                SUM(occupied_units) as occupied_units,
                SUM(vacant_units) as vacant_units
            ')
            ->first();
        
        $totalUnits = $propertyStats->total_units ?? 0;
        $occupiedUnits = $propertyStats->occupied_units ?? 0;
        $occupancyRate = $totalUnits > 0 ? ($occupiedUnits / $totalUnits) * 100 : 0;
        
        // Calculate this month earnings
        $thisMonthEarnings = Payment::where('status', 'completed')
            ->whereHas('lease', function($q) use ($ownerId) {
                $q->where('property_owner_id', $ownerId);
            })
            ->whereBetween('payment_date', [now()->startOfMonth(), now()->endOfMonth()])
            ->sum('amount');
        
        return [
            'financial_summary' => [
                'total_earned' => (float) ($ownerBalance->total_earned ?? 0),
                'amount_owed' => (float) ($ownerBalance->amount_owed ?? 0),
                'amount_paid' => (float) ($ownerBalance->amount_paid ?? 0),
                'total_paid' => (float) ($ownerBalance->total_paid ?? 0),
                'this_month_earnings' => (float) $thisMonthEarnings,
                'last_payment_date' => $ownerBalance->last_payment_date ?? null,
                'last_payment_amount' => (float) ($ownerBalance->last_payment_amount ?? 0),
            ],
            'property_overview' => [
                'total_properties' => $propertyStats->total_properties ?? 0,
                'total_units' => $totalUnits,
                'occupied_units' => $occupiedUnits,
                'vacant_units' => $propertyStats->vacant_units ?? 0,
                'occupancy_rate' => round($occupancyRate, 2),
            ],
        ];
    }
    
    /**
     * Get owner property performance
     * Schema verified: properties, units, expenses
     */
    public function getOwnerPropertyPerformance(string $ownerId): array
    {
        // Get properties with performance data
        $properties = Property::where('property_owner_id', $ownerId)
            ->with('units')
            ->get()
            ->map(function($property) {
                // Revenue by property - Schema: units (monthly_rent, status)
                $monthlyRevenue = $property->units()
                    ->where('status', 'occupied')
                    ->sum('monthly_rent');
                
                // Occupancy by property - Schema: units (status)
                $totalUnits = $property->units()->count();
                $occupiedUnits = $property->units()->where('status', 'occupied')->count();
                $occupancyRate = $totalUnits > 0 ? ($occupiedUnits / $totalUnits) * 100 : 0;
                
                // Maintenance costs - Schema: expenses (property_id, amount, expense_date)
                $maintenanceCosts = Expense::where('property_id', $property->id)
                    ->whereBetween('expense_date', [now()->startOfMonth(), now()->endOfMonth()])
                    ->sum('amount');
                
                return [
                    'property_id' => $property->id,
                    'property_name' => $property->property_name,
                    'monthly_revenue' => (float) $monthlyRevenue,
                    'occupancy_rate' => round($occupancyRate, 2),
                    'maintenance_costs' => (float) $maintenanceCosts,
                    'net_income' => (float) ($monthlyRevenue - $maintenanceCosts),
                ];
            });
        
        return [
            'property_performance' => $properties,
        ];
    }
    
    /**
     * Get tenant lease information
     * Schema verified: leases, units, properties
     */
    public function getTenantLeaseInfo(string $userId): array
    {
        // Get active lease - Schema: leases (tenant_id → users.id, start_date, end_date, monthly_rent, status)
        $lease = Lease::with(['unit', 'property'])
            ->where('tenant_id', $userId)
            ->where('status', 'active')
            ->first();
        
        if (!$lease) {
            return [
                'lease_info' => null,
            ];
        }
        
        // Calculate days until expiration
        $daysUntilExpiration = now()->diffInDays($lease->end_date, false);
        
        return [
            'lease_info' => [
                'lease_id' => $lease->id,
                'start_date' => $lease->start_date,
                'end_date' => $lease->end_date,
                'monthly_rent' => (float) $lease->monthly_rent,
                'deposit_amount' => (float) $lease->deposit_amount,
                'status' => $lease->status,
                'days_until_expiration' => $daysUntilExpiration,
                'unit' => [
                    'unit_number' => $lease->unit->unit_number,
                    'unit_type' => $lease->unit->unit_type,
                    'bedrooms' => $lease->unit->bedrooms,
                    'bathrooms' => $lease->unit->bathrooms,
                ],
                'property' => [
                    'property_name' => $lease->property->property_name,
                    'address' => $lease->property->address,
                    'city' => $lease->property->city,
                ],
            ],
        ];
    }
    
    /**
     * Get tenant payment summary
     * Schema verified: payments, leases
     */
    public function getTenantPaymentSummary(string $userId): array
    {
        // Get user's leases
        $leaseIds = Lease::where('tenant_id', $userId)->pluck('id');
        
        // Next payment due - Schema: payments (lease_id, amount, status, payment_date)
        $nextPayment = Payment::whereIn('lease_id', $leaseIds)
            ->where('status', 'pending')
            ->where('payment_date', '>=', now())
            ->orderBy('payment_date', 'asc')
            ->first();
        
        // Payment history - Schema: payments (amount, status, payment_date, payment_method)
        $paymentHistory = Payment::whereIn('lease_id', $leaseIds)
            ->orderBy('payment_date', 'desc')
            ->limit(10)
            ->get()
            ->map(function($payment) {
                return [
                    'id' => $payment->id,
                    'amount' => (float) $payment->amount,
                    'status' => $payment->status,
                    'payment_date' => $payment->payment_date,
                    'payment_method' => $payment->payment_method,
                ];
            });
        
        // Total paid - Schema: payments (amount, status)
        $totalPaid = Payment::whereIn('lease_id', $leaseIds)
            ->where('status', 'completed')
            ->sum('amount');
        
        // Outstanding balance - Schema: payments (amount, status)
        $outstandingBalance = Payment::whereIn('lease_id', $leaseIds)
            ->where('status', 'pending')
            ->sum('amount');
        
        return [
            'payment_summary' => [
                'next_payment_due' => $nextPayment ? [
                    'amount' => (float) $nextPayment->amount,
                    'due_date' => $nextPayment->payment_date,
                ] : null,
                'total_paid' => (float) $totalPaid,
                'outstanding_balance' => (float) $outstandingBalance,
                'payment_history' => $paymentHistory,
            ],
        ];
    }
}
