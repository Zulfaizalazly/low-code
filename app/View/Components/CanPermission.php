<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class CanPermission extends Component
{
    public string $permission;
    public bool $hasPermission;

    /**
     * Create a new component instance.
     */
    public function __construct(string $permission)
    {
        $this->permission = $permission;
        $this->hasPermission = auth()->check() && auth()->user()->hasPermissionTo($permission);
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.can-permission');
    }

    /**
     * Determine if the component should be rendered.
     */
    public function shouldRender(): bool
    {
        return $this->hasPermission;
    }
}
