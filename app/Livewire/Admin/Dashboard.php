<?php

namespace App\Livewire\Admin;

use App\Models\Organization\Branch;
use App\Models\Organization\Department;
use App\Models\Organization\Region;
use App\Models\Organization\StaffAssignment;
use App\Models\User;
use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        $activeBranchCount = Branch::active()->count();
        $totalStaffCount = User::active()->count();
        $departmentCount = Department::active()->count();
        $regionCount = Region::active()->count();

        $branchTypeBreakdown = Branch::query()
            ->selectRaw('type, count(*) as count')
            ->groupBy('type')
            ->pluck('count', 'type')
            ->toArray();

        $employmentTypeBreakdown = StaffAssignment::active()
            ->selectRaw('employment_type, count(*) as count')
            ->groupBy('employment_type')
            ->pluck('count', 'employment_type')
            ->toArray();

        $branchesWithStats = Branch::query()
            ->withCount('activeStaffAssignments')
            ->with('manager')
            ->orderBy('name')
            ->get();

        $recentAssignments = StaffAssignment::query()
            ->where(function ($q) {
                $q->where('created_at', '>=', now()->subDays(30))
                  ->orWhere('updated_at', '>=', now()->subDays(30));
            })
            ->with(['user', 'branch', 'department'])
            ->latest('updated_at')
            ->limit(10)
            ->get();

        return view('livewire.admin.dashboard', [
            'activeBranchCount' => $activeBranchCount,
            'totalStaffCount' => $totalStaffCount,
            'departmentCount' => $departmentCount,
            'regionCount' => $regionCount,
            'branchTypeBreakdown' => $branchTypeBreakdown,
            'employmentTypeBreakdown' => $employmentTypeBreakdown,
            'branchesWithStats' => $branchesWithStats,
            'recentAssignments' => $recentAssignments,
        ])->layout('layouts.admin');
    }
}
