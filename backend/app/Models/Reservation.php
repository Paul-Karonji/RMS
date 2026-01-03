<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Reservation extends BaseTenantModel
{
    /** @use HasFactory<\Database\Factories\ReservationFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'unit_id',
        'rental_inquiry_id',
        'tenant_name',
        'tenant_email',
        'tenant_phone',
        'deposit_amount',
        'move_in_date',
        'expires_at',
        'status',
        'payment_method',
        'payment_status',
        'transaction_id',
        'refunded_amount',
        'refunded_at',
        'refund_reason',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'move_in_date' => 'date',
        'expires_at' => 'datetime',
        'refunded_at' => 'datetime',
        'deposit_amount' => 'decimal:2',
        'refunded_amount' => 'decimal:2',
    ];

    /**
     * Get the unit for this reservation.
     */
    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    /**
     * Get the rental inquiry for this reservation.
     */
    public function rentalInquiry()
    {
        return $this->belongsTo(RentalInquiry::class);
    }

    /**
     * Get payments for this reservation.
     */
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Get deposit payment for this reservation.
     */
    public function depositPayment()
    {
        return $this->hasOne(Payment::class)
            ->where('payment_type', 'reservation_deposit');
    }

    /**
     * Check if reservation is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active' && $this->expires_at > now();
    }

    /**
     * Check if reservation is expired.
     */
    public function isExpired(): bool
    {
        return $this->status === 'active' && $this->expires_at <= now();
    }

    /**
     * Check if reservation is converted to lease.
     */
    public function isConverted(): bool
    {
        return $this->status === 'converted';
    }

    /**
     * Check if reservation is cancelled.
     */
    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    /**
     * Check if reservation is refunded.
     */
    public function isRefunded(): bool
    {
        return $this->status === 'refunded';
    }

    /**
     * Get remaining time until expiration.
     */
    public function timeUntilExpiration(): string
    {
        if (!$this->isActive()) {
            return 'Expired';
        }

        return $this->expires_at->diffForHumans(now(), true);
    }

    /**
     * Get formatted deposit amount.
     */
    public function formattedDepositAmount(): string
    {
        return number_format($this->deposit_amount, 2);
    }
}
