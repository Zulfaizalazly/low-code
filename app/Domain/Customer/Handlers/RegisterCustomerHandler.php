<?php

namespace App\Domain\Customer\Handlers;

use App\Domain\Customer\Commands\RegisterCustomer;
use App\Domain\Customer\Events\CustomerCreated;
use App\Domain\Customer\Models\Customer;
use App\Kernel\Contracts\CommandHandler;
use App\Kernel\Contracts\Command;

class RegisterCustomerHandler implements CommandHandler
{
    /**
     * @param RegisterCustomer $command
     */
    public function handle(Command $command): Customer
    {
        $customer = Customer::create([
            'name' => $command->name,
            'ic_number' => $command->icNumber,
            'phone' => $command->phone,
            'email' => $command->email,
            'address' => $command->address,
            'date_of_birth' => $command->dateOfBirth,
            'customer_type' => $command->customerType,
            'status' => 'active',
        ]);
        event(new CustomerCreated($customer));

        return $customer;
    }
}
