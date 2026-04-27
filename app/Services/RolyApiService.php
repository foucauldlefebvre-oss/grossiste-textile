<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class RolyApiService
{
    private string $baseUrl;
    private string $user;
    private string $password;
    private string $company;
    private ?string $token = null;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.roly.base_url', 'https://api.gorfactory.es'), '/');
        $this->user = config('services.roly.username', '');
        $this->password = config('services.roly.password', '');
        $this->company = config('services.roly.company', 'GOR');
    }

    /**
     * POST /Client/Auth
     */
    public function login(): void
    {
        $response = Http::timeout(30)
            ->withOptions(['verify' => false])
            ->acceptJson()
            ->post("{$this->baseUrl}/Client/Auth", [
                'Company' => $this->company,
                'User' => $this->user,
                'Password' => $this->password,
            ]);

        if (! $response->successful()) {
            throw new RuntimeException("Roly login failed: HTTP {$response->status()} - {$response->body()}");
        }

        $json = $response->json();
        $this->token = $json['property'] ?? null;

        if (! $this->token) {
            Log::warning('Roly login: no token in response', ['body' => substr($response->body(), 0, 500)]);
            throw new RuntimeException('Roly login: no token found.');
        }
    }

    /**
     * Test connection.
     */
    public function testConnection(): bool
    {
        $this->login();

        return $this->token !== null;
    }

    public function isConfigured(): bool
    {
        return $this->user !== '' && $this->password !== '';
    }

    /**
     * GET /Client — account info
     */
    public function getClient(): array
    {
        return $this->request('GET', '/Client');
    }

    /**
     * GET /Product/Stock — all stock data
     * Returns: [ "REF" => [ "SIZE_CODE" => [ "STOCK" => int, "BRAND_ORG" => string, ... ] ] ]
     */
    public function getStock(): array
    {
        return $this->request('GET', '/Product/Stock');
    }

    /**
     * GET /Product/Alert — product alerts
     */
    public function getAlerts(): array
    {
        return $this->request('GET', '/Product/Alert');
    }

    /**
     * GET /Order — order info
     */
    public function getOrder(): array
    {
        return $this->request('GET', '/Order');
    }

    public function isAuthenticated(): bool
    {
        return $this->token !== null;
    }

    // ─── Internal ────────────────────────────────────────

    private function request(string $method, string $endpoint, array $query = []): array
    {
        if (! $this->token) {
            $this->login();
        }

        $url = "{$this->baseUrl}{$endpoint}" . ($query ? '?' . http_build_query($query) : '');

        $response = Http::timeout(120)
            ->withOptions(['verify' => false])
            ->withToken($this->token)
            ->acceptJson()
            ->get($url);

        // Auto-retry on 401
        if ($response->status() === 401) {
            $this->login();
            $response = Http::timeout(120)
                ->withOptions(['verify' => false])
                ->withToken($this->token)
                ->acceptJson()
                ->get($url);
        }

        if (! $response->successful()) {
            throw new RuntimeException("Roly API {$method} {$endpoint} failed: HTTP {$response->status()} - " . substr($response->body(), 0, 300));
        }

        return $response->json() ?? [];
    }
}
