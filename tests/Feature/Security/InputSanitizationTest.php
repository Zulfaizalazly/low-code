<?php

namespace Tests\Feature\Security;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InputSanitizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Seed roles and permissions for each test
        $this->artisan('db:seed', ['--class' => 'Database\\Seeders\\RolePermissionSeeder']);
    }

    public function test_xss_attack_is_sanitized()
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $maliciousInput = '<script>alert("XSS")</script>';
        
        $response = $this->actingAs($user)->post('/studio/support/submit-report', [
            'title' => $maliciousInput,
            'description' => 'Test description',
        ]);

        // The input should be sanitized
        $this->assertStringNotContainsString('<script>', $response->getContent());
    }

    public function test_sql_injection_is_prevented()
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $maliciousInput = "'; DROP TABLE users; --";
        
        // This should not cause any SQL injection
        $response = $this->actingAs($user)->post('/studio/support/submit-report', [
            'title' => $maliciousInput,
            'description' => 'Test description',
        ]);

        // Users table should still exist
        $this->assertDatabaseHas('users', ['id' => $user->id]);
    }

    public function test_html_tags_are_stripped()
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $inputWithHtml = '<b>Bold text</b> and <i>italic text</i>';
        
        $response = $this->actingAs($user)->post('/studio/support/submit-report', [
            'title' => $inputWithHtml,
            'description' => 'Test description',
        ]);

        // HTML tags should be stripped
        $this->assertStringNotContainsString('<b>', $response->getContent());
        $this->assertStringNotContainsString('<i>', $response->getContent());
    }
}
