<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Toptex API Service (v3).
 *
 * Auth flow: AWS Cognito USER_SRP_AUTH (SRP-6a) → ID token (JWT)
 * API calls: x-toptex-authorization: <JWT> + x-api-key: <API_KEY>
 * Base URL: https://api.toptex.io/v3
 */
class ToptexApiService
{
    private string $apiKey;
    private string $baseUrl;
    private string $username;
    private string $password;
    private string $region;
    private string $userPoolId;
    private string $clientId;

    // Cognito tokens
    private ?string $idToken = null;
    private ?int $tokenExpiresAt = null;

    // SRP-6a constants (3072-bit group)
    private const SRP_N_HEX = 'FFFFFFFFFFFFFFFFC90FDAA22168C234C4C6628B80DC1CD1'
        . '29024E088A67CC74020BBEA63B139B22514A08798E3404DD'
        . 'EF9519B3CD3A431B302B0A6DF25F14374FE1356D6D51C245'
        . 'E485B576625E7EC6F44C42E9A637ED6B0BFF5CB6F406B7ED'
        . 'EE386BFB5A899FA5AE9F24117C4B1FE649286651ECE45B3D'
        . 'C2007CB8A163BF0598DA48361C55D39A69163FA8FD24CF5F'
        . '83655D23DCA3AD961C62F356208552BB9ED529077096966D'
        . '670C354E4ABC9804F1746C08CA18217C32905E462E36CE3B'
        . 'E39E772C180E86039B2783A2EC07A28FB5C55DF06F4C52C9'
        . 'DE2BCBF6955817183995497CEA956AE515D2261898FA0510'
        . '15728E5A8AAAC42DAD33170D04507A33A85521ABDF1CBA64'
        . 'ECFB850458DBEF0A8AEA71575D060C7DB3970F85A6E1E4C7'
        . 'ABF5AE8CDB0933D71E8C94E04A25619DCEE3D2261AD2EE6B'
        . 'F12FFA06D98A0864D87602733EC86A64521F2B18177B200C'
        . 'BBE117577A615D6C770988C0BAD946E208E24FA074E5AB31'
        . '43DB5BFCE0FD108E4B82D120A93AD2CAFFFFFFFFFFFFFFFF';

    private const SRP_G = '2';

    public function __construct()
    {
        $config = config('services.toptex');
        $this->apiKey = $config['api_key'] ?? '';
        $this->baseUrl = rtrim($config['base_url'] ?? 'https://api.toptex.io', '/');
        $this->username = $config['username'] ?? '';
        $this->password = $config['password'] ?? '';
        $this->region = $config['region'] ?? 'eu-central-1';
        $this->userPoolId = $config['user_pool_id'] ?? '';
        $this->clientId = $config['client_id'] ?? '';
    }

    public function isConfigured(): bool
    {
        return ! empty($this->username) && ! empty($this->password) && ! empty($this->clientId);
    }

    // ─── Authentication ──────────────────────────────────────────

    /**
     * Authenticate via Cognito SRP to get JWT token.
     */
    public function authenticate(): void
    {
        $this->cognitoSrpLogin();
    }

    /**
     * Ensure we have a valid token, refreshing if needed.
     */
    private function ensureAuthenticated(): void
    {
        if (! $this->idToken || ($this->tokenExpiresAt && time() >= $this->tokenExpiresAt)) {
            $this->authenticate();
        }
    }

    public function testConnection(): bool
    {
        $this->authenticate();

        return $this->idToken !== null;
    }

    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    // ─── API Calls (v3) ──────────────────────────────────────────

    /**
     * GET /v3/products/all — all catalog products (paginated).
     * Returns items[], total_count, page_number, page_size.
     */
    /**
     * GET /v3/products/all — iterate all products page by page via callback.
     * Uses a callback to avoid loading all 3000+ products into memory at once.
     *
     * @param  callable  $callback  fn(array $products, int $totalCount, int $page)
     */
    public function eachProductPage(callable $callback, string $usageRight = 'b2b_uniquement', int $pageSize = 50): void
    {
        $page = 1;

        do {
            $data = $this->apiGet('/v3/products/all', [
                'usage_right' => $usageRight,
                'page_number' => $page,
                'page_size' => $pageSize,
            ]);

            if (isset($data['errorType'])) {
                break;
            }

            $products = $data['items'] ?? [];
            $totalCount = (int) ($data['total_count'] ?? 0);

            if (! empty($products)) {
                $stop = $callback($products, $totalCount, $page);
                if ($stop === true) {
                    break;
                }
            }

            $hasMore = count($products) >= $pageSize;
            $page++;
        } while ($hasMore);
    }

