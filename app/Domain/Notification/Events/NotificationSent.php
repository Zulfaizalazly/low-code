<?php

namespace App\Domain\Notification\Events;

use App\Domain\Notification\Models\NotificationLog;
use App\Kernel\Events\DomainEvent;

class NotificationSent extends DomainEvent
{
    public function __construct(public NotificationLog $log)
    {
        parent::__construct();
    }

    public function toPayload(): array
    {
        return [
            'id' => $this->log->id,
            'recipient' => $this->log->recipient,
            'channel' => $this->log->channel,
            'status' => $this->log->status,
        ];
    }

    public function getSource(): object
    {
        return $this->log;
    }
}
