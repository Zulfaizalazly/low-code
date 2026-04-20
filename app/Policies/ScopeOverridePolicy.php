<?php

namespace App\Policies;

use App\Models\User;
use App\Studio\Registry\ScopeOverride;

class ScopeOverridePolicy
{
    /**
     * Determine if the user can view any scope overrides.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('scopes.view');
    }

    /**
     * Determine if the user can view the scope override.
     */
    public function view(User $user, ScopeOverride $scopeOverride): bool
    {
        return $user->hasPermissionTo('scopes.view');
    }

    /**
     * Determine if the user can create scope overrides.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('scopes.create');
    }

    /**
     * Determine if the user can update the scope override.
     */
    public function update(User $user, ScopeOverride $scopeOverride): bool
    {
        return $user->hasPermissionTo('scopes.edit');
    }

    /**
     * Determine if the user can delete the scope override.
     */
    public function delete(User $user, ScopeOverride $scopeOverride): bool
    {
        return $user->hasPermissionTo('scopes.delete');
    }
}
