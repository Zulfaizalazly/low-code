<?php

namespace App\Kernel\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

abstract class DomainEvent
{
    use Dispatchable, SerializesModels;

    public float $emittedAt;

    public function __construct()
    {
        $this->emittedAt = microtime(true);
    }

    /**
     * Convert event to loggable payload.
     * 
     * @return array
     */
    abstract public function toPayload(): array;

    /**
     * Get the source entity of the event.
     * 
     * @return object|null
     */
    public function getSource(): ?object
    {
        return null;
    }
}
