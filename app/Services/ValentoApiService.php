<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class ValentoApiService
{
    private string $baseUrl;
    private string $username;
    private string $password;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.valento.base_url', ''), '/');
        $this->username = config('services.valento.username', '');
        $this->password = config('services.valento.password', '');
    }

    public function isConfigured(): bool
    {
        return ! empty($this->username) && ! empty($this->password) && ! empty($this->baseUrl);
    }

    /**
     * Test connection by fetching stock for a known reference.
     */
    public function testConnection(): bool
    {
        // Use a single reference request (not full catalog, which is restricted to 3h-6h)
        $response = $this->request()
            ->get("{$this->baseUrl}/stock.php?ALVARELBL20");

        return $response->successful();
    }

    /**
     * GET /stock.php?{reference} — stock for a single reference.
     *
     * @return array<int, array{cref: string, stock: int}>
     */
    public function getStock(string $reference): array
    {
        $response = $this->request()
            ->get("{$this->baseUrl}/stock.php?{$reference}");

        if (! $response->successful()) {
            throw new RuntimeException(
                "Valento API GET /stock.php?{$reference} failed: HTTP {$response->status()} - " . substr($response->body(), 0, 300)
            );
        }

        $data = $response->json() ?? [];

        return $data['catalog'] ?? $data;
    }

    /**
     * GET /stock.php — full stock catalog.
     * Restriction: only between 3h-6h, max 5x/hour, 10x/day.
     *
     * @return array<int, array{cref: string, stock: int}>
     */
    public function getAllStock(): array
    {
        $response = $this->request()
            ->timeout(300)
            ->get("{$this->baseUrl}/stock.php");

        if (! $response->successful()) {
            throw new RuntimeException(
                "Valento API GET /stock.php (full) failed: HTTP {$response->status()} - " . substr($response->body(), 0, 300)
            );
        }

        $data = $response->json() ?? [];

        // Response is a flat array: [{cref, stock}, ...]
        return is_array($data) && isset($data[0]) ? $data : ($data['catalog'] ?? $data);
    }

    /**
     * GET /price.php?{reference} or /price.php?{reference}&{quantity}
     */
    public function getPrice(string $reference, ?int $quantity = null): array
    {
        $url = "{$this->baseUrl}/price.php?{$reference}";
        if ($quantity !== null) {
            $url .= "&{$quantity}";
        }

        $response = $this->request()
            ->get($url);

        if (! $response->successful()) {
            throw new RuntimeException(
                "Valento API GET /price.php?{$reference} failed: HTTP {$response->status()} - " . substr($response->body(), 0, 300)
            );
        }

        return $response->json() ?? [];
    }

    /**
     * GET /price.php — all base prices.
     * Restriction: max 100x/hour, 1000x/day.
     *
     * @return array<int, array{cref: string, price_for_0_uds: float}>
     */
    public function getAllPrices(): array
    {
        $response = $this->request()
            ->timeout(300)
            ->get("{$this->baseUrl}/price.php");

        if (! $response->successful()) {
            throw new RuntimeException(
                "Valento API GET /price.php (full) failed: HTTP {$response->status()} - " . substr($response->body(), 0, 300)
            );
        }

        $data = $response->json() ?? [];

        // Response: {catalog: [{cref, price_for_0_uds}, ...]}
        return $data['catalog'] ?? (is_array($data) && isset($data[0]) ? $data : []);
    }

    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    // ─── Internal ─────────────────────────────────────────────────

    private function request(): \Illuminate\Http\Client\PendingRequest
    {
        return Http::withBasicAuth($this->username, $this->password)
            ->timeout(120)
            ->retry(2, 3000, fn ($e) => $e instanceof \Illuminate\Http\Client\ConnectionException)
            ->acceptJson()
            ->withOptions(['verify' => false]);
    }
}
