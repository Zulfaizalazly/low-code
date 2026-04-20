<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create Permissions
        $permissions = [
            // Feature Management
            'features.view',
            'features.create',
            'features.edit',
            'features.delete',
            'features.publish',
            
            // Flow Management
            'flows.view',
            'flows.create',
            'flows.edit',
            'flows.delete',
            'flows.simulate',
            
            // Page Management
            'pages.view',
            'pages.create',
            'pages.edit',
            'pages.delete',
            
            // Version Management
            'versions.view',
            'versions.create',
            'versions.submit',
            'versions.review',
            'versions.approve',
            'versions.reject',
            'versions.publish',
            'versions.rollback',
            
            // Scope Override Management
            'scopes.view',
            'scopes.create',
            'scopes.edit',
            'scopes.delete',
            
            // Audit & Monitoring
            'audit.view',
            'monitor.view',
            
            // User Management
            'users.view',
            'users.create',
            'users.edit',
            'users.delete',
            'users.assign-roles',
            
            // AI Features
            'ai.generate-ui',
            'ai.refine-ui',
            
            // Runtime
            'runtime.execute',
            'runtime.view-logs',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create Roles and Assign Permissions
        
        // Super Admin - Full Access
        $superAdmin = Role::create(['name' => 'super-admin']);
        $superAdmin->givePermissionTo(Permission::all());

        // System Admin - All except user management
        $systemAdmin = Role::create(['name' => 'system-admin']);
        $systemAdmin->givePermissionTo([
            'features.view', 'features.create', 'features.edit', 'features.delete', 'features.publish',
            'flows.view', 'flows.create', 'flows.edit', 'flows.delete', 'flows.simulate',
            'pages.view', 'pages.create', 'pages.edit', 'pages.delete',
            'versions.view', 'versions.create', 'versions.submit', 'versions.review', 
            'versions.approve', 'versions.reject', 'versions.publish', 'versions.rollback',
            'scopes.view', 'scopes.create', 'scopes.edit', 'scopes.delete',
            'audit.view', 'monitor.view',
            'ai.generate-ui', 'ai.refine-ui',
            'runtime.execute', 'runtime.view-logs',
        ]);

        // Feature Developer - Can build features but not publish
        $developer = Role::create(['name' => 'feature-developer']);
        $developer->givePermissionTo([
            'features.view', 'features.create', 'features.edit',
            'flows.view', 'flows.create', 'flows.edit', 'flows.simulate',
            'pages.view', 'pages.create', 'pages.edit',
            'versions.view', 'versions.create', 'versions.submit',
            'scopes.view',
            'monitor.view',
            'ai.generate-ui', 'ai.refine-ui',
            'runtime.execute',
        ]);

        // Reviewer - Can review and approve versions
        $reviewer = Role::create(['name' => 'reviewer']);
        $reviewer->givePermissionTo([
            'features.view',
            'flows.view', 'flows.simulate',
            'pages.view',
            'versions.view', 'versions.review', 'versions.approve', 'versions.reject',
            'audit.view', 'monitor.view',
            'runtime.execute',
        ]);

        // Publisher - Can publish approved versions
        $publisher = Role::create(['name' => 'publisher']);
        $publisher->givePermissionTo([
            'features.view',
            'flows.view',
            'pages.view',
            'versions.view', 'versions.publish', 'versions.rollback',
            'audit.view', 'monitor.view',
            'runtime.execute',
        ]);

        // Business User - Can only execute features
        $businessUser = Role::create(['name' => 'business-user']);
        $businessUser->givePermissionTo([
            'runtime.execute',
        ]);

        // Auditor - Read-only access to everything
        $auditor = Role::create(['name' => 'auditor']);
        $auditor->givePermissionTo([
            'features.view',
            'flows.view',
            'pages.view',
            'versions.view',
            'scopes.view',
            'audit.view',
            'monitor.view',
            'runtime.view-logs',
        ]);

        $this->command->info('Roles and permissions created successfully!');
    }
}
