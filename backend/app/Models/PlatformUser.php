<?php

namespace App\Models;

class PlatformUser extends BaseUuidModel
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password_hash',
        'role',
        'status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password_hash',
    ];

    /**
     * Get the companies created by this platform user.
     */
    public function createdTenants()
    {
        return $this->hasMany(Tenant::class, 'created_by');
    }

    /**
     * Get the cashout requests approved by this platform user.
     */
    public function approvedCashoutRequests()
    {
        return $this->hasMany(CashoutRequest::class, 'approved_by');
    }
}
