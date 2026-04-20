<?php

namespace App\Livewire\Studio;

use App\Studio\Registry\Feature;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        $mtdCost = DB::table('ai_usage_logs')
            ->whereMonth('used_at', now()->month)
            ->whereYear('used_at', now()->year)
            ->sum('cost_usd');

        $budget = config('ai.monthly_budget_usd', 50.0);
        $budgetUsedPercent = ($mtdCost / $budget) * 100;

        return view('livewire.studio.dashboard', [
            'features' => Feature::withCount('versions')->latest()->get(),
            'mtd_ai_cost' => $mtdCost,
            'budget_used_percent' => $budgetUsedPercent,
        ])->layout('layouts.studio');
    }
}
