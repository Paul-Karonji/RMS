<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class ChangeRequest extends BaseTenantModel
{
    /** @use HasFactory<\Database\Factories\ChangeRequestFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'tenant_id',
        'property_owner_id',
        'property_id',
        'unit_id',
        'request_type',
        'current_value',
        'requested_value',
        'reason',
        'affects_existing_leases',
        'effective_from',
        'status',
        'reviewed_by',
        'reviewed_at',
        'review_notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'affects_existing_leases' => 'boolean',
        'effective_from' => 'date',
        'reviewed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the property owner who made the request.
     */
    public function propertyOwner()
    {
        return $this->belongsTo(PropertyOwner::class);
    }

    /**
     * Get the property this request is for.
     */
    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * Get the unit this request is for.
     */
    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    /**
     * Get the user who reviewed the request.
     */
    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * Scope to only include pending requests.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope to only include approved requests.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope to only include rejected requests.
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    /**
     * Scope to filter by request type.
     */
    public function scopeType($query, string $type)
    {
        return $query->where('request_type', $type);
    }

    /**
     * Check if request is pending.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if request is approved.
     */
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * Check if request is rejected.
     */
    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }
}
