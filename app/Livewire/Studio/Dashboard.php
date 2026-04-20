<?php

namespace App\Livewire\Studio;

use App\Studio\Registry\Feature;
use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        return view('livewire.studio.dashboard', [
            'features' => Feature::withCount('versions')->latest()->get(),
        ])->layout('layouts.studio');
    }
}
