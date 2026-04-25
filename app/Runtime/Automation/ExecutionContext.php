<?php

namespace App\Runtime\Automation;

use App\Runtime\Models\AutomationExecutionLog;

class ExecutionContext
{
    private array $data = [];

    public function __construct(
        public AutomationExecutionLog $log,
        array $initialData = [],
        public bool $isSimulation = false
    ) {
        // Auto-detect simulation mode from data if not explicitly set
        if (!$isSimulation && isset($initialData['_simulation'])) {
            $this->isSimulation = (bool) $initialData['_simulation'];
            unset($initialData['_simulation']); // Remove from data
        }
        
        $this->data = $initialData;
    }

    /**
     * Get data from the context using dot notation.
     */
    public function get(string|null $path, mixed $default = null): mixed
    {
        if ($path === null || $path === '') {
            return $default;
        }
        return data_get($this->data, $path, $default);
    }

    /**
     * Set data in the context using dot notation.
     */
    public function set(string $path, mixed $value): void
    {
        data_set($this->data, $path, $value);
    }

    /**
     * Get the full payload.
     */
    public function all(): array
    {
        return $this->data;
    }
}
