<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Services\PermissionService;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class ManagePermissions extends Command
{
    protected $signature = 'permission:manage 
                            {action : Action to perform (list-roles, list-permissions, assign-role, create-role, show-user)}
                            {--user= : User ID or email}
                            {--role= : Role name}
                            {--permissions=* : Permission names}';

    protected $description = 'Manage roles and permissions';

    protected PermissionService $permissionService;

    public function __construct(PermissionService $permissionService)
    {
        parent::__construct();
        $this->permissionService = $permissionService;
    }

    public function handle()
    {
        $action = $this->argument('action');

        match($action) {
            'list-roles' => $this->listRoles(),
            'list-permissions' => $this->listPermissions(),
            'assign-role' => $this->assignRole(),
            'create-role' => $this->createRole(),
            'show-user' => $this->showUser(),
            default => $this->error("Unknown action: {$action}")
        };
    }

    protected function listRoles()
    {
        $roles = $this->permissionService->getRolesWithPermissions();

        $this->info('Available Roles:');
        $this->newLine();

        foreach ($roles as $role) {
            $this->line("📋 <fg=cyan>{$role->name}</>");
            $this->line("   Permissions: " . $role->permissions->count());
            
            if ($this->option('verbose')) {
                foreach ($role->permissions as $permission) {
                    $this->line("   - {$permission->name}");
                }
            }
            $this->newLine();
        }
    }

    protected function listPermissions()
    {
        $grouped = $this->permissionService->getPermissionsByCategory();

        $this->info('Available Permissions:');
        $this->newLine();

        foreach ($grouped as $category => $permissions) {
            $this->line("<fg=yellow>{$category}</>");
            foreach ($permissions as $permission) {
                $this->line("  - {$permission->name}");
            }
            $this->newLine();
        }
    }

    protected function assignRole()
    {
        $userIdentifier = $this->option('user');
        $roleName = $this->option('role');

        if (!$userIdentifier || !$roleName) {
            $this->error('Both --user and --role options are required');
            return;
        }

        $user = $this->findUser($userIdentifier);
        if (!$user) {
            return;
        }

        $role = $this->permissionService->getRole($roleName);
        if (!$role) {
            $this->error("Role '{$roleName}' not found");
            return;
        }

        $this->permissionService->assignRole($user, $roleName);
        $this->info("✅ Role '{$roleName}' assigned to {$user->name}");
    }

    protected function createRole()
    {
        $roleName = $this->option('role');
        $permissions = $this->option('permissions');

        if (!$roleName) {
            $this->error('--role option is required');
            return;
        }

        if (Role::findByName($roleName)) {
            $this->error("Role '{$roleName}' already exists");
            return;
        }

        $role = $this->permissionService->createRole($roleName, $permissions);
        $this->info("✅ Role '{$roleName}' created successfully");
        
        if (!empty($permissions)) {
            $this->info("   Permissions: " . implode(', ', $permissions));
        }
    }

    protected function showUser()
    {
        $userIdentifier = $this->option('user');

        if (!$userIdentifier) {
            $this->error('--user option is required');
            return;
        }

        $user = $this->findUser($userIdentifier);
        if (!$user) {
            return;
        }

        $this->info("User: {$user->name} ({$user->email})");
        $this->newLine();

        $this->line('<fg=cyan>Roles:</>');
        foreach ($user->roles as $role) {
            $this->line("  - {$role->name}");
        }
        $this->newLine();

        $this->line('<fg=cyan>Permissions (via roles):</>');
        $rolePermissions = $this->permissionService->getUserRolePermissions($user);
        foreach ($rolePermissions as $permission) {
            $this->line("  - {$permission->name}");
        }
        $this->newLine();

        $directPermissions = $this->permissionService->getUserDirectPermissions($user);
        if ($directPermissions->count() > 0) {
            $this->line('<fg=yellow>Direct Permissions:</>');
            foreach ($directPermissions as $permission) {
                $this->line("  - {$permission->name}");
            }
        }
    }

    protected function findUser($identifier): ?User
    {
        $user = is_numeric($identifier) 
            ? User::find($identifier)
            : User::where('email', $identifier)->first();

        if (!$user) {
            $this->error("User not found: {$identifier}");
            return null;
        }

        return $user;
    }
}
