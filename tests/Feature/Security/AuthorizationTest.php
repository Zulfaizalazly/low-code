<?php

namespace Tests\Feature\Security;

use App\Models\User;
use App\Studio\Registry\Feature;
use App\Studio\Registry\FeatureVersion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Spatie\Permission\Models\Role;

class AuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Seed roles and permissions
        $this->artisan('db:seed', ['--class' => 'Database\\Seeders\\RolePermissionSeeder']);
    }

    public function test_unauthenticated_user_cannot_access_studio()
    {
        $response = $this->get('/studio');
        $response->assertRedirect('/login');
    }

    public function test_business_user_cannot_access_studio()
    {
        $user = User::factory()->create();
        $user->assignRole('business-user');

        $response = $this->actingAs($user)->get('/studio');
        $response->assertStatus(403);
    }

    public function test_super_admin_can_access_all_studio_features()
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $response = $this->actingAs($user)->get('/studio');
        $response->assertStatus(200);
    }

    public function test_feature_developer_can_create_features()
    {
        $user = User::factory()->create();
        $user->assignRole('feature-developer');

        $this->assertTrue($user->can('create', Feature::class));
    }

    public function test_feature_developer_cannot_publish_features()
    {
        $user = User::factory()->create();
        $user->assignRole('feature-developer');

        $feature = Feature::factory()->create();
        $this->assertFalse($user->can('publish', $feature));
    }

    public function test_reviewer_can_approve_versions()
    {
        $user = User::factory()->create();
        $user->assignRole('reviewer');

        $version = FeatureVersion::factory()->create(['status' => 'pending_review']);
        $this->assertTrue($user->can('approve', $version));
    }

    public function test_reviewer_cannot_edit_features()
    {
        $user = User::factory()->create();
        $user->assignRole('reviewer');

        $feature = Feature::factory()->create();
        $this->assertFalse($user->can('update', $feature));
    }

    public function test_publisher_can_publish_approved_versions()
    {
        $user = User::factory()->create();
        $user->assignRole('publisher');

        $version = FeatureVersion::factory()->create(['status' => 'approved']);
        $this->assertTrue($user->can('publish', $version));
    }

    public function test_publisher_cannot_publish_draft_versions()
    {
        $user = User::factory()->create();
        $user->assignRole('publisher');

        $version = FeatureVersion::factory()->create(['status' => 'draft']);
        $this->assertFalse($user->can('publish', $version));
    }

    public function test_auditor_has_read_only_access()
    {
        $user = User::factory()->create();
        $user->assignRole('auditor');

        $feature = Feature::factory()->create();
        
        $this->assertTrue($user->can('view', $feature));
        $this->assertFalse($user->can('update', $feature));
        $this->assertFalse($user->can('delete', $feature));
    }
}
