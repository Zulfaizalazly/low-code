<?php

namespace App\Studio\Publishing;

use Exception;

class PublishGateException extends Exception
{
    private PublishGateResult $gateResult;

    public function __construct(PublishGateResult $gateResult)
    {
        $this->gateResult = $gateResult;

        $failedKeys = $gateResult->failures()->pluck('key')->implode(', ');
        $message = "Publish blocked: {$gateResult->summary()}. Failed checks: {$failedKeys}";

        parent::__construct($message);
    }

    /**
     * Get the full gate validation result.
     */
    public function getGateResult(): PublishGateResult
    {
        return $this->gateResult;
    }

    /**
     * Get just the failed checks for display.
     */
    public function getFailures(): array
    {
        return $this->gateResult->failures()->toArray();
    }
}
