<?php

namespace App\Studio\Publishing;

use App\Kernel\Audit\AuditLog;
use App\Studio\Registry\FeatureVersion;
use Exception;
use Illuminate\Support\Facades\DB;

class VersionPublisher
{
    /**
     * Publish an approved version of a feature.
     *
     * @throws Exception If version is not approved
     */
    public function publish(FeatureVersion $version, int $userId, bool $skipGates = false): PublishGateResult
    {
        if ($version->status === 'published') {
            throw new Exception("Version {$version->version_no} is already published.");
        }

        if ($version->status !== 'approved') {
            throw new Exception("Only approved versions can be published. Current status: {$version->status}");
        }

        // Run publish gate validations
        $validator = new PublishGateValidator();
        $result = $validator->validate($version);

        if (!$skipGates && $result->hasFailures()) {
            throw new PublishGateException($result);
        }

        DB::transaction(function () use ($version, $userId) {
            // 1. Unpublish any previous versions
            FeatureVersion::where('feature_id', $version->feature_id)
                ->where('status', 'published')
                ->update(['status' => 'archived']);

            // 2. Mark this version as published
            $version->update([
                'status' => 'published',
                'published_at' => now(),
                'published_by' => $userId,
            ]);

            // 3. Update parent feature status
            $version->feature->update(['status' => 'published']);

            // 4. Create ChangeDeployment for branch visibility
            \App\Models\Branch\ChangeDeployment::create([
                'feature_id' => $version->feature_id,
                'feature_version_id' => $version->id,
                'deployed_by' => $userId,
                'deployed_at' => now(),
                'change_summary' => $version->change_summary ?? "Published v{$version->version_no}",
                'is_visible_to_branches' => true,
            ]);

            // 5. Audit trail
            AuditLog::record('published', $version,
                ['status' => 'approved'],
                ['status' => 'published', 'published_by' => $userId]
            );
        });

        return $result;
    }

    /**
     * Rollback a feature to a specific version.
     */
    public function rollback(FeatureVersion $targetVersion, int $userId, string $reason): void
    {
        $currentPublished = FeatureVersion::where('feature_id', $targetVersion->feature_id)
            ->where('status', 'published')
            ->first();

        if (!$currentPublished) {
            throw new Exception("No published version found for this feature.");
        }

        if ($currentPublished->id === $targetVersion->id) {
            // Version 1 with no previous version — can't rollback, suggest retire
            $hasPreviousVersion = FeatureVersion::where('feature_id', $targetVersion->feature_id)
                ->where('id', '!=', $currentPublished->id)
                ->whereIn('status', ['archived', 'rolled_back'])
                ->exists();

            if (!$hasPreviousVersion) {
                throw new Exception("Cannot rollback — this is the only version. Use 'Retire Feature' to decommission it.");
            }

            throw new Exception("Feature is already on version {$targetVersion->version_no}.");
        }

        DB::transaction(function () use ($currentPublished, $targetVersion, $userId, $reason) {
            // 1. Mark current as rolled_back
            $currentPublished->update(['status' => 'rolled_back']);

            // 2. Mark target as published
            $targetVersion->update([
                'status' => 'published',
                'published_at' => now(),
                'published_by' => $userId,
                'rollback_from_version_id' => $currentPublished->id,
            ]);

            // 3. Log to rollback_logs
            DB::table('rollback_logs')->insert([
                'feature_version_id' => $targetVersion->id,
                'rolled_back_from_version' => $currentPublished->id,
                'reason' => $reason,
                'rolled_back_by' => $userId,
                'rolled_back_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 4. Create ChangeDeployment for rollback visibility
            \App\Models\Branch\ChangeDeployment::create([
                'feature_id' => $targetVersion->feature_id,
                'feature_version_id' => $targetVersion->id,
                'deployed_by' => $userId,
                'deployed_at' => now(),
                'change_summary' => "Rollback to v{$targetVersion->version_no}: {$reason}",
                'is_visible_to_branches' => true,
            ]);

            // 5. Audit trail
            AuditLog::record('rolled_back', $targetVersion,
                ['status' => 'published', 'id' => $currentPublished->id],
                ['status' => 'published', 'id' => $targetVersion->id, 'reason' => $reason]
            );
        });
    }

    /**
     * Retire a feature — archive all versions and mark feature as archived.
     * Data is preserved for audit/compliance (BNM/SKM), but feature is no longer active.
     */
    public function retire(FeatureVersion $version, int $userId, string $reason): void
    {
        $feature = $version->feature;

        DB::transaction(function () use ($feature, $version, $userId, $reason) {
            // 1. Archive all versions of this feature
            FeatureVersion::where('feature_id', $feature->id)
                ->whereNotIn('status', ['archived'])
                ->update(['status' => 'archived']);

            // 2. Mark feature as archived
            $feature->update(['status' => 'archived']);

            // 3. Log retirement
            DB::table('rollback_logs')->insert([
                'feature_version_id' => $version->id,
                'rolled_back_from_version' => $version->id,
                'reason' => "[RETIRED] {$reason}",
                'rolled_back_by' => $userId,
                'rolled_back_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 4. Notify branches
            \App\Models\Branch\ChangeDeployment::create([
                'feature_id' => $feature->id,
                'feature_version_id' => $version->id,
                'deployed_by' => $userId,
                'deployed_at' => now(),
                'change_summary' => "Feature retired: {$reason}",
                'is_visible_to_branches' => true,
            ]);

            // 5. Audit trail
            AuditLog::record('retired', $version,
                ['status' => 'published', 'feature_status' => 'published'],
                ['status' => 'archived', 'feature_status' => 'archived', 'reason' => $reason]
            );
        });
    }
}

