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
        
        $currentValue = $context->get($variable);
        
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
