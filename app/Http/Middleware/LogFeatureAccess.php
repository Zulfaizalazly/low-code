<?php

namespace App\Http\Middleware;

use App\Kernel\Audit\AuditLog;
use App\Models\Branch\FeatureAccessLog;
use App\Studio\Registry\Feature;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LogFeatureAccess
{
    /**
     * Log every /portal/operations/{featureKey} access for branch dashboard visibility.
     * Captures user, feature, branch, and timestamp for staff activity monitoring.
     * Additionally logs to audit_trails when a manager is operating in Staff View.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only log for authenticated users
        if (!$request->user()) {
            return $response;
        }

        // Extract feature key from route
        $featureKey = $request->route('featureKey');
        if (!$featureKey) {
            return $response;
        }

        // Find the feature
        $feature = Feature::where('key', $featureKey)->first();
        if (!$feature) {
            return $response;
        }

        // Get the published version
        $publishedVersion = $feature->versions()
            ->where('status', 'published')
            ->latest('version_no')
            ->first();

        $user = $request->user();

        try {
            FeatureAccessLog::create([
                'user_id' => $user->id,
                'feature_id' => $feature->id,
                'feature_version_id' => $publishedVersion?->id,
                'branch_id' => $user->branch_id,
                'access_type' => 'view',
                'accessed_at' => now(),
            ]);
        } catch (\Exception $e) {
            // Fail silently — logging should never break the request
            \Log::warning('Failed to log feature access', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'feature_key' => $featureKey,
            ]);
        }

        // Audit Trail: Log when a Branch Manager executes features in Staff View mode
        if (session('branch_view_mode') === 'staff' && $user->hasRole('branch_manager')) {
            try {
                AuditLog::record(
                    action: 'FEATURE_EXECUTION_AS_STAFF',
                    branchId: $user->branch_id,
                    description: "{$user->name} executed feature '{$feature->name}' ({$featureKey}) while in Staff View mode.",
                    payload: [
                        'feature_id'         => $feature->id,
                        'feature_key'        => $featureKey,
                        'feature_name'       => $feature->name,
                        'feature_version_id' => $publishedVersion?->id,
                        'version_no'         => $publishedVersion?->version_no,
                        'page_key'           => $request->route('pageKey'),
                        'session_id'         => session()->getId(),
                        'request_url'        => $request->fullUrl(),
                    ]
                );
            } catch (\Exception $e) {
                \Log::warning('Audit trail failed for feature execution', [
                    'error' => $e->getMessage(),
                    'user_id' => $user->id,
                    'feature_key' => $featureKey,
                ]);
            }
        }

        return $response;
    }
}
