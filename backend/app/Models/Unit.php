<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Unit extends BaseUuidModel
{
    /** @use HasFactory<\Database\Factories\UnitFactory> */
    use HasFactory;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id', 'tenant_id'];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($unit) {
            // Units table doesn't have tenant_id column, so remove it if present
            unset($unit->tenant_id);
        });
    }

    /**
     * Get the property this unit belongs to.
     */
    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * Get active lease for this unit.
     */
    public function activeLease()
    {
        return $this->hasOne(Lease::class)->where('status', 'active');
    }

    /**
     * Get all leases for this unit.
     */
    public function leases()
    {
        return $this->hasMany(Lease::class);
    }

    /**
     * Get current tenant through active lease.
     */
    public function currentTenant()
    {
        return $this->hasOneThrough(User::class, Lease::class, 'unit_id', 'id', 'id', 'tenant_id')
            ->where('leases.status', 'active');
    }

    /**
     * Get photos for this unit.
     */
    public function photos()
    {
        return $this->hasMany(UnitPhoto::class)->orderBy('sort_order');
    }

    /**
     * Get primary photo for this unit.
     */
    public function primaryPhoto()
    {
        return $this->hasOne(UnitPhoto::class)->where('is_primary', true);
    }

    /**
     * Get maintenance requests for this unit.
     */
    public function maintenanceRequests()
    {
        return $this->hasMany(MaintenanceRequest::class);
    }

    /**
     * Get pending maintenance requests for this unit.
     */
    public function pendingMaintenanceRequests()
    {
        return $this->hasMany(MaintenanceRequest::class)
            ->whereIn('status', ['pending', 'assigned', 'in_progress']);
    }

    /**
     * Get expenses for this unit.
     */
    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    /**
     * Get reservations for this unit.
     */
    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    /**
     * Get active reservation for this unit.
     */
    public function activeReservation()
    {
        return $this->hasOne(Reservation::class)
            ->where('status', 'active')
            ->where('expiry_date', '>', now());
    }

    /**
     * Get rental inquiries for this unit.
     */
    public function rentalInquiries()
    {
        return $this->hasMany(RentalInquiry::class);
    }

    /**
     * Check if unit is available.
     */
    public function isAvailable(): bool
    {
        return $this->status === 'available' && !$this->activeLease && !$this->activeReservation;
    }

    /**
     * Check if unit is occupied.
     */
    public function isOccupied(): bool
    {
        return $this->status === 'occupied' || $this->activeLease;
    }

    /**
     * Check if unit is under maintenance.
     */
    public function isUnderMaintenance(): bool
    {
        return $this->status === 'under_maintenance';
    }

    /**
     * Get formatted rent amount.
     */
    public function formattedRent(): string
    {
        return number_format($this->monthly_rent, 2);
    }

    /**
     * Get full unit identifier.
     */
    public function fullIdentifier(): string
    {
        return "{$this->property->name} - Unit {$this->unit_number}";
    }
}
