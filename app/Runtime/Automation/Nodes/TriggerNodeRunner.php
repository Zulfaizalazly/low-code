<?php

namespace App\Runtime\Automation\Nodes;

use App\Runtime\Automation\Contracts\NodeRunner;
use App\Runtime\Automation\ExecutionContext;
use App\Studio\Registry\FlowNode;

class TriggerNodeRunner implements NodeRunner
{
    public function run(FlowNode $node, ExecutionContext $context): mixed
    {
        // Triggers simply pass the context through
        return $context->all();
    }
}
