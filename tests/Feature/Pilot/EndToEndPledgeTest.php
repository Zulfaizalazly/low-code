<?php

namespace Tests\Feature\Pilot;

use App\Models\User;
use App\Runtime\Models\AutomationExecutionLog;
use Database\Seeders\ReferenceFeatureSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class EndToEndPledgeTest extends TestCase
{
    use RefreshDatabase;

    public function test_v3_pilot_shippable_end_to_end_journey()
    {
        // 1. Setup Pilot Environment (Seed)
        $seeder = new ReferenceFeatureSeeder();
        $seeder->run();

        $staff = User::where('email', 'staff@arrahnu.com')->first();
        $this->actingAs($staff);

        // 2. Simulate Dynamic Form Submission
        // We'll use the Livewire component directly to simulate the user journey
        Livewire::test(\App\Livewire\Runtime\FormEngine::class, ['featureKey' => 'new-pledge'])
            ->set('formData', [
                'name' => 'Ahmad Pilot',
                'ic_number' => 'PILOT-888',
                'email' => 'ahmad@pilot.com',
                'amount' => 5000,
                'items' => [
                    ['item_type' => 'Jewelry', 'weight_grams' => 10, 'purity' => 916]
                ],
            ])
            ->call('submit')
            ->assertHasNoErrors();

        // 3. Verify Domain Outcomes (Phase 1 Logic)
        $this->assertDatabaseHas('customers', [
            'name' => 'Ahmad Pilot',
            'ic_number' => 'PILOT-888',
        ]);

        $this->assertDatabaseHas('facilities', [
            'principal_amount' => 5000,
        ]);

        // 4. Verify Automation Traceability (Phase 2/5 Logic)
        $this->assertDatabaseHas('automation_execution_logs', [
            'status' => 'completed',
        ]);

        $execution = AutomationExecutionLog::latest()->first();
        $this->assertGreaterThan(0, $execution->nodeLogs()->count(), "Execution should have node traces");
        
        // 5. Verify Audit Trail (Hardening)
        $this->assertDatabaseHas('audit_trails', [
            'auditable_type' => \App\Domain\Customer\Models\Customer::class,
            'user_id' => $staff->id,
        ]);

        dump("End-to-End Pilot Test Passed! V3 is ready for launch.");
    }
}
