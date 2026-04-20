<?php

namespace App\Domain\Customer\Events;

use App\Domain\Customer\Models\Customer;
use App\Kernel\Events\DomainEvent;

class CustomerCreated extends DomainEvent
{
    public function __construct(public Customer $customer)
    {
        parent::__construct();
    }

    public function toPayload(): array
    {
        return [
            'id' => $this->customer->id,
            'name' => $this->customer->name,
            'ic_number' => $this->customer->ic_number,
            'type' => $this->customer->customer_type,
        ];
    }

    public function getSource(): object
    {
        return $this->customer;
    }
}
