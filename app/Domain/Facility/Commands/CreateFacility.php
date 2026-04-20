<?php

namespace App\Domain\Facility\Commands;

use App\Kernel\Contracts\Command;

class CreateFacility implements Command
{
    public function __construct(
        public int $customerId,
        public string $productCode,
        public int $branchId,
        public int $entityId,
        public float $principalAmount,
        public int $tenureMonths = 6,
        public float $profitRate = 0,
        public array $items = [],
        public array $nominees = []
    ) {}

    public function rules(): array
    {
        return [
            'customerId' => 'required|exists:customers,id',
            'productCode' => 'required|string',
            'branchId' => 'required|integer',
            'entityId' => 'required|integer',
            'principalAmount' => 'required|numeric|min:0',
            'tenureMonths' => 'required|integer|min:1',
            'profitRate' => 'required|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.item_type' => 'required|string',
            'items.*.weight_grams' => 'required|numeric|min:0.0001',
            'items.*.purity' => 'required|numeric|min:0',
            'nominees' => 'nullable|array',
        ];
    }
}
