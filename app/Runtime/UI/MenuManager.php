<?php

namespace App\Runtime\UI;

use App\Studio\Registry\FeatureMenuItem;
use Illuminate\Support\Collection;

class MenuManager
{
    /**
     * Get sidebar menu items grouped by their parent feature's domain.
     *
     * Only returns items from published feature versions that are enabled,
     * reflecting what HQ/IT has configured and deployed via Studio.
     *
     * URLs are generated from the named route (portal.operations.launch)
     * using the feature key — never from hardcoded route_key strings.
     */
    public function getSidebarMenus(): Collection
    {
        return FeatureMenuItem::where('feature_menu_items.is_enabled', true)
            ->join('feature_versions', 'feature_versions.id', '=', 'feature_menu_items.feature_version_id')
            ->join('features', 'features.id', '=', 'feature_versions.feature_id')
            ->where('feature_versions.status', 'published')
            ->where('features.status', 'published')
            ->orderBy('features.domain')
            ->orderBy('feature_menu_items.sort_order')
            ->select(
                'feature_menu_items.*',
                'features.domain as group_label',
                'features.key as feature_key',
                'features.icon as feature_icon'
            )
            ->get()
            ->groupBy('group_label');
    }
}
