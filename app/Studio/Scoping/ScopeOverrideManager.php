<?php

namespace App\Studio\Scoping;

use App\Studio\Registry\ScopeOverride;
use App\Kernel\Audit\AuditLog;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ScopeOverrideManager
{
    protected ScopeResolver $resolver;

    public function __construct(ScopeResolver $resolver)
    {
        $this->resolver = $resolver;
    }

    /**
     * Create a new scope override.
     */
    public function create(array $data): ScopeOverride
    {
        DB::beginTransaction();

        try {
            $override = ScopeOverride::create([
                'feature_version_id' => $data['feature_version_id'],
                'scope_type' => $data['scope_type'],
                'scope_id' => $data['scope_id'],
                'target_table' => $data['target_table'],
                'target_key' => $data['target_key'],
                'override_value' => $data['override_value'],
                'effective_from' => $data['effective_from'] ?? now(),
                'effective_to' => $data['effective_to'] ?? null,
            ]);

            // Log the creation
            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'scope_override_created',
                'auditable_type' => ScopeOverride::class,
                'auditable_id' => $override->id,
                'old_values' => null,
                'new_values' => $override->toArray(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'performed_at' => now(),
            ]);

            // Clear cache
            $this->resolver->clearFeatureCache($override->feature_version_id);

            DB::commit();

            return $override;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Update an existing scope override.
     */
    public function update(ScopeOverride $override, array $data): ScopeOverride
    {
        DB::beginTransaction();

        try {
            $oldValues = $override->toArray();

            $override->update([
                'override_value' => $data['override_value'] ?? $override->override_value,
                'effective_from' => $data['effective_from'] ?? $override->effective_from,
                'effective_to' => $data['effective_to'] ?? $override->effective_to,
            ]);

            // Log the update
            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'scope_override_updated',
                'auditable_type' => ScopeOverride::class,
                'auditable_id' => $override->id,
                'old_values' => $oldValues,
                'new_values' => $override->toArray(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'performed_at' => now(),
            ]);

            // Clear cache
            $this->resolver->clearFeatureCache($override->feature_version_id);

            DB::commit();

            return $override->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Delete a scope override.
     */
    public function delete(ScopeOverride $override): bool
    {
        DB::beginTransaction();

        try {
            $featureVersionId = $override->feature_version_id;
            $overrideData = $override->toArray();

            $override->delete();

            // Log the deletion
            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'scope_override_deleted',
                'auditable_type' => ScopeOverride::class,
                'auditable_id' => $override->id,
                'old_values' => $overrideData,
                'new_values' => null,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'performed_at' => now(),
            ]);

            // Clear cache
            $this->resolver->clearFeatureCache($featureVersionId);

            DB::commit();

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Expire an override (set effective_to to now).
     */
    public function expire(ScopeOverride $override): ScopeOverride
    {
        return $this->update($override, [
            'effective_to' => now()->toDateString(),
        ]);
    }

    /**
     * Bulk create overrides.
     */
    public function bulkCreate(array $overrides): array
    {
        $created = [];

        DB::beginTransaction();

        try {
            foreach ($overrides as $data) {
                $created[] = $this->create($data);
            }

            DB::commit();

            return $created;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Get overrides for a specific scope.
     */
    public function getForScope(
        int $featureVersionId,
        string $scopeType,
        string $scopeId,
        bool $activeOnly = true
    ) {
        $query = ScopeOverride::where('feature_version_id', $featureVersionId)
            ->forScope($scopeType, $scopeId);

        if ($activeOnly) {
            $query->active();
        }

        return $query->get();
    }

    /**
     * Get overrides for a specific target.
     */
    public function getForTarget(
        int $featureVersionId,
        string $targetTable,
        string $targetKey,
        bool $activeOnly = true
    ) {
        $query = ScopeOverride::where('feature_version_id', $featureVersionId)
            ->forTarget($targetTable, $targetKey);

        if ($activeOnly) {
            $query->active();
        }

        return $query->get();
    }

    /**
     * Check for conflicts (overlapping overrides for same scope).
     */
    public function checkConflicts(array $data): array
    {
        $conflicts = [];

        $existingOverrides = ScopeOverride::where('feature_version_id', $data['feature_version_id'])
            ->forTarget($data['target_table'], $data['target_key'])
            ->forScope($data['scope_type'], $data['scope_id'])
            ->active()
            ->get();

        foreach ($existingOverrides as $existing) {
            // Check date overlap
            $newFrom = Carbon::parse($data['effective_from'] ?? now());
            $newTo = isset($data['effective_to']) ? Carbon::parse($data['effective_to']) : null;

            $existingFrom = $existing->effective_from;
            $existingTo = $existing->effective_to;

            $hasOverlap = $this->datesOverlap(
                $newFrom,
                $newTo,
                $existingFrom,
                $existingTo
            );

            if ($hasOverlap) {
                $conflicts[] = $existing;
            }
        }

        return $conflicts;
    }

    /**
     * Check if two date ranges overlap.
     */
    protected function datesOverlap(
        Carbon $start1,
        ?Carbon $end1,
        Carbon $start2,
        ?Carbon $end2
    ): bool {
        // If either range has no end date, check if starts overlap
        if (!$end1 || !$end2) {
            return true; // Assume overlap for open-ended ranges
        }

        // Check for overlap
        return $start1->lte($end2) && $end1->gte($start2);
    }

    /**
     * Get override history for audit trail.
     */
    public function getHistory(int $featureVersionId): array
    {
        return AuditLog::where('auditable_type', ScopeOverride::class)
            ->whereIn('action', [
                'scope_override_created',
                'scope_override_updated',
                'scope_override_deleted'
            ])
            ->whereHas('auditable', function ($query) use ($featureVersionId) {
                $query->where('feature_version_id', $featureVersionId);
            })
            ->orderBy('created_at', 'desc')
            ->get()
            ->toArray();
    }
}
