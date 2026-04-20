<?php

namespace App\Providers;

use App\Kernel\Events\DomainEvent;
use App\Kernel\Events\EventLog;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register domain event logging for all concrete DomainEvent subclasses
        $domainEvents = [
            \App\Domain\Customer\Events\CustomerCreated::class,
            \App\Domain\Facility\Events\FacilityCreated::class,
            \App\Domain\Valuation\Events\ValuationRecorded::class,
            \App\Domain\Approval\Events\ApprovalTaskCreated::class,
            \App\Domain\Approval\Events\ApprovalDecisionRecorded::class,
            \App\Domain\Payment\Events\PaymentRecorded::class,
            \App\Domain\Accounting\Events\JournalEntryPosted::class,
            \App\Domain\Document\Events\DocumentGenerated::class,
            \App\Domain\Notification\Events\NotificationSent::class,
        ];

        foreach ($domainEvents as $eventClass) {
            Event::listen($eventClass, function (DomainEvent $event) {
                $source = $event->getSource();

                EventLog::create([
                    'event_type' => get_class($event),
                    'event_payload' => $event->toPayload(),
                    'source_type' => $source ? get_class($source) : null,
                    'source_id' => $source?->getKey(),
                    'emitted_at' => now(),
                ]);
            });
        }
    }
}

