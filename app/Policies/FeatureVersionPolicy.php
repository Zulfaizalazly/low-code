<?php

namespace App\Policies;

use App\Models\User;
use App\Studio\Registry\FeatureVersion;

class FeatureVersionPolicy
{
    /**
     * Determine if the user can view any versions.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('versions.view');
    }

    /**
     * Determine if the user can view the version.
     */
    public function view(User $user, FeatureVersion $version): bool
    {
        return $user->hasPermissionTo('versions.view');
    }

    /**
     * Determine if the user can create versions.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('versions.create');
    }

    /**
     * Determine if the user can submit the version for review.
     */
    public function submit(User $user, FeatureVersion $version): bool
    {
        return $user->hasPermissionTo('versions.submit') 
            && $version->status === 'draft';
    }

    /**
     * Determine if the user can review the version.
     */
    public function review(User $user, FeatureVersion $version): bool
    {
        return $user->hasPermissionTo('versions.review')
            && $version->status === 'pending_review';
    }

    /**
     * Determine if the user can approve the version.
     */
    public function approve(User $user, FeatureVersion $version): bool
    {
        return $user->hasPermissionTo('versions.approve')
            && $version->status === 'pending_review';
    }

    /**
     * Determine if the user can reject the version.
     */
    public function reject(User $user, FeatureVersion $version): bool
    {
        return $user->hasPermissionTo('versions.reject')
            && $version->status === 'pending_review';
    }

    /**
     * Determine if the user can publish the version.
     */
    public function publish(User $user, FeatureVersion $version): bool
    {
        return $user->hasPermissionTo('versions.publish')
            && $version->status === 'approved';
    }

    /**
     * Determine if the user can rollback the version.
     */
    public function rollback(User $user, FeatureVersion $version): bool
    {
        return $user->hasPermissionTo('versions.rollback')
            && $version->status === 'published';
    }
}
