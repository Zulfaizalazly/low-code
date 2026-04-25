<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\User;
use App\Services\PermissionService;
use Spatie\Permission\Models\Role;

class UserRoleManager extends Component
{
    public User $user;
    public array $selectedRoles = [];
    public array $selectedPermissions = [];
    
    protected PermissionService $permissionService;

    public function boot(PermissionService $permissionService)
    {
        $this->permissionService = $permissionService;
    }

    public function mount(User $user)
    {
        // Check permission
        if (!auth()->user()->hasPermissionTo('users.assign-roles')) {
            abort(403, 'Unauthorized to manage user roles');
        }

        $this->user = $user;
        $this->selectedRoles = $user->roles->pluck('name')->toArray();
        $this->selectedPermissions = $user->getDirectPermissions()->pluck('name')->toArray();
    }

    public function updateRoles()
    {
        // Validate
        $this->validate([
            'selectedRoles' => 'array',
            'selectedRoles.*' => 'exists:roles,name',
        ]);

        // Sync roles
        $this->permissionService->syncRoles($this->user, $this->selectedRoles);

        session()->flash('message', 'User roles updated successfully.');
    }

    public function updatePermissions()
    {
        // Validate
        $this->validate([
            'selectedPermissions' => 'array',
            'selectedPermissions.*' => 'exists:permissions,name',
        ]);

        // Sync direct permissions
        $this->user->syncPermissions($this->selectedPermissions);
        $this->permissionService->clearCache();

        session()->flash('message', 'User permissions updated successfully.');
    }

    public function render()
    {
        $roles = Role::all();
        $permissionsByCategory = $this->permissionService->getPermissionsByCategory();
        $userPermissions = $this->permissionService->getUserPermissions($this->user);

        return view('livewire.admin.user-role-manager', [
            'roles' => $roles,
            'permissionsByCategory' => $permissionsByCategory,
            'userPermissions' => $userPermissions,
        ])->layout('layouts.admin');
    }
}
