<?php

namespace App\Services;

use App\Models\Property;
use App\Models\Unit;
use App\Models\User;
use App\Models\Payment;
use App\Models\Lease;
use Illuminate\Database\Eloquent\Builder;

class SearchService
{
    /**
     * Search properties with advanced filters.
     */
    public function searchProperties(User $user, array $filters): Builder
    {
        $query = Property::query();

        // Tenant scoping
        if (!$user->hasRole('platform_owner')) {
            $query->where('tenant_id', $user->tenant_id);
        }

        // Filter by status
        if (!empty($filters['status'])) {
            $query->where('approval_status', $filters['status']);
        }

        // Filter by property type
        if (!empty($filters['property_type'])) {
            $query->where('property_type', $filters['property_type']);
        }

        // Filter by city
        if (!empty($filters['city'])) {
            $query->where('city', 'ILIKE', "%{$filters['city']}%");
        }

        // Filter by county
        if (!empty($filters['county'])) {
            $query->where('county', 'ILIKE', "%{$filters['county']}%");
        }

        // Filter by owner
        if (!empty($filters['owner_id'])) {
            $query->where('owner_id', $filters['owner_id']);
        }

        // Filter by manager
        if (!empty($filters['manager_id'])) {
            $query->where('property_manager_id', $filters['manager_id']);
        }

        // Search by name or address
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('property_name', 'ILIKE', "%{$search}%")
                  ->orWhere('address', 'ILIKE', "%{$search}%")
                  ->orWhere('city', 'ILIKE', "%{$search}%");
            });
        }

        // Sort
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';
        $query->orderBy($sortBy, $sortOrder);

        return $query;
    }

    /**
     * Search units with advanced filters.
     */
    public function searchUnits(User $user, array $filters): Builder
    {
        $query = Unit::with('property');

        // Tenant scoping
        if (!$user->hasRole('platform_owner')) {
            $query->whereHas('property', function ($q) use ($user) {
                $q->where('tenant_id', $user->tenant_id);
            });
        }

        // Filter by status
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Filter by bedrooms
        if (!empty($filters['bedrooms'])) {
            $query->where('bedrooms', $filters['bedrooms']);
        }

        // Filter by bathrooms
        if (!empty($filters['bathrooms'])) {
            $query->where('bathrooms', $filters['bathrooms']);
        }

        // Filter by rent range
        if (!empty($filters['min_rent'])) {
            $query->where('monthly_rent', '>=', $filters['min_rent']);
        }
        if (!empty($filters['max_rent'])) {
            $query->where('monthly_rent', '<=', $filters['max_rent']);
        }

        // Filter by furnished
        if (isset($filters['is_furnished'])) {
            $query->where('is_furnished', $filters['is_furnished']);
        }

        // Search by unit number or type
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('unit_number', 'ILIKE', "%{$search}%")
                  ->orWhere('unit_type', 'ILIKE', "%{$search}%");
            });
        }

        // Sort
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';
        $query->orderBy($sortBy, $sortOrder);

        return $query;
    }

    /**
     * Search payments with advanced filters.
     */
    public function searchPayments(User $user, array $filters): Builder
    {
        $query = Payment::with(['lease', 'tenant']);

        // Tenant scoping
        if (!$user->hasRole('platform_owner')) {
            $query->where('tenant_id', $user->tenant_id);
        }

        // Filter by status
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Filter by payment method
        if (!empty($filters['payment_method'])) {
            $query->where('payment_method', $filters['payment_method']);
        }

        // Filter by date range
        if (!empty($filters['start_date'])) {
            $query->whereDate('payment_date', '>=', $filters['start_date']);
        }
        if (!empty($filters['end_date'])) {
            $query->whereDate('payment_date', '<=', $filters['end_date']);
        }

        // Filter by amount range
        if (!empty($filters['min_amount'])) {
            $query->where('amount', '>=', $filters['min_amount']);
        }
        if (!empty($filters['max_amount'])) {
            $query->where('amount', '<=', $filters['max_amount']);
        }

        // Search by transaction ID
        if (!empty($filters['search'])) {
            $query->where('transaction_id', 'ILIKE', "%{$filters['search']}%");
        }

        // Sort
        $sortBy = $filters['sort_by'] ?? 'payment_date';
        $sortOrder = $filters['sort_order'] ?? 'desc';
        $query->orderBy($sortBy, $sortOrder);

        return $query;
    }

    /**
     * Search leases with advanced filters.
     */
    public function searchLeases(User $user, array $filters): Builder
    {
        $query = Lease::with(['unit', 'tenant']);

        // Tenant scoping
        if (!$user->hasRole('platform_owner')) {
            $query->where('tenant_id', $user->tenant_id);
        }

        // Filter by status
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Filter by start date range
        if (!empty($filters['start_date_from'])) {
            $query->whereDate('start_date', '>=', $filters['start_date_from']);
        }
        if (!empty($filters['start_date_to'])) {
            $query->whereDate('start_date', '<=', $filters['start_date_to']);
        }

        // Filter by end date range
        if (!empty($filters['end_date_from'])) {
            $query->whereDate('end_date', '>=', $filters['end_date_from']);
        }
        if (!empty($filters['end_date_to'])) {
            $query->whereDate('end_date', '<=', $filters['end_date_to']);
        }

        // Filter expiring soon (within 30 days)
        if (!empty($filters['expiring_soon'])) {
            $query->where('end_date', '<=', now()->addDays(30))
                  ->where('end_date', '>=', now())
                  ->where('status', 'active');
        }

        // Search by lease number or tenant name
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('lease_number', 'ILIKE', "%{$search}%")
                  ->orWhereHas('tenant', function ($tq) use ($search) {
                      $tq->where('name', 'ILIKE', "%{$search}%");
                  });
            });
        }

        // Sort
        $sortBy = $filters['sort_by'] ?? 'start_date';
        $sortOrder = $filters['sort_order'] ?? 'desc';
        $query->orderBy($sortBy, $sortOrder);

        return $query;
    }

    /**
     * Global search across all modules.
     */
    public function globalSearch(User $user, string $query): array
    {
        $results = [];

        // Search properties
        $properties = $this->searchProperties($user, ['search' => $query])
            ->limit(5)
            ->get();
        
        if ($properties->isNotEmpty()) {
            $results['properties'] = $properties;
        }

        // Search units
        $units = $this->searchUnits($user, ['search' => $query])
            ->limit(5)
            ->get();
        
        if ($units->isNotEmpty()) {
            $results['units'] = $units;
        }

        // Search payments
        $payments = $this->searchPayments($user, ['search' => $query])
            ->limit(5)
            ->get();
        
        if ($payments->isNotEmpty()) {
            $results['payments'] = $payments;
        }

        // Search leases
        $leases = $this->searchLeases($user, ['search' => $query])
            ->limit(5)
            ->get();
        
        if ($leases->isNotEmpty()) {
            $results['leases'] = $leases;
        }

        return $results;
    }
}
