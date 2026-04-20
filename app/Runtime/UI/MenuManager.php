<?php

namespace App\Runtime\UI;

use App\Studio\Registry\FeatureMenuItem;
use Illuminate\Support\Collection;

class MenuManager
{
    /**
     * Get all active menu items for the sidebar.
     * In a real app, this would check permissions and published status.
     */
    public function getSidebarMenus(): Collection
    {
        return FeatureMenuItem::where('is_enabled', true)
            ->orderBy('sort_order')
            ->get()
            ->groupBy('parent_menu_key');
    }
}
