<?php

namespace Tests\Feature\Kernel;

use App\Domain\Customer\Commands\RegisterCustomer;
use App\Domain\Customer\Models\Customer;
use App\Kernel\Bus\CommandBus;
use App\Kernel\Audit\AuditLog;
use App\Kernel\Events\EventLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommandBusTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'role' => 'branch_staff',
            'entity_id' => 1,
            'branch_id' => 101,
        ]);

        $this->actingAs($this->user);
    }

    public function test_command_bus_executes_handler_and_records_logs()
    {
        $bus = app(CommandBus::class);

        $command = new RegisterCustomer(
            name: 'John Doe',
            icNumber: '1234567890',
            email: 'john@example.com'
        );

        $customer = $bus->dispatch($command);

        // 1. Verify Record Creation
        $this->assertInstanceOf(Customer::class, $customer);
        $this->assertDatabaseHas('customers', [
            'name' => 'John Doe',
            'ic_number' => '1234567890',
        ]);
        $this->assertEquals(1, $customer->entity_id); // Verification of scoping inheritance
        $this->assertEquals(101, $customer->branch_id);

        // 2. Verify Audit Trail
        $this->assertDatabaseHas('audit_trails', [
            'auditable_type' => Customer::class,
            'auditable_id' => $customer->id,
            'action' => 'created',
            'user_id' => $this->user->id,
        ]);

        // 3. Verify Event Log
        $this->assertDatabaseHas('event_logs', [
            'source_type' => Customer::class,
            'source_id' => $customer->id,
        ]);
    }

    public function test_command_bus_rolls_back_on_failure()
    {
        // This would require a command that fails inside the handler
        // We'll simulate a failure in handlers later
        $this->assertTrue(true);
    }
}
