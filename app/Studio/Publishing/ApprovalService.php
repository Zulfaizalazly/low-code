<?php

namespace App\Studio\Publishing;

use App\Models\User;
use App\Studio\Registry\FeatureVersion;
use App\Studio\Publishing\Models\ApprovalWorkflow;
use App\Domain\Notification\Commands\SendNotification;
use App\Kernel\Bus\CommandBus;
use App\Kernel\Audit\AuditLog;
use Exception;
use Illuminate\Support\Facades\DB;

class ApprovalService
{
    public function __construct(private CommandBus $bus) {}

    /**
     * Submit a feature version for review.
     */
    public function submit(FeatureVersion $version, User $user): ApprovalWorkflow
    {
        if ($version->status !== 'draft') {
            throw new Exception("Only draft versions can be submitted for review.");
        }

        return DB::transaction(function () use ($version, $user) {
            $version->update(['status' => 'in_review']);

            $workflow = ApprovalWorkflow::create([
                'feature_version_id' => $version->id,
                'submitted_by' => $user->id,
                'submitted_at' => now(),
            ]);

            // Audit log
            AuditLog::record(
                action: 'submit_for_review',
                subject: $version,
                old: ['status' => 'draft'],
                new: ['status' => 'in_review', 'submitted_by' => $user->id]
            );

            // Notify reviewers (In a real system, we would fetch users with 'reviewer' role)
            $this->notifyReviewers($version, $user);

            return $workflow;
        });
    }

    /**
     * Approve a feature version.
     */
    public function approve(FeatureVersion $version, User $user, string $comments = ''): ApprovalWorkflow
    {
        if ($version->status !== 'in_review') {
            throw new Exception("Only versions in review can be approved.");
        }

        return DB::transaction(function () use ($version, $user, $comments) {
            $version->update([
                'status' => 'approved',
                'approved_at' => now(),
                'approved_by' => $user->id,
            ]);

            $workflow = ApprovalWorkflow::where('feature_version_id', $version->id)
                ->whereNull('decision')
                ->first();

            if ($workflow) {
                $workflow->update([
                    'reviewed_by' => $user->id,
                    'reviewed_at' => now(),
                    'decision' => 'approved',
                    'comments' => $comments,
                ]);
            }

            // Audit log
            AuditLog::record(
                action: 'approve_version',
                subject: $version,
                old: ['status' => 'in_review'],
                new: ['status' => 'approved', 'approved_by' => $user->id, 'comments' => $comments]
            );

            $this->notifySubmitter($version, 'approved', $comments);

            return $workflow;
        });
    }

    /**
     * Reject a feature version.
     */
    public function reject(FeatureVersion $version, User $user, string $comments): ApprovalWorkflow
    {
        if ($version->status !== 'in_review') {
            throw new Exception("Only versions in review can be rejected.");
        }

        return DB::transaction(function () use ($version, $user, $comments) {
            $version->update(['status' => 'draft']);

            $workflow = ApprovalWorkflow::where('feature_version_id', $version->id)
                ->whereNull('decision')
                ->first();

            if ($workflow) {
                $workflow->update([
                    'reviewed_by' => $user->id,
                    'reviewed_at' => now(),
                    'decision' => 'rejected',
                    'comments' => $comments,
                ]);
            }

            // Audit log
            AuditLog::record(
                action: 'reject_version',
                subject: $version,
                old: ['status' => 'in_review'],
                new: ['status' => 'draft', 'rejected_by' => $user->id, 'reason' => $comments]
            );

            $this->notifySubmitter($version, 'rejected', $comments);

            return $workflow;
        });
    }

    private function notifyReviewers(FeatureVersion $version, User $submitter): void
    {
        // Get all users with reviewer/admin roles via Spatie
        $reviewers = User::role(['reviewer', 'super-admin', 'system-admin'])->get();

        foreach ($reviewers as $reviewer) {
            $this->bus->dispatch(new SendNotification(
                notifiableType: 'App\Models\User',
                notifiableId: $reviewer->id,
                channel: 'email',
                recipient: $reviewer->email,
                subject: "Feature Review Required: {$version->feature->name} (v{$version->version_no})",
                body: "Designer {$submitter->name} has submitted a new feature version for review. Please review it in the Release Center."
            ));
        }
    }

    private function notifySubmitter(FeatureVersion $version, string $decision, string $comments): void
    {
        // Resolve submitter from ApprovalWorkflow (not published_by which is null at this stage)
        $workflow = ApprovalWorkflow::where('feature_version_id', $version->id)
            ->latest()
            ->first();

        $submitter = $workflow ? User::find($workflow->submitted_by) : null;
        
        if (!$submitter) {
            return; // No submitter to notify
        }
        
        $this->bus->dispatch(new SendNotification(
            notifiableType: 'App\Models\User',
            notifiableId: $submitter->id,
            channel: 'email',
            recipient: $submitter->email,
            subject: "Feature Version {$decision}: {$version->feature->name} (v{$version->version_no})",
            body: "Your feature submission has been {$decision}. Comments: {$comments}"
        ));
    }
}
