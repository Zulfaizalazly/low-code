<?php

namespace App\Livewire\Studio;

use App\Models\AuditTrail;
use Livewire\Component;
use Livewire\WithPagination;

class AuditLogs extends Component
{
    use WithPagination;

    // Filters
    public string $search = '';
    public string $filterAction = '';
    public string $filterDateRange = '7d';

    // Available action types for filter
    public array $actionTypes = [
        ''                           => 'All Actions',
        'STAFF_VIEW_ENTERED'         => 'Staff View Entered',
        'STAFF_VIEW_EXITED'          => 'Staff View Exited',
        'FEATURE_EXECUTION_AS_STAFF' => 'Feature Execution (Staff Mode)',
    ];

    // Reset pagination when filters change
    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedFilterAction(): void
    {
        $this->resetPage();
    }

    public function updatedFilterDateRange(): void
    {
        $this->resetPage();
    }

    /**
     * Get the action badge color based on the action type.
     */
    public static function actionColor(string $action): string
    {
        return match ($action) {
            'STAFF_VIEW_ENTERED'         => 'bg-amber-100 text-amber-800',
            'STAFF_VIEW_EXITED'          => 'bg-emerald-100 text-emerald-800',
            'FEATURE_EXECUTION_AS_STAFF' => 'bg-red-100 text-red-800',
            default                      => 'bg-gray-100 text-gray-700',
        };
    }

    /**
     * Get the action icon SVG path based on type.
     */
    public static function actionIcon(string $action): string
    {
        return match ($action) {
            'STAFF_VIEW_ENTERED'         => 'M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z',
            'STAFF_VIEW_EXITED'          => 'M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21',
            'FEATURE_EXECUTION_AS_STAFF' => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4.5c-.77-.833-2.694-.833-3.464 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z',
            default                      => 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
        };
    }

    public function render()
    {
        $query = AuditTrail::with('user')->latest();

        // Search filter
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('description', 'like', "%{$this->search}%")
                  ->orWhere('action', 'like', "%{$this->search}%")
                  ->orWhereHas('user', function ($uq) {
                      $uq->where('name', 'like', "%{$this->search}%")
                         ->orWhere('email', 'like', "%{$this->search}%");
                  });
            });
        }

        // Action type filter
        if ($this->filterAction) {
            $query->where('action', $this->filterAction);
        }

        // Date range filter
        $query->when($this->filterDateRange === '24h', fn($q) => $q->where('created_at', '>=', now()->subDay()))
              ->when($this->filterDateRange === '7d', fn($q) => $q->where('created_at', '>=', now()->subDays(7)))
              ->when($this->filterDateRange === '30d', fn($q) => $q->where('created_at', '>=', now()->subDays(30)));

        // Stats for summary cards
        $today = AuditTrail::whereDate('created_at', today());
        $statsTotal = $today->count();
        $statsStaffViews = (clone $today)->where('action', 'STAFF_VIEW_ENTERED')->count();
        $statsFeatureExecs = (clone $today)->where('action', 'FEATURE_EXECUTION_AS_STAFF')->count();
        $statsUniqueUsers = (clone $today)->distinct('user_id')->count('user_id');

        return view('livewire.studio.audit-logs', [
            'logs'              => $query->paginate(20),
            'statsTotal'        => $statsTotal,
            'statsStaffViews'   => $statsStaffViews,
            'statsFeatureExecs' => $statsFeatureExecs,
            'statsUniqueUsers'  => $statsUniqueUsers,
        ])->layout('layouts.studio');
    }
}
