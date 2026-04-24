<?php

if (!function_exists('can_permission')) {
    /**
     * Check if current user has permission.
     */
    function can_permission(string $permission): bool
    {
        return auth()->check() && auth()->user()->hasPermissionTo($permission);
    }
}

if (!function_exists('has_role')) {
    /**
     * Check if current user has role.
     */
    function has_role(string|array $role): bool
    {
        if (!auth()->check()) {
            return false;
        }

        $roles = is_array($role) ? $role : [$role];
        return auth()->user()->hasAnyRole($roles);
    }
}

if (!function_exists('has_any_permission')) {
    /**
     * Check if current user has any of the given permissions.
     */
    function has_any_permission(array $permissions): bool
    {
        return auth()->check() && auth()->user()->hasAnyPermission($permissions);
    }
}

if (!function_exists('has_all_permissions')) {
    /**
     * Check if current user has all of the given permissions.
     */
    function has_all_permissions(array $permissions): bool
    {
        return auth()->check() && auth()->user()->hasAllPermissions($permissions);
    }
}

if (!function_exists('is_super_admin')) {
    /**
     * Check if current user is super admin.
     */
    function is_super_admin(): bool
    {
        return auth()->check() && auth()->user()->hasRole('super-admin');
    }
}

if (!function_exists('is_hq_staff')) {
    /**
     * Check if current user is HQ staff.
     */
    function is_hq_staff(): bool
    {
        return auth()->check() && auth()->user()->hasAnyRole([
            'super-admin',
            'system-admin',
            'feature-developer',
            'reviewer',
            'publisher',
        ]);
    }
}

if (!function_exists('is_branch_staff')) {
    /**
     * Check if current user is branch staff.
     */
    function is_branch_staff(): bool
    {
        return auth()->check() && auth()->user()->hasAnyRole([
            'branch_staff',
            'branch_manager',
        ]);
    }
}

if (!function_exists('can_manage_users')) {
    /**
     * Check if current user can manage users.
     */
    function can_manage_users(): bool
    {
        return can_permission('users.view') && can_permission('users.edit');
    }
}

if (!function_exists('can_publish')) {
    /**
     * Check if current user can publish features.
     */
    function can_publish(): bool
    {
        return can_permission('versions.publish');
    }
}

if (!function_exists('can_approve')) {
    /**
     * Check if current user can approve versions.
     */
    function can_approve(): bool
    {
        return can_permission('versions.approve');
    }
}
