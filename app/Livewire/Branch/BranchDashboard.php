<?php

namespace App\Livewire\Branch;

use App\Models\Branch\ChangeDeployment;
use App\Models\Branch\FeatureAccessLog;
use App\Models\Branch\FeatureHealthCheck;
use App\Models\Branch\SupportTicket;
use App\Studio\Registry\Feature;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class BranchDashboard extends Component
{
    /**
     * Branch Operations Dashboard (Req 1, 4, 5, 7, 10)
     *
     * Provides operational visibility into:
     * - Active feature count
     * - Staff currently using features
     * - Feature usage stats (today/week)
     * - Branch health status
     * - Recent IT deployments (change tracker)
     * - Feature availability
     */

    public function render()
    {
        $user = auth()->user();
        $branchId = $user->branch_id;
        $activeWindow = config('branch.dashboard.active_staff_window_minutes', 15);

        // ─── Stats Cards ───
        $activeFeatures = Feature::where('status', 'published')->count();

        $staffActiveNow = FeatureAccessLog::forBranch($branchId)
            ->recent($activeWindow)
            ->distinct('user_id')
            ->count('user_id');

        $usageToday = FeatureAccessLog::forBranch($branchId)->today()->count();
        $usageThisWeek = FeatureAccessLog::forBranch($branchId)->thisWeek()->count();

        // ─── Health Status ───
        $activeIssues = FeatureHealthCheck::hasIssues()->count();
        $healthStatus = match (true) {
            $activeIssues === 0 => ['label' => 'All Systems Operational', 'color' => 'emerald'],
            $activeIssues <= 2 => ['label' => 'Minor Issues Detected', 'color' => 'amber'],
            default => ['label' => 'Service Disruption', 'color' => 'rose'],
        };

        // ─── Change Tracker (Req 5) ───
        $recentDeployments = ChangeDeployment::visibleToBranches()
            ->recent(7)
            ->with('feature')
            ->latest('deployed_at')
            ->limit(5)
            ->get();

        // ─── Feature Availability (Req 4) ───
        $features = Feature::where('status', 'published')
            ->get()
            ->map(function ($feature) use ($branchId) {
                $healthCheck = FeatureHealthCheck::forFeature($feature->id)
                    ->active()
                    ->latest('checked_at')
                    ->first();

                $lastAccess = FeatureAccessLog::where('feature_id', $feature->id)
                    ->forBranch($branchId)
                    ->latest('accessed_at')
                    ->first();

                $feature->availability = $healthCheck
                    ? $healthCheck->status
                    : 'available';
                $feature->last_used = $lastAccess?->accessed_at;
                $feature->health_error = $healthCheck?->error_message;

                return $feature;
            });

        // ─── Open Support Tickets ───
        $openTickets = SupportTicket::forUser($user->id)->open()->count();

        // ─── Performance metrics (Req 10) ───
        $todayAccessCount = FeatureAccessLog::forBranch($branchId)->today()->count();
        $avgPerHour = now()->hour > 0 ? round($todayAccessCount / now()->hour, 1) : $todayAccessCount;

        return view('livewire.branch.dashboard', [
            'activeFeatures' => $activeFeatures,
            'staffActiveNow' => $staffActiveNow,
            'usageToday' => $usageToday,
            'usageThisWeek' => $usageThisWeek,
            'healthStatus' => $healthStatus,
            'recentDeployments' => $recentDeployments,
            'features' => $features,
            'openTickets' => $openTickets,
            'avgPerHour' => $avgPerHour,
            'itSupport' => config('branch.it_support'),
        ])->layout('layouts.branch');
    }
}
