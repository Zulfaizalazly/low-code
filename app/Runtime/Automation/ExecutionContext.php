<?php

namespace App\Runtime\Automation;

use App\Runtime\Models\AutomationExecutionLog;

class ExecutionContext
{
    private array $data = [];

    public function __construct(
        public AutomationExecutionLog $log,
        array $initialData = []
    ) {
        $this->data = $initialData;
    }

    /**
     * Get data from the context using dot notation.
     */
    public function get(string $path, mixed $default = null): mixed
    {
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
