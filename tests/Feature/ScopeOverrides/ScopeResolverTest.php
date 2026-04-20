<?php

namespace Tests\Feature\ScopeOverrides;

use App\Models\User;
use App\Studio\Registry\Feature;
use App\Studio\Registry\FeatureVersion;
use App\Studio\Registry\ScopeOverride;
use App\Studio\Scoping\ScopeResolver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScopeResolverTest extends TestCase
{
    use RefreshDatabase;

    protected ScopeResolver $resolver;
    protected FeatureVersion $version;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->resolver = app(ScopeResolver::class);
        
        $feature = Feature::factory()->create();
        $this->version = FeatureVersion::factory()->create([
            'feature_id' => $feature->id,
            'status' => 'published',
        ]);
    }

    public function test_returns_default_value_when_no_override_exists()
    {
        $result = $this->resolver->resolve(
            $this->version->id,
            'page_definitions',
            'test_page.name',
            ['branch' => '1'],
            'Default Name'
        );

        $this->assertEquals('Default Name', $result);
    }

    public function test_returns_override_value_when_exists()
    {
        ScopeOverride::create([
            'feature_version_id' => $this->version->id,
            'scope_type' => 'branch',
            'scope_id' => '1',
            'target_table' => 'page_definitions',
            'target_key' => 'test_page.name',
            'override_value' => 'Overridden Name',
            'effective_from' => now()->subDay(),
        ]);

        $result = $this->resolver->resolve(
            $this->version->id,
            'page_definitions',
            'test_page.name',
            ['branch' => '1'],
            'Default Name'
        );

        $this->assertEquals('Overridden Name', $result);
    }

    public function test_respects_precedence_order()
    {
        // Create branch-level override
        ScopeOverride::create([
            'feature_version_id' => $this->version->id,
            'scope_type' => 'branch',
            'scope_id' => '1',
            'target_table' => 'page_definitions',
            'target_key' => 'test_page.name',
            'override_value' => 'Branch Override',
            'effective_from' => now()->subDay(),
        ]);

        // Create user-level override (higher precedence)
        ScopeOverride::create([
            'feature_version_id' => $this->version->id,
            'scope_type' => 'user',
            'scope_id' => '123',
            'target_table' => 'page_definitions',
            'target_key' => 'test_page.name',
            'override_value' => 'User Override',
            'effective_from' => now()->subDay(),
        ]);

        $result = $this->resolver->resolve(
            $this->version->id,
            'page_definitions',
            'test_page.name',
            ['user' => '123', 'branch' => '1'],
            'Default Name'
        );

        // Should return user override (higher precedence)
        $this->assertEquals('User Override', $result);
    }

    public function test_ignores_inactive_overrides()
    {
        // Create future override
        ScopeOverride::create([
            'feature_version_id' => $this->version->id,
            'scope_type' => 'branch',
            'scope_id' => '1',
            'target_table' => 'page_definitions',
            'target_key' => 'test_page.name',
            'override_value' => 'Future Override',
            'effective_from' => now()->addDay(),
        ]);

        $result = $this->resolver->resolve(
            $this->version->id,
            'page_definitions',
            'test_page.name',
            ['branch' => '1'],
            'Default Name'
        );

        // Should return default (override not yet active)
        $this->assertEquals('Default Name', $result);
    }

    public function test_ignores_expired_overrides()
    {
        // Create expired override
        ScopeOverride::create([
            'feature_version_id' => $this->version->id,
            'scope_type' => 'branch',
            'scope_id' => '1',
            'target_table' => 'page_definitions',
            'target_key' => 'test_page.name',
            'override_value' => 'Expired Override',
            'effective_from' => now()->subDays(10),
            'effective_to' => now()->subDay(),
        ]);

        $result = $this->resolver->resolve(
            $this->version->id,
            'page_definitions',
            'test_page.name',
            ['branch' => '1'],
            'Default Name'
        );

        // Should return default (override expired)
        $this->assertEquals('Default Name', $result);
    }

    public function test_resolve_many_returns_multiple_values()
    {
        ScopeOverride::create([
            'feature_version_id' => $this->version->id,
            'scope_type' => 'branch',
            'scope_id' => '1',
            'target_table' => 'page_definitions',
            'target_key' => 'test_page.name',
            'override_value' => 'Overridden Name',
            'effective_from' => now()->subDay(),
        ]);

        $results = $this->resolver->resolveMany(
            $this->version->id,
            'page_definitions',
            ['test_page.name', 'test_page.title'],
            ['branch' => '1'],
            ['test_page.name' => 'Default Name', 'test_page.title' => 'Default Title']
        );

        $this->assertEquals('Overridden Name', $results['test_page.name']);
        $this->assertEquals('Default Title', $results['test_page.title']);
    }

    public function test_cache_is_used_for_repeated_calls()
    {
        ScopeOverride::create([
            'feature_version_id' => $this->version->id,
            'scope_type' => 'branch',
            'scope_id' => '1',
            'target_table' => 'page_definitions',
            'target_key' => 'test_page.name',
            'override_value' => 'Cached Value',
            'effective_from' => now()->subDay(),
        ]);

        // First call
        $result1 = $this->resolver->resolve(
            $this->version->id,
            'page_definitions',
            'test_page.name',
            ['branch' => '1'],
            'Default Name'
        );

        // Second call (should use cache)
        $result2 = $this->resolver->resolve(
            $this->version->id,
            'page_definitions',
            'test_page.name',
            ['branch' => '1'],
            'Default Name'
        );

        $this->assertEquals($result1, $result2);
        $this->assertEquals('Cached Value', $result2);
    }
}