    /**
     * GET /v3/products/all — get a single page of products.
     */
    public function getProductPage(int $page = 1, int $pageSize = 50, string $usageRight = 'b2b_uniquement'): array
    {
        $data = $this->apiGet('/v3/products/all', [
            'usage_right' => $usageRight,
            'page_number' => $page,
            'page_size' => $pageSize,
        ]);

        if (isset($data['errorType'])) {
            return ['items' => [], 'total_count' => 0];
        }

        return $data;
    }

    /**
     * GET /v3/products — search by SKU or catalog_reference.
     */
    public function getProduct(string $catalogReference): array
    {
        return $this->apiGet('/v3/products', ['catalog_reference' => $catalogReference]);
    }

    /**
     * GET /v3/products/inventory — stocks by Catalog_Ref.
     */
    public function getInventory(string $catalogRef): array
    {
        return $this->extractList($this->apiGet('/v3/products/inventory', [
            'catalog_ref' => $catalogRef,
        ]));
    }

    /**
     * Paginate through all inventory entries.
     * Callback receives (array $items, int $totalCount, int $page).
     * Return true from callback to stop early.
     */
    public function eachInventoryPage(callable $callback, int $pageSize = 50): void
    {
        $page = 1;

        do {
            $data = $this->apiGet('/v3/products/inventory', [
                'page_number' => $page,
                'page_size' => $pageSize,
            ]);

            if (isset($data['errorType'])) {
                break;
            }

            $items = $data['items'] ?? [];
            $totalCount = (int) ($data['total_count'] ?? 0);

            if (! empty($items)) {
                $stop = $callback($items, $totalCount, $page);
                if ($stop === true) {
                    break;
                }
            }

            $hasMore = count($items) >= $pageSize;
            $page++;
        } while ($hasMore);
    }

    /**
     * GET /v3/products/{sku}/inventory — stock by SKU.
     */
    public function getInventoryBySku(string $sku): array
    {
        return $this->apiGet("/v3/products/{$sku}/inventory");
    }

    /**
     * GET /v3/products/price — prices by Catalog_Ref.
     */
    public function getPrice(string $catalogRef): array
    {
        return $this->extractList($this->apiGet('/v3/products/price', [
            'catalog_ref' => $catalogRef,
        ]));
    }

    /**
     * GET /v3/products/{sku}/price — price by SKU.
     */
    public function getPriceBySku(string $sku): array
    {
        return $this->apiGet("/v3/products/{$sku}/price");
    }

    /**
     * GET /v3/products/deleted — deleted products.
     */
    public function getDeletedProducts(): array
    {
        return $this->extractList($this->apiGet('/v3/products/deleted'));
    }

    /**
     * GET /v3/attributes — get attributes (brand, family, subfamily).
     */
    public function getAttributes(string $attributes = 'brand,family,subfamily'): array
    {
        $data = $this->apiGet('/v3/attributes', [
            'attributes' => $attributes,
            'page_size' => 1000,
            'page_number' => 1,
        ]);

        return $data['items'] ?? [];
    }

    /**
     * Batch get inventory for multiple catalog refs.
     */
    public function getStockPrice(array $catalogRefs): array
    {
        $allResults = [];

        foreach ($catalogRefs as $ref) {
            try {
                $inventory = $this->getInventory($ref);
                $allResults = array_merge($allResults, $inventory);
            } catch (\Throwable $e) {
                // Skip failed refs
            }
        }

        return $allResults;
    }

    // ─── HTTP Layer ──────────────────────────────────────────────

    /**
     * Make authenticated GET request to the Toptex API.
     */
    private function apiGet(string $path, array $queryParams = []): array
    {
        $this->ensureAuthenticated();

        $url = $this->baseUrl . $path;

        $response = Http::timeout(120)
            ->withOptions(['verify' => false])
            ->withHeaders($this->buildHeaders())
            ->get($url, $queryParams);

        // Auto-retry on 401 (expired token)
        if ($response->status() === 401) {
            $this->idToken = null;
            $this->ensureAuthenticated();
            $response = Http::timeout(120)
                ->withOptions(['verify' => false])
                ->withHeaders($this->buildHeaders())
                ->get($url, $queryParams);
        }

        if (! $response->successful()) {
            throw new RuntimeException(
                "Toptex API GET {$path} failed: HTTP {$response->status()} - " . substr($response->body(), 0, 300)
            );
        }

        return $response->json() ?? [];
    }

