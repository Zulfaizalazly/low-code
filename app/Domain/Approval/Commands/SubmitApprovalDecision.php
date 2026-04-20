<?php

namespace App\Domain\Approval\Commands;

use App\Kernel\Contracts\Command;

class SubmitApprovalDecision implements Command
{
    public function __construct(
        public int $taskId,
        public string $decision, // approved, rejected, escalated
        public ?string $remarks = null
    ) {}

    public function rules(): array
    {
        return [
            'taskId' => 'required|exists:approval_tasks,id',
            'decision' => 'required|in:approved,rejected,escalated',
            'remarks' => 'nullable|string',
        ];
    }
}
