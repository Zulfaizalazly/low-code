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

            // 4. Audit trail
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

            // 4. Audit trail
            AuditLog::record('rolled_back', $targetVersion,
                ['status' => 'published', 'id' => $currentPublished->id],
                ['status' => 'published', 'id' => $targetVersion->id, 'reason' => $reason]
            );
        });
    }
}

