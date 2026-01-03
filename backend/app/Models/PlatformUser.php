<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Laravel\Sanctum\HasApiTokens;

class PlatformUser extends Authenticatable
{
    use HasUuids, HasApiTokens;

    /**
     * Indicates if the model's ID is auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The data type of the auto-incrementing ID.
     *
     * @var string
     */
    protected $keyType = 'string';

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
     * Get the password for authentication.
     */
    public function getAuthPassword()
    {
        return $this->password_hash;
    }

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