    /**
     * Make authenticated POST request to the Toptex API.
     */
    private function apiPost(string $path, array $body = []): array
    {
        $this->ensureAuthenticated();

        $url = $this->baseUrl . $path;

        $response = Http::timeout(120)
            ->withOptions(['verify' => false])
            ->withHeaders($this->buildHeaders())
            ->post($url, $body);

        if ($response->status() === 401) {
            $this->idToken = null;
            $this->ensureAuthenticated();
            $response = Http::timeout(120)
                ->withOptions(['verify' => false])
                ->withHeaders($this->buildHeaders())
                ->post($url, $body);
        }

        if (! $response->successful()) {
            throw new RuntimeException(
                "Toptex API POST {$path} failed: HTTP {$response->status()} - " . substr($response->body(), 0, 300)
            );
        }

        return $response->json() ?? [];
    }

    /**
     * Build API request headers: JWT token + API key.
     */
    private function buildHeaders(): array
    {
        return [
            'x-toptex-authorization' => $this->idToken,
            'x-api-key' => $this->apiKey,
            'Accept' => 'application/json',
        ];
    }

    /**
     * Extract list from API response (handles various wrapper formats).
     */
    private function extractList(array $data): array
    {
        // Direct array of items
        if (isset($data[0])) {
            return $data;
        }

        // Wrapped in known keys
        return $data['products'] ?? $data['items'] ?? $data['data'] ?? $data['results'] ?? $data['inventories'] ?? $data['prices'] ?? [];
    }

    // ─── Cognito SRP-6a Authentication ───────────────────────────

