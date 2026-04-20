<?php

namespace App\Policies;

use App\Models\User;
use App\Studio\Registry\PageDefinition;

class PageDefinitionPolicy
{
    /**
     * Determine if the user can view any pages.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('pages.view');
    }

    /**
     * Determine if the user can view the page.
     */
    public function view(User $user, PageDefinition $page): bool
    {
        return $user->hasPermissionTo('pages.view');
    }

    /**
     * Determine if the user can create pages.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('pages.create');
    }

    /**
     * Determine if the user can update the page.
     */
    public function update(User $user, PageDefinition $page): bool
    {
        return $user->hasPermissionTo('pages.edit');
    }

    /**
     * Determine if the user can delete the page.
     */
    public function delete(User $user, PageDefinition $page): bool
    {
        return $user->hasPermissionTo('pages.delete');
    }
}
