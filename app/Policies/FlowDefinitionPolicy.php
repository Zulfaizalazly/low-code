<?php

namespace App\Policies;

use App\Models\User;
use App\Studio\Registry\FlowDefinition;

class FlowDefinitionPolicy
{
    /**
     * Determine if the user can view any flows.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('flows.view');
    }

    /**
     * Determine if the user can view the flow.
     */
    public function view(User $user, FlowDefinition $flow): bool
    {
        return $user->hasPermissionTo('flows.view');
    }

    /**
     * Determine if the user can create flows.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('flows.create');
    }

    /**
     * Determine if the user can update the flow.
     */
    public function update(User $user, FlowDefinition $flow): bool
    {
        return $user->hasPermissionTo('flows.edit');
    }

    /**
     * Determine if the user can delete the flow.
     */
    public function delete(User $user, FlowDefinition $flow): bool
    {
        return $user->hasPermissionTo('flows.delete');
    }

    /**
     * Determine if the user can simulate the flow.
     */
    public function simulate(User $user, FlowDefinition $flow): bool
    {
        return $user->hasPermissionTo('flows.simulate');
    }
}
