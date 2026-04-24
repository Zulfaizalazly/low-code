<?php

namespace App\Commands;

use App\Kernel\Contracts\Command;

abstract class BaseCommand implements Command
{
    public function rules(): array
    {
        return [];
    }
}
