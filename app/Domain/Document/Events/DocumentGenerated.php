<?php

namespace App\Domain\Document\Events;

use App\Domain\Document\Models\Document;
use App\Kernel\Events\DomainEvent;

class DocumentGenerated extends DomainEvent
{
    public function __construct(public Document $document)
    {
        parent::__construct();
    }

    public function toPayload(): array
    {
        return [
            'id' => $this->document->id,
            'file_name' => $this->document->file_name,
            'type' => $this->document->document_type,
            'target_type' => $this->document->documentable_type,
            'target_id' => $this->document->documentable_id,
        ];
    }

    public function getSource(): object
    {
        return $this->document;
    }
}
