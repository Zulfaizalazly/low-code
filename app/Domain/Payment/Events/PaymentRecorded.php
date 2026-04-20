<?php

namespace App\Domain\Payment\Events;

use App\Domain\Payment\Models\PaymentTransaction;
use App\Kernel\Events\DomainEvent;

class PaymentRecorded extends DomainEvent
{
    public function __construct(public PaymentTransaction $transaction)
    {
        parent::__construct();
    }

    public function toPayload(): array
    {
        return [
            'id' => $this->transaction->id,
            'facility_id' => $this->transaction->facility_id,
            'type' => $this->transaction->payment_type,
            'amount' => $this->transaction->amount,
            'method' => $this->transaction->payment_method,
        ];
    }

    public function getSource(): object
    {
        return $this->transaction;
    }
}
