<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class SoloApiService
{
    private string $baseUrl;
    private string $tokenUrl;
    private string $clientId;
    private string $clientSecret;

    /**
     * API paths on api.sologroup-paris.com (Gravitee.io gateway).
     *
     * Catalogue: warehouse + language as HTTP headers
     * StockItems: ALL params as HTTP headers (warehouse, language, Full/Model/SKU)
     * Customer Prices: ALL params as HTTP headers (warehouse, full/model/sku)
     */
    private const PATHS = [
        'catalogue_model' => '/products/catalogue/model',
        'catalogue_sku' => '/products/catalogue/sku',
        'customer_prices' => '/customer/prices/customer_prices',
        'stock_items' => '/products/inventory/stock/StockItems',
    ];

    private const CACHE_KEY = 'solo_oauth_token';

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.solo.base_url', ''), '/');
        $this->tokenUrl = config('services.solo.token_url', '');
        $this->clientId = config('services.solo.client_id', '');
        $this->clientSecret = config('services.solo.client_secret', '');
    }

    public function isConfigured(): bool
    {
        return ! empty($this->clientId) && ! empty($this->clientSecret) && ! empty($this->baseUrl);
    }

    // ─── OAuth2 ───────────────────────────────────────────────────

    public function getAccessToken(): string
    {
        $cached = Cache::get(self::CACHE_KEY);
        if ($cached) {
            return $cached;
        }

        return $this->refreshToken();
    }

    public function refreshToken(): string
    {
        if (empty($this->tokenUrl)) {
            throw new RuntimeException(
                'SOLO_TOKEN_URL non configurée. Renseignez l\'URL du endpoint OAuth2 dans .env'
            );
        }

        $response = Http::asForm()
            ->timeout(30)
            ->withOptions(['verify' => false])
            ->post($this->tokenUrl, [
                'grant_type' => 'client_credentials',
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
            ]);

        if (! $response->successful()) {
            $response = Http::asForm()
                ->timeout(30)
                ->withOptions(['verify' => false])
                ->withBasicAuth($this->clientId, $this->clientSecret)
                ->post($this->tokenUrl, [
                    'grant_type' => 'client_credentials',
                ]);
        }

        if (! $response->successful()) {
            throw new RuntimeException(
                "Solo OAuth2 token request failed: HTTP {$response->status()} - " . substr($response->body(), 0, 500)
            );
        }

        $data = $response->json();
        $token = $data['access_token'] ?? null;
        $expiresIn = (int) ($data['expires_in'] ?? 3600);

        if (! $token) {
            throw new RuntimeException(
                'Solo OAuth2 response missing access_token: ' . substr($response->body(), 0, 500)
            );
        }

        Cache::put(self::CACHE_KEY, $token, max($expiresIn - 60, 60));

        return $token;
    }

    /**
     * @return array{success: bool, message: string, token_preview?: string, details?: array}
     */
    public function testConnection(): array
    {
        if (empty($this->tokenUrl)) {
            return $this->probeEndpoints();
        }

        try {
            Cache::forget(self::CACHE_KEY);
            $token = $this->refreshToken();

            return [
                'success' => true,
                'message' => 'Authentification OAuth2 réussie',
                'token_preview' => substr($token, 0, 20) . '...',
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    public function probeEndpoints(): array
    {
        $results = [];

        foreach (self::PATHS as $name => $path) {
            try {
                $response = Http::timeout(10)
                    ->withOptions(['verify' => false])
                    ->get($this->baseUrl . $path);

                $results[$name] = [
                    'path' => $path,
                    'status' => $response->status(),
                    'ok' => in_array($response->status(), [401, 400]),
                    'body' => substr($response->body(), 0, 100),
                ];
            } catch (\Throwable $e) {
                $results[$name] = [
                    'path' => $path,
                    'status' => 0,
                    'ok' => false,
                    'body' => $e->getMessage(),
                ];
            }
        }

        $found = count(array_filter($results, fn ($r) => $r['ok']));

        return [
            'success' => false,
            'message' => "SOLO_TOKEN_URL non configurée. {$found}/" . count($results) . ' endpoints API détectés.',
            'details' => $results,
        ];
    }

    // ─── Catalogue ───────────────────────────────────────────────

    /**
     * Full catalogue by model (663 products).
     */
    public function getCatalogue(string $warehouse = '001', string $language = 'fr'): array
    {
        return $this->get(self::PATHS['catalogue_model'], [
            'warehouse' => $warehouse,
            'language' => $language,
        ]);
    }

    /**
     * All SKUs (colors/sizes) — returns ~18k entries, needs high memory.
     */
    public function getAllSkus(string $warehouse = '001', string $language = 'fr'): array
    {
        return $this->get(self::PATHS['catalogue_sku'], [
            'warehouse' => $warehouse,
            'language' => $language,
        ]);
    }

    /**
     * SKUs filtered by model code prefix.
     */
    public function getProductBySku(string $sku, string $warehouse = '001', string $language = 'fr'): array
    {
        return $this->get(self::PATHS['catalogue_sku'], [
            'sku' => $sku,
            'warehouse' => $warehouse,
            'language' => $language,
        ]);
    }

    // ─── Stock ───────────────────────────────────────────────────

    /**
     * Get stock levels from StockItems endpoint.
     * Returns flat array of ['SKU' => ..., 'StockAvailable' => ...].
     *
     * @param  string  $warehouse  001=France, 201=Pologne
     */
    public function getStock(string $warehouse = '001', string $language = 'fr', ?string $model = null): array
    {
        $headers = [
            'warehouse' => $warehouse,
            'language' => $language,
        ];

        if ($model) {
            $headers['Model'] = $model;
        } else {
            $headers['Full'] = 'true';
        }

        $data = $this->getWithHeaders(self::PATHS['stock_items'], $headers);

        // Flatten: Warehouses[].ProductDetails[] → flat array
        $items = [];
        foreach ($data['Warehouses'] ?? [] as $wh) {
            foreach ($wh['ProductDetails'] ?? [] as $item) {
                $items[] = $item;
            }
        }

        return $items;
    }

    // ─── Prices ──────────────────────────────────────────────────

    /**
     * Customer prices — all params as HTTP headers (like StockItems).
     * Returns full price list or filtered by model/sku.
     */
    public function getPrices(string $warehouse = '001', ?string $model = null, ?string $sku = null): array
    {
        $headers = [
            'warehouse' => $warehouse,
        ];

        if ($sku) {
            $headers['sku'] = $sku;
        } elseif ($model) {
            $headers['model'] = $model;
        } else {
            $headers['full'] = 'true';
        }

        return $this->getWithHeaders(self::PATHS['customer_prices'], $headers);
    }

    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    public function getTokenUrl(): string
    {
        return $this->tokenUrl ?: '(non configurée)';
    }

    // ─── Internal ─────────────────────────────────────────────────

    /**
     * GET request where warehouse + language go as HTTP headers.
     * Other params go as query params (for catalogue endpoints).
     */
    private function get(string $path, array $params = []): array
    {
        $token = $this->getAccessToken();
        $url = $this->baseUrl . $path;

        $headers = [];
        foreach (['warehouse', 'language'] as $key) {
            if (isset($params[$key])) {
                $headers[$key] = $params[$key];
                unset($params[$key]);
            }
        }

        $response = Http::withToken($token)
            ->timeout(120)
            ->withOptions(['verify' => false])
            ->withHeaders($headers)
            ->get($url, $params);

        if ($response->status() === 401) {
            $token = $this->refreshToken();
            $response = Http::withToken($token)
                ->timeout(120)
                ->withOptions(['verify' => false])
                ->withHeaders($headers)
                ->get($url, $params);
        }

        if (! $response->successful()) {
            throw new RuntimeException(
                "Solo API GET {$path} failed: HTTP {$response->status()} - " . substr($response->body(), 0, 500)
            );
        }

        return $response->json() ?? [];
    }

    /**
     * GET request where ALL params go as HTTP headers.
     * Used for StockItems endpoint which requires this convention.
     */
    private function getWithHeaders(string $path, array $headers = []): array
    {
        $token = $this->getAccessToken();
        $url = $this->baseUrl . $path;

        $response = Http::withToken($token)
            ->timeout(120)
            ->withOptions(['verify' => false])
            ->withHeaders($headers)
            ->get($url);

        if ($response->status() === 401) {
            $token = $this->refreshToken();
            $response = Http::withToken($token)
                ->timeout(120)
                ->withOptions(['verify' => false])
                ->withHeaders($headers)
                ->get($url);
        }

        if (! $response->successful()) {
            throw new RuntimeException(
                "Solo API GET {$path} failed: HTTP {$response->status()} - " . substr($response->body(), 0, 500)
            );
        }

        return $response->json() ?? [];
    }
}
