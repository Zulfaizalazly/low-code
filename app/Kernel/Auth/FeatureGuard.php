<?php

namespace App\Kernel\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Log;

class FeatureGuard
{
    /**
     * Check if a user has access to a specific feature based on their role.
     * 
     * @param User $user
     * @param string $featureKey
     * @param string $permission (view, launch, approve, etc.)
     * @return bool
     */
    public function canAccess(User $user, string $featureKey, string $permission = 'view'): bool
    {
        // HQ Admins have bypass
        if ($user->role === 'hq_admin') {
            return true;
        }

        // Logic for Published Features will be added in Phase 3.
        // For Phase 1, we use a simple hardcoded map or check for domain-level access.
        
        $rolesWithAccess = config("permissions.features.{$featureKey}.{$permission}", []);

        return in_array($user->role, $rolesWithAccess);
    }
}
