<?php

namespace App\Domain\Notification\Commands;

use App\Kernel\Contracts\Command;

class SendNotification implements Command
{
    public function __construct(
        public string $notifiableType,
        public int $notifiableId,
        public string $channel, // sms, email, push
        public string $recipient,
        public ?string $subject = null,
        public ?string $body = null
    ) {}

    public function rules(): array
    {
        return [
            'notifiableType' => 'required|string',
            'notifiableId' => 'required|integer',
            'channel' => 'required|in:sms,email,push,whatsapp',
            'recipient' => 'required|string',
            'subject' => 'nullable|string',
            'body' => 'nullable|string',
        ];
    }
}
