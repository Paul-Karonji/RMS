<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RentalInquiry extends BaseTenantModel
{
    /** @use HasFactory<\Database\Factories\RentalInquiryFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'unit_id',
        'name',
        'email',
        'phone',
        'message',
        'preferred_move_in_date',
        'status',
        'notes',
        'follow_up_date',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'preferred_move_in_date' => 'date',
        'follow_up_date' => 'date',
    ];

    /**
     * Get the unit for this inquiry.
     */
    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    /**
     * Get the property for this inquiry through unit.
     */
    public function property()
    {
        return $this->hasOneThrough(Property::class, Unit::class);
    }

    /**
     * Get reservations created from this inquiry.
     */
    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    /**
     * Check if inquiry is pending.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if inquiry is approved.
     */
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * Check if inquiry is rejected.
     */
    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    /**
     * Check if inquiry is converted.
     */
    public function isConverted(): bool
    {
        return $this->status === 'converted';
    }
}
