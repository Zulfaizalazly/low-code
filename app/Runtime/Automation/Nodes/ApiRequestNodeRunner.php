<?php

namespace App\Runtime\Automation\Nodes;

use App\Runtime\Automation\Contracts\NodeRunner;
use App\Runtime\Automation\ExecutionContext;
use App\Studio\Registry\FlowNode;
use Exception;
use Illuminate\Support\Facades\Http;

class ApiRequestNodeRunner implements NodeRunner
{
    public function run(FlowNode $node, ExecutionContext $context): mixed
    {
        $config = $node->config ?? [];
        
        $url = $config['url'] ?? null;
        $method = strtoupper($config['method'] ?? 'GET');
        $authType = $config['auth_type'] ?? 'none';
        $headers = $config['headers'] ?? [];
        
        if (!$url) {
            throw new Exception("ApiRequestNodeRunner: URL is required.");
        }

        // Simulation Mode: Skip real HTTP request
        if ($context->isSimulation) {
            $mockResponse = $config['mock_response'] ?? [
                'status' => 200,
                'successful' => true,
                'body' => ['price_gram_24k' => 350.50, 'price_gram_22k' => 320.00, 'simulated' => true]
            ];
            
            $outputKey = $config['output_key'] ?? 'api_response';
            $context->set($outputKey, $mockResponse);
            return $mockResponse;
        }

        // Allow URL to contain context variables e.g. https://api.domain.com/users/{$.user.id}
        $url = preg_replace_callback('/\{\$\.([a-zA-Z0-9_\.]+)\}/', function($matches) use ($context) {
            return $context->get($matches[1], '');
        }, $url);

        // Initialize Http Client
        $request = Http::withHeaders(is_array($headers) ? $headers : []);

        // Authentication
        if ($authType === 'bearer') {
            $token = $config['auth_token'] ?? '';
            if (str_starts_with($token, '$.')) {
                $token = $context->get(substr($token, 2), '');
            }
            $request->withToken($token);
        } elseif ($authType === 'basic') {
            $username = $config['auth_username'] ?? '';
            $password = $config['auth_password'] ?? '';
            if (str_starts_with($username, '$.')) $username = $context->get(substr($username, 2), '');
            if (str_starts_with($password, '$.')) $password = $context->get(substr($password, 2), '');
            
            $request->withBasicAuth($username, $password);
        }

        // Payload
        $payload = [];
        if (in_array($method, ['POST', 'PUT', 'PATCH'])) {
            $rawPayload = $config['payload'] ?? [];
            if (is_array($rawPayload)) {
                $payload = $this->resolvePayloadVariables($rawPayload, $context);
            }
        }

        // Execute Request
        try {
            $response = match ($method) {
                'GET' => $request->get($url),
                'POST' => $request->post($url, $payload),
                'PUT' => $request->put($url, $payload),
                'PATCH' => $request->patch($url, $payload),
                'DELETE' => $request->delete($url),
                default => throw new Exception("Unsupported HTTP Method: {$method}")
            };

            $data = [
                'status' => $response->status(),
                'successful' => $response->successful(),
                'body' => $response->json() ?? $response->body(),
                'headers' => $response->headers()
            ];

        } catch (Exception $e) {
            $data = [
                'status' => 500,
                'successful' => false,
                'error' => $e->getMessage()
            ];
        }
        
        $outputKey = $config['output_key'] ?? 'api_response';
        $context->set($outputKey, $data);

        return $data;
    }

    /**
     * Recursively resolve variable mappings like "$.customer.id" into actual context values.
     */
    private function resolvePayloadVariables(array $payload, ExecutionContext $context): array
    {
        $resolved = [];
        foreach ($payload as $key => $value) {
            if (is_array($value)) {
                $resolved[$key] = $this->resolvePayloadVariables($value, $context);
            } elseif (is_string($value) && str_starts_with($value, '$.')) {
                $resolved[$key] = $context->get(substr($value, 2));
            } else {
                $resolved[$key] = $value;
            }
        }
        return $resolved;
    }
}
