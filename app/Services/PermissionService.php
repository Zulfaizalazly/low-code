<?php

namespace App\Services;

use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Collection;

class PermissionService
{
    /**
     * Get all available permissions grouped by category.
     */
    public function getPermissionsByCategory(): array
    {
        $permissions = Permission::all();
        
        $grouped = [
            'Features' => [],
            'Flows' => [],
            'Pages' => [],
            'Versions' => [],
            'Scopes' => [],
            'Audit & Monitoring' => [],
            'Users' => [],
            'AI' => [],
            'Runtime' => [],
        ];

        foreach ($permissions as $permission) {
            $parts = explode('.', $permission->name);
            $category = ucfirst($parts[0]);
            
            $categoryMap = [
                'Features' => 'Features',
                'Flows' => 'Flows',
                'Pages' => 'Pages',
                'Versions' => 'Versions',
                'Scopes' => 'Scopes',
                'Audit' => 'Audit & Monitoring',
                'Monitor' => 'Audit & Monitoring',
                'Users' => 'Users',
                'Ai' => 'AI',
                'Runtime' => 'Runtime',
            ];

            $mappedCategory = $categoryMap[$category] ?? 'Other';
            
            if (!isset($grouped[$mappedCategory])) {
                $grouped[$mappedCategory] = [];
            }
            
            $grouped[$mappedCategory][] = $permission;
        }

        return array_filter($grouped, fn($perms) => !empty($perms));
    }

    /**
     * Get all roles with their permissions.
     */
    public function getRolesWithPermissions(): Collection
    {
        return Role::with('permissions')->get();
    }

    /**
     * Assign role to user.
     */
    public function assignRole(User $user, string $roleName): void
    {
        $user->assignRole($roleName);
        
        // Clear permission cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }

    /**
     * Remove role from user.
     */
    public function removeRole(User $user, string $roleName): void
    {
        $user->removeRole($roleName);
        
        // Clear permission cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }

    /**
     * Sync user roles.
     */
    public function syncRoles(User $user, array $roleNames): void
    {
        $user->syncRoles($roleNames);
        
        // Clear permission cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }

    /**
     * Give direct permission to user.
     */
    public function givePermission(User $user, string $permissionName): void
    {
        $user->givePermissionTo($permissionName);
        
        // Clear permission cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }

    /**
     * Revoke direct permission from user.
     */
    public function revokePermission(User $user, string $permissionName): void
    {
        $user->revokePermissionTo($permissionName);
        
        // Clear permission cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }

    /**
     * Check if user has permission (via role or direct).
     */
    public function userHasPermission(User $user, string $permissionName): bool
    {
        return $user->hasPermissionTo($permissionName);
    }

    /**
     * Check if user has any of the given permissions.
     */
    public function userHasAnyPermission(User $user, array $permissions): bool
    {
        return $user->hasAnyPermission($permissions);
    }

    /**
     * Check if user has all of the given permissions.
     */
    public function userHasAllPermissions(User $user, array $permissions): bool
    {
        return $user->hasAllPermissions($permissions);
    }

    /**
     * Get all permissions for a user (via roles and direct).
     */
    public function getUserPermissions(User $user): Collection
    {
        return $user->getAllPermissions();
    }

    /**
     * Get permissions that user has via roles.
     */
    public function getUserRolePermissions(User $user): Collection
    {
        return $user->getPermissionsViaRoles();
    }

    /**
     * Get direct permissions assigned to user.
     */
    public function getUserDirectPermissions(User $user): Collection
    {
        return $user->getDirectPermissions();
    }

    /**
     * Check if user has role.
     */
    public function userHasRole(User $user, string $roleName): bool
    {
        return $user->hasRole($roleName);
    }

    /**
     * Check if user has any of the given roles.
     */
    public function userHasAnyRole(User $user, array $roles): bool
    {
        return $user->hasAnyRole($roles);
    }

    /**
     * Check if user has all of the given roles.
     */
    public function userHasAllRoles(User $user, array $roles): bool
    {
        return $user->hasAllRoles($roles);
    }

    /**
     * Get role by name.
     */
    public function getRole(string $roleName): ?Role
    {
        return Role::findByName($roleName);
    }

    /**
     * Get permission by name.
     */
    public function getPermission(string $permissionName): ?Permission
    {
        return Permission::findByName($permissionName);
    }

    /**
     * Create new role.
     */
    public function createRole(string $name, array $permissions = []): Role
    {
        $role = Role::create(['name' => $name]);
        
        if (!empty($permissions)) {
            $role->givePermissionTo($permissions);
        }
        
        return $role;
    }

    /**
     * Create new permission.
     */
    public function createPermission(string $name): Permission
    {
        return Permission::create(['name' => $name]);
    }

    /**
     * Sync permissions for a role.
     */
    public function syncRolePermissions(Role $role, array $permissions): void
    {
        $role->syncPermissions($permissions);
        
        // Clear permission cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }

    /**
     * Clear permission cache.
     */
    public function clearCache(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
