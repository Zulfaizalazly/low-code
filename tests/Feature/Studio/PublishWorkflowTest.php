<?php

namespace Tests\Feature\Studio;

use App\Models\User;
use App\Studio\Registry\Feature;
use App\Studio\Registry\FeatureVersion;
use App\Studio\Publishing\ApprovalService;
use App\Studio\Publishing\VersionPublisher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublishWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private FeatureVersion $version;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        
        $feature = Feature::create([
            'key' => 'test-feature',
            'name' => 'Test Feature',
            'domain' => 'facility'
        ]);

        $this->version = FeatureVersion::create([
            'feature_id' => $feature->id,
            'version_no' => 1,
            'status' => 'draft'
        ]);
    }

    public function test_can_submit_for_review()
    {
        $service = app(ApprovalService::class);
        $service->submit($this->version, $this->user);

        $this->assertEquals('in_review', $this->version->fresh()->status);
        $this->assertDatabaseHas('approval_workflows', [
            'feature_version_id' => $this->version->id,
            'submitted_by' => $this->user->id
        ]);
    }

    public function test_can_approve_version()
    {
        $service = app(ApprovalService::class);
        $service->submit($this->version, $this->user);
        $service->approve($this->version, $this->user, 'Looks good');

        $this->assertEquals('approved', $this->version->fresh()->status);
        $this->assertDatabaseHas('approval_workflows', [
            'decision' => 'approved',
            'comments' => 'Looks good'
        ]);
    }

    public function test_cannot_publish_without_approval()
    {
        $publisher = app(VersionPublisher::class);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Only approved versions can be published');

        $publisher->publish($this->version, $this->user->id);
    }

    public function test_can_publish_approved_version()
    {
        $service = app(ApprovalService::class);
        $publisher = app(VersionPublisher::class);

        $service->submit($this->version, $this->user);
        $service->approve($this->version, $this->user);
        
        $publisher->publish($this->version, $this->user->id, true); // Skip gates for test simplicity

        $this->assertEquals('published', $this->version->fresh()->status);
    }

    public function test_can_rollback_published_version()
    {
        $service = app(ApprovalService::class);
        $publisher = app(VersionPublisher::class);

        // Publish V1
        $service->submit($this->version, $this->user);
        $service->approve($this->version, $this->user);
        $publisher->publish($this->version, $this->user->id, true);

        // Create V2
        $v2 = FeatureVersion::create([
            'feature_id' => $this->version->feature_id,
            'version_no' => 2,
            'status' => 'draft'
        ]);

        // Publish V2
        $service->submit($v2, $this->user);
        $service->approve($v2, $this->user);
        $publisher->publish($v2, $this->user->id, true);

        $this->assertEquals('published', $v2->fresh()->status);
        $this->assertEquals('archived', $this->version->fresh()->status);

        // Rollback to V1
        $this->version->refresh(); 
        $publisher->rollback($this->version, $this->user->id, 'Critical bug in V2');

        $this->assertEquals('published', $this->version->fresh()->status);
        $this->assertEquals('rolled_back', $v2->fresh()->status);
        $this->assertDatabaseHas('rollback_logs', [
            'feature_version_id' => $this->version->id,
            'reason' => 'Critical bug in V2'
        ]);
    }
}
