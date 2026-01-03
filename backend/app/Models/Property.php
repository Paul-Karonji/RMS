<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Property extends BaseTenantModel
{
    /** @use HasFactory<\Database\Factories\PropertyFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'property_owner_id',
        'name',
        'description',
        'address_line_1',
        'address_line_2',
        'city',
        'state',
        'postal_code',
        'country',
        'latitude',
        'longitude',
        'property_type',
        'total_units',
        'occupied_units',
        'status',
        'approved_by',
        'approved_at',
        'rejection_reason',
        'manager_id',
        'commission_percentage',
    ];

    /**
     * Get the property owner who owns this property.
     */
    public function propertyOwner()
    {
        return $this->belongsTo(PropertyOwner::class);
    }

    /**
     * Alias for propertyOwner relationship.
     */
    public function owner()
    {
        return $this->propertyOwner();
    }

    /**
     * Get the user who approved this property.
     */
    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get the manager assigned to this property.
     */
    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    /**
     * Get all units for this property.
     */
    public function units()
    {
        return $this->hasMany(Unit::class);
    }

    /**
     * Get active units for this property.
     */
    public function activeUnits()
    {
        return $this->hasMany(Unit::class)->where('status', '!=', 'under_maintenance');
    }

    /**
     * Get vacant units for this property.
     */
    public function vacantUnits()
    {
        return $this->hasMany(Unit::class)->where('status', 'available');
    }

    /**
     * Get occupied units for this property.
     */
    public function occupiedUnits()
    {
        return $this->hasMany(Unit::class)->where('status', 'occupied');
    }

    /**
     * Get property amenities for this property.
     */
    public function amenities()
    {
        return $this->hasMany(PropertyAmenity::class);
    }

    /**
     * Get leases for this property.
     */
    public function leases()
    {
        return $this->hasMany(Lease::class);
    }

    /**
     * Get active leases for this property.
     */
    public function activeLeases()
    {
        return $this->hasMany(Lease::class)->where('status', 'active');
    }

    /**
     * Get maintenance requests for this property.
     */
    public function maintenanceRequests()
    {
        return $this->hasMany(MaintenanceRequest::class);
    }

    /**
     * Get expenses for this property.
     */
    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    /**
     * Get rental inquiries for this property.
     */
    public function rentalInquiries()
    {
        return $this->hasMany(RentalInquiry::class);
    }

    /**
     * Get reservations for units in this property.
     */
    public function reservations()
    {
        return $this->hasManyThrough(Reservation::class, Unit::class);
    }

    /**
     * Get payments for this property.
     */
    public function payments()
    {
        return $this->hasManyThrough(Payment::class, Lease::class);
    }

    /**
     * Get platform fees for this property.
     */
    public function platformFees()
    {
        return $this->hasManyThrough(PlatformFee::class, Payment::class);
    }

    /**
     * Check if property is approved.
     */
    public function isApproved(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if property is pending approval.
     */
    public function isPendingApproval(): bool
    {
        return $this->status === 'pending_approval';
    }

    /**
     * Calculate occupancy rate.
     */
    public function occupancyRate(): float
    {
        if ($this->total_units === 0) {
            return 0;
        }

        return ($this->occupied_units / $this->total_units) * 100;
    }

    /**
     * Get monthly rental income.
     */
    public function monthlyRentalIncome(): float
    {
        return $this->activeLeases()->sum('monthly_rent');
    }
}
