<?php

namespace App\Runtime\UI;

use App\Studio\Registry\Feature;
use App\Studio\Registry\PageDefinition;

class PageLoader
{
    /**
     * Load a page definition based on feature key and optional page key.
     */
    public function load(string $featureKey, string $pageKey = null): ?PageDefinition
    {
        $feature = Feature::where('key', $featureKey)
            ->where('status', 'published')
            ->first();

        if (!$feature) {
            return null;
        }

        $version = $feature->currentVersion;
        if (!$version) {
            return null;
        }

        $query = PageDefinition::where('feature_version_id', $version->id);

        if ($pageKey) {
            return $query->where('key', $pageKey)->first();
        }

        return $query->where('is_entry_page', true)->first();
    }
}
