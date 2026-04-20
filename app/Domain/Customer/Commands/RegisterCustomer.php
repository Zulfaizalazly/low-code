<?php

namespace App\Domain\Customer\Commands;

use App\Kernel\Contracts\Command;

class RegisterCustomer implements Command
{
    public function __construct(
        public string $name,
        public string $icNumber,
        public ?string $phone = null,
        public ?string $email = null,
        public ?string $address = null,
        public ?string $dateOfBirth = null,
        public string $customerType = 'individual'
    ) {}

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'icNumber' => 'required|string|unique:customers,ic_number',
            'phone' => 'nullable|string',
            'email' => 'nullable|email',
            'address' => 'nullable|string',
            'dateOfBirth' => 'nullable|date',
            'customerType' => 'required|in:individual,corporate',
        ];
    }
}
