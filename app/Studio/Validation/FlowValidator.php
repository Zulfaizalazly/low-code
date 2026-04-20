<?php

namespace App\Studio\Validation;

use App\Studio\Registry\FlowDefinition;
use App\Studio\Registry\FlowNode;
use App\Studio\Registry\FlowEdge;

class FlowValidator
{
    public function validate(FlowDefinition $flow): array
    {
        $errors = [];
        $warnings = [];

        $nodes = $flow->nodes;
        $edges = $flow->edges;

        // VR-1.1: Has trigger
        if (!$nodes->where('node_type', 'trigger')->count()) {
            $errors[] = "Flow missing a trigger node.";
        }

        // VR-1.2: Has end node
        if (!$nodes->where('node_type', 'end')->count()) {
            $errors[] = "Flow missing an end node.";
        }

        // VR-1.3 & 1.4: Orphans and connectivity
        foreach ($nodes as $node) {
            $hasIncoming = $edges->where('target_node_id', $node->id)->count() > 0;
            $hasOutgoing = $edges->where('source_node_id', $node->id)->count() > 0;

            if ($node->node_type === 'trigger' && !$hasOutgoing) {
                $errors[] = "Trigger node '{$node->label}' is not connected to any outcome.";
            } elseif ($node->node_type === 'end' && !$hasIncoming) {
                $errors[] = "End node '{$node->label}' is unreachable.";
            } elseif ($node->node_type !== 'trigger' && $node->node_type !== 'end') {
                if (!$hasIncoming) {
                    $errors[] = "Node '{$node->label}' is unreachable (no incoming edges).";
                }
                if (!$hasOutgoing) {
                    $errors[] = "Node '{$node->label}' is a dead end (no outgoing edges).";
                }
            }

            // VR-1.5: Config completeness
            $this->validateNodeConfig($node, $errors);
        }

        // VR-1.6: Circular dependency detection (Simplified DFS)
        if ($this->hasCycles($nodes, $edges)) {
            $errors[] = "Flow contains a circular dependency (infinite loop).";
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'warnings' => $warnings,
        ];
    }

    private function validateNodeConfig(FlowNode $node, &$errors): void
    {
        $config = $node->config ?? [];

        switch ($node->node_type) {
            case 'command':
                if (empty($config['command_class'])) {
                    $errors[] = "Command node '{$node->label}' missing command class.";
                }
                break;
            case 'decision':
                if (empty($config['expression']) && empty($config['condition_type'])) {
                    $errors[] = "Decision node '{$node->label}' missing logic condition.";
                }
                break;
            case 'formula':
                if (empty($config['formula_key'])) {
                    $errors[] = "Formula node '{$node->label}' missing formula reference.";
                }
                break;
        }
    }

    private function hasCycles($nodes, $edges): bool
    {
        $adj = [];
        foreach ($edges as $edge) {
            $adj[$edge->source_node_id][] = $edge->target_node_id;
        }

        $visited = [];
        $stack = [];

        foreach ($nodes as $node) {
            if ($this->isCyclic($node->id, $adj, $visited, $stack)) {
                return true;
            }
        }

        return false;
    }

    private function isCyclic($v, $adj, &$visited, &$stack): bool
    {
        if (!isset($visited[$v])) {
            $visited[$v] = true;
            $stack[$v] = true;

            if (isset($adj[$v])) {
                foreach ($adj[$v] as $neighbor) {
                    if (!isset($visited[$neighbor]) && $this->isCyclic($neighbor, $adj, $visited, $stack)) {
                        return true;
                    } elseif (isset($stack[$neighbor]) && $stack[$neighbor]) {
                        return true;
                    }
                }
            }
        }
        $stack[$v] = false;
        return false;
    }
}
