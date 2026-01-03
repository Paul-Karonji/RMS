<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expense extends BaseTenantModel
{
    /** @use HasFactory<\Database\Factories\ExpenseFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'property_id',
        'unit_id',
        'maintenance_request_id',
        'category',
        'description',
        'amount',
        'expense_date',
        'invoice_number',
        'receipt_url',
        'owner_share',
        'platform_share',
        'status',
        'approved_by',
        'approved_at',
        'rejection_reason',
        'rejected_by',
        'rejected_at',
        'created_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'expense_date' => 'date',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'amount' => 'decimal:2',
        'owner_share' => 'decimal:2',
        'platform_share' => 'decimal:2',
    ];

    /**
     * Get the property for this expense.
     */
    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * Get the unit for this expense.
     */
    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    /**
     * Get the maintenance request for this expense.
     */
    public function maintenanceRequest()
    {
        return $this->belongsTo(MaintenanceRequest::class);
    }

    /**
     * Get the user who approved this expense.
     */
    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get the user who rejected this expense.
     */
    public function rejectedBy()
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    /**
     * Get the user who created this expense.
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get balance transactions for this expense.
     */
    public function balanceTransactions()
    {
        return $this->hasMany(BalanceTransaction::class);
    }

    /**
     * Check if expense is pending approval.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if expense is approved.
     */
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * Check if expense is rejected.
     */
    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    /**
     * Get formatted amount.
     */
    public function formattedAmount(): string
    {
        return number_format($this->amount, 2);
    }

    /**
     * Get category label.
     */
    public function getCategoryLabel(): string
    {
        return ucwords(str_replace('_', ' ', $this->category));
    }
}
