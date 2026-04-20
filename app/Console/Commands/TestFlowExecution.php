<?php

namespace App\Console\Commands;

use App\Domain\Customer\Commands\RegisterCustomer;
use App\Runtime\Automation\FlowOrchestrator;
use App\Studio\Registry\Feature;
use App\Studio\Registry\FeatureVersion;
use Illuminate\Console\Command;
use App\Models\User;

class TestFlowExecution extends Command
{
    protected $signature = 'v3:test-flow';
    protected $description = 'Simulate a multi-node flow execution in the terminal';

    public function handle(FlowOrchestrator $orchestrator)
    {
        $this->info("🚀 Starting V3 Flow Simulation...");

        // Ensure we have a user for scoping
        $user = User::first() ?? User::create([
            'name' => 'Demo Admin',
            'email' => 'admin@demo.com',
            'password' => bcrypt('password'),
            'role' => 'hq_admin',
            'entity_id' => 1,
            'branch_id' => 1,
        ]);

        auth()->login($user);

        // 1. Create a transient flow definition
        $this->comment("Creating Flow: 'Auto-Register System'...");
        
        $feature = Feature::create(['key' => 'sim_' . time(), 'name' => 'Simulation', 'domain' => 'customer']);
        $version = $feature->versions()->create(['version_no' => 1, 'status' => 'published']);
        $flow = $version->flows()->create([
            'key' => 'sim_flow',
            'name' => 'Simulation Flow',
            'trigger_type' => 'manual_entry'
        ]);

        $startNode = $flow->nodes()->create(['node_key' => 'start', 'node_type' => 'trigger', 'label' => 'Start']);
        $commandNode = $flow->nodes()->create([
            'node_key' => 'cmd_1',
            'node_type' => 'command',
            'label' => 'Register Node',
            'config' => [
                'command_class' => RegisterCustomer::class,
                'payload_mapping' => [
                    'name' => 'trigger_name',
                    'icNumber' => 'trigger_ic',
                    'email' => 'trigger_email',
                ]
            ]
        ]);
        $endNode = $flow->nodes()->create(['node_key' => 'end', 'node_type' => 'end', 'label' => 'End']);

        $flow->edges()->create(['source_node_id' => $startNode->id, 'target_node_id' => $commandNode->id, 'condition_type' => 'always']);
        $flow->edges()->create(['source_node_id' => $commandNode->id, 'target_node_id' => $endNode->id, 'condition_type' => 'always']);

        // 2. Execute
        $triggerData = [
            'trigger_name' => 'Simulated User ' . rand(100, 999),
            'trigger_ic' => 'IC-' . time(),
            'trigger_email' => 'sim@test.com'
        ];

        $this->info("Executing Flow with data: " . json_encode($triggerData));
        
        try {
            $log = $orchestrator->execute($flow, $triggerData);
            
            $this->info("✅ Execution finished with status: {$log->status}");
            $this->comment("Trace ID: {$log->id}");
            
            foreach ($log->nodeLogs as $nodeLog) {
                $statusIcon = $nodeLog->status === 'completed' ? '✅' : '❌';
                $this->line(" - {$statusIcon} Node: [{$nodeLog->node_key}] ({$nodeLog->node_type})");
            }

        } catch (\Exception $e) {
            $this->error("❌ Simulation Failed: " . $e->getMessage());
        }

        return 0;
    }
}
