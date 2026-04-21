<?php

namespace App\Domain\Compliance\Commands;

use App\Kernel\Contracts\Command;

class AmlaCheck implements Command
{
    public function __construct(
        public string $icNumber,
        public string $name,
        public float $amount = 0.0,
        public string $sourceOfFunds = 'salary'
    ) {}

    public function rules(): array
    {
        return [
            'icNumber' => 'required|string|min:6',
            'name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'sourceOfFunds' => 'required|string',
        ];
    }
}
