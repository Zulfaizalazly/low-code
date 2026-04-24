<?php

namespace App\Runtime\Automation\Nodes;

use App\Runtime\Automation\Contracts\NodeRunner;
use App\Runtime\Automation\ExecutionContext;
use App\Studio\Registry\FlowNode;
use Exception;

class GeneratePdfNodeRunner implements NodeRunner
{
    public function run(FlowNode $node, ExecutionContext $context): mixed
    {
        $config = $node->config ?? [];
        
        $templateId = $config['template_id'] ?? null;
        $outputKey = $config['output_key'] ?? 'generated_pdf';

        if (!$templateId) {
            throw new Exception("GeneratePdfNodeRunner: template_id is required.");
        }

        // Placeholder for PDF Generation Logic
        // In a real implementation, this would load the template, inject $context data,
        // render HTML, generate PDF, save to storage, and return the path/URL.
        
        $pdfUrl = "/storage/documents/sag_fake_" . time() . ".pdf";
        
        $context->set($outputKey, [
            'url' => $pdfUrl,
            'status' => 'generated',
            'timestamp' => now()->toIso8601String()
        ]);

        return ['url' => $pdfUrl];
    }
}
