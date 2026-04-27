<?php
ini_set('memory_limit', '256M');

$token = 'gT_E)P{IT*c_qJ@v??OGX3pPC%mX@}MTh4XL_;M^#%;JO$Thap7Ljk}KBD^K';
$base = 'https://api.imbretex.fr/api';

function api_get($url, $token) {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_TIMEOUT => 60,
        CURLOPT_HTTPHEADER => ["Accept: application/json", "Authorization: Bearer $token"],
    ]);
    $body = curl_exec($ch);
    curl_close($ch);
    return json_decode($body, true);
}

// 1. Get a B&C product with full structure
echo "=== SAMPLE B&C PRODUCT (full structure) ===\n";
$data = api_get("$base/products/products?perPage=50&page=1", $token);
$bcProduct = null;
foreach ($data['products'] ?? [] as $p) {
    if (($p['brands']['name'] ?? '') === 'B&C') {
        $bcProduct = $p;
        break;
    }
}
if (!$bcProduct) {
    // Search further
    for ($pg = 2; $pg <= 70 && !$bcProduct; $pg++) {
        $data = api_get("$base/products/products?perPage=50&page=$pg", $token);
        foreach ($data['products'] ?? [] as $p) {
            if (($p['brands']['name'] ?? '') === 'B&C') {
                $bcProduct = $p;
                break 2;
            }
        }
    }
}
if ($bcProduct) {
    echo json_encode($bcProduct, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
}

// 2. Price structure for that product
echo "\n=== PRICE-STOCK for " . ($bcProduct['reference'] ?? '?') . " ===\n";
$ref = $bcProduct['reference'] ?? 'BC01T';
$ps = api_get("$base/products/price-stock/$ref", $token);
echo json_encode(array_slice($ps['products'] ?? [], 0, 3), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";

// 3. Price from /prices endpoint
echo "\n=== PRICES ENDPOINT (sample) ===\n";
$prices = api_get("$base/products/prices?perPage=3&page=1", $token);
echo json_encode(array_slice($prices['prices'] ?? [], 0, 2), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";

// 4. Stock from /stocks endpoint
echo "\n=== STOCKS ENDPOINT (sample) ===\n";
$stocks = api_get("$base/products/stocks?perPage=3&page=1", $token);
echo json_encode(array_slice($stocks['stocks'] ?? [], 0, 2), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
