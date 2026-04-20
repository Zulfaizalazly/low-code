<?php

namespace App\Domain\Valuation\Commands;

use App\Kernel\Contracts\Command;

class RecordValuation implements Command
{
    public function __construct(
        public int $facilityId,
        public ?int $facilityItemId,
        public float $goldPricePerGram,
        public float $weightGrams,
        public float $purityPercentage,
        public float $ltvPercentage,
        public ?int $valuedBy = null
    ) {}

    public function rules(): array
    {
        return [
            'facilityId' => 'required|exists:facilities,id',
            'facilityItemId' => 'nullable|exists:facility_items,id',
            'goldPricePerGram' => 'required|numeric|min:0',
            'weightGrams' => 'required|numeric|min:0.0001',
            'purityPercentage' => 'required|numeric|min:0|max:100',
            'ltvPercentage' => 'required|numeric|min:0|max:100',
            'valuedBy' => 'nullable|exists:users,id',
        ];
    }
}
