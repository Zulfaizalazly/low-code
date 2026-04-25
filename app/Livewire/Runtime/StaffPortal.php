<?php

namespace App\Livewire\Runtime;

use App\Models\Branch\FeatureHealthCheck;
use App\Studio\Registry\FeatureVersion;
use Livewire\Component;

class StaffPortal extends Component
{
    public function render()
    {
        $user = auth()->user();
        $branchId = $user->branch_id;

        $features = FeatureVersion::with('feature')
            ->where('status', 'published')
            ->get()
            ->map(function ($version) {
                // Attach health status
                $healthCheck = FeatureHealthCheck::where('feature_id', $version->feature_id)
                    ->whereNull('resolved_at')
                    ->latest('checked_at')
                    ->first();

                $version->availability = $healthCheck ? $healthCheck->status : 'available';
                $version->health_error = $healthCheck?->error_message;

                return $version;
            });

        return view('livewire.runtime.staff-portal', [
            'features' => $features,
            'itSupport' => config('branch.it_support'),
        ])->layout('layouts.app', ['title' => 'Staff Portal']);
    }
}
