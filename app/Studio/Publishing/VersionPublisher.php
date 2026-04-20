<?php

namespace App\Studio\Publishing;

use App\Kernel\Audit\AuditLog;
use App\Studio\Registry\FeatureVersion;
use Exception;
use Illuminate\Support\Facades\DB;

class VersionPublisher
{
    /**
     * Publish a draft version of a feature.
     *
     * Runs all 14 publish gate validations first.
     * If any gate fails, throws PublishGateException.
     *
     * @throws PublishGateException If validation fails
     * @throws Exception If version is already published
     */
    public function publish(FeatureVersion $version, int $userId, bool $skipGates = false): PublishGateResult
    {
        if ($version->status === 'published') {
            throw new Exception("Version {$version->version_no} is already published.");
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
                'approved_at' => now(),
                'approved_by' => $userId,
            ]);

            // 3. Update parent feature status
            $version->feature->update(['status' => 'published']);

            // 4. Audit trail
            AuditLog::record('published', $version,
                ['status' => 'draft'],
                ['status' => 'published', 'published_by' => $userId]
            );
        });

        return $result;
    }
}

