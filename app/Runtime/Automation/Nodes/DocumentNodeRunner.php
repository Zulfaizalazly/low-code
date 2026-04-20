<?php

namespace App\Runtime\Automation\Nodes;

use App\Domain\Document\Commands\GenerateDocument;
use App\Kernel\Bus\CommandBus;
use App\Runtime\Automation\Contracts\NodeRunner;
use App\Runtime\Automation\ExecutionContext;
use App\Studio\Registry\FlowNode;

class DocumentNodeRunner implements NodeRunner
{
    public function __construct(private CommandBus $bus) {}

    public function run(FlowNode $node, ExecutionContext $context): mixed
    {
        $config = $node->config ?? [];

        $documentableType = $this->resolve($config['documentable_type'] ?? null, $context);
        $documentableId = $this->resolve($config['documentable_id'] ?? null, $context);
        $documentType = $config['document_type'] ?? 'contract';
        $templateId = $config['template_id'] ?? null;

        // Collect additional data from context if configured
        $dataMapping = $config['data_mapping'] ?? [];
        $data = [];
        foreach ($dataMapping as $key => $path) {
            $data[$key] = $this->resolve($path, $context);
        }

        // Simulation Mode: Skip real dispatch
        if ($context->get('_simulation')) {
            return [
                'simulated_execution' => true,
                'node_type' => 'document',
                'document_type' => $documentType,
                'documentable_type' => $documentableType,
                'documentable_id' => $documentableId,
                'status' => 'skipped (simulation)',
            ];
        }

        $command = new GenerateDocument(
            documentableType: (string) $documentableType,
            documentableId: (int) $documentableId,
            documentType: $documentType,
            templateId: $templateId ? (int) $templateId : null,
            data: $data,
        );

        $document = $this->bus->dispatch($command);

        return [
            'document_id' => $document->id,
            'file_path' => $document->file_path,
            'file_name' => $document->file_name,
            'document_type' => $documentType,
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
}
