<?php

namespace App\Domain\Approval\Events;

use App\Domain\Approval\Models\ApprovalTask;
use App\Kernel\Events\DomainEvent;

class ApprovalTaskCreated extends DomainEvent
{
    public function __construct(public ApprovalTask $task)
    {
        parent::__construct();
    }

    public function toPayload(): array
    {
        return [
            'id' => $this->task->id,
            'target_type' => $this->task->approvable_type,
            'target_id' => $this->task->approvable_id,
            'tier' => $this->task->approval_tier,
            'assigned_role' => $this->task->assigned_role,
        ];
    }

    public function getSource(): object
    {
        return $this->task;
    }
}
