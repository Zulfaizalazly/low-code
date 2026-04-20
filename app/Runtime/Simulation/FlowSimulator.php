<?php

namespace App\Runtime\Simulation;

use App\Runtime\Automation\ExecutionContext;
use App\Runtime\Automation\FlowOrchestrator;
use App\Runtime\Models\AutomationExecutionLog;
use App\Runtime\Simulation\Models\SimulationLog;
use App\Studio\Registry\FlowDefinition;

class FlowSimulator extends FlowOrchestrator
{
    /**
     * Execute a flow in simulation mode.
     * Side effects are mocked, and the log is marked as a simulation.
     */
    public function simulate(FlowDefinition $flow, array $inputData = []): SimulationLog
    {
        // Execute the flow using parent logic
        $executionLog = $this->execute($flow, $inputData, true);

        // Map execution log results for the simulation report
        $results = $executionLog->nodeLogs()
            ->orderBy('started_at')
            ->get()
            ->map(fn($log) => [
                'node_key' => $log->node_key,
                'status' => $log->status,
                'input' => $log->input_data,
                'output' => $log->output_data,
                'error' => $log->error_message,
                'duration_ms' => $log->completed_at ? $log->completed_at->diffInMilliseconds($log->started_at) : 0,
            ])
            ->toArray();

        // Persist to simulation_logs
        return SimulationLog::create([
            'feature_version_id' => $flow->feature_version_id,
            'test_data' => $inputData,
            'results' => $results,
            'status' => $executionLog->status === 'completed' ? 'success' : 'failed',
            'executed_by' => auth()->id(),
            'executed_at' => now(),
        ]);
    }
}
