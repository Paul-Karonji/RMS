<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PropertyOwner extends BaseTenantModel
{
    /** @use HasFactory<\Database\Factories\PropertyOwnerFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'owner_name',
        'email',
        'phone',
        'address',
        'id_number',
        'kra_pin',
        'bank_name',
        'bank_account_number',
        'bank_account_name',
        'bank_branch',
        'bank_swift_code',
        'mpesa_phone',
        'commission_percentage',
        'status',
        'added_by',
    ];

    /**
     * Get the user account for this property owner.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the user who added this property owner.
     */
    public function addedBy()
    {
        return $this->belongsTo(User::class, 'added_by');
    }

    /**
     * Get properties owned by this owner.
     */
    public function properties()
    {
        return $this->hasMany(Property::class);
    }

    /**
     * Get active properties owned by this owner.
     */
    public function activeProperties()
    {
        return $this->hasMany(Property::class)->where('status', 'active');
    }

    /**
     * Get leases for properties owned by this owner.
     */
    public function leases()
    {
        return $this->hasManyThrough(Lease::class, Property::class);
    }

    /**
     * Get active leases for properties owned by this owner.
     */
    public function activeLeases()
    {
        return $this->hasManyThrough(Lease::class, Property::class)
            ->where('leases.status', 'active');
    }

    /**
     * Get owner balance record.
     */
    public function ownerBalance()
    {
        return $this->hasOne(OwnerBalance::class);
    }

    /**
     * Alias for ownerBalance relationship.
     */
    public function balance()
    {
        return $this->ownerBalance();
    }

    /**
     * Get owner payments.
     */
    public function ownerPayments()
    {
        return $this->hasMany(OwnerPayment::class);
    }

    /**
     * Get maintenance requests for properties owned by this owner.
     */
    public function maintenanceRequests()
    {
        return $this->hasManyThrough(MaintenanceRequest::class, Property::class);
    }

    /**
     * Get expenses for properties owned by this owner.
     */
    public function expenses()
    {
        return $this->hasManyThrough(Expense::class, Property::class);
    }

    /**
     * Get payments from tenants for properties owned by this owner.
     */
    public function tenantPayments()
    {
        return $this->hasManyThrough(Payment::class, Lease::class);
    }

    /**
     * Get total monthly rental income.
     */
    public function monthlyRentalIncome(): float
    {
        return $this->activeLeases()->sum('monthly_rent');
    }

    /**
     * Get total amount owed to owner.
     */
    public function totalAmountOwed(): float
    {
        return $this->ownerBalance?->amount_owed ?? 0;
    }

    /**
     * Get total properties count.
     */
    public function totalProperties(): int
    {
        return $this->properties()->count();
    }

    /**
     * Get total units count across all properties.
     */
    public function totalUnits(): int
    {
        return $this->properties()->sum('total_units');
    }

    /**
     * Get occupied units count across all properties.
     */
    public function occupiedUnits(): int
    {
        return $this->properties()->sum('occupied_units');
    }

    /**
     * Calculate occupancy rate across all properties.
     */
    public function occupancyRate(): float
    {
        $totalUnits = $this->totalUnits();
        
        if ($totalUnits === 0) {
            return 0;
        }

        return ($this->occupiedUnits() / $totalUnits) * 100;
    }

    /**
     * Check if owner is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
