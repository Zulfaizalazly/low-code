<?php

namespace App\Http\Controllers\Api\Studio;

use App\Http\Controllers\Controller;
use App\Studio\Registry\ScopeOverride;
use App\Studio\Scoping\ScopeOverrideManager;
use App\Studio\Scoping\ScopeResolver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ScopeOverrideController extends Controller
{
    protected ScopeOverrideManager $manager;
    protected ScopeResolver $resolver;

    public function __construct(ScopeOverrideManager $manager, ScopeResolver $resolver)
    {
        $this->manager = $manager;
        $this->resolver = $resolver;
    }

    /**
     * Get all overrides for a feature version.
     */
    public function index(Request $request, int $featureVersionId)
    {
        $query = ScopeOverride::where('feature_version_id', $featureVersionId);

        // Filter by scope type
        if ($request->has('scope_type')) {
            $query->where('scope_type', $request->scope_type);
        }

        // Filter by scope ID
        if ($request->has('scope_id')) {
            $query->where('scope_id', $request->scope_id);
        }

        // Filter by target
        if ($request->has('target_table')) {
            $query->where('target_table', $request->target_table);
        }

        // Filter by active status
        if ($request->boolean('active_only', true)) {
            $query->active();
        }

        $overrides = $query->orderBy('created_at', 'desc')->paginate(20);

        return response()->json($overrides);
    }

    /**
     * Get a specific override.
     */
    public function show(int $id)
    {
        $override = ScopeOverride::findOrFail($id);

        return response()->json([
            'override' => $override,
            'is_active' => $override->isActive(),
        ]);
    }

    /**
     * Create a new override.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'feature_version_id' => 'required|exists:feature_versions,id',
            'scope_type' => 'required|string|in:user,branch,region,product,entity,global',
            'scope_id' => 'required|string',
            'target_table' => 'required|string',
            'target_key' => 'required|string',
            'override_value' => 'required',
            'effective_from' => 'nullable|date',
            'effective_to' => 'nullable|date|after:effective_from',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        // Check for conflicts
        $conflicts = $this->manager->checkConflicts($request->all());

        if (!empty($conflicts) && !$request->boolean('force')) {
            return response()->json([
                'error' => 'Conflicting overrides found',
                'conflicts' => $conflicts,
                'message' => 'Set force=true to create anyway'
            ], 409);
        }

        try {
            $override = $this->manager->create($request->all());

            return response()->json([
                'message' => 'Override created successfully',
                'override' => $override
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to create override',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update an existing override.
     */
    public function update(Request $request, int $id)
    {
        $override = ScopeOverride::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'override_value' => 'sometimes|required',
            'effective_from' => 'sometimes|date',
            'effective_to' => 'sometimes|nullable|date|after:effective_from',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $updated = $this->manager->update($override, $request->all());

            return response()->json([
                'message' => 'Override updated successfully',
                'override' => $updated
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to update override',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete an override.
     */
    public function destroy(int $id)
    {
        $override = ScopeOverride::findOrFail($id);

        try {
            $this->manager->delete($override);

            return response()->json([
                'message' => 'Override deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to delete override',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Expire an override.
     */
    public function expire(int $id)
    {
        $override = ScopeOverride::findOrFail($id);

        try {
            $expired = $this->manager->expire($override);

            return response()->json([
                'message' => 'Override expired successfully',
                'override' => $expired
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to expire override',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk create overrides.
     */
    public function bulkStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'overrides' => 'required|array',
            'overrides.*.feature_version_id' => 'required|exists:feature_versions,id',
            'overrides.*.scope_type' => 'required|string|in:user,branch,region,product,entity,global',
            'overrides.*.scope_id' => 'required|string',
            'overrides.*.target_table' => 'required|string',
            'overrides.*.target_key' => 'required|string',
            'overrides.*.override_value' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $created = $this->manager->bulkCreate($request->overrides);

            return response()->json([
                'message' => 'Overrides created successfully',
                'count' => count($created),
                'overrides' => $created
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to create overrides',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get override history for audit.
     */
    public function history(int $featureVersionId)
    {
        $history = $this->manager->getHistory($featureVersionId);

        return response()->json([
            'history' => $history
        ]);
    }

    /**
     * Test override resolution.
     */
    public function testResolve(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'feature_version_id' => 'required|exists:feature_versions,id',
            'target_table' => 'required|string',
            'target_key' => 'required|string',
            'scope_context' => 'required|array',
            'default_value' => 'nullable',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        $resolved = $this->resolver->resolve(
            $request->feature_version_id,
            $request->target_table,
            $request->target_key,
            $request->scope_context,
            $request->default_value
        );

        return response()->json([
            'resolved_value' => $resolved,
            'scope_context' => $request->scope_context,
        ]);
    }

    /**
     * Clear cache for feature version.
     */
    public function clearCache(int $featureVersionId)
    {
        $this->resolver->clearFeatureCache($featureVersionId);

        return response()->json([
            'message' => 'Cache cleared successfully'
        ]);
    }
}
