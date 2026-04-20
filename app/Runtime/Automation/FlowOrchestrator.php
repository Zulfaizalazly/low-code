<?php

namespace App\Runtime\Automation;

use App\Runtime\Models\AutomationExecutionLog;
use App\Runtime\Models\AutomationNodeLog;
use App\Studio\Registry\FlowDefinition;
use App\Studio\Registry\FlowNode;
use Exception;
use Throwable;

class FlowOrchestrator
{
    public function execute(FlowDefinition $flow, array $triggerData = []): AutomationExecutionLog
    {
        $log = AutomationExecutionLog::create([
            'flow_definition_id' => $flow->id,
            'feature_version_id' => $flow->feature_version_id,
            'trigger_type' => $flow->trigger_type,
            'status' => 'running',
            'started_at' => now(),
        ]);

        $initialData = [
            'auth' => [
                'id' => auth()->id(),
                'branch_id' => auth()->user()?->branch_id,
                'entity_id' => auth()->user()?->entity_id,
            ]
        ];

        $initialData = array_merge($initialData, $triggerData);
        $context = new ExecutionContext($log, $initialData);
        $context->set('auth', $initialData['auth']);

        try {
            $startNode = $flow->nodes()->where('node_type', 'trigger')->first();
            if (!$startNode) {
                throw new Exception("Flow has no trigger node.");
            }

            $this->runNode($startNode, $context);

            $log->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);
        } catch (Throwable $e) {
            $log->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'completed_at' => now(),
            ]);
            throw $e;
        }

        return $log;
    }

    private function runNode(FlowNode $node, ExecutionContext $context): void
    {
        $nodeLog = AutomationNodeLog::create([
            'execution_log_id' => $context->log->id,
            'flow_node_id' => $node->id,
            'node_key' => $node->node_key,
            'node_type' => $node->node_type,
            'input_data' => $context->all(),
            'status' => 'running',
            'started_at' => now(),
        ]);

        try {
            $runner = NodeRunnerFactory::make($node->node_type);
            $output = $runner->run($node, $context);

            // Store output in context if applicable
            $context->set("nodes.{$node->node_key}.output", $output);

            $nodeLog->update([
                'status' => 'completed',
                'output_data' => $output,
                'completed_at' => now(),
            ]);

            $this->transition($node, $context, $output);
        } catch (Throwable $e) {
            $nodeLog->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'completed_at' => now(),
            ]);
            throw $e;
        }
    }

    private function transition(FlowNode $node, ExecutionContext $context, mixed $lastOutput): void
    {
        // Resolve next edge
        $edges = \App\Studio\Registry\FlowEdge::where('source_node_id', $node->id)
            ->orderBy('priority', 'asc')
            ->get();

        foreach ($edges as $edge) {
            if ($this->shouldTransition($edge, $context, $lastOutput)) {
                $nextNode = FlowNode::find($edge->target_node_id);
                if ($nextNode) {
                    $this->runNode($nextNode, $context);
                }
                return;
            }
        }
    }

    private function shouldTransition(\App\Studio\Registry\FlowEdge $edge, ExecutionContext $context, mixed $lastOutput): bool
    {
        if ($edge->condition_type === 'always') {
            return true;
        }

        if ($edge->condition_type === 'outcome') {
            $expectedOutcome = $edge->condition_config['outcome'] ?? null;
            $actualOutcome = is_array($lastOutput) ? ($lastOutput['outcome'] ?? null) : null;
            
            return $expectedOutcome === $actualOutcome;
        }

        return false;
    }
}
