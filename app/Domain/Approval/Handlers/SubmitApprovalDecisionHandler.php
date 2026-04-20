<?php

namespace App\Domain\Approval\Handlers;

use App\Domain\Approval\Commands\SubmitApprovalDecision;
use App\Domain\Approval\Events\ApprovalDecisionRecorded;
use App\Domain\Approval\Models\ApprovalTask;
use App\Kernel\Contracts\CommandHandler;
use App\Kernel\Contracts\Command;

class SubmitApprovalDecisionHandler implements CommandHandler
{
    /**
     * @param SubmitApprovalDecision $command
     */
    public function handle(Command $command): ApprovalTask
    {
        $task = ApprovalTask::findOrFail($command->taskId);

        $task->update([
            'status' => $command->decision === 'approved' ? 'approved' : ($command->decision === 'rejected' ? 'rejected' : 'escalated'),
            'decision' => $command->decision,
            'remarks' => $command->remarks,
            'decided_at' => now(),
        ]);

        event(new ApprovalDecisionRecorded($task));

        return $task;
    }
}
