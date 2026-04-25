<?php

namespace App\Livewire\Branch;

use App\Models\Branch\ChangeDeployment;
use App\Models\Branch\FeatureAccessLog;
use App\Models\Branch\FeatureHealthCheck;
use App\Models\Branch\SupportTicket;
use App\Studio\Registry\Feature;
use App\Studio\Registry\FeatureVersion;
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
        $publishedFeatureIds = Feature::where('status', 'published')->pluck('id');
        $activeIssues = FeatureHealthCheck::hasIssues()
            ->whereIn('feature_id', $publishedFeatureIds)
            ->count();
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

        // ─── Weekly Performance Summary (Req 8.2, 8.5) ───
        $weekStart = now()->startOfWeek();
        $weekEnd = now()->endOfWeek();

        $weeklyExecStats = DB::table('automation_execution_logs')
            ->whereBetween('started_at', [$weekStart, $weekEnd])
            ->selectRaw('COUNT(*) as total_executions')
            ->selectRaw("SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_count")
            ->selectRaw("AVG(CASE WHEN status = 'completed' AND completed_at IS NOT NULL THEN (strftime('%s', completed_at) - strftime('%s', started_at)) ELSE NULL END) as avg_completion_seconds")
            ->first();

        $totalExecutions = (int) ($weeklyExecStats->total_executions ?? 0);
        $completedCount = (int) ($weeklyExecStats->completed_count ?? 0);

        $weeklyPerformance = [
            'total_accesses' => $usageThisWeek,
            'total_executions' => $totalExecutions,
            'completion_rate' => $totalExecutions > 0
                ? round(($completedCount / $totalExecutions) * 100, 1)
                : 100,
            'avg_completion_seconds' => round((float) ($weeklyExecStats->avg_completion_seconds ?? 0), 1),
            'active_staff_count' => FeatureAccessLog::forBranch($branchId)
                ->thisWeek()
                ->distinct('user_id')
                ->count('user_id'),
        ];

        // ─── Usage Drop Detection (Req 8.3) ───
        $usageDropAlert = null;
        $lastWeekStart = now()->subWeek()->startOfWeek();
        $lastWeekEnd = now()->subWeek()->endOfWeek();

        $publishedFeatures = Feature::where('status', 'published')->get();

        foreach ($publishedFeatures as $feature) {
            $currentWeekCount = FeatureAccessLog::forBranch($branchId)
                ->where('feature_id', $feature->id)
                ->thisWeek()
                ->count();

            $previousWeekCount = FeatureAccessLog::forBranch($branchId)
                ->where('feature_id', $feature->id)
                ->whereBetween('accessed_at', [$lastWeekStart, $lastWeekEnd])
                ->count();

            if ($previousWeekCount === 0) {
                continue;
            }

            $dropPercent = round(($previousWeekCount - $currentWeekCount) / $previousWeekCount * 100, 1);

            if ($dropPercent > config('branch.dashboard.usage_drop_threshold_percent', 30)) {
                $usageDropAlert = [
                    'feature_name' => $feature->name,
                    'current_week' => $currentWeekCount,
                    'previous_week' => $previousWeekCount,
                    'drop_percent' => $dropPercent,
                ];
                break; // Show the first significant drop
            }
        }

        // ─── Performance Degradation Alert (Req 10.4) ───
        $perfDegradationAlert = null;
        $perfThreshold = config('branch.dashboard.perf_degradation_threshold_per_hour', 2);

        if ($avgPerHour < $perfThreshold && now()->hour > 2) {
            $perfDegradationAlert = "Performance degradation detected: average {$avgPerHour} executions/hour is below the threshold of {$perfThreshold}/hour.";
        }

        // ─── Notifications (Req 7.2, 7.4) ───
        $notifications = [];

        // Unavailable feature notifications (deduplicated by feature_id, scoped to published)
        $healthIssues = FeatureHealthCheck::hasIssues()
            ->whereIn('feature_id', $publishedFeatureIds)
            ->with('feature')
            ->get()
            ->unique('feature_id');

        foreach ($healthIssues as $issue) {
            $notifications[] = [
                'type' => 'unavailable',
                'message' => "Feature \"{$issue->feature->name}\" is currently {$issue->status}.",
                'time' => $issue->checked_at,
            ];
        }

        // Recent deployment notifications (deduplicated by feature_id)
        $recentDeploymentNotifs = ChangeDeployment::visibleToBranches()
            ->where('deployed_at', '>=', now()->subHour())
            ->with('feature')
            ->latest('deployed_at')
            ->get()
            ->unique('feature_id');

        foreach ($recentDeploymentNotifs as $deployment) {
            $notifications[] = [
                'type' => 'deployment',
                'message' => "Feature \"{$deployment->feature->name}\" has been updated and deployed.",
                'time' => $deployment->deployed_at,
            ];
        }

        // ─── Version Diff for Deployment Tracker (Req 5.2) ───
        $recentDeployments->load('featureVersion');

        foreach ($recentDeployments as $deployment) {
            $currentVersionNo = $deployment->featureVersion?->version_no;

            if ($currentVersionNo) {
                $previousVersion = FeatureVersion::where('feature_id', $deployment->feature_id)
                    ->where('version_no', '<', $currentVersionNo)
                    ->orderByDesc('version_no')
                    ->first();

                $deployment->setAttribute('previous_version_no', $previousVersion?->version_no);
            } else {
                $deployment->setAttribute('previous_version_no', null);
            }
        }

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
            'weeklyPerformance' => $weeklyPerformance,
            'usageDropAlert' => $usageDropAlert,
            'perfDegradationAlert' => $perfDegradationAlert,
            'notifications' => $notifications,
        ])->layout('layouts.branch');
    }
}
