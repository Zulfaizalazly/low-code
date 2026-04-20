<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'ai.rate' => \App\Http\Middleware\AIRateLimitMiddleware::class,
            'publish.permission' => \App\Http\Middleware\PublishWorkflowPermissions::class,
            'permission' => \App\Http\Middleware\CheckPermission::class,
            'role' => \App\Http\Middleware\CheckRole::class,
        ]);

        // Apply sanitization to all web requests
        $middleware->web(append: [
            \App\Http\Middleware\SanitizeInput::class,
        ]);

        // Ensure CSRF protection is enabled for all web routes
        $middleware->validateCsrfTokens(except: [
            // Add any routes that need to be excluded from CSRF protection
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
