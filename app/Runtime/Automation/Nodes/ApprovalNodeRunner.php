<?php

namespace App\Runtime\Automation\Nodes;

use App\Domain\Approval\Commands\CreateApprovalTask;
use App\Kernel\Bus\CommandBus;
use App\Runtime\Automation\Contracts\NodeRunner;
use App\Runtime\Automation\ExecutionContext;
use App\Studio\Registry\FlowNode;

class ApprovalNodeRunner implements NodeRunner
{
    public function __construct(private CommandBus $bus) {}

    public function run(FlowNode $node, ExecutionContext $context): mixed
    {
        $config = $node->config ?? [];

        // Resolve payload from context paths or literal values
        $approvableType = $this->resolve($config['approvable_type'] ?? null, $context);
        $approvableId = $this->resolve($config['approvable_id'] ?? null, $context);
        $approvalTier = $config['approval_tier'] ?? 'tier_1';
        $assignedRole = $config['assigned_role'] ?? null;
        $assignedTo = $this->resolve($config['assigned_to'] ?? null, $context);
        $remarks = $this->resolve($config['remarks'] ?? null, $context);

        // Simulation Mode: Skip real dispatch
        if ($context->get('_simulation')) {
            return [
                'simulated_execution' => true,
                'node_type' => 'approval',
                'approvable_type' => $approvableType,
                'approvable_id' => $approvableId,
                'approval_tier' => $approvalTier,
                'status' => 'skipped (simulation)',
            ];
        }

        $command = new CreateApprovalTask(
            approvableType: (string) $approvableType,
            approvableId: (int) $approvableId,
            approvalTier: $approvalTier,
            assignedRole: $assignedRole,
            assignedTo: $assignedTo ? (int) $assignedTo : null,
            remarks: $remarks,
        );

        $task = $this->bus->dispatch($command);

        return [
            'approval_task_id' => $task->id,
            'status' => 'pending',
            'approval_tier' => $approvalTier,
            'assigned_role' => $assignedRole,
        ];
    }

    /**
     * Resolve a value from the execution context if it looks like a path,
     * otherwise return it as a literal value.
     */
    private function resolve(mixed $value, ExecutionContext $context): mixed
    {
        if (!is_string($value)) {
            return $value;
        }

        // {{path}} syntax
        if (preg_match('/^\{\{(.*)\}\}$/', $value, $matches)) {
            return $context->get(trim($matches[1]));
        }

        // Implicit path detection (starts with known prefixes)
        if (preg_match('/^(nodes\.|form\.|auth\.)/', $value)) {
            return $context->get($value);
        }

        return $value;
    }
}
