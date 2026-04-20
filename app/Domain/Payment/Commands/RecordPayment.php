<?php

namespace App\Domain\Payment\Commands;

use App\Kernel\Contracts\Command;

class RecordPayment implements Command
{
    public function __construct(
        public int $facilityId,
        public string $paymentType, // disbursement, repayment, profit, etc.
        public float $amount,
        public ?string $paymentMethod = 'cash',
        public ?string $referenceNumber = null,
        public ?int $branchId = null
    ) {}

    public function rules(): array
    {
        return [
            'facilityId' => 'required|exists:facilities,id',
            'paymentType' => 'required|in:disbursement,repayment,profit,penalty,refund',
            'amount' => 'required|numeric|min:0.01',
            'paymentMethod' => 'nullable|string',
            'referenceNumber' => 'nullable|string',
            'branchId' => 'required|integer',
        ];
    }
}
