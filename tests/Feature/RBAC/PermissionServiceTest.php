<?php

namespace Tests\Feature\RBAC;

use Tests\TestCase;
use App\Models\User;
use App\Services\PermissionService;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PermissionServiceTest extends TestCase
{
    use RefreshDatabase;

    protected PermissionService $service;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Seed roles and permissions
        $this->artisan('db:seed', ['--class' => 'Database\\Seeders\\RolePermissionSeeder']);
        
        $this->service = new PermissionService();
    }

    public function test_get_permissions_by_category()
    {
        $grouped = $this->service->getPermissionsByCategory();

        $this->assertIsArray($grouped);
        $this->assertArrayHasKey('Features', $grouped);
        $this->assertArrayHasKey('Flows', $grouped);
        $this->assertArrayHasKey('Versions', $grouped);
    }

    public function test_get_roles_with_permissions()
    {
        $roles = $this->service->getRolesWithPermissions();

        $this->assertGreaterThan(0, $roles->count());
        $this->assertTrue($roles->first()->relationLoaded('permissions'));
    }

    public function test_assign_role_to_user()
    {
        $user = User::factory()->create();

        $this->service->assignRole($user, 'feature-developer');

        $this->assertTrue($user->hasRole('feature-developer'));
    }

    public function test_remove_role_from_user()
    {
        $user = User::factory()->create();
        $user->assignRole('feature-developer');

        $this->service->removeRole($user, 'feature-developer');

        $this->assertFalse($user->hasRole('feature-developer'));
    }

    public function test_sync_roles()
    {
        $user = User::factory()->create();
        $user->assignRole(['feature-developer', 'reviewer']);

        $this->service->syncRoles($user, ['publisher']);

        $this->assertFalse($user->hasRole('feature-developer'));
        $this->assertFalse($user->hasRole('reviewer'));
        $this->assertTrue($user->hasRole('publisher'));
    }

    public function test_give_permission_to_user()
    {
        $user = User::factory()->create();

        $this->service->givePermission($user, 'features.view');

        $this->assertTrue($user->hasPermissionTo('features.view'));
    }

    public function test_revoke_permission_from_user()
    {
        $user = User::factory()->create();
        $user->givePermissionTo('features.view');

        $this->service->revokePermission($user, 'features.view');

        $this->assertFalse($user->hasPermissionTo('features.view'));
    }

    public function test_user_has_permission()
    {
        $user = User::factory()->create();
        $user->assignRole('feature-developer');

        $this->assertTrue($this->service->userHasPermission($user, 'features.create'));
        $this->assertFalse($this->service->userHasPermission($user, 'versions.publish'));
    }

    public function test_user_has_any_permission()
    {
        $user = User::factory()->create();
        $user->assignRole('feature-developer');

        $this->assertTrue($this->service->userHasAnyPermission($user, [
            'features.create',
            'versions.publish'
        ]));

        $this->assertFalse($this->service->userHasAnyPermission($user, [
            'versions.publish',
            'versions.approve'
        ]));
    }

    public function test_user_has_all_permissions()
    {
        $user = User::factory()->create();
        $user->assignRole('feature-developer');

        $this->assertTrue($this->service->userHasAllPermissions($user, [
            'features.create',
            'features.edit'
        ]));

        $this->assertFalse($this->service->userHasAllPermissions($user, [
            'features.create',
            'versions.publish'
        ]));
    }

    public function test_get_user_permissions()
    {
        $user = User::factory()->create();
        $user->assignRole('feature-developer');

        $permissions = $this->service->getUserPermissions($user);

        $this->assertGreaterThan(0, $permissions->count());
        $this->assertTrue($permissions->contains('name', 'features.create'));
    }

    public function test_get_user_role_permissions()
    {
        $user = User::factory()->create();
        $user->assignRole('feature-developer');

        $permissions = $this->service->getUserRolePermissions($user);

        $this->assertGreaterThan(0, $permissions->count());
    }

    public function test_get_user_direct_permissions()
    {
        $user = User::factory()->create();
        $user->givePermissionTo('features.view');

        $permissions = $this->service->getUserDirectPermissions($user);

        $this->assertEquals(1, $permissions->count());
        $this->assertEquals('features.view', $permissions->first()->name);
    }

    public function test_user_has_role()
    {
        $user = User::factory()->create();
        $user->assignRole('feature-developer');

        $this->assertTrue($this->service->userHasRole($user, 'feature-developer'));
        $this->assertFalse($this->service->userHasRole($user, 'super-admin'));
    }

    public function test_user_has_any_role()
    {
        $user = User::factory()->create();
        $user->assignRole('feature-developer');

        $this->assertTrue($this->service->userHasAnyRole($user, [
            'feature-developer',
            'reviewer'
        ]));

        $this->assertFalse($this->service->userHasAnyRole($user, [
            'super-admin',
            'reviewer'
        ]));
    }

    public function test_create_role()
    {
        $role = $this->service->createRole('test-role', ['features.view']);

        $this->assertInstanceOf(Role::class, $role);
        $this->assertEquals('test-role', $role->name);
        $this->assertTrue($role->hasPermissionTo('features.view'));
    }

    public function test_create_permission()
    {
        $permission = $this->service->createPermission('test.permission');

        $this->assertInstanceOf(Permission::class, $permission);
        $this->assertEquals('test.permission', $permission->name);
    }

    public function test_sync_role_permissions()
    {
        $role = Role::findByName('feature-developer');
        
        $this->service->syncRolePermissions($role, ['features.view', 'flows.view']);

        $role->refresh();
        $this->assertTrue($role->hasPermissionTo('features.view'));
        $this->assertTrue($role->hasPermissionTo('flows.view'));
        $this->assertFalse($role->hasPermissionTo('features.create'));
    }
}
