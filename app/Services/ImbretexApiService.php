<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class ImbretexApiService
{
    private string $baseUrl;
    private string $token;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.imbretex.base_url', ''), '/');
        $this->token = config('services.imbretex.token', '');
    }

    /**
     * Test authentication by fetching a single product.
     */
    public function testConnection(): bool
    {
        $response = $this->buildRequest()
            ->get("{$this->baseUrl}/products/products", ['perPage' => 1, 'page' => 1]);

        return $response->successful();
    }

    /**
     * GET /products/products — paginated product catalog.
     * Returns all pages merged into a single array.
     *
     * @param  string|null $sinceCreated  Format d-m-Y
     * @param  string|null $sinceUpdated  Format d-m-Y
     */
    public function getProducts(?string $sinceCreated = null, ?string $sinceUpdated = null): array
    {
        $allProducts = [];
        $page = 1;
        $perPage = 50; // max allowed by API

        do {
            $params = ['perPage' => $perPage, 'page' => $page];
            if ($sinceCreated) {
                $params['sinceCreated'] = $sinceCreated;
            }
            if ($sinceUpdated) {
                $params['sinceUpdated'] = $sinceUpdated;
            }

            $response = $this->buildRequest()
                ->get("{$this->baseUrl}/products/products", $params);

            if (! $response->successful()) {
                throw new RuntimeException(
                    "Imbretex API GET /products/products failed: HTTP {$response->status()} - " . substr($response->body(), 0, 300)
                );
            }

            $data = $response->json();
            $products = $data['products'] ?? [];

            // The response structure has metadata at top level + products array
            if (is_array($products)) {
                $allProducts = array_merge($allProducts, $products);
            }

            $totalPages = $data['totalNumberPage'] ?? 1;
            $page++;
        } while ($page <= $totalPages);

        return $allProducts;
    }

    /**
     * GET /products/products — single page (for memory-efficient pagination).
     * Returns raw API response with totalNumberPage, productCount, products[].
     */
    public function getProductsPage(int $page = 1, int $perPage = 50): array
    {
        $response = $this->buildRequest()
            ->get("{$this->baseUrl}/products/products", ['perPage' => $perPage, 'page' => $page]);

        if (! $response->successful()) {
            throw new RuntimeException(
                "Imbretex API GET /products/products page {$page} failed: HTTP {$response->status()}"
            );
        }

        return $response->json() ?? [];
    }

    /**
     * GET /products/stocks — paginated stock data.
     *
     * @param  string|null $since  Format d-m-Y
     */
    public function getStocks(?string $since = null): array
    {
        $allStocks = [];
        $page = 1;
        $perPage = 5000; // max allowed

        do {
            $params = ['perPage' => $perPage, 'page' => $page];
            if ($since) {
                $params['since'] = $since;
            }

            $response = $this->buildRequest()
                ->get("{$this->baseUrl}/products/stocks", $params);

            if (! $response->successful()) {
                throw new RuntimeException(
                    "Imbretex API GET /products/stocks failed: HTTP {$response->status()} - " . substr($response->body(), 0, 300)
                );
            }

            $data = $response->json();
            $stocks = $data['stocks'] ?? [];

            if (is_array($stocks)) {
                $allStocks = array_merge($allStocks, $stocks);
            }

            $totalPages = $data['totalNumberPage'] ?? 1;
            $page++;
        } while ($page <= $totalPages);

        return $allStocks;
    }

    /**
     * GET /products/prices — paginated price data.
     *
     * @param  string|null $since  Format d-m-Y
     */
    public function getPrices(?string $since = null): array
    {
        $allPrices = [];
        $page = 1;
        $perPage = 5000; // max allowed

        do {
            $params = ['perPage' => $perPage, 'page' => $page];
            if ($since) {
                $params['since'] = $since;
            }

            $response = $this->buildRequest()
                ->get("{$this->baseUrl}/products/prices", $params);

            if (! $response->successful()) {
                throw new RuntimeException(
                    "Imbretex API GET /products/prices failed: HTTP {$response->status()} - " . substr($response->body(), 0, 300)
                );
            }

            $data = $response->json();
            $prices = $data['prices'] ?? [];

            if (is_array($prices)) {
                $allPrices = array_merge($allPrices, $prices);
            }

            $totalPages = $data['totalNumberPage'] ?? 1;
            $page++;
        } while ($page <= $totalPages);

        return $allPrices;
    }

    /**
     * POST /products/price-stock — get price+stock for specific references (max 150).
     *
     * @param  array $references  e.g. ['BC01T00M', 'BC01T12XL']
     * @return array  Keyed by reference: ['BC01T00M' => ['price' => ..., 'stock' => ..., ...]]
     */
    public function getStockPrice(array $references): array
    {
        if (empty($references)) {
            return [];
        }

        // API limit is 150 references per request
        $chunks = array_chunk($references, 150);
        $allProducts = [];

        foreach ($chunks as $chunk) {
            $response = $this->buildRequest()
                ->post("{$this->baseUrl}/products/price-stock", [
                    'products' => $chunk,
                ]);

            if (! $response->successful()) {
                throw new RuntimeException(
                    "Imbretex API POST /products/price-stock failed: HTTP {$response->status()} - " . substr($response->body(), 0, 300)
                );
            }

            $data = $response->json();
            $products = $data['products'] ?? [];

            if (is_array($products)) {
                $allProducts = array_merge($allProducts, $products);
            }
        }

        return $allProducts;
    }

    /**
     * GET /products/price-stock/{productReference} — price+stock for all variants of a product.
     */
    public function getStockPriceByReference(string $productReference): array
    {
        $response = $this->buildRequest()
            ->get("{$this->baseUrl}/products/price-stock/" . urlencode($productReference));

        if (! $response->successful()) {
            throw new RuntimeException(
                "Imbretex API GET /products/price-stock/{$productReference} failed: HTTP {$response->status()} - " . substr($response->body(), 0, 300)
            );
        }

        return $response->json()['products'] ?? [];
    }

    /**
     * GET /products/deleted — deleted products since date.
     *
     * @param  string $since  Format d-m-Y
     */
    public function getDeletedProducts(string $since): array
    {
        $allProducts = [];
        $page = 1;
        $perPage = 50;

        do {
            $response = $this->buildRequest()
                ->get("{$this->baseUrl}/products/deleted", [
                    'since' => $since,
                    'perPage' => $perPage,
                    'page' => $page,
                ]);

            if (! $response->successful()) {
                throw new RuntimeException(
                    "Imbretex API GET /products/deleted failed: HTTP {$response->status()} - " . substr($response->body(), 0, 300)
                );
            }

            $data = $response->json();
            $products = $data['products'] ?? [];

            if (is_array($products)) {
                $allProducts = array_merge($allProducts, $products);
            }

            $totalPages = $data['totalNumberPage'] ?? 1;
            $page++;
        } while ($page <= $totalPages);

        return $allProducts;
    }

    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    public function isConfigured(): bool
    {
        return ! empty($this->token) && ! empty($this->baseUrl);
    }

    // ─── Internal ─────────────────────────────────────────────────

    private function buildRequest(): PendingRequest
    {
        return Http::timeout(120)
            ->retry(3, 2000, fn ($exception) => $exception instanceof \Illuminate\Http\Client\ConnectionException
                || ($exception instanceof \Illuminate\Http\Client\RequestException && $exception->response->status() >= 500))
            ->withToken($this->token)
            ->acceptJson()
            ->withOptions(['verify' => false]);
    }
}
