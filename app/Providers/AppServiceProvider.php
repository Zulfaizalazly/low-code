<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

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
        // Automatically log all DomainEvents to event_logs table
        \Illuminate\Support\Facades\Event::listen(\App\Kernel\Events\DomainEvent::class, function (\App\Kernel\Events\DomainEvent $event) {
            \App\Kernel\Events\EventLog::create([
                'event_type' => get_class($event),
                'event_payload' => $event->toPayload(),
                'source_type' => $event->getSource() ? get_class($event->getSource()) : null,
                'source_id' => $event->getSource() ? $event->getSource()->getKey() : null,
                'emitted_at' => \Illuminate\Support\Carbon::now(),
            ]);
        });
    }
}
