<?php

namespace App\Domain\Notification\Handlers;

use App\Domain\Notification\Commands\SendNotification;
use App\Domain\Notification\Events\NotificationSent;
use App\Domain\Notification\Models\NotificationLog;
use App\Kernel\Contracts\CommandHandler;
use App\Kernel\Contracts\Command;

class SendNotificationHandler implements CommandHandler
{
    /**
     * @param SendNotification $command
     */
    public function handle(Command $command): NotificationLog
    {
        // Simulation of notification dispatch
        $log = NotificationLog::create([
            'notifiable_type' => $command->notifiableType,
            'notifiable_id' => $command->notifiableId,
            'channel' => $command->channel,
            'recipient' => $command->recipient,
            'subject' => $command->subject,
            'body' => $command->body,
            'status' => 'sent',
            'sent_at' => now(),
        ]);

        event(new NotificationSent($log));

        return $log;
    }
}
