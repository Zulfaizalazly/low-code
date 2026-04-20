<?php

namespace Tests\Feature\ScopeOverrides;

use App\Models\User;
use App\Studio\Registry\Feature;
use App\Studio\Registry\FeatureVersion;
use App\Studio\Registry\ScopeOverride;
use App\Studio\Scoping\ScopeOverrideManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScopeOverrideManagerTest extends TestCase
{
    use RefreshDatabase;

    protected ScopeOverrideManager $manager;
    protected FeatureVersion $version;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->manager = app(ScopeOverrideManager::class);
        
        $feature = Feature::factory()->create();
        $this->version = FeatureVersion::factory()->create([
            'feature_id' => $feature->id,
            'status' => 'published',
        ]);

        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_can_create_override()
    {
        $data = [
            'feature_version_id' => $this->version->id,
            'scope_type' => 'branch',
            'scope_id' => '1',
            'target_table' => 'page_definitions',
            'target_key' => 'test_page.name',
            'override_value' => 'Test Override',
            'effective_from' => now(),
        ];

        $override = $this->manager->create($data);

        $this->assertInstanceOf(ScopeOverride::class, $override);
        $this->assertEquals('Test Override', $override->override_value);
        $this->assertDatabaseHas('scoped_overrides', [
            'id' => $override->id,
            'scope_type' => 'branch',
        ]);
    }

    public function test_can_update_override()
    {
        $override = ScopeOverride::create([
            'feature_version_id' => $this->version->id,
            'scope_type' => 'branch',
            'scope_id' => '1',
            'target_table' => 'page_definitions',
            'target_key' => 'test_page.name',
            'override_value' => 'Original Value',
            'effective_from' => now(),
        ]);

        $updated = $this->manager->update($override, [
            'override_value' => 'Updated Value',
        ]);

        $this->assertEquals('Updated Value', $updated->override_value);
    }

    public function test_can_delete_override()
    {
        $override = ScopeOverride::create([
            'feature_version_id' => $this->version->id,
            'scope_type' => 'branch',
            'scope_id' => '1',
            'target_table' => 'page_definitions',
            'target_key' => 'test_page.name',
            'override_value' => 'Test Value',
            'effective_from' => now(),
        ]);

        $result = $this->manager->delete($override);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('scoped_overrides', [
            'id' => $override->id,
        ]);
    }

    public function test_can_expire_override()
    {
        $override = ScopeOverride::create([
            'feature_version_id' => $this->version->id,
            'scope_type' => 'branch',
            'scope_id' => '1',
            'target_table' => 'page_definitions',
            'target_key' => 'test_page.name',
            'override_value' => 'Test Value',
            'effective_from' => now()->subDay(),
            'effective_to' => null,
        ]);

        $expired = $this->manager->expire($override);

        $this->assertNotNull($expired->effective_to);
        $this->assertEquals(now()->toDateString(), $expired->effective_to->toDateString());
    }

    public function test_can_bulk_create_overrides()
    {
        $overrides = [
            [
                'feature_version_id' => $this->version->id,
                'scope_type' => 'branch',
                'scope_id' => '1',
                'target_table' => 'page_definitions',
                'target_key' => 'page1.name',
                'override_value' => 'Override 1',
                'effective_from' => now(),
            ],
            [
                'feature_version_id' => $this->version->id,
                'scope_type' => 'branch',
                'scope_id' => '1',
                'target_table' => 'page_definitions',
                'target_key' => 'page2.name',
                'override_value' => 'Override 2',
                'effective_from' => now(),
            ],
        ];

        $created = $this->manager->bulkCreate($overrides);

        $this->assertCount(2, $created);
        $this->assertDatabaseCount('scoped_overrides', 2);
    }

    public function test_detects_conflicts()
    {
        // Create existing override
        ScopeOverride::create([
            'feature_version_id' => $this->version->id,
            'scope_type' => 'branch',
            'scope_id' => '1',
            'target_table' => 'page_definitions',
            'target_key' => 'test_page.name',
            'override_value' => 'Existing Override',
            'effective_from' => now()->subDay(),
            'effective_to' => now()->addDay(),
        ]);

        // Try to create conflicting override
        $conflicts = $this->manager->checkConflicts([
            'feature_version_id' => $this->version->id,
            'scope_type' => 'branch',
            'scope_id' => '1',
            'target_table' => 'page_definitions',
            'target_key' => 'test_page.name',
            'effective_from' => now(),
        ]);

        $this->assertNotEmpty($conflicts);
    }
}
