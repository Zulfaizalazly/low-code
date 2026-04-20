<?php

namespace App\Runtime\Automation\Nodes;

use App\Domain\Notification\Commands\SendNotification;
use App\Kernel\Bus\CommandBus;
use App\Runtime\Automation\Contracts\NodeRunner;
use App\Runtime\Automation\ExecutionContext;
use App\Studio\Registry\FlowNode;

class NotificationNodeRunner implements NodeRunner
{
    public function __construct(private CommandBus $bus) {}

    public function run(FlowNode $node, ExecutionContext $context): mixed
    {
        $config = $node->config ?? [];

        $notifiableType = $this->resolve($config['notifiable_type'] ?? null, $context);
        $notifiableId = $this->resolve($config['notifiable_id'] ?? null, $context);
        $channel = $config['channel'] ?? 'email';
        $recipient = $this->resolve($config['recipient'] ?? null, $context);
        $subject = $this->interpolate($config['subject'] ?? null, $context);
        $body = $this->interpolate($config['body'] ?? null, $context);

        // Simulation Mode: Skip real dispatch
        if ($context->isSimulation) {
            return [
                'simulated_execution' => true,
                'node_type' => 'notification',
                'channel' => $channel,
                'recipient' => $recipient,
                'subject' => $subject,
                'status' => 'skipped (simulation)',
            ];
        }

        $command = new SendNotification(
            notifiableType: (string) $notifiableType,
            notifiableId: (int) $notifiableId,
            channel: $channel,
            recipient: (string) $recipient,
            subject: $subject,
            body: $body,
        );

        $log = $this->bus->dispatch($command);

        return [
            'notification_id' => $log->id,
            'channel' => $channel,
            'recipient' => $recipient,
            'status' => 'sent',
        ];
    }

    /**
     * Resolve a value from the execution context if it looks like a path.
     */
    private function resolve(mixed $value, ExecutionContext $context): mixed
    {
        if (!is_string($value)) {
            return $value;
        }

        if (preg_match('/^\{\{(.*)\}\}$/', $value, $matches)) {
            return $context->get(trim($matches[1]));
        }

        if (preg_match('/^(nodes\.|form\.|auth\.)/', $value)) {
            return $context->get($value);
        }

        return $value;
    }

    /**
     * Interpolate {{variable}} placeholders in template strings.
     * Example: "Dear {{form.name}}, your pledge is approved."
     */
    private function interpolate(?string $template, ExecutionContext $context): ?string
    {
        if ($template === null) {
            return null;
        }

        return preg_replace_callback('/\{\{(.*?)\}\}/', function ($matches) use ($context) {
            $path = trim($matches[1]);
            $value = $context->get($path);
            return is_scalar($value) ? (string) $value : json_encode($value);
        }, $template);
    }
}
