<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SystempayService
{
    private string $shopId;
    private string $apiUrl;
    private string $password;
    private string $hmacKey;

    public function __construct()
    {
        $isProduction = config('services.systempay.mode', 'TEST') === 'PRODUCTION';
        $this->shopId = config('services.systempay.shop_id');
        $this->apiUrl = config('services.systempay.api_url', 'https://api.systempay.fr');
        $this->password = $isProduction
            ? config('services.systempay.password_prod')
            : config('services.systempay.password_test');
        $this->hmacKey = $isProduction
            ? config('services.systempay.hmac_key_prod', '')
            : config('services.systempay.hmac_key_test', '');
    }

    /**
     * Create a payment form token via REST API.
     * Returns the formToken to use with the JS embedded form.
     */
    public function createPayment(Order $order, ?float $amount = null): string
    {
        $amountCents = (int) round(($amount ?? (float) $order->total_ttc) * 100);

        $payload = [
            'amount' => $amountCents,
            'currency' => 'EUR',
            'orderId' => $order->reference,
            'customer' => [
                'email' => $order->user?->email,
                'reference' => (string) ($order->user_id ?? ''),
            ],
        ];

        if ($order->user?->name) {
            $names = explode(' ', $order->user->name, 2);
            $payload['customer']['billingDetails'] = [
                'firstName' => $names[0] ?? '',
                'lastName' => $names[1] ?? '',
            ];
        }

        $http = Http::withBasicAuth($this->shopId, $this->password);

        // Disable SSL verification in local/test environments
        if (app()->environment('local', 'testing')) {
            $http = $http->withoutVerifying();
        }

        $response = $http->post($this->apiUrl . '/api-payment/V4/Charge/CreatePayment', $payload);

        if (! $response->successful()) {
            Log::error('Systempay CreatePayment failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            throw new \RuntimeException('Erreur Systempay: ' . ($response->json('answer.errorMessage') ?? $response->body()));
        }

        $data = $response->json();

        if (($data['status'] ?? '') !== 'SUCCESS') {
            $errorMsg = $data['answer']['errorMessage'] ?? 'Erreur inconnue';
            throw new \RuntimeException('Systempay: ' . $errorMsg);
        }

        return $data['answer']['formToken'];
    }

    /**
     * Verify IPN (Instant Payment Notification) hash.
     */
    public function verifyIpnHash(string $payload, string $receivedHash): bool
    {
        if (empty($this->hmacKey)) {
            Log::warning('Systempay HMAC key not configured, skipping verification');
            return true;
        }

        $computed = hash_hmac('sha256', $payload, $this->hmacKey);
        return hash_equals($computed, $receivedHash);
    }

    /**
     * Process IPN data and mark order as paid.
     */
    public function handleIpn(array $data): void
    {
        $orderRef = $data['orderDetails']['orderId'] ?? null;
        $transStatus = $data['orderStatus'] ?? null;

        if (! $orderRef) {
            Log::warning('Systempay IPN: no orderId');
            return;
        }

        $order = Order::where('reference', $orderRef)->first();
        if (! $order) {
            Log::warning('Systempay IPN: order not found', ['ref' => $orderRef]);
            return;
        }

        if ($transStatus === 'PAID') {
            $amountPaid = ($data['orderDetails']['orderTotalAmount'] ?? 0) / 100;
            $transId = $data['transactions'][0]['uuid'] ?? null;

            app(OrderService::class)->markAsPaid($order, $transId, $amountPaid);

            Log::info('Systempay IPN: order paid', ['ref' => $orderRef, 'amount' => $amountPaid]);
        } else {
            Log::info('Systempay IPN: status ' . $transStatus, ['ref' => $orderRef]);
        }
    }

    /**
     * Get the public key for the JS embedded form.
     */
    public function getPublicKey(): string
    {
        return config('services.systempay.mode', 'TEST') === 'PRODUCTION'
            ? config('services.systempay.public_key_prod')
            : config('services.systempay.public_key_test');
    }

    public function getJsUrl(): string
    {
        return config('services.systempay.js_url');
    }
}
