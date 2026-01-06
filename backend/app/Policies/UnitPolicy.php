<?php

namespace App\Policies;

use App\Models\Unit;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class UnitPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Unit $unit): bool
    {
        return $unit->property->tenant_id === $user->tenant_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasRole(['company_admin', 'company_staff']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Unit $unit): bool
    {
        $property = $unit->property;

        if ($user->hasRole('company_admin')) {
            return $property->tenant_id === $user->tenant_id;
        }

        if ($user->hasRole('company_staff')) {
            return $property->tenant_id === $user->tenant_id && 
                   $property->manager_id === $user->id;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Unit $unit): bool
    {
        if (!$user->hasRole('company_admin')) {
            return false;
        }

        if ($unit->property->tenant_id !== $user->tenant_id) {
            return false;
        }

        return $unit->status === 'available';
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Unit $unit): bool
    {
        return $user->hasRole('company_admin') &&
               $unit->property->tenant_id === $user->tenant_id;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Unit $unit): bool
    {
        return false;
    }
}
