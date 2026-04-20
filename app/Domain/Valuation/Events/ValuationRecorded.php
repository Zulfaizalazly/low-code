<?php

namespace App\Domain\Valuation\Events;

use App\Domain\Valuation\Models\Valuation;
use App\Kernel\Events\DomainEvent;

class ValuationRecorded extends DomainEvent
{
    public function __construct(public Valuation $valuation)
    {
        parent::__construct();
    }

    public function toPayload(): array
    {
        return [
            'id' => $this->valuation->id,
            'facility_id' => $this->valuation->facility_id,
            'valuation_amount' => $this->valuation->valuation_amount,
            'weight' => $this->valuation->weight_grams,
            'purity' => $this->valuation->purity_percentage,
        ];
    }

    public function getSource(): object
    {
        return $this->valuation;
    }
}
