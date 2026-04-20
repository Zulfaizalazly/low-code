<?php

namespace App\Domain\Document\Commands;

use App\Kernel\Contracts\Command;

class GenerateDocument implements Command
{
    public function __construct(
        public string $documentableType,
        public int $documentableId,
        public string $documentType, // contract, receipt, etc.
        public ?int $templateId = null,
        public array $data = []
    ) {}

    public function rules(): array
    {
        return [
            'documentableType' => 'required|string',
            'documentableId' => 'required|integer',
            'documentType' => 'required|in:contract,receipt,letter,report',
            'templateId' => 'nullable|integer',
            'data' => 'nullable|array',
        ];
    }
}
