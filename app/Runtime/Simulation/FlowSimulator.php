<?php

namespace App\Runtime\Simulation;

use App\Runtime\Automation\ExecutionContext;
use App\Runtime\Automation\FlowOrchestrator;
use App\Runtime\Models\AutomationExecutionLog;
use App\Studio\Registry\FlowDefinition;

class FlowSimulator extends FlowOrchestrator
{
    /**
     * Execute a flow in simulation mode.
     * Side effects are mocked, and the log is marked as a simulation.
     */
    public function simulate(FlowDefinition $flow, array $inputData = []): AutomationExecutionLog
    {
        // We can use the same execute logic but with a specialized context
        // that node runners can inspect to decide whether to perform real actions.
        
        $triggerData = array_merge($inputData, [
            '_simulation' => true,
            '_simulated_at' => now()->toIso8601String(),
        ]);

        return $this->execute($flow, $triggerData);
    }
}
