<?php

namespace App\Studio\Publishing;

use App\Kernel\Audit\AuditLog;
use App\Studio\Registry\FeatureVersion;
use Exception;
use Illuminate\Support\Facades\DB;

class RollbackService
{
    /**
     * Rollback a published feature version to the previous published version.
     *
     * @param FeatureVersion $currentVersion The currently published version to rollback
     * @param int $userId The user performing the rollback
     * @param string $reason The reason for rollback
     * @return FeatureVersion The version that is now active after rollback
     *
     * @throws Exception If no previous version exists to rollback to
     */
    public function rollback(FeatureVersion $currentVersion, int $userId, string $reason): FeatureVersion
    {
        if ($currentVersion->status !== 'published') {
            throw new Exception("Can only rollback a published version. Current status: {$currentVersion->status}");
        }

        // Find the most recent archived version (was previously published)
        $previousVersion = FeatureVersion::where('feature_id', $currentVersion->feature_id)
            ->where('status', 'archived')
            ->where('id', '<', $currentVersion->id)
            ->orderByDesc('id')
            ->first();

        if (!$previousVersion) {
            throw new Exception("No previous version found to rollback to for feature_id: {$currentVersion->feature_id}");
        }

        return DB::transaction(function () use ($currentVersion, $previousVersion, $userId, $reason) {
            // 1. Mark current version as rolled_back
            $currentVersion->update([
                'status' => 'rolled_back',
            ]);

            // 2. Re-publish the previous version
            $previousVersion->update([
                'status' => 'published',
                'published_at' => now(),
                'published_by' => $userId,
            ]);

            // 3. Log the rollback
            DB::table('rollback_logs')->insert([
                'feature_version_id' => $previousVersion->id,
                'rolled_back_from_version' => $currentVersion->id,
                'reason' => $reason,
                'rolled_back_by' => $userId,
                'rolled_back_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 4. Audit trail
            AuditLog::record('rolled_back', $currentVersion, 
                ['status' => 'published'],
                ['status' => 'rolled_back', 'reason' => $reason]
            );

            AuditLog::record('republished', $previousVersion,
                ['status' => 'archived'],
                ['status' => 'published']
            );

            return $previousVersion;
        });
    }
}
