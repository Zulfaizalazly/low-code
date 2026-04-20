<?php

namespace App\Http\Controllers\Api\Studio;

use App\Http\Controllers\Controller;
use App\Studio\Registry\FeatureVersion;
use App\Studio\Publishing\ApprovalService;
use App\Studio\Publishing\VersionPublisher;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class ApprovalController extends Controller
{
    public function __construct(
        private ApprovalService $approvalService,
        private VersionPublisher $publisher
    ) {}

    /**
     * List all feature versions categorized by status.
     */
    public function index(): JsonResponse
    {
        $versions = FeatureVersion::with('feature')
            ->orderBy('updated_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'drafts' => $versions->where('status', 'draft')->values(),
                'in_review' => $versions->where('status', 'in_review')->values(),
                'approved' => $versions->where('status', 'approved')->values(),
                'published' => $versions->where('status', 'published')->values(),
                'archived' => $versions->where('status', 'archived')->values(),
            ]
        ]);
    }

    /**
     * Get validation results for a feature version.
     */
    public function validations(int $id): JsonResponse
    {
        $version = FeatureVersion::findOrFail($id);
        
        // Get latest validation results from database
        $validations = \DB::table('publish_validations')
            ->where('feature_version_id', $version->id)
            ->where('check_type', 'publish_gate')
            ->orderBy('validated_at', 'desc')
            ->get()
            ->groupBy('check_key')
            ->map(fn($group) => $group->first());

        // If no validations exist, run them now
        if ($validations->isEmpty()) {
            $validator = app(\App\Studio\Publishing\PublishGateValidator::class);
            $result = $validator->validate($version);
            
            $validations = \DB::table('publish_validations')
                ->where('feature_version_id', $version->id)
                ->where('check_type', 'publish_gate')
                ->get();
        }

        $summary = [
            'total' => $validations->count(),
            'passed' => $validations->where('status', 'passed')->count(),
            'failed' => $validations->where('status', 'failed')->count(),
            'warning' => $validations->where('status', 'warning')->count(),
            'skipped' => $validations->where('status', 'skipped')->count(),
        ];

        return response()->json([
            'success' => true,
            'validations' => $validations->values(),
            'summary' => $summary,
            'can_publish' => $summary['failed'] === 0
        ]);
    }

    /**
     * Get a single feature version with all relations.
     */
    public function show(int $id): JsonResponse
    {
        $version = FeatureVersion::with([
            'feature',
            'flows.nodes',
            'pages.steps.fields',
            'menuItems'
        ])->findOrFail($id);

        return response()->json([
            'success' => true,
            'version' => $version
        ]);
    }

    /**
     * Get rollback logs for the history view.
     */
    public function rollbackHistory(): JsonResponse
    {
        $logs = DB::table('rollback_logs')
            ->join('feature_versions', 'rollback_logs.feature_version_id', '=', 'feature_versions.id')
            ->join('features', 'feature_versions.feature_id', '=', 'features.id')
            ->join('users', 'rollback_logs.rolled_back_by', '=', 'users.id')
            ->select(
                'rollback_logs.*',
                'features.name as feature_name',
                'feature_versions.version_no',
                'users.name as user_name'
            )
            ->orderBy('rolled_back_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'logs' => $logs
        ]);
    }

    public function submit(Request $request, int $id): JsonResponse
    {
        $version = FeatureVersion::findOrFail($id);
        
        try {
            $workflow = $this->approvalService->submit($version, $request->user());
            return response()->json([
                'success' => true,
                'message' => 'Feature version submitted for review.',
                'workflow' => $workflow
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function approve(Request $request, int $id): JsonResponse
    {
        $version = FeatureVersion::findOrFail($id);
        $comments = $request->input('comments', '');

        try {
            $workflow = $this->approvalService->approve($version, $request->user(), $comments);
            return response()->json([
                'success' => true,
                'message' => 'Feature version approved.',
                'workflow' => $workflow
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function reject(Request $request, int $id): JsonResponse
    {
        $version = FeatureVersion::findOrFail($id);
        $comments = $request->input('comments', '');

        if (empty($comments)) {
            return response()->json(['success' => false, 'message' => 'Comments are required for rejection.'], 422);
        }

        try {
            $workflow = $this->approvalService->reject($version, $request->user(), $comments);
            return response()->json([
                'success' => true,
                'message' => 'Feature version rejected and moved back to draft.',
                'workflow' => $workflow
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function publish(Request $request, int $id): JsonResponse
    {
        $version = FeatureVersion::findOrFail($id);

        try {
            $result = $this->publisher->publish($version, $request->user()->id);
            return response()->json([
                'success' => true,
                'message' => 'Feature version published successfully.',
                'validations' => $result->getEntries()
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function rollback(Request $request, int $id): JsonResponse
    {
        $targetVersion = FeatureVersion::findOrFail($id);
        $reason = $request->input('reason', '');

        if (empty($reason)) {
            return response()->json(['success' => false, 'message' => 'Reason is required for rollback.'], 422);
        }

        try {
            $this->publisher->rollback($targetVersion, $request->user()->id, $reason);
            return response()->json([
                'success' => true,
                'message' => "Successfully rolled back to version {$targetVersion->version_no}."
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }
}
