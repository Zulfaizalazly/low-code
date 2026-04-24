<?php

namespace App\Livewire\Runtime;

use Livewire\Component;
use App\Studio\Registry\FeatureVersion;

class StaffPortal extends Component
{
    public function render()
    {
        // For simplicity, just fetching published features.
        // In a real scenario, this would filter by branch access and feature availability.
        $features = FeatureVersion::with('feature')
            ->where('status', 'published')
            ->get();

        return view('livewire.runtime.staff-portal', [
            'features' => $features
        ])->layout('layouts.app', ['title' => 'Staff Portal']);
    }
}
