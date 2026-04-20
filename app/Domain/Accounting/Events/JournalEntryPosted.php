<?php

namespace App\Domain\Accounting\Events;

use App\Domain\Accounting\Models\JournalEntry;
use App\Kernel\Events\DomainEvent;

class JournalEntryPosted extends DomainEvent
{
    public function __construct(public JournalEntry $entry)
    {
        parent::__construct();
    }

    public function toPayload(): array
    {
        return [
            'id' => $this->entry->id,
            'entry_number' => $this->entry->entry_number,
            'amount' => $this->entry->lines()->sum('debit_amount'),
            'description' => $this->entry->description,
        ];
    }

    public function getSource(): object
    {
        return $this->entry;
    }
}
