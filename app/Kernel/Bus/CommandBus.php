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
     * Explicit mapping for legacy App\Commands\* classes
     * to their actual Domain handler counterparts.
     */
    protected static array $handlerMap = [
        \App\Commands\AmlaCheckCommand::class        => \App\Domain\Compliance\Handlers\AmlaCheckHandler::class,
        \App\Commands\FetchDuePaymentsCommand::class => \App\Domain\Payment\Handlers\RecordPaymentHandler::class,
        \App\Commands\PostGLEntryCommand::class      => \App\Domain\Accounting\Handlers\PostJournalEntryHandler::class,
        \App\Commands\SendNotificationCommand::class => \App\Domain\Notification\Handlers\SendNotificationHandler::class,
    ];

    /**
     * Resolve the handler class name from the command class name.
     * 
     * 1. Check explicit $handlerMap first (for legacy commands).
     * 2. Fall back to convention: \Commands\ -> \Handlers\, strip "Command" suffix.
     */
    protected function resolveHandler(Command $command): string
    {
        $commandClass = get_class($command);

        if (isset(static::$handlerMap[$commandClass])) {
            return static::$handlerMap[$commandClass];
        }

        return str_replace(
            ['\\Commands\\', 'Command'], 
            ['\\Handlers\\', ''], 
            $commandClass
        ) . 'Handler';
    }
}
