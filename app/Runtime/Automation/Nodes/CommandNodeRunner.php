<?php

namespace App\Runtime\Automation\Nodes;

use App\Kernel\Bus\CommandBus;
use App\Runtime\Automation\Contracts\NodeRunner;
use App\Runtime\Automation\ExecutionContext;
use App\Studio\Registry\FlowNode;
use Exception;

class CommandNodeRunner implements NodeRunner
{
    public function __construct(private CommandBus $bus) {}

    public function run(FlowNode $node, ExecutionContext $context): mixed
    {
        $commandClass = $node->config['command_class'] ?? null;
        $payloadMapping = $node->config['payload_mapping'] ?? [];

        if (!$commandClass || !class_exists($commandClass)) {
            throw new Exception("Invalid command class for node: {$node->node_key}");
        }

        // Resolve payload from context
        $resolvedPayload = [];
        foreach ($payloadMapping as $commandArg => $pathOrLiteral) {
            $isPath = false;
            $path = $pathOrLiteral;

            if (is_string($pathOrLiteral)) {
                if (preg_match('/^\{\{(.*)\}\}$/', $pathOrLiteral, $matches)) {
                    $isPath = true;
                    $path = trim($matches[1]);
                } elseif (preg_match('/^(nodes\.|form\.|auth\.|trigger\.|formula\.)/', $pathOrLiteral)) {
                    $isPath = true;
                }
            }

            if ($isPath) {
                $resolvedPayload[$commandArg] = $context->get($path);
            } else {
                $resolvedPayload[$commandArg] = $pathOrLiteral;
            }
        }

        // Instantiate command dynamically
        $command = new $commandClass(...$resolvedPayload);

        // Simulation Mode: Skip real dispatch
        if ($context->isSimulation) {
            return [
                'simulated_execution' => true,
                'command' => $commandClass,
                'payload' => $resolvedPayload,
                'status' => 'skipped (simulation)',
            ];
        }

        // Dispatch via Kernel CommandBus
        return $this->bus->dispatch($command);
    }
}
