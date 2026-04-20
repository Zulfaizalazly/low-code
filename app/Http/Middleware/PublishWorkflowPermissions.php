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

        // Get user role (assuming User model has a 'role' attribute or relationship)
        $userRole = $user->role ?? 'designer';

        $allowed = match ($action) {
            'submit' => in_array($userRole, ['designer', 'reviewer', 'admin']),
            'review' => in_array($userRole, ['reviewer', 'admin']),
            'publish' => in_array($userRole, ['admin']),
            'rollback' => in_array($userRole, ['admin']),
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
            'submit' => ['designer', 'reviewer', 'admin'],
            'review' => ['reviewer', 'admin'],
            'publish' => ['admin'],
            'rollback' => ['admin'],
            default => [],
        };
    }
}
