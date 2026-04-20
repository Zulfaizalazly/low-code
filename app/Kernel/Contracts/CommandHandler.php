<?php

namespace App\Kernel\Contracts;

interface CommandHandler
{
    /**
     * Handle the execution of the command.
     * 
     * @param Command $command
     * @return mixed
     */
    public function handle(Command $command): mixed;
}
