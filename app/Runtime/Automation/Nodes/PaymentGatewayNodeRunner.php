<?php

namespace App\Runtime\Automation\Nodes;

use App\Runtime\Automation\Contracts\NodeRunner;
use App\Runtime\Automation\ExecutionContext;
use App\Studio\Registry\FlowNode;
use Exception;
use Illuminate\Support\Facades\Http;

class PaymentGatewayNodeRunner implements NodeRunner
{
    public function run(FlowNode $node, ExecutionContext $context): mixed
    {
        $config = $node->config ?? [];
        
        $provider = $config['provider'] ?? null;
        $amountPath = $config['amount'] ?? null;
        $description = $config['description'] ?? 'Ar-Rahnu Payment';
        $type = $config['type'] ?? 'collection';
        $credentials = $config['credentials'] ?? [];

        if (!$provider) {
            throw new Exception("PaymentGatewayNodeRunner: No payment provider selected.");
        }

        $amount = (float) $context->get($amountPath, 0);
        $amountCents = (int) ($amount * 100);

        // We will generate the payment link/intent for the customer
        $responsePayload = [];

        try {
            switch ($provider) {
                case 'billplz':
                    $responsePayload = $this->handleBillplz($amountCents, $description, $credentials);
                    break;
                case 'toyyibpay':
                    $responsePayload = $this->handleToyyibPay($amount, $description, $credentials);
                    break;
                case 'stripe':
                    $responsePayload = $this->handleStripe($amountCents, $description, $credentials);
                    break;
                case 'bayarcash':
                    $responsePayload = $this->handleBayarCash($amount, $description, $credentials);
                    break;
                case 'chip':
                    $responsePayload = $this->handleChip($amountCents, $description, $credentials);
                    break;
                default:
                    throw new Exception("Unsupported provider: {$provider}");
            }
        } catch (Exception $e) {
            $responsePayload = [
                'status' => 'FAILED',
                'error' => $e->getMessage()
            ];
        }
        
        $outputKey = $config['output_key'] ?? 'payment_response';
        $context->set($outputKey, $responsePayload);

        return $responsePayload;
    }

    private function handleBillplz(int $amountCents, string $description, array $credentials): array
    {
        $apiKey = $credentials['api_key'] ?? '';
        $collectionId = $credentials['collection_id'] ?? '';

        $response = Http::withBasicAuth($apiKey, '')
            ->post('https://www.billplz.com/api/v3/bills', [
                'collection_id' => $collectionId,
                'email' => 'customer@example.com',
                'name' => 'Customer',
                'amount' => $amountCents,
                'description' => $description,
                'callback_url' => url('/api/payment/callback/billplz')
            ]);

        if (!$response->successful()) {
            throw new Exception("Billplz API Error: " . $response->body());
        }

        $data = $response->json();
        return [
            'status' => 'PENDING',
            'transaction_id' => $data['id'],
            'payment_url' => $data['url'],
            'provider' => 'billplz'
        ];
    }

    private function handleToyyibPay(float $amount, string $description, array $credentials): array
    {
        $secretKey = $credentials['user_secret_key'] ?? '';
        $categoryCode = $credentials['category_code'] ?? '';

        $response = Http::asForm()->post('https://toyyibpay.com/index.php/api/createBill', [
            'userSecretKey' => $secretKey,
            'categoryCode' => $categoryCode,
            'billName' => $description,
            'billDescription' => $description,
            'billPriceSetting' => 1,
            'billPayorInfo' => 1,
            'billAmount' => $amount * 100, // toyyibpay uses cents
            'billReturnUrl' => url('/api/payment/return/toyyibpay'),
            'billCallbackUrl' => url('/api/payment/callback/toyyibpay'),
            'billExternalReferenceNo' => uniqid('REF_'),
            'billTo' => 'Customer',
            'billEmail' => 'customer@example.com',
            'billPhone' => '0123456789'
        ]);

        if (!$response->successful()) {
            throw new Exception("ToyyibPay API Error: " . $response->body());
        }

        $data = $response->json();
        if (is_array($data) && isset($data[0]['BillCode'])) {
            return [
                'status' => 'PENDING',
                'transaction_id' => $data[0]['BillCode'],
                'payment_url' => "https://dev.toyyibpay.com/" . $data[0]['BillCode'],
                'provider' => 'toyyibpay'
            ];
        }

        throw new Exception("ToyyibPay API returned unexpected format.");
    }

