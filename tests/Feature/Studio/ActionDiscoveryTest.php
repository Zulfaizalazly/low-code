<?php

namespace Tests\Feature\Studio;

use App\Studio\Discovery\ActionRegistry;
use App\Domain\Customer\Commands\RegisterCustomer;
use Tests\TestCase;

class ActionDiscoveryTest extends TestCase
{
    /**
     * Verify that the CommandDiscoverer correctly finds and inspects domain commands.
     */
    public function test_it_discovers_domain_commands_and_extracts_arguments()
    {
        $registry = new ActionRegistry();
        $actions = $registry->getActions();

        // 1. Verify Domain exists in results
        $this->assertArrayHasKey('Customer', $actions);

        // 2. Find RegisterCustomer command in the Customer domain
        $customerActions = collect($actions['Customer']);
        $registerAction = $customerActions->firstWhere('class', RegisterCustomer::class);

        $this->assertNotNull($registerAction, "RegisterCustomer command should be discovered");

        // 3. Verify Argument Metadata
        $args = collect($registerAction['arguments']);
        
        $this->assertTrue($args->contains('name', 'name'), "Arguments should include 'name'");
        $this->assertTrue($args->contains('name', 'icNumber'), "Arguments should include 'icNumber'");
        $this->assertTrue($args->contains('name', 'email'), "Arguments should include 'email'");
        
        $emailArg = $args->firstWhere('name', 'email');
        $this->assertEquals('?string', $emailArg['type']);
        $this->assertFalse($emailArg['required']);
    }
}
