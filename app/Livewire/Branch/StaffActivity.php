<?php

namespace App\Livewire\Branch;

use App\Models\Branch\FeatureAccessLog;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class StaffActivity extends Component
{
    /**
     * Staff Activity Monitoring (Req 3, 8)
     *
     * Shows:
     * - List of staff and their current feature usage
     * - Feature access frequency per staff
     * - Inactive staff flagging
     * - Daily/weekly summary toggle
     */

    public string $period = 'today';

    public function setPeriod(string $period): void
    {
        $this->period = $period;
    }

    public function render()
    {
        $user = auth()->user();
        $branchId = $user->branch_id;
        $inactiveThreshold = config('branch.dashboard.inactive_staff_threshold_hours', 4);

        // ─── Staff list with activity ───
        $branchStaff = User::where('branch_id', $branchId)
            ->where('id', '!=', $user->id) // Exclude self (the manager)
            ->get()
            ->map(function ($staff) use ($branchId, $inactiveThreshold) {
                // Last access
                $lastAccess = FeatureAccessLog::where('user_id', $staff->id)
                    ->latest('accessed_at')
                    ->first();

                // Today's access count
                $todayCount = FeatureAccessLog::where('user_id', $staff->id)
                    ->today()
                    ->count();

                // This week's access count
                $weekCount = FeatureAccessLog::where('user_id', $staff->id)
                    ->thisWeek()
                    ->count();

                // Current status
                $isActive = $lastAccess && $lastAccess->accessed_at->diffInMinutes(now()) < 15;
                $isInactive = !$lastAccess || $lastAccess->accessed_at->diffInHours(now()) >= $inactiveThreshold;

                // Most used feature
                $topFeature = FeatureAccessLog::where('user_id', $staff->id)
                    ->thisWeek()
                    ->select('feature_id', DB::raw('count(*) as cnt'))
                    ->groupBy('feature_id')
                    ->orderByDesc('cnt')
                    ->first();

                $staff->last_access = $lastAccess?->accessed_at;
                $staff->last_feature = $lastAccess?->feature?->name;
                $staff->today_count = $todayCount;
                $staff->week_count = $weekCount;
                $staff->is_active = $isActive;
                $staff->is_inactive = $isInactive;
                $staff->top_feature_count = $topFeature?->cnt ?? 0;

                return $staff;
            })
            ->sortByDesc('is_active');

        // ─── Summary stats ───
        $totalStaff = $branchStaff->count();
        $activeStaff = $branchStaff->where('is_active', true)->count();
        $inactiveStaff = $branchStaff->where('is_inactive', true)->count();

        $periodQuery = FeatureAccessLog::forBranch($branchId);
        if ($this->period === 'today') {
            $periodQuery = $periodQuery->today();
        } else {
            $periodQuery = $periodQuery->thisWeek();
        }
        $totalAccesses = $periodQuery->count();

        // ─── Completion rates from execution logs ───
        $completionRate = DB::table('automation_execution_logs')
            ->where('status', 'completed')
            ->when($this->period === 'today', fn($q) => $q->whereDate('started_at', today()))
            ->when($this->period === 'week', fn($q) => $q->whereBetween('started_at', [now()->startOfWeek(), now()->endOfWeek()]))
            ->count();

        $totalExecutions = DB::table('automation_execution_logs')
            ->when($this->period === 'today', fn($q) => $q->whereDate('started_at', today()))
            ->when($this->period === 'week', fn($q) => $q->whereBetween('started_at', [now()->startOfWeek(), now()->endOfWeek()]))
            ->count();

        $completionPercent = $totalExecutions > 0 ? round(($completionRate / $totalExecutions) * 100, 1) : 100;

        return view('livewire.branch.staff-activity', [
            'branchStaff' => $branchStaff,
            'totalStaff' => $totalStaff,
            'activeStaff' => $activeStaff,
            'inactiveStaff' => $inactiveStaff,
            'totalAccesses' => $totalAccesses,
            'completionPercent' => $completionPercent,
            'totalExecutions' => $totalExecutions,
        ])->layout('layouts.branch');
    }
}
