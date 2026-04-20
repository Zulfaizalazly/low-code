<?php

namespace App\Studio\Publishing;

use Illuminate\Support\Collection;

class PublishGateResult
{
    private Collection $checks;

    public function __construct(Collection $checks)
    {
        $this->checks = $checks;
    }

    /**
     * Check if any gate has failed.
     */
    public function hasFailures(): bool
    {
        return $this->checks->where('status', 'failed')->isNotEmpty();
    }

    /**
     * Get all failed checks.
     */
    public function failures(): Collection
    {
        return $this->checks->where('status', 'failed');
    }

    /**
     * Get all passed checks.
     */
    public function passed(): Collection
    {
        return $this->checks->where('status', 'passed');
    }

    /**
     * Get all warning checks.
     */
    public function warnings(): Collection
    {
        return $this->checks->where('status', 'warning');
    }

    /**
     * Get all checks.
     */
    public function all(): Collection
    {
        return $this->checks;
    }

    /**
     * Get a summary string.
     */
    public function summary(): string
    {
        $passed = $this->passed()->count();
        $failed = $this->failures()->count();
        $warnings = $this->warnings()->count();
        $total = $this->checks->count();

        return "{$passed}/{$total} passed, {$failed} failed, {$warnings} warnings";
    }

    /**
     * Convert to array for JSON responses.
     */
    public function toArray(): array
    {
        return [
            'summary' => $this->summary(),
            'has_failures' => $this->hasFailures(),
            'checks' => $this->checks->toArray(),
        ];
    }
}
