<?php

namespace App\Runtime\Automation\Contracts;

use App\Runtime\Automation\ExecutionContext;
use App\Studio\Registry\FlowNode;

interface NodeRunner
{
    /**
     * Execute the node logic.
     * 
     * @param FlowNode $node
     * @param ExecutionContext $context
     * @return mixed Output data of the node
     */
    public function run(FlowNode $node, ExecutionContext $context): mixed;
}
