<?php

namespace Tests\Feature\RBAC;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MiddlewareTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Seed roles and permissions
        $this->artisan('db:seed', ['--class' => 'Database\\Seeders\\RolePermissionSeeder']);
    }

    public function test_unauthenticated_user_redirected_to_login()
    {
        $response = $this->get('/studio/flow-canvas/1');
        
        $response->assertRedirect('/login');
    }

    public function test_user_without_permission_gets_403()
    {
        $user = User::factory()->create();
        $user->assignRole('business-user'); // No flows.edit permission

        $response = $this->actingAs($user)
            ->get('/studio/flow-canvas/1');

        $response->assertStatus(403);
    }

    public function test_user_with_permission_can_access()
    {
        $user = User::factory()->create();
        $user->assignRole('feature-developer'); // Has flows.edit permission

        // Note: This will fail if flow doesn't exist, but middleware passes
        $response = $this->actingAs($user)
            ->get('/studio/flow-canvas/1');

        // Should not be 403 (permission denied)
        $this->assertNotEquals(403, $response->status());
    }

    public function test_super_admin_can_access_all_routes()
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        // Test various protected routes
        $routes = [
            '/studio/flow-canvas/1',
            '/studio/page-builder/1/1',
            '/studio/monitor',
        ];

        foreach ($routes as $route) {
            $response = $this->actingAs($user)->get($route);
            $this->assertNotEquals(403, $response->status(), "Super admin denied access to {$route}");
        }
    }

    public function test_role_middleware_works()
    {
        $user = User::factory()->create();
        $user->assignRole('business-user');

        // Create a test route with role middleware
        \Route::get('/test-role', function () {
            return 'OK';
        })->middleware(['auth', 'role:super-admin']);

        $response = $this->actingAs($user)->get('/test-role');
        $response->assertStatus(403);

        // Now test with correct role
        $admin = User::factory()->create();
        $admin->assignRole('super-admin');

        $response = $this->actingAs($admin)->get('/test-role');
        $response->assertStatus(200);
    }

    public function test_permission_middleware_works()
    {
        $user = User::factory()->create();
        $user->assignRole('business-user');

        // Create a test route with permission middleware
        \Route::get('/test-permission', function () {
            return 'OK';
        })->middleware(['auth', 'permission:features.create']);

        $response = $this->actingAs($user)->get('/test-permission');
        $response->assertStatus(403);

        // Now test with correct permission
        $developer = User::factory()->create();
        $developer->assignRole('feature-developer');

        $response = $this->actingAs($developer)->get('/test-permission');
        $response->assertStatus(200);
    }
}
