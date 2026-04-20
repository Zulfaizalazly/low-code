<?php

namespace App\Livewire\Runtime;

use App\Runtime\UI\MenuManager;
use Livewire\Component;

class Sidebar extends Component
{
    public function render(MenuManager $manager)
    {
        return view('livewire.runtime.sidebar', [
            'menuGroups' => $manager->getSidebarMenus(),
        ]);
    }
}
