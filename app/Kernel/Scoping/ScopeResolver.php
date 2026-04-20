<?php

namespace App\Kernel\Scoping;

use App\Studio\Registry\ScopeOverride;
use App\Models\User;

class ScopeResolver
{
    /**
     * Resolve the effective configuration for a feature context.
     * Hierarchy: Branch Override > Entity Override > Feature Version Default
     */
    public function resolveEffectiveConfig(int $featureVersionId, array $defaultConfig, User $user): array
    {
        $overrides = ScopeOverride::where('feature_version_id', $featureVersionId)
            ->where('is_active', true)
            ->get();

        // 1. Check for Branch Override
        $branchOverride = $overrides->where('scope_type', 'branch')
            ->where('scope_id', $user->branch_id)
            ->first();

        if ($branchOverride) {
            return array_merge($defaultConfig, $branchOverride->override_config);
        }

        // 2. Check for Entity Override
        $entityOverride = $overrides->where('scope_type', 'entity')
            ->where('scope_id', $user->entity_id)
            ->first();

        if ($entityOverride) {
            return array_merge($defaultConfig, $entityOverride->override_config);
        }

        // 3. Fallback to Default
        return $defaultConfig;
    }
}
