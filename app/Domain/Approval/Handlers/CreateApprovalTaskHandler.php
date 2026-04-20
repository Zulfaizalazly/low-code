<?php

namespace App\Domain\Approval\Handlers;

use App\Domain\Approval\Commands\CreateApprovalTask;
use App\Domain\Approval\Events\ApprovalTaskCreated;
use App\Domain\Approval\Models\ApprovalTask;
use App\Kernel\Contracts\CommandHandler;
use App\Kernel\Contracts\Command;

class CreateApprovalTaskHandler implements CommandHandler
{
    /**
     * @param CreateApprovalTask $command
     */
    public function handle(Command $command): ApprovalTask
    {
        $task = ApprovalTask::create([
            'approvable_type' => $command->approvableType,
            'approvable_id' => $command->approvableId,
            'approval_tier' => $command->approvalTier,
            'assigned_to' => $command->assignedTo,
            'assigned_role' => $command->assignedRole,
            'status' => 'pending',
            'remarks' => $command->remarks,
        ]);

        event(new ApprovalTaskCreated($task));

        return $task;
    }
}
