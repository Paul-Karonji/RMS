<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;

abstract class BaseTenantModel extends BaseUuidModel
{
    use BelongsToTenant;

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
        
        // Automatically apply tenant scope for non-platform users
        static::addGlobalScope('tenant', function ($builder) {
            if (auth()->check() && auth()->user()->tenant_id) {
                $builder->where('tenant_id', auth()->user()->tenant_id);
            }
        });
    }
}
