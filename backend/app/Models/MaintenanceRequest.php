<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaintenanceRequest extends BaseTenantModel
{
    /** @use HasFactory<\Database\Factories\MaintenanceRequestFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'property_id',
        'unit_id',
        'tenant_id',
        'reported_by',
        'assigned_to',
        'category',
        'priority',
        'title',
        'description',
        'photos',
        'estimated_cost',
        'actual_cost',
        'status',
        'resolution_notes',
        'completion_photos',
        'completed_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'photos' => 'array',
        'completion_photos' => 'array',
        'estimated_cost' => 'decimal:2',
        'actual_cost' => 'decimal:2',
        'completed_at' => 'datetime',
    ];

    /**
     * Get the property for this maintenance request.
     */
    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * Get the unit for this maintenance request.
     */
    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    /**
     * Get the tenant who reported this maintenance request.
     */
    public function tenant()
    {
        return $this->belongsTo(User::class, 'tenant_id');
    }

    /**
     * Get the user who reported this maintenance request.
     */
    public function reportedBy()
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    /**
     * Alias for reportedBy relationship.
     */
    public function reporter()
    {
        return $this->reportedBy();
    }

    /**
     * Get the user assigned to this maintenance request.
     */
    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Get the lease for this maintenance request.
     */
    public function lease()
    {
        return $this->belongsTo(Lease::class);
    }

    /**
     * Get updates for this maintenance request.
     */
    public function updates()
    {
        return $this->hasMany(MaintenanceUpdate::class);
    }

    /**
     * Get expenses related to this maintenance request.
     */
    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    /**
     * Check if maintenance request is pending.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if maintenance request is assigned.
     */
    public function isAssigned(): bool
    {
        return $this->status === 'assigned';
    }

    /**
     * Check if maintenance request is in progress.
     */
    public function isInProgress(): bool
    {
        return $this->status === 'in_progress';
    }

    /**
     * Check if maintenance request is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if maintenance request is cancelled.
     */
    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    /**
     * Get priority label.
     */
    public function getPriorityLabel(): string
    {
        return ucfirst($this->priority);
    }

    /**
     * Get category label.
     */
    public function getCategoryLabel(): string
    {
        return ucwords(str_replace('_', ' ', $this->category));
    }

    /**
     * Get status label.
     */
    public function getStatusLabel(): string
    {
        return ucwords(str_replace('_', ' ', $this->status));
    }
}
