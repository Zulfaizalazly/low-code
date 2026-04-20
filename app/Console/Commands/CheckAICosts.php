<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CheckAICosts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ai:check-costs {--org= : Filter by Organization ID}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check and report AI generation costs and budget status';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $orgId = $this->option('org');
        
        $query = DB::table('ai_usage_logs')
            ->whereMonth('used_at', now()->month)
            ->whereYear('used_at', now()->year);

        if ($orgId) {
            $query->where('organization_id', $orgId);
        }

        $totalCost = (float) $query->sum('cost_usd');
        $totalRequests = $query->count();
        $budget = config('ai.monthly_budget_usd', 50.0);
        $percentUsed = ($totalCost / $budget) * 100;

        $this->info("AI Usage Report for " . now()->format('F Y'));
        $this->info("----------------------------------");
        
        $this->table(
            ['Metric', 'Value'],
            [
                ['Total Requests', $totalRequests],
                ['Month-to-Date Cost', '$' . number_with_precision($totalCost, 4)],
                ['Monthly Budget', '$' . number_with_precision($budget, 2)],
                ['Budget Used', number_with_precision($percentUsed, 2) . '%'],
            ]
        );

        if ($percentUsed >= 100) {
            $this->error("CRITICAL: AI Budget exceeded!");
        } elseif ($percentUsed >= (config('ai.warn_cost_threshold', 0.8) * 100)) {
            $this->warn("WARNING: AI Budget usage is over " . (config('ai.warn_cost_threshold', 0.8) * 100) . "%!");
        } else {
            $this->info("Budget health: Good");
        }

        // Breakdown by model
        $modelBreakdown = $query->select('model_used', DB::raw('count(*) as count'), DB::raw('sum(cost_usd) as cost'))
            ->groupBy('model_used')
            ->get();

        if ($modelBreakdown->isNotEmpty()) {
            $this->newLine();
            $this->info("Model Breakdown:");
            $this->table(
                ['Model', 'Requests', 'Cost'],
                $modelBreakdown->map(fn($m) => [
                    $m->model_used,
                    $m->count,
                    '$' . number_with_precision($m->cost, 4)
                ])->toArray()
            );
        }
    }
}

function number_with_precision($number, $precision) {
    return number_format($number, $precision, '.', '');
}
