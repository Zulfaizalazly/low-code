<?php

namespace App\Domain\Approval\Events;

use App\Domain\Approval\Models\ApprovalTask;
use App\Kernel\Events\DomainEvent;

class ApprovalDecisionRecorded extends DomainEvent
{
    public function __construct(public ApprovalTask $task)
    {
        parent::__construct();
    }

    public function toPayload(): array
    {
        return [
            'id' => $this->task->id,
            'status' => $this->task->status,
            'decision' => $this->task->decision,
            'target_type' => $this->task->approvable_type,
            'target_id' => $this->task->approvable_id,
        ];
    }

    public function getSource(): object
    {
        return $this->task;
    }
}
