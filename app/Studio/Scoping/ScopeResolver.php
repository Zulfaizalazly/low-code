<?php

namespace App\Studio\Scoping;

use App\Studio\Registry\ScopeOverride;
use Illuminate\Support\Facades\Cache;

class ScopeResolver
{
    /**
     * Scope precedence order (highest to lowest).
     */
    protected array $precedence = [
        'user',      // User-specific override (highest priority)
        'branch',    // Branch-specific override
        'region',    // Region-specific override
        'product',   // Product-specific override
        'entity',    // Entity-specific override
        'global',    // Global override (lowest priority)
    ];

    /**
     * Resolve configuration value with scope overrides.
     */
    public function resolve(
        int $featureVersionId,
        string $targetTable,
        string $targetKey,
        array $scopeContext,
        $defaultValue = null
    ) {
        $cacheKey = $this->getCacheKey($featureVersionId, $targetTable, $targetKey, $scopeContext);

        // Register key for bulk clearing (no tags needed)
        $this->registerCacheKey($featureVersionId, $cacheKey);

        return Cache::remember($cacheKey, 3600, function () use (
            $featureVersionId,
            $targetTable,
            $targetKey,
            $scopeContext,
            $defaultValue
        ) {
            return $this->resolveFromDatabase(
                $featureVersionId,
                $targetTable,
                $targetKey,
                $scopeContext,
                $defaultValue
            );
        });
    }

    /**
     * Resolve from database with precedence logic.
     */
    protected function resolveFromDatabase(
        int $featureVersionId,
        string $targetTable,
        string $targetKey,
        array $scopeContext,
        $defaultValue
    ) {
        $overrides = ScopeOverride::where('feature_version_id', $featureVersionId)
            ->forTarget($targetTable, $targetKey)
            ->active()
            ->get();

        if ($overrides->isEmpty()) {
            return $defaultValue;
        }

        foreach ($this->precedence as $scopeType) {
            if (!isset($scopeContext[$scopeType])) {
                continue;
            }

            $scopeId = $scopeContext[$scopeType];

            $override = $overrides->first(function ($override) use ($scopeType, $scopeId) {
                return $override->scope_type === $scopeType
                    && $override->scope_id == $scopeId;
            });

            if ($override) {
                return $override->override_value;
            }
        }

        return $defaultValue;
    }

    /**
     * Resolve multiple values at once.
     */
    public function resolveMany(
        int $featureVersionId,
        string $targetTable,
        array $targetKeys,
        array $scopeContext,
        array $defaults = []
    ): array {
        $results = [];
        foreach ($targetKeys as $key) {
            $results[$key] = $this->resolve(
                $featureVersionId, $targetTable, $key, $scopeContext, $defaults[$key] ?? null
            );
        }
        return $results;
    }

    /**
     * Clear cache for specific override (compatible with all cache drivers).
     */
    public function clearCache(
        int $featureVersionId,
        string $targetTable,
        string $targetKey,
        array $scopeContext = []
    ): void {
        if (empty($scopeContext)) {
            // Clear all registered keys for this feature version
            $this->clearFeatureCache($featureVersionId);
        } else {
            $cacheKey = $this->getCacheKey($featureVersionId, $targetTable, $targetKey, $scopeContext);
            Cache::forget($cacheKey);
        }
    }

    /**
     * Clear all cache for a feature version (compatible with all drivers).
     */
    public function clearFeatureCache(int $featureVersionId): void
    {
        $registryKey = "scope_override_registry_{$featureVersionId}";
        $keys = Cache::get($registryKey, []);

        foreach ($keys as $key) {
            Cache::forget($key);
        }

        Cache::forget($registryKey);
    }

    /**
     * Register a cache key in the feature version registry.
     */
    protected function registerCacheKey(int $featureVersionId, string $cacheKey): void
    {
        $registryKey = "scope_override_registry_{$featureVersionId}";
        $keys = Cache::get($registryKey, []);

        if (!in_array($cacheKey, $keys)) {
            $keys[] = $cacheKey;
            Cache::put($registryKey, $keys, 7200); // 2 hours
        }
    }

    /**
     * Get cache key for override resolution.
     */
    protected function getCacheKey(
        int $featureVersionId,
        string $targetTable,
        string $targetKey,
        array $scopeContext
    ): string {
        $contextHash = md5(json_encode($scopeContext));
        return "scope_override_{$featureVersionId}_{$targetTable}_{$targetKey}_{$contextHash}";
    }

    /**
     * Get scope context from user.
     */
    public function getScopeContextFromUser($user): array
    {
        return [
            'user' => $user->id,
            'branch' => $user->branch_id,
            'entity' => $user->entity_id,
        ];
    }

    /**
     * Get all active overrides for a feature version.
     */
    public function getActiveOverrides(int $featureVersionId): array
    {
        return ScopeOverride::where('feature_version_id', $featureVersionId)
            ->active()
            ->get()
            ->groupBy('scope_type')
            ->toArray();
    }

    /**
     * Check if override exists for specific scope.
     */
    public function hasOverride(
        int $featureVersionId,
        string $targetTable,
        string $targetKey,
        string $scopeType,
        string $scopeId
    ): bool {
        return ScopeOverride::where('feature_version_id', $featureVersionId)
            ->forTarget($targetTable, $targetKey)
            ->forScope($scopeType, $scopeId)
            ->active()
            ->exists();
    }
}