    private function handleStripe(int $amountCents, string $description, array $credentials): array
    {
        $secretKey = $credentials['secret_key'] ?? '';

        $response = Http::withToken($secretKey)
            ->asForm()
            ->post('https://api.stripe.com/v1/checkout/sessions', [
                'payment_method_types' => ['card', 'fpx'],
                'line_items' => [
                    [
                        'price_data' => [
                            'currency' => 'myr',
                            'product_data' => ['name' => $description],
                            'unit_amount' => $amountCents,
                        ],
                        'quantity' => 1,
                    ],
                ],
                'mode' => 'payment',
                'success_url' => url('/api/payment/return/stripe?session_id={CHECKOUT_SESSION_ID}'),
                'cancel_url' => url('/api/payment/cancel/stripe'),
            ]);

        if (!$response->successful()) {
            throw new Exception("Stripe API Error: " . $response->body());
        }

        $data = $response->json();
        return [
            'status' => 'PENDING',
            'transaction_id' => $data['id'],
            'payment_url' => $data['url'],
            'provider' => 'stripe'
        ];
    }

    private function handleBayarCash(float $amount, string $description, array $credentials): array
    {
        $pat = $credentials['pat'] ?? '';
        $secretKey = $credentials['secret_key'] ?? '';
        $portalKey = $credentials['portal_key'] ?? '';

        $referenceId = uniqid('BC_');
        $signatureRaw = $portalKey . $referenceId . number_format($amount, 2, '.', '') . 'MYR' . $secretKey;
        $signature = hash('sha256', $signatureRaw);

        // Assuming sandbox URL for integration defaults, could be configurable
        $response = Http::withToken($pat)
            ->post('https://api.bayarcash-sandbox.com/v1/payments', [
                'portal_key' => $portalKey,
                'reference_id' => $referenceId,
                'amount' => $amount,
                'currency' => 'MYR',
                'description' => $description,
                'signature' => $signature,
                'callback_url' => url('/api/payment/callback/bayarcash')
            ]);

        if (!$response->successful()) {
            throw new Exception("BayarCash API Error: " . $response->body());
        }

        $data = $response->json();
        return [
            'status' => 'PENDING',
            'transaction_id' => $data['data']['payment_id'] ?? $referenceId,
            'payment_url' => $data['data']['payment_url'] ?? '',
            'provider' => 'bayarcash'
        ];
    }

    private function handleChip(int $amountCents, string $description, array $credentials): array
    {
        $brandId = $credentials['brand_id'] ?? '';
        $apiKey = $credentials['api_key'] ?? '';

        $response = Http::withToken($apiKey)
            ->post('https://gate.chip-in.asia/api/v1/purchases/', [
                'client' => [
                    'email' => 'customer@example.com',
                    'full_name' => 'Customer'
                ],
                'purchase' => [
                    'currency' => 'MYR',
                    'products' => [
                        [
                            'name' => $description,
                            'price' => $amountCents
                        ]
                    ]
                ],
                'brand_id' => $brandId,
                'success_redirect' => url('/api/payment/return/chip'),
                'failure_redirect' => url('/api/payment/cancel/chip')
            ]);

        if (!$response->successful()) {
            throw new Exception("CHIP API Error: " . $response->body());
        }

        $data = $response->json();
        return [
            'status' => 'PENDING',
            'transaction_id' => $data['id'],
            'payment_url' => $data['checkout_url'],
            'provider' => 'chip'
        ];
    }
}
