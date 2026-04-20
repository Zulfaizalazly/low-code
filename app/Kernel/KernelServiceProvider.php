<?php

namespace App\Kernel;

use App\Kernel\Events\DomainEvent;
use App\Kernel\Events\EventLog;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class KernelServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(\App\Kernel\Bus\CommandBus::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
