<?php

namespace App\Kernel\Events;

class DomainEventLogger
{
    /**
     * Handle any DomainEvent by logging it to the event_logs table.
     */
    public function handle(DomainEvent $event): void
    {
        $source = $event->getSource();

        EventLog::create([
            'event_type' => class_basename($event),
            'event_payload' => $event->toPayload(),
            'source_type' => $source ? get_class($source) : null,
            'source_id' => $source?->getKey(),
            'emitted_at' => now(),
        ]);
    }
}
