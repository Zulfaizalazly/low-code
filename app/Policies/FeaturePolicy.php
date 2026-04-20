<?php

namespace App\Policies;

use App\Models\User;
use App\Studio\Registry\Feature;

class FeaturePolicy
{
    /**
     * Determine if the user can view any features.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('features.view');
    }

    /**
     * Determine if the user can view the feature.
     */
    public function view(User $user, Feature $feature): bool
    {
        return $user->hasPermissionTo('features.view');
    }

    /**
     * Determine if the user can create features.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('features.create');
    }

    /**
     * Determine if the user can update the feature.
     */
    public function update(User $user, Feature $feature): bool
    {
        return $user->hasPermissionTo('features.edit');
    }

    /**
     * Determine if the user can delete the feature.
     */
    public function delete(User $user, Feature $feature): bool
    {
        return $user->hasPermissionTo('features.delete');
    }

    /**
     * Determine if the user can publish the feature.
     */
    public function publish(User $user, Feature $feature): bool
    {
        return $user->hasPermissionTo('features.publish');
    }
}
