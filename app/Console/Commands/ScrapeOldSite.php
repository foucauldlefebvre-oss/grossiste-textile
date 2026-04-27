<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Product;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ScrapeOldSite extends Command
{
    protected $signature = 'scrape:old-site
        {--dry-run : Show what would be done without modifying the database}
        {--category=  : Only process a specific category slug}
        {--skip-images : Skip image downloading}
        {--skip-references : Skip reference updates}';

    protected $description = 'Scrape marquage-textile.fr to import images, references, and PrestaShop IDs';

    private Client $http;

    private string $baseUrl = 'https://www.marquage-textile.fr';

    private int $requestCount = 0;

    private array $stats = [
        'categories_matched' => 0,
        'categories_unmatched' => 0,
        'products_matched' => 0,
        'products_unmatched' => 0,
        'images_downloaded' => 0,
        'images_failed' => 0,
        'references_updated' => 0,
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

        $this->info('');

        // Phase A: Scrape categories
        $this->info('Phase A: Scraping categories...');
        $categoryMap = $this->scrapeCategories($dryRun);

        // Phase B: Scrape products per category
        $this->info('');
        $this->info('Phase B: Scraping products...');
        $this->scrapeProducts($categoryMap, $dryRun);

        // Summary
        $this->info('');
        $this->info('=== Summary ===');
        foreach ($this->stats as $key => $val) {
            $this->line("  {$key}: {$val}");
        }

        $this->log('Scrape completed', $this->stats);

        return self::SUCCESS;
    }

    /**
     * Scrape all categories from the old site navigation menu.
     * Returns an array of [prestashop_id => category_url] for product scraping.
     */
    private function scrapeCategories(bool $dryRun): array
    {
        $html = $this->fetch("{$this->baseUrl}/fr/");
        if (! $html) {
            $this->error('Failed to fetch homepage');

            return [];
        }

        // Extract category links from the navigation tree
        $categoryLinks = [];
        if (preg_match_all('#href="https?://www\.marquage-textile\.fr/fr/(\d+)-([^"]+)"#', $html, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $m) {
                $prestaId = (int) $m[1];
                $slug = $m[2];
                if ($prestaId > 1 && ! isset($categoryLinks[$prestaId])) {
                    $categoryLinks[$prestaId] = [
                        'url' => "{$this->baseUrl}/fr/{$prestaId}-{$slug}",
                        'slug' => $slug,
                        'presta_id' => $prestaId,
                    ];
                }
            }
        }

        $this->info("  Found " . count($categoryLinks) . " categories on old site");

        $dbCategories = Category::all();
        $matched = 0;

        foreach ($categoryLinks as $prestaId => $catData) {
            // Try to match by slug (normalized)
            $normalizedSlug = $this->normalizeSlug($catData['slug']);

            $dbCat = $dbCategories->first(function ($c) use ($normalizedSlug, $catData) {
                return $c->slug === $normalizedSlug
                    || $c->slug === $catData['slug']
                    || Str::slug($c->name) === $normalizedSlug;
            });

            if ($dbCat) {
                $matched++;
                if ($dryRun) {
                    $this->line("  [DRY] Category #{$prestaId} '{$catData['slug']}' → DB #{$dbCat->id} '{$dbCat->name}'");
                } else {
                    $dbCat->update(['prestashop_id' => $prestaId]);
                    $this->line("  Mapped category #{$prestaId} → '{$dbCat->name}'");
                }
            } else {
                $this->warn("  No match for category #{$prestaId} '{$catData['slug']}'");
                $this->log("Unmatched category", ['presta_id' => $prestaId, 'slug' => $catData['slug']]);
            }
        }

        $this->stats['categories_matched'] = $matched;
        $this->stats['categories_unmatched'] = count($categoryLinks) - $matched;
        $this->info("  Matched: {$matched} / " . count($categoryLinks));

        return $categoryLinks;
    }

    /**
     * Scrape products from each category page (with pagination).
     */
    private function scrapeProducts(array $categoryMap, bool $dryRun): void
    {
        $filterSlug = $this->option('category');
        $dbProducts = Product::all()->keyBy('id');

        // Build a lookup by normalized name for matching
        $productsByName = [];
        foreach ($dbProducts as $p) {
            $key = $this->normalizeName($p->name);
            $productsByName[$key] = $p;
        }

        $allScrapedProducts = [];

        // Only scrape leaf categories (those that have product listings)
        // We'll scrape each unique category URL
        $processedCategories = [];

        foreach ($categoryMap as $prestaId => $catData) {
            $slug = $catData['slug'];

            if ($filterSlug && $slug !== $filterSlug) {
                continue;
            }

            if (isset($processedCategories[$prestaId])) {
                continue;
            }
            $processedCategories[$prestaId] = true;

            $this->info("  Scraping category: {$slug} (#{$prestaId})");
            $page = 1;
            $hasMore = true;

            while ($hasMore) {
                $url = $catData['url'] . ($page > 1 ? "?p={$page}" : '');
                $html = $this->fetch($url);

                if (! $html) {
                    break;
                }

                $products = $this->extractProductsFromListing($html);

                if (empty($products)) {
                    break;
                }

                foreach ($products as $scraped) {
                    // Avoid duplicates (same product can appear in multiple categories)
                    if (isset($allScrapedProducts[$scraped['presta_id']])) {
                        continue;
                    }
                    $allScrapedProducts[$scraped['presta_id']] = $scraped;
                }

                // Check for next page
                $hasMore = str_contains($html, "?p=" . ($page + 1));
                $page++;
            }
        }

        $this->info("  Total scraped products: " . count($allScrapedProducts));
        $this->info('');

        // Now match and process each scraped product
        $bar = $this->output->createProgressBar(count($allScrapedProducts));
        $bar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %message%');
        $bar->setMessage('Processing...');
        $bar->start();

        foreach ($allScrapedProducts as $scraped) {
            $bar->setMessage($scraped['name']);

            $dbProduct = $this->matchProduct($scraped, $productsByName);

            if (! $dbProduct) {
                $this->stats['products_unmatched']++;
                $this->log('Unmatched product', $scraped);
                $bar->advance();
                continue;
            }

            $this->stats['products_matched']++;

            // Skip if already processed (resume support)
            if (! $dryRun && $dbProduct->prestashop_id && $dbProduct->main_image) {
                $bar->advance();
                continue;
            }

            // Update prestashop_id
            if (! $dryRun) {
                $dbProduct->prestashop_id = $scraped['presta_id'];
            }

            // Scrape product detail page for reference and large image
            $detailData = $this->scrapeProductDetail($scraped['url']);

            // Update reference
            if (! $this->option('skip-references') && $detailData && $detailData['reference']) {
                if ($dryRun) {
                    $this->log('DRY: Would update reference', [
                        'product' => $dbProduct->name,
                        'old' => $dbProduct->reference,
                        'new' => $detailData['reference'],
                    ]);
                } else {
                    // Check for duplicate reference before setting
                    $existing = Product::where('reference', $detailData['reference'])
                        ->where('id', '!=', $dbProduct->id)
                        ->exists();
                    if (! $existing) {
                        $dbProduct->reference = $detailData['reference'];
                        $this->stats['references_updated']++;
                    } else {
                        $this->log('Duplicate reference, skipped', [
                            'product' => $dbProduct->name,
                            'reference' => $detailData['reference'],
                        ]);
                    }
                }
            }

            // Download image
            if (! $this->option('skip-images')) {
                $imageUrl = $detailData['large_image'] ?? $scraped['thumbnail_url'] ?? null;
                if ($imageUrl) {
                    $this->downloadImage($dbProduct, $imageUrl, $dryRun);
                }
            }

            if (! $dryRun) {
                try {
                    $dbProduct->save();
                } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
                    $this->log('Save failed (unique constraint)', [
                        'product' => $dbProduct->name,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            $bar->advance();
        }

        $bar->setMessage('Done!');
        $bar->finish();
        $this->info('');
    }

    /**
     * Extract product data from a category listing page HTML.
     */
    private function extractProductsFromListing(string $html): array
    {
        $products = [];

        // Match product links: /fr/accueil/{id}-{slug}.html
        // Pattern: <a class="product_img_link" href=".../{id}-{slug}.html"
        //          <img ... src=".../{image_id}-home_default/{slug}.jpg"
        if (preg_match_all('#<li[^>]*class="[^"]*ajax_block_product[^"]*"[^>]*>(.*?)</li>#si', $html, $items)) {
            foreach ($items[1] as $itemHtml) {
                $product = [];

                // Extract product URL and presta_id
                if (preg_match('#href="[^"]*?/fr/accueil/(\d+)-([^"]+?)\.html"#', $itemHtml, $m)) {
                    $product['presta_id'] = (int) $m[1];
                    $product['slug'] = $m[2];
                    $product['url'] = "{$this->baseUrl}/fr/accueil/{$m[1]}-{$m[2]}.html";
                } else {
                    continue;
                }

                // Extract name
                if (preg_match('#<h5[^>]*class="[^"]*product-name[^"]*"[^>]*>\s*<a[^>]*>([^<]+)</a>#si', $itemHtml, $m)) {
                    $product['name'] = trim(html_entity_decode($m[1], ENT_QUOTES, 'UTF-8'));
                } else {
                    $product['name'] = str_replace('-', ' ', $product['slug']);
                }

                // Extract thumbnail URL
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
        if (! $html) {
            return null;
        }

        $data = [
            'reference' => null,
            'large_image' => null,
        ];

        // Extract reference from JS: var productReference = 'ROTSATO';
        if (preg_match("/var\s+productReference\s*=\s*'([^']+)'/", $html, $m)) {
            $ref = trim($m[1]);
            if ($ref && $ref !== '' && $ref !== 'undefined') {
                $data['reference'] = $ref;
            }
        }

        // Extract large image from JS: var sharing_img = '...';
        if (preg_match("/var\s+sharing_img\s*=\s*'([^']+)'/", $html, $m)) {
            $data['large_image'] = $m[1];
        }

        // Fallback: try idDefaultImage
        if (! $data['large_image']) {
            if (preg_match("/var\s+idDefaultImage\s*=\s*(\d+)/", $html, $m)) {
                $imageId = $m[1];
                // Try to build the large_default URL
                $slug = basename(parse_url($url, PHP_URL_PATH), '.html');
                // Remove numeric prefix from slug
                $slug = preg_replace('/^\d+-/', '', $slug);
                $data['large_image'] = "{$this->baseUrl}/{$imageId}-large_default/{$slug}.jpg";
            }
        }

        return $data;
    }

    /**
     * Match a scraped product to a database product by name.
     */
    private function matchProduct(array $scraped, array &$productsByName): ?Product
    {
        $normalizedName = $this->normalizeName($scraped['name']);

        // Direct name match
        if (isset($productsByName[$normalizedName])) {
            return $productsByName[$normalizedName];
        }

        // Try matching by slug
        $slug = Str::slug($scraped['name']);
        foreach ($productsByName as $key => $p) {
            if ($p->slug === $slug || Str::slug($p->name) === $slug) {
                return $p;
            }
        }

        // Try partial match (old name contains new name or vice versa)
        foreach ($productsByName as $key => $p) {
            $dbNorm = $this->normalizeName($p->name);
            if ($dbNorm && $normalizedName && (str_contains($dbNorm, $normalizedName) || str_contains($normalizedName, $dbNorm))) {
                return $p;
            }
        }

        return null;
    }

    /**
     * Download a product image and save to storage.
     */
    private function downloadImage(Product $product, string $imageUrl, bool $dryRun): void
    {
        // Ensure URL is absolute
        if (! str_starts_with($imageUrl, 'http')) {
            $imageUrl = $this->baseUrl . $imageUrl;
        }

        $extension = pathinfo(parse_url($imageUrl, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'jpg';
        $filename = "products/{$product->slug}.{$extension}";

        if ($dryRun) {
            $this->log('DRY: Would download image', [
                'product' => $product->name,
                'url' => $imageUrl,
                'path' => $filename,
            ]);

            return;
        }

        try {
            $response = $this->http->get($imageUrl);
            $status = $response->getStatusCode();

            if ($status >= 400) {
                // Try fallback: replace large_default with home_default
                if (str_contains($imageUrl, 'large_default')) {
                    $fallbackUrl = str_replace('large_default', 'home_default', $imageUrl);
                    $this->downloadImage($product, $fallbackUrl, $dryRun);

                    return;
                }
                $this->stats['images_failed']++;
                $this->log('Image download failed (HTTP ' . $status . ')', ['url' => $imageUrl]);

                return;
            }

            $imageContent = $response->getBody()->getContents();

            if (strlen($imageContent) < 1000) {
                $this->stats['images_failed']++;
                $this->log('Image too small (likely error)', ['url' => $imageUrl, 'size' => strlen($imageContent)]);

                return;
            }

            Storage::disk('public')->put($filename, $imageContent);
            $product->main_image = $filename;
            $this->stats['images_downloaded']++;
        } catch (\Exception $e) {
            $this->stats['images_failed']++;
            $this->log('Image download failed', [
                'url' => $imageUrl,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Fetch a URL with rate limiting and retry.
     */
    private function fetch(string $url): ?string
    {
        // Rate limit
        if ($this->requestCount > 0) {
            usleep(500_000); // 500ms between requests
        }
        $this->requestCount++;

        $maxRetries = 3;
        for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
            try {
                $response = $this->http->get($url);
                $status = $response->getStatusCode();
                $body = $response->getBody()->getContents();

                // Accept 200 and 404 (PrestaShop returns 404 but with valid HTML for some pages)
                if ($status >= 200 && $status < 500) {
                    return $body ?: null;
                }

                if ($attempt < $maxRetries) {
                    sleep($attempt);
                }
            } catch (\Exception $e) {
                if ($attempt < $maxRetries) {
                    sleep($attempt);
                } else {
                    $this->log('Fetch failed after retries', ['url' => $url, 'error' => $e->getMessage()]);

                    return null;
                }
            }
        }

        return null;
    }

    /**
     * Normalize a product name for matching.
     */
    private function normalizeName(string $name): string
    {
        $name = mb_strtolower($name);
        $name = preg_replace('/[^\p{L}\p{N}]/u', '', $name);

        return $name;
    }

    /**
     * Normalize a slug for category matching.
     */
    private function normalizeSlug(string $slug): string
    {
        // PrestaShop slugs use hyphens, may have accented chars encoded differently
        return Str::slug(str_replace('-', ' ', $slug));
    }

    /**
     * Write to the scrape log file.
     */
    private function log(string $message, array $context = []): void
    {
        Log::channel('single')->info("[scrape:old-site] {$message}", $context);
    }
}
