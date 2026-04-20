<?php

namespace App\Domain\Approval\Commands;

use App\Kernel\Contracts\Command;

class CreateApprovalTask implements Command
{
    public function __construct(
        public string $approvableType,
        public int $approvableId,
        public string $approvalTier = 'tier_1',
        public ?string $assignedRole = null,
        public ?int $assignedTo = null,
        public ?string $remarks = null
    ) {}

    public function rules(): array
    {
        return [
            'approvableType' => 'required|string',
            'approvableId' => 'required|integer',
            'approvalTier' => 'required|string',
            'assignedRole' => 'nullable|string',
            'assignedTo' => 'nullable|exists:users,id',
            'remarks' => 'nullable|string',
        ];
    }
}
