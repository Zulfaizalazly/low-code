<?php

namespace App\Runtime\Automation\Nodes;

use App\Runtime\Automation\Contracts\NodeRunner;
use App\Runtime\Automation\ExecutionContext;
use App\Studio\Registry\FlowNode;
use Exception;

class VaultActionNodeRunner implements NodeRunner
{
    public function run(FlowNode $node, ExecutionContext $context): mixed
    {
        $config = $node->config ?? [];
        
        $action = $config['action'] ?? 'check_in'; // check_in, check_out, audit
        $marhunIdPath = $config['marhun_id'] ?? null;
        
        if (!$marhunIdPath) {
            throw new Exception("VaultActionNodeRunner: marhun_id is required.");
        }

        $marhunId = $context->get($marhunIdPath);

        // Placeholder for Vault Audit Trail logic
        // This would update the Wadiah storage registry in the database.
        
        $logEntry = [
            'marhun_id' => $marhunId,
            'action' => $action,
            'status' => 'SUCCESS',
            'location' => 'Vault A',
            'timestamp' => now()->toIso8601String()
        ];
        
        $outputKey = $config['output_key'] ?? 'vault_log';
        $context->set($outputKey, $logEntry);

        return $logEntry;
    }
}
