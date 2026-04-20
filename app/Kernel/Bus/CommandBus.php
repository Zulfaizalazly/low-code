<?php

namespace App\Kernel\Bus;

use App\Kernel\Contracts\Command;
use App\Kernel\Contracts\CommandHandler;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class CommandBus
{
    /**
     * Dispatch a command to its corresponding handler.
     *
     * @param Command $command
     * @return mixed
     * @throws ValidationException
     */
    public function dispatch(Command $command): mixed
    {
        // 1. Validate Command
        $validator = Validator::make((array) $command, $command->rules());
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        // 2. Resolve Handler
        $handlerClass = $this->resolveHandler($command);
        $handler = app($handlerClass);

        if (!$handler instanceof CommandHandler) {
            throw new RuntimeException("Handler {$handlerClass} must implement CommandHandler interface.");
        }

        // 3. Execute in Transaction with Logging
        return DB::transaction(function () use ($handler, $command) {
            return $handler->handle($command);
        });
    }

    /**
     * Resolve the handler class name from the command class name.
     * Pattern: App\Domain\{Domain}\Commands\{CommandName} 
     *      -> App\Domain\{Domain}\Handlers\{CommandName}Handler
     */
    protected function resolveHandler(Command $command): string
    {
        $commandClass = get_class($command);
        
        return str_replace(
            ['\\Commands\\', 'Command'], 
            ['\\Handlers\\', ''], 
            $commandClass
        ) . 'Handler';
    }
}
