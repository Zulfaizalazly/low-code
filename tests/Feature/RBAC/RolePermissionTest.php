<?php

namespace Tests\Feature\RBAC;

use Tests\TestCase;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RolePermissionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Seed roles and permissions
        $this->artisan('db:seed', ['--class' => 'Database\\Seeders\\RolePermissionSeeder']);
    }

    public function test_super_admin_has_all_permissions()
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $allPermissions = Permission::all();

        foreach ($allPermissions as $permission) {
            $this->assertTrue($user->hasPermissionTo($permission->name));
        }
    }

    public function test_feature_developer_cannot_publish()
    {
        $user = User::factory()->create();
        $user->assignRole('feature-developer');

        $this->assertTrue($user->hasPermissionTo('features.create'));
        $this->assertTrue($user->hasPermissionTo('features.edit'));
        $this->assertFalse($user->hasPermissionTo('features.publish'));
        $this->assertFalse($user->hasPermissionTo('versions.approve'));
    }

    public function test_reviewer_can_approve_but_not_publish()
    {
        $user = User::factory()->create();
        $user->assignRole('reviewer');

        $this->assertTrue($user->hasPermissionTo('versions.approve'));
        $this->assertTrue($user->hasPermissionTo('versions.reject'));
        $this->assertFalse($user->hasPermissionTo('versions.publish'));
        $this->assertFalse($user->hasPermissionTo('features.create'));
    }

    public function test_publisher_can_publish_but_not_approve()
    {
        $user = User::factory()->create();
        $user->assignRole('publisher');

        $this->assertTrue($user->hasPermissionTo('versions.publish'));
        $this->assertTrue($user->hasPermissionTo('versions.rollback'));
        $this->assertFalse($user->hasPermissionTo('versions.approve'));
        $this->assertFalse($user->hasPermissionTo('features.create'));
    }

    public function test_business_user_can_only_execute_runtime()
    {
        $user = User::factory()->create();
        $user->assignRole('business-user');

        $this->assertTrue($user->hasPermissionTo('runtime.execute'));
        $this->assertFalse($user->hasPermissionTo('features.view'));
        $this->assertFalse($user->hasPermissionTo('flows.view'));
    }

    public function test_auditor_has_read_only_access()
    {
        $user = User::factory()->create();
        $user->assignRole('auditor');

        $this->assertTrue($user->hasPermissionTo('features.view'));
        $this->assertTrue($user->hasPermissionTo('audit.view'));
        $this->assertFalse($user->hasPermissionTo('features.create'));
        $this->assertFalse($user->hasPermissionTo('features.edit'));
        $this->assertFalse($user->hasPermissionTo('runtime.execute'));
    }

    public function test_branch_staff_can_execute_runtime()
    {
        $user = User::factory()->create();
        $user->assignRole('branch_staff');

        $this->assertTrue($user->hasPermissionTo('runtime.execute'));
        $this->assertFalse($user->hasPermissionTo('flows.view'));
    }

    public function test_branch_manager_can_edit_flows_and_pages()
    {
        $user = User::factory()->create();
        $user->assignRole('branch_manager');

        $this->assertTrue($user->hasPermissionTo('flows.view'));
        $this->assertTrue($user->hasPermissionTo('flows.edit'));
        $this->assertTrue($user->hasPermissionTo('pages.view'));
        $this->assertTrue($user->hasPermissionTo('pages.edit'));
        $this->assertTrue($user->hasPermissionTo('runtime.execute'));
        $this->assertFalse($user->hasPermissionTo('flows.create'));
    }

    public function test_user_can_have_multiple_roles()
    {
        $user = User::factory()->create();
        $user->assignRole(['feature-developer', 'reviewer']);

        $this->assertTrue($user->hasRole('feature-developer'));
        $this->assertTrue($user->hasRole('reviewer'));
        $this->assertTrue($user->hasPermissionTo('features.create'));
        $this->assertTrue($user->hasPermissionTo('versions.approve'));
    }

    public function test_direct_permission_can_be_assigned()
    {
        $user = User::factory()->create();
        $user->assignRole('business-user');
        
        // Give direct permission
        $user->givePermissionTo('features.view');

        $this->assertTrue($user->hasPermissionTo('features.view'));
        $this->assertTrue($user->hasPermissionTo('runtime.execute'));
    }

    public function test_permission_can_be_revoked()
    {
        $user = User::factory()->create();
        
        // Give direct permission (not via role)
        $user->givePermissionTo('features.create');
        
        $this->assertTrue($user->hasPermissionTo('features.create'));
        
        // Revoke direct permission
        $user->revokePermissionTo('features.create');
        
        $this->assertFalse($user->hasPermissionTo('features.create'));
    }

    public function test_role_can_be_removed()
    {
        $user = User::factory()->create();
        $user->assignRole('feature-developer');
        
        $this->assertTrue($user->hasRole('feature-developer'));
        
        $user->removeRole('feature-developer');
        
        $this->assertFalse($user->hasRole('feature-developer'));
    }

    public function test_all_required_roles_exist()
    {
        $expectedRoles = [
            'super-admin',
            'system-admin',
            'feature-developer',
            'reviewer',
            'publisher',
            'business-user',
            'auditor',
            'branch_staff',
            'branch_manager',
        ];

        foreach ($expectedRoles as $roleName) {
            $this->assertNotNull(
                Role::findByName($roleName),
                "Role {$roleName} does not exist"
            );
        }
    }

    public function test_all_required_permissions_exist()
    {
        $expectedPermissions = [
            'features.view', 'features.create', 'features.edit', 'features.delete', 'features.publish',
            'flows.view', 'flows.create', 'flows.edit', 'flows.delete', 'flows.simulate',
            'pages.view', 'pages.create', 'pages.edit', 'pages.delete',
            'versions.view', 'versions.create', 'versions.submit', 'versions.review',
            'versions.approve', 'versions.reject', 'versions.publish', 'versions.rollback',
            'scopes.view', 'scopes.create', 'scopes.edit', 'scopes.delete',
            'audit.view', 'monitor.view',
            'users.view', 'users.create', 'users.edit', 'users.delete', 'users.assign-roles',
            'ai.generate-ui', 'ai.refine-ui',
            'runtime.execute', 'runtime.view-logs',
        ];

        foreach ($expectedPermissions as $permissionName) {
            $this->assertNotNull(
                Permission::findByName($permissionName),
                "Permission {$permissionName} does not exist"
            );
        }
    }
}
