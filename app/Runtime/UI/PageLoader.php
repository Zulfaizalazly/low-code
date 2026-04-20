<?php

namespace App\Runtime\UI;

use App\Studio\Registry\Feature;
use App\Studio\Registry\PageDefinition;
use App\Studio\Scoping\ScopeResolver;

class PageLoader
{
    protected ScopeResolver $scopeResolver;

    public function __construct(ScopeResolver $scopeResolver)
    {
        $this->scopeResolver = $scopeResolver;
    }

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
            $page = $query->where('key', $pageKey)->first();
        } else {
            $page = $query->where('is_entry_page', true)->first();
        }

        if (!$page) {
            return null;
        }

        // Apply scope overrides if user is authenticated
        if (auth()->check()) {
            $page = $this->applyScopeOverrides($page, $version->id);
        }

        return $page;
    }

    /**
     * Apply scope overrides to page definition.
     */
    protected function applyScopeOverrides(PageDefinition $page, int $versionId): PageDefinition
    {
        $scopeContext = $this->scopeResolver->getScopeContextFromUser(auth()->user());

        // Apply overrides to page properties
        $overridableFields = ['name', 'page_type', 'config'];

        foreach ($overridableFields as $field) {
            $overrideValue = $this->scopeResolver->resolve(
                $versionId,
                'page_definitions',
                "{$page->key}.{$field}",
                $scopeContext,
                $page->$field
            );

            if ($overrideValue !== $page->$field) {
                $page->$field = $overrideValue;
            }
        }

        return $page;
    }
}
