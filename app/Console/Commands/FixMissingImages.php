<?php

namespace App\Console\Commands;

use App\Models\Product;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FixMissingImages extends Command
{
    protected $signature = 'products:fix-images
        {--dry-run : Show what would be done without modifying anything}';

    protected $description = 'Find and download images for products that have no image, using PrestaShop IDs and search';

    private Client $http;

    private string $baseUrl = 'https://www.marquage-textile.fr';

    private int $requestCount = 0;

    private array $prefixToBrand = [
        'RO' => 'Roly', 'SO' => "Sol's", 'AW' => 'Awdis', 'KA' => 'Kariban',
        'BC' => "B&C", 'SS' => 'Stanley/Stella', 'FL' => 'Fruit of the Loom',
        'RE' => 'Result', 'GI' => 'Gildan', 'VA' => 'Valento', 'BB' => 'BabyBugz',
        'NI' => 'Nimbus', 'RU' => 'Russell', 'AD' => 'Adidas', 'NK' => 'Nike',
        'RT' => 'ProRTX', 'SG' => 'SG', 'CO' => 'Continental Clothing',
        'FO' => 'Fruit of the Loom', 'FR' => 'Front Row',
    ];

    private array $stats = [
        'total_missing' => 0,
        'found_by_presta_ref' => 0,
        'found_by_search' => 0,
        'images_downloaded' => 0,
        'images_failed' => 0,
        'references_fixed' => 0,
        'suppliers_fixed' => 0,
        'not_found' => 0,
    ];

    public function handle(): int
    {
        $this->http = new Client([
            'timeout' => 30,
            'verify' => false,
            'http_errors' => false,
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (compatible; MigrationBot/1.0)',
                'Accept-Language' => 'fr-FR,fr;q=0.9',
            ],
        ]);

        $dryRun = $this->option('dry-run');
        if ($dryRun) {
            $this->info('=== DRY RUN MODE ===');
        }

        $products = Product::where(function ($q) {
            $q->whereNull('main_image')->orWhere('main_image', '');
        })->get();

        $this->stats['total_missing'] = $products->count();
        $this->info("Found {$products->count()} products without images");

        $bar = $this->output->createProgressBar($products->count());
        $bar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %message%');
        $bar->setMessage('Starting...');
        $bar->start();

        foreach ($products as $product) {
            $bar->setMessage(Str::limit($product->name, 30));

            $result = $this->findOnOldSite($product);

            if ($result) {
                // Download image
                if ($result['image_url']) {
                    $this->downloadImage($product, $result['image_url'], $dryRun);
                }

                // Fix reference if it's still a PrestaShop internal ref
                if ($result['reference'] && $this->isInternalRef($product->reference)) {
                    if (! $dryRun) {
                        // Check uniqueness
                        $exists = Product::where('reference', $result['reference'])
                            ->where('id', '!=', $product->id)->exists();
                        if (! $exists) {
                            $product->reference = $result['reference'];
                            $this->stats['references_fixed']++;
                        }
                    } else {
                        $this->stats['references_fixed']++;
                    }
                }

                // Fix supplier
                if (empty($product->supplier) && $result['reference']) {
                    $supplier = $this->guessSupplier($result['reference']);
                    if ($supplier) {
                        if (! $dryRun) {
                            $product->supplier = $supplier;
                        }
                        $this->stats['suppliers_fixed']++;
                    }
                }

                // Set prestashop_id
                if (! $product->prestashop_id && $result['presta_id']) {
                    if (! $dryRun) {
                        $existing = Product::where('prestashop_id', $result['presta_id'])
                            ->where('id', '!=', $product->id)->exists();
                        if (! $existing) {
                            $product->prestashop_id = $result['presta_id'];
                        }
                    }
                }

                if (! $dryRun) {
                    try {
                        $product->save();
                    } catch (\Exception $e) {
                        Log::error("[fix-images] Save failed: {$product->name}: {$e->getMessage()}");
                    }
                }
            } else {
                $this->stats['not_found']++;
                Log::info("[fix-images] Not found: {$product->name} (ref: {$product->reference})");
            }

            $bar->advance();
        }

        $bar->setMessage('Done!');
        $bar->finish();
        $this->info('');

        // Update products.php
        if (! $dryRun) {
            $this->syncProductsFile();
        }

        $this->info('');
        $this->info('=== Summary ===');
        foreach ($this->stats as $key => $val) {
            $this->line('  ' . str_replace('_', ' ', $key) . ': ' . $val);
        }

        return self::SUCCESS;
    }

    /**
     * Try to find a product on the old site.
     * Strategy 1: Use the NNN-NNNNN reference (first number = prestashop product ID)
     * Strategy 2: Search the old site by product name
     */
    private function findOnOldSite(Product $product): ?array
    {
        // Strategy 1: Parse prestashop ID from NNN-NNNNN reference
        if (preg_match('/^(\d{1,4})-\d{4,6}$/', $product->reference, $m)) {
            $prestaId = (int) $m[1];
            $result = $this->fetchProductByPrestaId($prestaId);
            if ($result) {
                $this->stats['found_by_presta_ref']++;
                return $result;
            }
        }

        // Strategy 2: Search by name on old site
        $searchTerms = $this->getSearchTerms($product->name);
        foreach ($searchTerms as $term) {
            $result = $this->searchOldSite($term);
            if ($result) {
                $this->stats['found_by_search']++;
                return $result;
            }
        }

        return null;
    }

    /**
     * Fetch product data by PrestaShop ID by trying to access the product page directly.
     */
    private function fetchProductByPrestaId(int $prestaId): ?array
    {
        // PrestaShop URLs: /fr/accueil/{id}-{slug}.html
        // We don't know the slug, but we can try the ID with any slug — PS redirects
        $url = "{$this->baseUrl}/fr/accueil/{$prestaId}-.html";
        $html = $this->fetch($url);

        if (! $html || strlen($html) < 1000) {
            return null;
        }

        // Check if this is actually a product page (not 404/redirect to home)
        if (! str_contains($html, 'productReference') && ! str_contains($html, 'idDefaultImage')) {
            return null;
        }

        return $this->extractProductData($html, $prestaId);
    }

    /**
     * Search the old site for a product by name.
     */
    private function searchOldSite(string $query): ?array
    {
        $url = "{$this->baseUrl}/fr/recherche?search_query=" . urlencode($query) . "&submit_search=";
        $html = $this->fetch($url);

        if (! $html) return null;

        // Extract the first product link from results
        if (preg_match('#/fr/accueil/(\d+)-([^"&]+)\.html#', $html, $m)) {
            $prestaId = (int) $m[1];
            $productUrl = "{$this->baseUrl}/fr/accueil/{$m[1]}-{$m[2]}.html";

            $productHtml = $this->fetch($productUrl);
            if ($productHtml && str_contains($productHtml, 'productReference')) {
                return $this->extractProductData($productHtml, $prestaId);
            }
        }

        return null;
    }

    /**
     * Extract product reference and image from a product detail page.
     */
    private function extractProductData(string $html, int $prestaId): ?array
    {
        $data = [
            'presta_id' => $prestaId,
            'reference' => null,
            'image_url' => null,
        ];

        if (preg_match("/var\s+productReference\s*=\s*'([^']+)'/", $html, $m)) {
            $ref = trim($m[1]);
            if ($ref && $ref !== 'undefined') {
                $data['reference'] = $ref;
            }
        }

        if (preg_match("/var\s+sharing_img\s*=\s*'([^']+)'/", $html, $m)) {
            $data['image_url'] = $m[1];
        }

        if (! $data['image_url'] && preg_match("/var\s+idDefaultImage\s*=\s*(\d+)/", $html, $m)) {
            $data['image_url'] = "{$this->baseUrl}/{$m[1]}-large_default/.jpg";
        }

        if (! $data['image_url'] && ! $data['reference']) {
            return null;
        }

        return $data;
    }

    /**
     * Get search terms from a product name (try multiple variations).
     */
    private function getSearchTerms(string $name): array
    {
        $terms = [];

        // Remove common prefixes/suffixes
        $clean = preg_replace('/\b(T-shirt|Polo|Sweat|Chemise|Blouse|Veste|Pantalon|Debardeur|Maillot)\b/i', '', $name);
        $clean = trim($clean);

        // Use the most distinctive part of the name
        if (strlen($clean) > 2) {
            $terms[] = $clean;
        }

        // Also try first meaningful word
        $words = preg_split('/\s+/', $clean);
        foreach ($words as $word) {
            if (strlen($word) > 3 && ! in_array(strtolower($word), ['adulte', 'enfant', 'femme', 'homme', 'sport', 'coton', 'polyester'])) {
                $terms[] = $word;
                break;
            }
        }

        return array_unique(array_filter($terms));
    }

    private function downloadImage(Product $product, string $imageUrl, bool $dryRun): void
    {
        if (! str_starts_with($imageUrl, 'http')) {
            $imageUrl = $this->baseUrl . $imageUrl;
        }

        $extension = pathinfo(parse_url($imageUrl, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'jpg';
        $filename = "products/{$product->slug}.{$extension}";

        if ($dryRun) {
            $this->stats['images_downloaded']++;
            return;
        }

        try {
            $response = $this->http->get($imageUrl);

            if ($response->getStatusCode() >= 400) {
                if (str_contains($imageUrl, 'large_default')) {
                    $fallback = str_replace('large_default', 'home_default', $imageUrl);
                    $this->downloadImage($product, $fallback, $dryRun);
                    return;
                }
                $this->stats['images_failed']++;
                return;
            }

            $content = $response->getBody()->getContents();
            if (strlen($content) < 1000) {
                $this->stats['images_failed']++;
                return;
            }

            Storage::disk('public')->put($filename, $content);
            $product->main_image = $filename;
            $this->stats['images_downloaded']++;
        } catch (\Exception $e) {
            $this->stats['images_failed']++;
        }
    }

    private function isInternalRef(?string $ref): bool
    {
        if (empty($ref)) return true;
        return (bool) preg_match('/^\d{1,4}-\d{4,6}/', $ref);
    }

    private function guessSupplier(?string $ref): ?string
    {
        if (empty($ref) || strlen($ref) < 2) return null;
        return $this->prefixToBrand[strtoupper(substr($ref, 0, 2))] ?? null;
    }

    private function syncProductsFile(): void
    {
        $filePath = database_path('seeders/data/products.php');
        if (! file_exists($filePath)) return;

        $data = require $filePath;
        $dbProducts = Product::all();
        $dbByName = [];
        foreach ($dbProducts as $p) {
            $key = mb_strtolower(preg_replace('/[^\p{L}\p{N}]/u', '', $p->name));
            $dbByName[$key] = $p;
        }

        $updated = 0;
        foreach ($data as $catSlug => &$products) {
            foreach ($products as &$entry) {
                $key = mb_strtolower(preg_replace('/[^\p{L}\p{N}]/u', '', $entry[0]));
                $dbP = $dbByName[$key] ?? null;
                if (! $dbP) continue;

                $fileRef = $entry[1] ?? '';
                $dbRef = $dbP->reference ?? '';

                if ($dbRef && $fileRef !== $dbRef && $this->isInternalRef($fileRef)) {
                    if (! $this->isInternalRef($dbRef)) {
                        $entry[1] = $dbRef;
                        $updated++;
                    }
                }

                if (empty($entry[2]) && ! empty($dbP->supplier)) {
                    $entry[2] = $dbP->supplier;
                }
            }
            unset($entry);
        }
        unset($products);

        if ($updated > 0) {
            // Regenerate file
            $totalProducts = 0;
            foreach ($data as $prods) $totalProducts += count($prods);

            $output = "<?php\n\n/**\n * Donnees produits pour CategoryProductSeeder\n * Genere depuis les donnees scrappees de marquage-textile.fr\n * Format: [nom, reference, marque, composition, grammage (g/m2), coupe, prix HT (EUR)]\n *\n * Total: {$totalProducts} produits dans " . count($data) . " categories\n */\n\nreturn [\n";

            foreach ($data as $catSlug => $prods) {
                $count = count($prods);
                $label = strtoupper(str_replace('-', ' ', $catSlug));
                $output .= "\n    // ===== {$label} ({$count}) =====\n";
                $output .= "    '{$catSlug}' => [\n";
                foreach ($prods as $e) {
                    $n = addcslashes($e[0] ?? '', "'\\");
                    $r = addcslashes($e[1] ?? '', "'\\");
                    $s = addcslashes($e[2] ?? '', "'\\");
                    $m = addcslashes($e[3] ?? '', "'\\");
                    $g = ($e[4] === null) ? 'null' : (int) $e[4];
                    $c = addcslashes($e[5] ?? 'mixte', "'\\");
                    $p = number_format((float) ($e[6] ?? 0), 2, '.', '');
                    $output .= "        ['{$n}', '{$r}', '{$s}', '{$m}', {$g}, '{$c}', {$p}],\n";
                }
                $output .= "    ],\n";
            }
            $output .= "];\n";

            file_put_contents($filePath, $output);
            $this->info("  Updated {$updated} more entries in products.php");
        }
    }

    private function fetch(string $url): ?string
    {
        if ($this->requestCount > 0) usleep(300_000);
        $this->requestCount++;

        for ($attempt = 1; $attempt <= 3; $attempt++) {
            try {
                $response = $this->http->get($url);
                if ($response->getStatusCode() < 500) {
                    return $response->getBody()->getContents() ?: null;
                }
                if ($attempt < 3) sleep($attempt);
            } catch (\Exception $e) {
                if ($attempt >= 3) return null;
                sleep($attempt);
            }
        }
        return null;
    }
}
