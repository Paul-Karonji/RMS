<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Lease extends BaseTenantModel
{
    /** @use HasFactory<\Database\Factories\LeaseFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'property_id',
        'unit_id',
        'property_owner_id',
        'tenant_id',
        'start_date',
        'end_date',
        'monthly_rent',
        'deposit_amount',
        'payment_frequency',
        'payment_day',
        'late_fee_type',
        'late_fee_amount',
        'grace_period_days',
        'status',
        'termination_reason',
        'terminated_at',
        'created_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'terminated_at' => 'datetime',
        'monthly_rent' => 'decimal:2',
        'deposit_amount' => 'decimal:2',
        'late_fee_amount' => 'decimal:2',
    ];

    /**
     * Get the property for this lease.
     */
    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * Get the unit for this lease.
     */
    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    /**
     * Get the property owner for this lease.
     */
    public function propertyOwner()
    {
        return $this->belongsTo(PropertyOwner::class);
    }

    /**
     * Get the tenant (user) for this lease.
     */
    public function tenant()
    {
        return $this->belongsTo(User::class, 'tenant_id');
    }

    /**
     * Get the user who created this lease.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Alias for creator() - for consistency with eager loading
     */
    public function createdBy()
    {
        return $this->creator();
    }

    /**
     * Get payments for this lease.
     */
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Get rent payments for this lease.
     */
    public function rentPayments()
    {
        return $this->hasMany(Payment::class)->where('payment_type', 'rent');
    }

    /**
     * Get deposit payment for this lease.
     */
    public function depositPayment()
    {
        return $this->hasMany(Payment::class)->where('payment_type', 'deposit');
    }

    /**
     * Get late fee payments for this lease.
     */
    public function lateFeePayments()
    {
        return $this->hasMany(Payment::class)->where('payment_type', 'late_fee');
    }

    /**
     * Get lease signatures for this lease.
     */
    public function signatures()
    {
        return $this->hasMany(LeaseSignature::class);
    }

    /**
     * Get tenant signature for this lease.
     */
    public function tenantSignature()
    {
        return $this->hasOne(LeaseSignature::class)
            ->where('signer_role', 'tenant');
    }

    /**
     * Get owner signature for this lease.
     */
    public function ownerSignature()
    {
        return $this->hasOne(LeaseSignature::class)
            ->where('signer_role', 'owner');
    }

    /**
     * Get maintenance requests for this lease.
     */
    public function maintenanceRequests()
    {
        return $this->hasMany(MaintenanceRequest::class);
    }

    /**
     * Get expenses related to this lease.
     */
    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    /**
     * Check if lease is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active' && 
               $this->start_date <= now() && 
               $this->end_date >= now();
    }

    /**
     * Check if lease is expired.
     */
    public function isExpired(): bool
    {
        return $this->end_date < now();
    }

    /**
     * Check if lease is terminated.
     */
    public function isTerminated(): bool
    {
        return $this->status === 'terminated';
    }

    /**
     * Get remaining days in lease.
     */
    public function remainingDays(): int
    {
        if ($this->isExpired() || $this->isTerminated()) {
            return 0;
        }

        return now()->diffInDays($this->end_date);
    }

    /**
     * Get next rent due date.
     */
    public function nextRentDueDate(): Carbon
    {
        $dueDate = Carbon::createFromDate(now()->year, now()->month, $this->payment_day);

        // If due date has passed this month, move to next month
        if ($dueDate < now()) {
            $dueDate->addMonth();
        }

        // Don't go beyond lease end date
        if ($dueDate > $this->end_date) {
            return $this->end_date;
        }

        return $dueDate;
    }

    /**
     * Check if rent is overdue.
     */
    public function isRentOverdue(): bool
    {
        if (!$this->isActive()) {
            return false;
        }

        $dueDate = $this->nextRentDueDate();
        $gracePeriod = $dueDate->copy()->addDays($this->grace_period_days ?? 3);

        return now() > $gracePeriod;
    }

    /**
     * Calculate late fee amount.
     */
    public function calculateLateFee(): float
    {
        if (!$this->isRentOverdue()) {
            return 0;
        }

        if ($this->late_fee_type === 'flat') {
            return (float) $this->late_fee_amount;
        }

        if ($this->late_fee_type === 'percentage') {
            return $this->monthly_rent * ($this->late_fee_amount / 100);
        }

        return 0;
    }

    /**
     * Get total amount due (rent + late fees).
     */
    public function totalAmountDue(): float
    {
        return $this->monthly_rent + $this->calculateLateFee();
    }

    /**
     * Get lease duration in months.
     */
    public function durationInMonths(): int
    {
        return $this->start_date->diffInMonths($this->end_date);
    }
}
