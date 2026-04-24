<?php

namespace App\Livewire\Branch;

use App\Models\Branch\ChangeDeployment;
use App\Models\Branch\FeatureAccessLog;
use App\Models\Branch\FeatureHealthCheck;
use App\Studio\Registry\Feature;
use Livewire\Component;

class AvailableFeatures extends Component
{
    /**
     * Available Features Display (Req 2, 4)
     *
     * Shows all published features with:
     * - Name, description, domain
     * - Availability status (available/degraded/unavailable)
     * - Last used timestamp
     * - Last deployment timestamp
     * - Error indicators
     * - "Contact IT" message when no features available
     */

    public string $search = '';

    public function render()
    {
        $user = auth()->user();
        $branchId = $user->branch_id;

        $features = Feature::where('status', 'published')
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->with(['versions' => fn($q) => $q->where('status', 'published')->latest('version_no')])
            ->get()
            ->map(function ($feature) use ($branchId) {
                // Health check
                $healthCheck = FeatureHealthCheck::forFeature($feature->id)
                    ->active()
                    ->latest('checked_at')
                    ->first();

                // Last access by branch
                $lastAccess = FeatureAccessLog::where('feature_id', $feature->id)
                    ->forBranch($branchId)
                    ->latest('accessed_at')
                    ->first();

                // Access count this week
                $weekCount = FeatureAccessLog::where('feature_id', $feature->id)
                    ->forBranch($branchId)
                    ->thisWeek()
                    ->count();

                // Last deployment
                $lastDeploy = ChangeDeployment::where('feature_id', $feature->id)
                    ->latest('deployed_at')
                    ->first();

                // Published version info
                $publishedVersion = $feature->versions->first();

                $feature->availability = $healthCheck ? $healthCheck->status : 'available';
                $feature->health_error = $healthCheck?->error_message;
                $feature->last_used = $lastAccess?->accessed_at;
                $feature->week_usage = $weekCount;
                $feature->last_deployed = $lastDeploy?->deployed_at ?? $publishedVersion?->published_at;
                $feature->is_new = $lastDeploy && $lastDeploy->isNew();
                $feature->version_no = $publishedVersion?->version_no;

                return $feature;
            });

        $totalFeatures = $features->count();
        $availableCount = $features->where('availability', 'available')->count();
        $issueCount = $features->whereIn('availability', ['degraded', 'unavailable'])->count();

        return view('livewire.branch.available-features', [
            'features' => $features,
            'totalFeatures' => $totalFeatures,
            'availableCount' => $availableCount,
            'issueCount' => $issueCount,
            'itSupport' => config('branch.it_support'),
        ])->layout('layouts.branch');
    }
}
