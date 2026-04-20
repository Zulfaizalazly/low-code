<?php

namespace Tests\Feature\Kernel;

use App\Domain\Customer\Commands\RegisterCustomer;
use App\Kernel\Bus\CommandBus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditHardeningTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_command_execution_must_produce_an_audit_log()
    {
        // 1. Setup User
        $user = User::create([
            'name' => 'Admin',
            'email' => 'admin@test.com',
            'password' => 'password',
            'role' => 'hq_admin',
            'entity_id' => 1,
            'branch_id' => 1,
        ]);
        $this->actingAs($user);

        // 2. Run a command
        $bus = app(CommandBus::class);
        $command = new RegisterCustomer(
            name: 'Audit Test',
            icNumber: 'AUDIT-123',
            email: 'audit@test.com'
        );
        $bus->dispatch($command);

        // 3. Verify Audit Log exists
        $this->assertDatabaseHas('audit_trails', [
            'action' => 'created',
            'auditable_type' => \App\Domain\Customer\Models\Customer::class,
            'user_id' => $user->id,
        ]);

        $log = \App\Kernel\Audit\AuditLog::where('auditable_type', \App\Domain\Customer\Models\Customer::class)->first();
        $this->assertNotNull($log->new_values);
        $this->assertEquals('Audit Test', $log->new_values['name']);
    }
}
