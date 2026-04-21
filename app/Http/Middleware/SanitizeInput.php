<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SanitizeInput
{
    /**
     * Routes whose payloads must NOT be mutated.
     * Livewire sends cryptographically-signed JSON snapshots.
     * Running htmlspecialchars() on them corrupts the signature.
     */
    protected array $skipPaths = [
        'livewire/*',
        'api/*',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        // Skip sanitization for Livewire and API payloads
        foreach ($this->skipPaths as $pattern) {
            if ($request->is($pattern)) {
                return $next($request);
            }
        }

        // Also skip if the request expects JSON (XHR/AJAX)
        if ($request->expectsJson() || $request->isJson()) {
            return $next($request);
        }

        // Only sanitize plain web form submissions (not JSON payloads)
        $input = $request->all();

        array_walk_recursive($input, function (&$value) {
            if (is_string($value)) {
                $value = strip_tags($value);
                $value = trim($value);
                // Do NOT run htmlspecialchars here — Blade already escapes output with {{ }}
            }
        });

        $request->merge($input);

        return $next($request);
    }
}
