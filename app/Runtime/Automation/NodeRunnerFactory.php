<?php

namespace App\Runtime\Automation;

use App\Runtime\Automation\Contracts\NodeRunner;
use Exception;

class NodeRunnerFactory
{
    private static array $runners = [
        'trigger' => Nodes\TriggerNodeRunner::class,
        'command' => Nodes\CommandNodeRunner::class,
        'end' => Nodes\EndNodeRunner::class,
        'decision' => Nodes\DecisionNodeRunner::class,
        'approval' => Nodes\ApprovalNodeRunner::class,
        'notification' => Nodes\NotificationNodeRunner::class,
        'document' => Nodes\DocumentNodeRunner::class,
        'gl_action' => Nodes\GLActionNodeRunner::class,
        'formula' => Nodes\FormulaNodeRunner::class,
    ];

    /**
     * Create a node runner instance for the given node type.
     */
    public static function make(string $nodeType): NodeRunner
    {
        if (!isset(self::$runners[$nodeType])) {
            throw new Exception("No runner registered for node type: {$nodeType}");
        }

        $class = self::$runners[$nodeType];
        return app($class);
    }
}
