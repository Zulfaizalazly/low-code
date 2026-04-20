<?php

namespace App\Runtime\Automation\Nodes;

use App\Domain\Accounting\Commands\PostJournalEntry;
use App\Kernel\Bus\CommandBus;
use App\Runtime\Automation\Contracts\NodeRunner;
use App\Runtime\Automation\ExecutionContext;
use App\Studio\Registry\FlowNode;

class GLActionNodeRunner implements NodeRunner
{
    public function __construct(private CommandBus $bus) {}

    public function run(FlowNode $node, ExecutionContext $context): mixed
    {
        $config = $node->config ?? [];

        $description = $this->interpolate($config['description'] ?? 'Auto-generated journal entry', $context);
        $referenceType = $this->resolve($config['reference_type'] ?? null, $context);
        $referenceId = $this->resolve($config['reference_id'] ?? null, $context);

        // Resolve journal lines — each line can have amounts from context
        $lineTemplates = $config['lines'] ?? [];
        $lines = [];
        foreach ($lineTemplates as $lineTemplate) {
            $lines[] = [
                'account_code' => $lineTemplate['account_code'] ?? '',
                'account_name' => $lineTemplate['account_name'] ?? '',
                'debit' => $this->resolveNumeric($lineTemplate['debit'] ?? 0, $context),
                'credit' => $this->resolveNumeric($lineTemplate['credit'] ?? 0, $context),
                'description' => $this->interpolate($lineTemplate['description'] ?? null, $context),
            ];
        }

        // Simulation Mode: Skip real dispatch
        if ($context->isSimulation) {
            return [
                'simulated_execution' => true,
                'node_type' => 'gl_action',
                'description' => $description,
                'lines_count' => count($lines),
                'total_debit' => collect($lines)->sum('debit'),
                'total_credit' => collect($lines)->sum('credit'),
                'status' => 'skipped (simulation)',
            ];
        }

        $command = new PostJournalEntry(
            description: $description,
            lines: $lines,
            referenceType: $referenceType ? (string) $referenceType : null,
            referenceId: $referenceId ? (int) $referenceId : null,
        );

        $entry = $this->bus->dispatch($command);

        return [
            'journal_entry_id' => $entry->id,
            'entry_number' => $entry->entry_number,
            'is_balanced' => $entry->is_balanced,
            'lines_count' => $entry->lines()->count(),
        ];
    }

    /**
     * Resolve a value from the execution context if it looks like a path.
     */
    private function resolve(mixed $value, ExecutionContext $context): mixed
    {
        if (!is_string($value)) {
            return $value;
        }

        if (preg_match('/^\{\{(.*)\}\}$/', $value, $matches)) {
            return $context->get(trim($matches[1]));
        }

        if (preg_match('/^(nodes\.|form\.|auth\.)/', $value)) {
            return $context->get($value);
        }

        return $value;
    }

    /**
     * Resolve a numeric value — could be a literal number or a context path.
     */
    private function resolveNumeric(mixed $value, ExecutionContext $context): float
    {
        if (is_numeric($value)) {
            return (float) $value;
        }

        $resolved = $this->resolve($value, $context);
        return is_numeric($resolved) ? (float) $resolved : 0;
    }

    /**
     * Interpolate {{variable}} placeholders in template strings.
     */
    private function interpolate(?string $template, ExecutionContext $context): ?string
    {
        if ($template === null) {
            return null;
        }

        return preg_replace_callback('/\{\{(.*?)\}\}/', function ($matches) use ($context) {
            $path = trim($matches[1]);
            $value = $context->get($path);
            return is_scalar($value) ? (string) $value : json_encode($value);
        }, $template);
    }
}
