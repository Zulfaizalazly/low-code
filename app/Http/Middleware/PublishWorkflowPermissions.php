<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PublishWorkflowPermissions
{
    /**
     * Handle an incoming request.
     *
     * Enforce role-based permissions for publish workflow actions:
     * - Designers: can submit for review
     * - Reviewers: can approve/reject
     * - Admins: can publish/rollback
     */
    public function handle(Request $request, Closure $next, string $action): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required.'
            ], 401);
        }

        $userRole = $user->getRoleNames()->first() ?? ($user->role ?? 'unknown');

        $allowed = match ($action) {
            'submit' => $user->hasAnyRole(['super-admin', 'system-admin', 'feature-developer', 'reviewer'])
                || $user->can('versions.submit'),
            'review' => $user->hasAnyRole(['super-admin', 'system-admin', 'reviewer'])
                || $user->can('versions.review')
                || $user->can('versions.approve')
                || $user->can('versions.reject'),
            'publish' => $user->hasAnyRole(['super-admin', 'system-admin', 'publisher'])
                || $user->can('versions.publish'),
            'rollback' => $user->hasAnyRole(['super-admin', 'system-admin', 'publisher'])
                || $user->can('versions.rollback'),
            default => false,
        };

        if (!$allowed) {
            return response()->json([
                'success' => false,
                'message' => "Insufficient permissions. Required role for '{$action}' action.",
                'user_role' => $userRole,
                'required_roles' => $this->getRequiredRoles($action)
            ], 403);
        }

        return $next($request);
    }

    private function getRequiredRoles(string $action): array
    {
        return match ($action) {
            'submit' => ['super-admin', 'system-admin', 'feature-developer'],
            'review' => ['super-admin', 'system-admin', 'reviewer'],
            'publish' => ['super-admin', 'system-admin', 'publisher'],
            'rollback' => ['super-admin', 'system-admin', 'publisher'],
            default => [],
        };
    }
}
