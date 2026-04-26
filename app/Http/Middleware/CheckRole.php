<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        if (!auth()->user()->hasAnyRole($roles)) {
            $roleLabels = collect($roles)->map(fn ($r) => str_replace('_', ' ', ucfirst($r)))->join(', ');

            return redirect('/')
                ->with('access_denied', "You don't have the required role ({$roleLabels}) to access this workspace. Please select a workspace that matches your account.");
        }

        return $next($request);
    }
}
