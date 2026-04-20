<?php

namespace App\Runtime\Automation\Nodes;

use App\Runtime\Automation\Contracts\NodeRunner;
use App\Runtime\Automation\ExecutionContext;
use App\Studio\Registry\FlowNode;

class EndNodeRunner implements NodeRunner
{
    public function run(FlowNode $node, ExecutionContext $context): mixed
    {
        return ['status' => 'end_reached'];
    }
}