    /**
     * Cognito USER_SRP_AUTH (SRP-6a protocol) to get ID token (JWT).
     * Implementation follows amazon-cognito-identity-js exactly.
     */
    private function cognitoSrpLogin(): void
    {
        $cognitoUrl = "https://cognito-idp.{$this->region}.amazonaws.com/";
        $N = gmp_init(self::SRP_N_HEX, 16);
        $g = gmp_init(self::SRP_G, 10);

        // k = H(pad(N) | pad(g)) — exact AWS format
        $kHex = $this->srpHexHash('00' . self::SRP_N_HEX . '0' . gmp_strval($g, 16));
        $k = gmp_init($kHex, 16);

        // Generate random a (128 bytes as in AWS SDK), compute A = g^a mod N
        $aHex = bin2hex(random_bytes(128));
        $a = gmp_mod(gmp_init($aHex, 16), $N);
        $A = gmp_powm($g, $a, $N);

        if (gmp_cmp(gmp_mod($A, $N), gmp_init(0)) === 0) {
            throw new RuntimeException('SRP safety check failed: A mod N = 0');
        }

        $AHex = gmp_strval($A, 16);

        // Step 1: InitiateAuth
        $response = Http::timeout(30)
            ->withOptions(['verify' => false])
            ->withHeaders([
                'Content-Type' => 'application/x-amz-json-1.1',
                'X-Amz-Target' => 'AWSCognitoIdentityProviderService.InitiateAuth',
            ])
            ->post($cognitoUrl, [
                'AuthFlow' => 'USER_SRP_AUTH',
                'ClientId' => $this->clientId,
                'AuthParameters' => [
                    'USERNAME' => $this->username,
                    'SRP_A' => $AHex,
                ],
            ]);

        if (! $response->successful()) {
            $body = $response->json();
            $error = $body['message'] ?? $body['__type'] ?? $response->body();
            throw new RuntimeException("Toptex Cognito SRP InitiateAuth failed: {$error}");
        }

        $data = $response->json();
        $challengeName = $data['ChallengeName'] ?? null;

        if ($challengeName !== 'PASSWORD_VERIFIER') {
            throw new RuntimeException("Toptex Cognito: expected PASSWORD_VERIFIER, got: {$challengeName}");
        }

        $challengeParams = $data['ChallengeParameters'];
        $srpBHex = $challengeParams['SRP_B'];
        $saltHex = $challengeParams['SALT'];
        $secretBlock = $challengeParams['SECRET_BLOCK'];
        $userIdForSrp = $challengeParams['USER_ID_FOR_SRP'];

        $B = gmp_init($srpBHex, 16);

        if (gmp_cmp(gmp_mod($B, $N), gmp_init(0)) === 0) {
            throw new RuntimeException('SRP safety check failed: B mod N = 0');
        }

        // u = H(padHex(A) | padHex(B))
        $uHex = $this->srpHexHash($this->srpPadHex($AHex) . $this->srpPadHex($srpBHex));
        $u = gmp_init($uHex, 16);

        if (gmp_cmp($u, gmp_init(0)) === 0) {
            throw new RuntimeException('SRP safety check failed: u = 0');
        }

        // Password hash: H(poolName | userId | ":" | password)
        $poolName = explode('_', $this->userPoolId, 2)[1] ?? $this->userPoolId;
        $usernamePasswordHash = hash('sha256', $poolName . $userIdForSrp . ':' . $this->password);

        // x = H(padHex(salt) | usernamePasswordHash)
        $xHex = $this->srpHexHash($this->srpPadHex($saltHex) . $usernamePasswordHash);
        $x = gmp_init($xHex, 16);

        // S = (B - k * g^x mod N) ^ (a + u * x) mod N
        $gx = gmp_powm($g, $x, $N);
        $kgx = gmp_mod(gmp_mul($k, $gx), $N);
        $diff = gmp_mod(gmp_add(gmp_sub($B, $kgx), $N), $N);
        $exp = gmp_add($a, gmp_mul($u, $x));
        $S = gmp_powm($diff, $exp, $N);

        // HKDF: derive key using padHex(u) as salt, padHex(S) as ikm
        $ikmBytes = hex2bin($this->srpPadHex(gmp_strval($S, 16)));
        $saltBytes = hex2bin($this->srpPadHex(gmp_strval($u, 16)));
        $prk = hash_hmac('sha256', $ikmBytes, $saltBytes, true);
        $derivedKey = hash_hmac('sha256', 'Caldera Derived Key' . chr(1), $prk, true);
        $derivedKey = substr($derivedKey, 0, 16);

        // Timestamp + signature
        $timestamp = gmdate('D M j H:i:s \U\T\C Y');
        $msg = $poolName . $userIdForSrp . base64_decode($secretBlock) . $timestamp;
        $signature = base64_encode(hash_hmac('sha256', $msg, $derivedKey, true));

        // Step 2: RespondToAuthChallenge
        $response = Http::timeout(30)
            ->withOptions(['verify' => false])
            ->withHeaders([
                'Content-Type' => 'application/x-amz-json-1.1',
                'X-Amz-Target' => 'AWSCognitoIdentityProviderService.RespondToAuthChallenge',
            ])
            ->post($cognitoUrl, [
                'ChallengeName' => 'PASSWORD_VERIFIER',
                'ClientId' => $this->clientId,
                'ChallengeResponses' => [
                    'USERNAME' => $userIdForSrp,
                    'PASSWORD_CLAIM_SECRET_BLOCK' => $secretBlock,
                    'PASSWORD_CLAIM_SIGNATURE' => $signature,
                    'TIMESTAMP' => $timestamp,
                ],
            ]);

        if (! $response->successful()) {
            $body = $response->json();
            $error = $body['message'] ?? $body['__type'] ?? $response->body();
            throw new RuntimeException("Toptex Cognito SRP challenge failed: {$error}");
        }

        $data = $response->json();

        if (isset($data['ChallengeName'])) {
            throw new RuntimeException("Toptex Cognito challenge: {$data['ChallengeName']}. Connectez-vous d'abord sur portal.toptex.io.");
        }

        $result = $data['AuthenticationResult'] ?? null;
        if (! $result || ! isset($result['IdToken'])) {
            throw new RuntimeException('Toptex Cognito SRP: pas de token dans la reponse.');
        }

        $this->idToken = $result['IdToken'];
        $this->tokenExpiresAt = time() + ($result['ExpiresIn'] ?? 3600) - 300;
    }

    private function srpPadHex(string $hex): string
    {
        if (strlen($hex) % 2 !== 0) {
            $hex = '0' . $hex;
        }
        if (preg_match('/^[89a-fA-F]/', $hex)) {
            $hex = '00' . $hex;
        }

        return $hex;
    }

    private function srpHexHash(string $hexData): string
    {
        return hash('sha256', hex2bin($hexData));
    }
}
