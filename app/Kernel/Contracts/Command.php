<?php

namespace App\Kernel\Contracts;

interface Command
{
    /**
     * Validation rules for the command data.
     * 
     * @return array<string, string|array>
     */
    public function rules(): array;
}
