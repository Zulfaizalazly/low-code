<?php

namespace App\Studio\Discovery;

class ActionRegistry
{
    /**
     * Get all discoverable actions (Commands) grouped by domain.
     */
    public function getActions(): array
    {
        $discoverer = new CommandDiscoverer();
        $commands = $discoverer->discover();

        return collect($commands)
            ->groupBy('domain')
            ->toArray();
    }
}
