<?php

namespace App\Runtime\Automation\Nodes;

use App\Runtime\Automation\Contracts\NodeRunner;
use App\Runtime\Automation\ExecutionContext;
use App\Studio\Registry\FlowNode;

class DecisionNodeRunner implements NodeRunner
{
    public function run(FlowNode $node, ExecutionContext $context): mixed
    {
        $expression = $node->config['expression'] ?? null;
        
        // Very basic evaluator for demo purposes in Phase 2.
        // In a real production app, we would use symfony/expression-language.
        
        $variable = $node->config['variable'] ?? null; // e.g., 'nodes.valuation.output.ltv'
        $operator = $node->config['operator'] ?? '==';
        $value = $node->config['value'] ?? null;

        // Handle missing variable config
        if ($variable === null || $variable === '') {
            if ($context->isSimulation) {
                return [
                    'outcome' => 'false',
                    'simulated_execution' => true,
                    'warnings' => ["Decision variable not configured — defaulted to 'false' for simulation."],
                ];
            }
            throw new \Exception("DecisionNodeRunner: 'variable' is required in node config for node {$node->node_key}");
        }

        $currentValue = $context->get($variable);

        // Handle missing variable value in simulation
        if ($currentValue === null && $context->isSimulation) {
            return [
                'outcome' => 'false',
                'simulated_execution' => true,
                'warnings' => ["Variable '{$variable}' resolved to null — defaulted to 'false' for simulation."],
            ];
        }
        
        $result = match($operator) {
            '>' => $currentValue > $value,
            '<' => $currentValue < $value,
            '>=' => $currentValue >= $value,
            '<=' => $currentValue <= $value,
            '!=' => $currentValue != $value,
            default => $currentValue == $value,
        };

        return ['outcome' => $result ? 'true' : 'false'];
    }
}
