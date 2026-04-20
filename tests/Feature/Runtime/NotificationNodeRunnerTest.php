<?php

namespace Tests\Feature\Runtime;

use App\Models\User;
use App\Runtime\Automation\ExecutionContext;
use App\Runtime\Automation\Nodes\NotificationNodeRunner;
use App\Runtime\Models\AutomationExecutionLog;
use App\Studio\Registry\Feature;
use App\Studio\Registry\FlowNode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationNodeRunnerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'name' => 'Test Staff',
            'email' => 'notif-test@arrahnu.com',
            'password' => 'password',
            'role' => 'branch_staff',
            'entity_id' => 1,
            'branch_id' => 1,
        ]);

        $this->actingAs($this->user);
    }

    public function test_notification_node_sends_notification_with_interpolation()
    {
        $feature = Feature::create(['key' => 'notif_test', 'name' => 'Notif', 'domain' => 'customer']);
        $version = $feature->versions()->create(['version_no' => 1, 'status' => 'published']);
        $flow = $version->flows()->create([
            'key' => 'notif_flow', 'name' => 'Notif Flow', 'trigger_type' => 'manual_entry',
        ]);

        $executionLog = AutomationExecutionLog::create([
            'flow_definition_id' => $flow->id,
            'feature_version_id' => $version->id,
            'trigger_type' => 'manual_entry',
            'status' => 'running',
            'started_at' => now(),
        ]);

        $context = new ExecutionContext($executionLog, [
            'form' => [
                'name' => 'Ahmad Test',
                'email' => 'ahmad@test.com',
            ],
        ]);

        $node = new FlowNode();
        $node->node_key = 'notify_customer';
        $node->node_type = 'notification';
        $node->config = [
            'notifiable_type' => 'App\\Models\\User',
            'notifiable_id' => $this->user->id,
            'channel' => 'email',
            'recipient' => 'form.email',
            'subject' => 'Welcome {{form.name}}!',
            'body' => 'Dear {{form.name}}, your pledge has been submitted.',
        ];

        $runner = app(NotificationNodeRunner::class);
        $output = $runner->run($node, $context);

        $this->assertEquals('sent', $output['status']);
        $this->assertEquals('email', $output['channel']);
        $this->assertEquals('ahmad@test.com', $output['recipient']);
        $this->assertNotNull($output['notification_id']);

        $this->assertDatabaseHas('notification_logs', [
            'channel' => 'email',
            'recipient' => 'ahmad@test.com',
            'subject' => 'Welcome Ahmad Test!',
            'status' => 'sent',
        ]);
    }

    public function test_notification_node_simulation_mode()
    {
        $feature = Feature::create(['key' => 'notif_sim', 'name' => 'Notif Sim', 'domain' => 'customer']);
        $version = $feature->versions()->create(['version_no' => 1, 'status' => 'draft']);
        $flow = $version->flows()->create([
            'key' => 'sim_flow', 'name' => 'Sim Flow', 'trigger_type' => 'manual_entry',
        ]);

        $executionLog = AutomationExecutionLog::create([
            'flow_definition_id' => $flow->id,
            'feature_version_id' => $version->id,
            'trigger_type' => 'manual_entry',
            'status' => 'running',
            'started_at' => now(),
        ]);

        $context = new ExecutionContext($executionLog, [
            '_simulation' => true,
            'form' => ['name' => 'Sim User', 'email' => 'sim@test.com'],
        ]);

        $node = new FlowNode();
        $node->node_key = 'notify_sim';
        $node->node_type = 'notification';
        $node->config = [
            'notifiable_type' => 'App\\Models\\User',
            'notifiable_id' => 1,
            'channel' => 'sms',
            'recipient' => 'form.email',
            'subject' => 'Test',
        ];

        $runner = app(NotificationNodeRunner::class);
        $output = $runner->run($node, $context);

        $this->assertTrue($output['simulated_execution']);
        $this->assertEquals('skipped (simulation)', $output['status']);
        $this->assertDatabaseCount('notification_logs', 0);
    }
}
