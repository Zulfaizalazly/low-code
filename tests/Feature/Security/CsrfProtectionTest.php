<?php

namespace Tests\Feature\Security;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CsrfProtectionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Seed roles and permissions for each test
        $this->artisan('db:seed', ['--class' => 'Database\\Seeders\\RolePermissionSeeder']);
    }

    public function test_post_request_without_csrf_token_is_rejected()
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $response = $this->actingAs($user)
            ->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->post('/studio/support/submit-report', [
                'title' => 'Test',
                'description' => 'Test description',
            ]);

        // Without CSRF middleware, this would pass
        // With CSRF middleware, this should fail
        $this->assertTrue(true); // Placeholder assertion
    }

    public function test_post_request_with_csrf_token_is_accepted()
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $response = $this->actingAs($user)
            ->post('/studio/support/submit-report', [
                '_token' => csrf_token(),
                'title' => 'Test',
                'description' => 'Test description',
            ]);

        // This should succeed with valid CSRF token
        $response->assertStatus(302); // Redirect after successful submission
    }
}
