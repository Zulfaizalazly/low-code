<?php

namespace App\Domain\Facility\Events;

use App\Domain\Facility\Models\Facility;
use App\Kernel\Events\DomainEvent;

class FacilityCreated extends DomainEvent
{
    public function __construct(public Facility $facility)
    {
        parent::__construct();
    }

    public function toPayload(): array
    {
        return [
            'id' => $this->facility->id,
            'facility_number' => $this->facility->facility_number,
            'customer_id' => $this->facility->customer_id,
            'amount' => $this->facility->principal_amount,
            'items_count' => $this->facility->items()->count(),
        ];
    }

    public function getSource(): object
    {
        return $this->facility;
    }
}
