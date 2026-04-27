<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Product;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FixProductData extends Command
{
    protected $signature = 'products:fix
        {--dry-run : Show what would be done without modifying anything}
        {--skip-images : Skip missing image downloads}
        {--skip-references : Skip reference correction}
        {--skip-file-update : Do not write changes back to products.php}
        {--category= : Only process a specific category slug}';

    protected $description = 'Fix product references, download missing images, complete prestashop_id and update products.php';

    private Client $http;

    private string $baseUrl = 'https://www.marquage-textile.fr';

    private int $requestCount = 0;

    private array $brandCodes = [
        'Roly' => 'RO',
        "Sol's" => 'SO',
        "SOL'S" => 'SO',
        'ATF' => 'SO',
        'Awdis' => 'AW',
        'AWDis' => 'AW',
        'Kariban' => 'KA',
        'B&C' => 'BC',
        'Stanley/Stella' => 'SS',
        'Fruit of the Loom' => 'FL',
        'Result' => 'RE',
        'Gildan' => 'GI',
        'Valento' => 'VA',
        'BabyBugz' => 'BB',
        'BabyBugz/Stanley Stella' => 'BB',
        'Nimbus' => 'NI',
        'Russell' => 'RU',
        'Adidas' => 'AD',
        'Nike' => 'NK',
        'ProRTX' => 'RT',
        'SG' => 'SG',
        'Continental Clothing' => 'CO',
        'BBR' => 'BR',
        'Front Row' => 'FR',
        'Clique' => 'CL',
        'Hymalaya' => 'HY',
    ];

    private array $prefixToBrand = [
        'RO' => 'Roly',
        'SO' => "Sol's",
        'AW' => 'Awdis',
        'KA' => 'Kariban',
        'BC' => "B&C",
        'SS' => 'Stanley/Stella',
        'FL' => 'Fruit of the Loom',
        'RE' => 'Result',
        'GI' => 'Gildan',
        'VA' => 'Valento',
        'BB' => 'BabyBugz',
        'NI' => 'Nimbus',
        'RU' => 'Russell',
        'AD' => 'Adidas',
        'NK' => 'Nike',
        'RT' => 'ProRTX',
        'SG' => 'SG',
        'CO' => 'Continental Clothing',
        'BR' => 'BBR',
        'FR' => 'Front Row',
        'CL' => 'Clique',
        'HY' => 'Hymalaya',
        'FO' => 'Fruit of the Loom',
    ];

    private array $stats = [
        'products_scraped' => 0,
        'products_matched' => 0,
        'products_unmatched' => 0,
        'references_fixed' => 0,
        'references_already_ok' => 0,
        'suppliers_filled' => 0,
        'images_downloaded' => 0,
        'images_already_ok' => 0,
        'images_failed' => 0,
        'prestashop_ids_set' => 0,
        'file_entries_updated' => 0,
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
            $this->info('=== DRY RUN MODE — no changes will be made ===');
        }

        // Phase 1: Scrape old site to get correct data
        $this->info('');
        $this->info('Phase 1: Scraping old site for references and images...');
        $scrapedProducts = $this->scrapeAllProducts();

        // Phase 2: Load DB products and match
        $this->info('');
        $this->info('Phase 2: Matching and fixing products...');
        $corrections = $this->matchAndFix($scrapedProducts, $dryRun);

        // Phase 3: Sync products.php file with DB data
        if (! $this->option('skip-file-update')) {
            $this->info('');
            $this->info('Phase 3: Syncing products.php with database...');
            $this->syncProductsFile($dryRun);
        }

        // Summary
        $this->info('');
        $this->info('=== Summary ===');
        foreach ($this->stats as $key => $val) {
            $label = str_replace('_', ' ', $key);
            $this->line("  {$label}: {$val}");
        }

        return self::SUCCESS;
    }

    /**
     * Scrape all products from the old site.
     * Returns array keyed by presta_id with reference, image URL, name, slug.
     */
    private function scrapeAllProducts(): array
    {
        $filterSlug = $this->option('category');

        // Get category links from homepage
        $html = $this->fetch("{$this->baseUrl}/fr/");
        if (! $html) {
            $this->error('Failed to fetch old site homepage');
            return [];
        }

        $categoryLinks = [];
        if (preg_match_all('#href="https?://www\.marquage-textile\.fr/fr/(\d+)-([^"]+)"#', $html, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $m) {
                $prestaId = (int) $m[1];
                $slug = $m[2];
                if ($prestaId > 1 && ! isset($categoryLinks[$prestaId])) {
                    $categoryLinks[$prestaId] = [
                        'url' => "{$this->baseUrl}/fr/{$prestaId}-{$slug}",
                        'slug' => $slug,
                    ];
                }
            }
        }

        $this->info("  Found " . count($categoryLinks) . " categories on old site");

        // Scrape all product listings
        $allProducts = [];
        $processedCategories = [];

        foreach ($categoryLinks as $prestaId => $catData) {
            if ($filterSlug && ! str_contains($catData['slug'], $filterSlug)) {
                continue;
            }

            if (isset($processedCategories[$prestaId])) {
                continue;
            }
            $processedCategories[$prestaId] = true;

            $this->line("  Scraping category: {$catData['slug']}");
            $page = 1;

            while (true) {
                $url = $catData['url'] . ($page > 1 ? "?p={$page}" : '');
                $html = $this->fetch($url);
                if (! $html) break;

                $products = $this->extractProductsFromListing($html);
                if (empty($products)) break;

                foreach ($products as $p) {
                    if (isset($allProducts[$p['presta_id']])) continue;
                    $allProducts[$p['presta_id']] = $p;
                }

                $hasMore = str_contains($html, "?p=" . ($page + 1));
                if (! $hasMore) break;
                $page++;
            }
        }

        $this->stats['products_scraped'] = count($allProducts);
        $this->info("  Total scraped: " . count($allProducts) . " products");

        // Now scrape each product detail page for reference and image
        $this->info('');
        $this->info('  Scraping product detail pages for references...');
        $bar = $this->output->createProgressBar(count($allProducts));
        $bar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %message%');
        $bar->setMessage('Starting...');
        $bar->start();

        foreach ($allProducts as $prestaId => &$product) {
            $bar->setMessage(Str::limit($product['name'], 30));

            $detail = $this->scrapeProductDetail($product['url']);
            if ($detail) {
                $product['reference'] = $detail['reference'];
                $product['large_image'] = $detail['large_image'];
            } else {
                $product['reference'] = null;
                $product['large_image'] = null;
            }

            $bar->advance();
        }
        unset($product);

        $bar->setMessage('Done!');
        $bar->finish();
        $this->info('');

        return $allProducts;
    }

    /**
     * Match scraped products to DB and apply fixes.
     * Returns array of corrections for file update.
     */
    private function matchAndFix(array $scrapedProducts, bool $dryRun): array
    {
        $dbProducts = Product::with('category')->get();

        // Build lookup indexes
        $byName = [];
        $bySlug = [];
        $byPrestaId = [];
        foreach ($dbProducts as $p) {
            $byName[$this->normalizeName($p->name)] = $p;
            $bySlug[$p->slug] = $p;
            if ($p->prestashop_id) {
                $byPrestaId[$p->prestashop_id] = $p;
            }
        }

        $corrections = []; // [product_id => ['old_ref' => ..., 'new_ref' => ..., 'supplier' => ...]]
        $usedReferences = [];

        // Collect all current references to check uniqueness
        foreach ($dbProducts as $p) {
            $usedReferences[$p->reference] = $p->id;
        }

        $bar = $this->output->createProgressBar(count($scrapedProducts));
        $bar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %message%');
        $bar->setMessage('Matching...');
        $bar->start();

        foreach ($scrapedProducts as $prestaId => $scraped) {
            $bar->setMessage(Str::limit($scraped['name'], 30));

            // Match to DB product
            $dbProduct = $this->matchProduct($scraped, $byName, $bySlug, $byPrestaId);

            if (! $dbProduct) {
                $this->stats['products_unmatched']++;
                Log::info("[products:fix] Unmatched: {$scraped['name']} (PS#{$prestaId})");
                $bar->advance();
                continue;
            }

            $this->stats['products_matched']++;
            $correction = ['product_name' => $dbProduct->name];

            // Fix prestashop_id
            if (! $dbProduct->prestashop_id) {
                $correction['prestashop_id'] = $prestaId;
                if (! $dryRun) {
                    $dbProduct->prestashop_id = $prestaId;
                }
                $this->stats['prestashop_ids_set']++;
            }

            // Fix reference
            if (! $this->option('skip-references') && $scraped['reference']) {
                $scrapedRef = $scraped['reference'];
                $currentRef = $dbProduct->reference;

                if ($this->isInternalPrestaRef($currentRef) || empty($currentRef)) {
                    // Current ref is bad — use scraped ref
                    // Check uniqueness
                    if (! isset($usedReferences[$scrapedRef]) || $usedReferences[$scrapedRef] === $dbProduct->id) {
                        $correction['old_ref'] = $currentRef;
                        $correction['new_ref'] = $scrapedRef;

                        // Remove old ref from used list
                        if ($currentRef && isset($usedReferences[$currentRef])) {
                            unset($usedReferences[$currentRef]);
                        }
                        $usedReferences[$scrapedRef] = $dbProduct->id;

                        if (! $dryRun) {
                            $dbProduct->reference = $scrapedRef;
                        }
                        $this->stats['references_fixed']++;
                    } else {
                        Log::info("[products:fix] Duplicate ref skipped: {$scrapedRef} for {$dbProduct->name}");
                    }
                } else {
                    $this->stats['references_already_ok']++;
                }
            }

            // Backfill supplier from reference prefix
            if (empty($dbProduct->supplier)) {
                $ref = $correction['new_ref'] ?? $dbProduct->reference;
                $supplier = $this->guessSupplierFromRef($ref);
                if ($supplier) {
                    $correction['supplier'] = $supplier;
                    if (! $dryRun) {
                        $dbProduct->supplier = $supplier;
                    }
                    $this->stats['suppliers_filled']++;
                }
            }

            // Download missing image
            if (! $this->option('skip-images')) {
                $hasImage = $dbProduct->main_image && Storage::disk('public')->exists($dbProduct->main_image);

                if (! $hasImage) {
                    $imageUrl = $scraped['large_image'] ?? $scraped['thumbnail_url'] ?? null;
                    if ($imageUrl) {
                        $this->downloadImage($dbProduct, $imageUrl, $dryRun);
                    } else {
                        $this->stats['images_failed']++;
                    }
                } else {
                    $this->stats['images_already_ok']++;
                }
            }

            // Save DB changes
            if (! $dryRun) {
                try {
                    $dbProduct->save();
                } catch (\Exception $e) {
                    Log::error("[products:fix] Save failed for {$dbProduct->name}: {$e->getMessage()}");
                }
            }

            if (isset($correction['new_ref']) || isset($correction['supplier'])) {
                $corrections[$dbProduct->id] = $correction;
            }

            $bar->advance();
        }

        $bar->setMessage('Done!');
        $bar->finish();
        $this->info('');

        return $corrections;
    }

    /**
     * Match a scraped product to a DB product.
     */
    private function matchProduct(array $scraped, array &$byName, array &$bySlug, array &$byPrestaId): ?Product
    {
        $prestaId = $scraped['presta_id'];

        // 1. By prestashop_id (most reliable)
        if (isset($byPrestaId[$prestaId])) {
            return $byPrestaId[$prestaId];
        }

        // 2. By normalized name
        $normalizedName = $this->normalizeName($scraped['name']);
        if (isset($byName[$normalizedName])) {
            return $byName[$normalizedName];
        }

        // 3. By slug
        $slug = Str::slug($scraped['name']);
        if (isset($bySlug[$slug])) {
            return $bySlug[$slug];
        }

        // 4. Partial match
        foreach ($byName as $key => $p) {
            if ($key && $normalizedName && strlen($key) > 3 && strlen($normalizedName) > 3) {
                if (str_contains($key, $normalizedName) || str_contains($normalizedName, $key)) {
                    return $p;
                }
            }
        }

        return null;
    }

    /**
     * Check if a reference looks like an internal PrestaShop ID (NNN-NNNNN format).
     */
    private function isInternalPrestaRef(?string $ref): bool
    {
        if (empty($ref)) return true;
        // Pattern: digits-digits (e.g. 292-13294, 323-15052)
        return (bool) preg_match('/^\d{1,4}-\d{4,6}$/', $ref);
    }

    /**
     * Guess the supplier/brand from a reference prefix.
     */
    private function guessSupplierFromRef(?string $ref): ?string
    {
        if (empty($ref) || strlen($ref) < 2) return null;

        $prefix = strtoupper(substr($ref, 0, 2));
        return $this->prefixToBrand[$prefix] ?? null;
    }

    /**
     * Sync the products.php data file with current DB state.
     * Compares each entry in the file to the DB and updates references + suppliers.
     */
    private function syncProductsFile(bool $dryRun): void
    {
        $filePath = database_path('seeders/data/products.php');
        if (! file_exists($filePath)) {
            $this->error("  products.php not found at {$filePath}");
            return;
        }

        $data = require $filePath;

        // Build DB lookup by normalized name
        $dbProducts = Product::all();
        $dbByName = [];
        foreach ($dbProducts as $p) {
            $dbByName[$this->normalizeName($p->name)] = $p;
        }

        $refsUpdated = 0;
        $suppliersUpdated = 0;

        foreach ($data as $categorySlug => &$products) {
            foreach ($products as &$entry) {
                $name = $entry[0];
                $key = $this->normalizeName($name);
                $fileRef = $entry[1] ?? '';
                $fileSupplier = $entry[2] ?? '';

                $dbProduct = $dbByName[$key] ?? null;
                if (! $dbProduct) continue;

                // Sync reference: if file has bad ref but DB has good one
                $dbRef = $dbProduct->reference ?? '';
                if ($dbRef && $fileRef !== $dbRef) {
                    if ($this->isInternalPrestaRef($fileRef) || empty($fileRef) || $this->isShortNumericRef($fileRef)) {
                        if (! $this->isInternalPrestaRef($dbRef)) {
                            $entry[1] = $dbRef;
                            $refsUpdated++;
                        }
                    }
                }

                // Sync supplier: if file has empty supplier but DB has one
                if (empty($fileSupplier) && ! empty($dbProduct->supplier)) {
                    $entry[2] = $dbProduct->supplier;
                    $suppliersUpdated++;
                }
            }
            unset($entry);
        }
        unset($products);

        $this->stats['file_entries_updated'] = $refsUpdated;

        if ($dryRun) {
            $this->info("  [DRY] Would update {$refsUpdated} references and {$suppliersUpdated} suppliers in products.php");
            return;
        }

        // Backup
        $backupPath = $filePath . '.bak';
        copy($filePath, $backupPath);
        $this->info("  Backup saved to products.php.bak");

        // Regenerate
        $output = $this->generateProductsPhp($data);
        file_put_contents($filePath, $output);
        $this->info("  Updated {$refsUpdated} references and {$suppliersUpdated} suppliers in products.php");
    }

    /**
     * Check if a reference is a short numeric-only ref (e.g. '319', '422').
     */
    private function isShortNumericRef(?string $ref): bool
    {
        if (empty($ref)) return false;
        return (bool) preg_match('/^\d{1,4}$/', $ref);
    }

    /**
     * Generate the products.php file content from the data array.
     */
    private function generateProductsPhp(array $data): string
    {
        $totalProducts = 0;
        foreach ($data as $products) {
            $totalProducts += count($products);
        }
        $totalCategories = count($data);

        $output = "<?php\n\n";
        $output .= "/**\n";
        $output .= " * Donnees produits pour CategoryProductSeeder\n";
        $output .= " * Genere depuis les donnees scrappees de marquage-textile.fr\n";
        $output .= " * Format: [nom, reference, marque, composition, grammage (g/m2), coupe, prix HT (EUR)]\n";
        $output .= " *\n";
        $output .= " * Total: {$totalProducts} produits dans {$totalCategories} categories\n";
        $output .= " */\n\n";
        $output .= "return [\n";

        foreach ($data as $categorySlug => $products) {
            $count = count($products);
            $label = strtoupper(str_replace('-', ' ', $categorySlug));
            $output .= "\n    // ===== {$label} ({$count}) =====\n";
            $output .= "    '{$categorySlug}' => [\n";

            foreach ($products as $entry) {
                $output .= "        " . $this->formatProductEntry($entry) . ",\n";
            }

            $output .= "    ],\n";
        }

        $output .= "];\n";

        return $output;
    }

    /**
     * Format a single product entry as PHP array literal.
     */
    private function formatProductEntry(array $entry): string
    {
        // [name, ref, supplier, material, grammage, cut, price]
        $name = addcslashes($entry[0] ?? '', "'\\");
        $ref = addcslashes($entry[1] ?? '', "'\\");
        $supplier = addcslashes($entry[2] ?? '', "'\\");
        $material = addcslashes($entry[3] ?? '', "'\\");
        $grammage = $entry[4] ?? null;
        $cut = addcslashes($entry[5] ?? 'mixte', "'\\");
        $price = $entry[6] ?? 0;

        $gramStr = ($grammage === null) ? 'null' : (int) $grammage;
        $priceStr = number_format((float) $price, 2, '.', '');

        return "['{$name}', '{$ref}', '{$supplier}', '{$material}', {$gramStr}, '{$cut}', {$priceStr}]";
    }

    /**
     * Extract product data from a category listing page HTML.
     */
    private function extractProductsFromListing(string $html): array
    {
        $products = [];

        if (preg_match_all('#<li[^>]*class="[^"]*ajax_block_product[^"]*"[^>]*>(.*?)</li>#si', $html, $items)) {
            foreach ($items[1] as $itemHtml) {
                $product = [];

                if (preg_match('#href="[^"]*?/fr/accueil/(\d+)-([^"]+?)\.html"#', $itemHtml, $m)) {
                    $product['presta_id'] = (int) $m[1];
                    $product['slug'] = $m[2];
                    $product['url'] = "{$this->baseUrl}/fr/accueil/{$m[1]}-{$m[2]}.html";
                } else {
                    continue;
                }

                if (preg_match('#<h5[^>]*class="[^"]*product-name[^"]*"[^>]*>\s*<a[^>]*>([^<]+)</a>#si', $itemHtml, $m)) {
                    $product['name'] = trim(html_entity_decode($m[1], ENT_QUOTES, 'UTF-8'));
                } else {
                    $product['name'] = str_replace('-', ' ', $product['slug']);
                }

                if (preg_match('#<img[^>]*src="([^"]*?/(\d+)-home_default/[^"]+\.jpg)"#', $itemHtml, $m)) {
                    $product['thumbnail_url'] = $m[1];
                    $product['image_id'] = (int) $m[2];
                }

                $products[] = $product;
            }
        }

        return $products;
    }

    /**
     * Scrape a product detail page for reference and large image URL.
     */
    private function scrapeProductDetail(string $url): ?array
    {
        $html = $this->fetch($url);
        if (! $html) return null;

        $data = ['reference' => null, 'large_image' => null];

        if (preg_match("/var\s+productReference\s*=\s*'([^']+)'/", $html, $m)) {
            $ref = trim($m[1]);
            if ($ref && $ref !== 'undefined') {
                $data['reference'] = $ref;
            }
        }

        if (preg_match("/var\s+sharing_img\s*=\s*'([^']+)'/", $html, $m)) {
            $data['large_image'] = $m[1];
        }

        if (! $data['large_image']) {
            if (preg_match("/var\s+idDefaultImage\s*=\s*(\d+)/", $html, $m)) {
                $imageId = $m[1];
                $slug = basename(parse_url($url, PHP_URL_PATH), '.html');
                $slug = preg_replace('/^\d+-/', '', $slug);
                $data['large_image'] = "{$this->baseUrl}/{$imageId}-large_default/{$slug}.jpg";
            }
        }

        return $data;
    }

    /**
     * Download a product image and save to storage.
     */
    private function downloadImage(Product $product, string $imageUrl, bool $dryRun): void
    {
        if (! str_starts_with($imageUrl, 'http')) {
            $imageUrl = $this->baseUrl . $imageUrl;
        }

        $extension = pathinfo(parse_url($imageUrl, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'jpg';
        $filename = "products/{$product->slug}.{$extension}";

        if ($dryRun) {
            Log::info("[products:fix] DRY: Would download image for {$product->name}");
            return;
        }

        try {
            $response = $this->http->get($imageUrl);
            $status = $response->getStatusCode();

            if ($status >= 400) {
                if (str_contains($imageUrl, 'large_default')) {
                    $fallbackUrl = str_replace('large_default', 'home_default', $imageUrl);
                    $this->downloadImage($product, $fallbackUrl, $dryRun);
                    return;
                }
                $this->stats['images_failed']++;
                return;
            }

            $imageContent = $response->getBody()->getContents();

            if (strlen($imageContent) < 1000) {
                $this->stats['images_failed']++;
                return;
            }

            Storage::disk('public')->put($filename, $imageContent);
            $product->main_image = $filename;
            $this->stats['images_downloaded']++;
        } catch (\Exception $e) {
            $this->stats['images_failed']++;
            Log::error("[products:fix] Image download failed for {$product->name}: {$e->getMessage()}");
        }
    }

    /**
     * Fetch a URL with rate limiting and retry.
     */
    private function fetch(string $url): ?string
    {
        if ($this->requestCount > 0) {
            usleep(300_000); // 300ms between requests
        }
        $this->requestCount++;

        for ($attempt = 1; $attempt <= 3; $attempt++) {
            try {
                $response = $this->http->get($url);
                $status = $response->getStatusCode();
                $body = $response->getBody()->getContents();

                if ($status >= 200 && $status < 500) {
                    return $body ?: null;
                }

                if ($attempt < 3) sleep($attempt);
            } catch (\Exception $e) {
                if ($attempt < 3) {
                    sleep($attempt);
                } else {
                    return null;
                }
            }
        }

        return null;
    }

    private function normalizeName(string $name): string
    {
        $name = mb_strtolower($name);
        return preg_replace('/[^\p{L}\p{N}]/u', '', $name);
    }
}
