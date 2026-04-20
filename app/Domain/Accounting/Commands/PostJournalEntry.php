<?php

namespace App\Domain\Accounting\Commands;

use App\Kernel\Contracts\Command;

class PostJournalEntry implements Command
{
    public function __construct(
        public string $description,
        public array $lines, // [['account_code' => '...', 'debit' => 0, 'credit' => 100], ...]
        public ?string $referenceType = null,
        public ?int $referenceId = null
    ) {}

    public function rules(): array
    {
        return [
            'description' => 'required|string',
            'lines' => 'required|array|min:2',
            'lines.*.account_code' => 'required|string',
            'lines.*.account_name' => 'required|string',
            'lines.*.debit' => 'required|numeric|min:0',
            'lines.*.credit' => 'required|numeric|min:0',
            'referenceType' => 'nullable|string',
            'referenceId' => 'nullable|integer',
        ];
    }
}
