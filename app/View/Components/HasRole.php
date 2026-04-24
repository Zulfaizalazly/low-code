<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class HasRole extends Component
{
    public string|array $role;
    public bool $hasRole;

    /**
     * Create a new component instance.
     */
    public function __construct(string|array $role)
    {
        $this->role = $role;
        
        $roles = is_array($role) ? $role : [$role];
        $this->hasRole = auth()->check() && auth()->user()->hasAnyRole($roles);
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.has-role');
    }

    /**
     * Determine if the component should be rendered.
     */
    public function shouldRender(): bool
    {
        return $this->hasRole;
    }
}
