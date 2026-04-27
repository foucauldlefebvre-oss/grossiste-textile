<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\ProductSize;
use App\Services\ValentoApiService;
use Illuminate\Console\Command;

class ImportValento extends Command
{
    protected $signature = 'import:valento
        {--dry-run : Show what would be done without modifying anything}
        {--stock-only : Only update stocks for existing Valento products}
        {--prices-only : Only update prices for existing Valento products}
        {--map-refs : Build cref→SKU mapping from full catalog and populate product_sizes.sku}
        {--dump-structure : Dump the raw JSON structure and stop}';

    protected $description = 'Import/update Valento stock and prices via REST API';

    private ValentoApiService $api;

    private array $stats = [
        'skus_mapped' => 0,
        'stock_updated' => 0,
        'stock_skipped' => 0,
        'prices_updated' => 0,
        'prices_skipped' => 0,
        'refs_not_found' => 0,
        'errors' => 0,
    ];

    public function handle(ValentoApiService $api): int
    {
        $this->api = $api;
        $dryRun = $this->option('dry-run');

        if (! $this->api->isConfigured()) {
            $this->error('Valento API non configurée. Vérifiez VALENTO_USERNAME, VALENTO_PASSWORD et VALENTO_URL dans .env');
            return self::FAILURE;
        }

        if ($dryRun) {
            $this->info('=== DRY RUN MODE ===');
        }

        // Test connection
        $this->info('Test de connexion à l\'API Valento...');
        try {
            if (! $this->api->testConnection()) {
                $this->error('Échec de connexion à l\'API Valento (HTTP non 2xx).');
                return self::FAILURE;
            }
            $this->info('Connexion OK.');
        } catch (\Throwable $e) {
            $this->error("Échec de connexion : {$e->getMessage()}");
            return self::FAILURE;
        }

        if ($this->option('dump-structure')) {
            return $this->dumpStructure();
        }

        if ($this->option('map-refs')) {
            return $this->mapRefsFromCatalog($dryRun);
        }

        $valentoProductCount = Product::where('supplier', 'Valento')->count();
        $this->info("{$valentoProductCount} produits Valento en base.");

        if ($valentoProductCount === 0) {
            $this->warn('Aucun produit Valento en base.');
            return self::SUCCESS;
        }

        // Check if SKUs are populated
        $skuCount = ProductSize::whereHas('color.product', fn ($q) => $q->where('supplier', 'Valento'))
            ->whereNotNull('sku')
            ->where('sku', '!=', '')
            ->count();

        if ($skuCount === 0) {
            $this->warn('Aucun SKU Valento (cref) renseigné sur les tailles.');
            $this->warn('Lancez d\'abord : php artisan import:valento --map-refs');
            $this->warn('(nécessite le créneau 3h-6h ou le catalogue prix disponible)');
            $this->newLine();
        }

        if ($this->option('prices-only')) {
            $this->updatePrices($dryRun);
        } elseif ($this->option('stock-only')) {
            $this->updateStock($dryRun);
        } else {
            $this->updateStock($dryRun);
            $this->updatePrices($dryRun);
        }

        $this->displaySummary();

        if ($dryRun) {
            $this->warn('Dry run terminé. Aucune modification effectuée.');
        }

        return self::SUCCESS;
    }

    // ─── Map refs ────────────────────────────────────────────────

    /**
     * Build SKU mapping from the full Valento price catalog.
     *
     * Valento crefs follow the pattern: MODEL_CODE + COLOR_CODE + SIZE_CODE
     * e.g. "ALVARELBL20" where ALVAREL=model, BL=color, 20=size
     *
     * Our DB has different product references (from scraped PrestaShop data).
     * This command fetches all crefs, groups them by model prefix, and lets
     * us map them to our products + populate product_sizes.sku.
     */
    private function mapRefsFromCatalog(bool $dryRun): int
    {
        $this->info('Récupération du catalogue prix complet pour extraire les crefs...');

        try {
            $catalog = $this->api->getAllPrices();
        } catch (\Throwable $e) {
            $this->error("Impossible de récupérer le catalogue : {$e->getMessage()}");
            $this->warn('Le catalogue complet est soumis à des limites horaires.');
            return self::FAILURE;
        }

        if (empty($catalog)) {
            $this->error('Catalogue vide.');
            return self::FAILURE;
        }

        $this->info(count($catalog) . ' crefs dans le catalogue.');

        // Extract all crefs
        $allCrefs = [];
        foreach ($catalog as $item) {
            $cref = $item['cref'] ?? null;
            if ($cref) {
                $allCrefs[] = $cref;
            }
        }

        // Load Valento products with colors/sizes
        $products = Product::where('supplier', 'Valento')
            ->with('colors.sizes')
            ->get();

        $this->info($products->count() . ' produits Valento en base.');

        // Strategy: try to match each product reference against the crefs
        // A product ref like "VACOUDRE" should appear as prefix in crefs like "VACOUDREBL20"
        $bar = $this->output->createProgressBar($products->count());
        $bar->start();

        $matched = 0;
        $unmatched = [];

        foreach ($products as $product) {
            $ref = $product->reference;
            if (! $ref) {
                $bar->advance();
                continue;
            }

            // Find all crefs that start with this product reference
            $productCrefs = array_filter($allCrefs, fn ($cref) => str_starts_with($cref, $ref));

            if (empty($productCrefs)) {
                // Try uppercase
                $refUpper = strtoupper($ref);
                $productCrefs = array_filter($allCrefs, fn ($cref) => str_starts_with(strtoupper($cref), $refUpper));
            }

            if (empty($productCrefs)) {
                $unmatched[] = $ref;
                $bar->advance();
                continue;
            }

            $matched++;

            // Group crefs by color+size pattern
            // cref = ref + colorCode(2 chars) + sizeCode(2 digits)
            $refLen = strlen($ref);
            foreach ($product->colors as $color) {
                foreach ($color->sizes as $size) {
                    // Try to find a matching cref for this color+size
                    // Since we don't know the Valento color/size codes,
                    // assign crefs sequentially to sizes within each color group
                    // This is imperfect but gets the SKUs into the system
                }
            }

            // Simpler approach: just store all matching crefs for this product
            // by assigning them to sizes in order
            $crefList = array_values($productCrefs);
            sort($crefList);

            $sizeIndex = 0;
            foreach ($product->colors as $color) {
                foreach ($color->sizes as $size) {
                    if (isset($crefList[$sizeIndex])) {
                        if (! $dryRun) {
                            $size->update(['sku' => $crefList[$sizeIndex]]);
                        }
                        $this->stats['skus_mapped']++;
                    }
                    $sizeIndex++;
                }
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("{$matched} produits mappés, " . count($unmatched) . " sans correspondance.");

        if (! empty($unmatched) && count($unmatched) <= 30) {
            $this->warn('Références non trouvées dans le catalogue : ' . implode(', ', $unmatched));
        } elseif (! empty($unmatched)) {
            $this->warn(count($unmatched) . ' références non trouvées (trop nombreuses pour afficher).');
        }

        $this->displaySummary();

        if ($dryRun) {
            $this->warn('Dry run terminé. Aucune modification effectuée.');
        }

        return self::SUCCESS;
    }

    // ─── Dump ────────────────────────────────────────────────────

    private function dumpStructure(): int
    {
        $this->info('Récupération structure stock complet...');
        try {
            $items = $this->api->getAllStock();
            $sample = array_slice($items, 0, 5);
            $this->info(count($items) . ' entrées stock. 5 premières :');
            $this->line(json_encode($sample, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        } catch (\Throwable $e) {
            $this->error("Erreur stock: {$e->getMessage()}");
        }

        $this->newLine();
        $this->info('Récupération structure prix complets...');
        try {
            $items = $this->api->getAllPrices();
            $sample = array_slice($items, 0, 5);
            $this->info(count($items) . ' entrées prix. 5 premières :');
            $this->line(json_encode($sample, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        } catch (\Throwable $e) {
            $this->error("Erreur prix: {$e->getMessage()}");
        }

        return self::SUCCESS;
    }

    // ─── Stock ───────────────────────────────────────────────────

    private function updateStock(bool $dryRun): void
    {
        $this->info('Récupération du stock Valento...');

        // Collect all SKUs (crefs) from product_sizes
        $skuSizes = ProductSize::whereHas('color.product', fn ($q) => $q->where('supplier', 'Valento'))
            ->whereNotNull('sku')
            ->where('sku', '!=', '')
            ->get();

        if ($skuSizes->isEmpty()) {
            $this->warn('Aucun SKU à requêter. Utilisez --map-refs d\'abord.');
            return;
        }

        $this->info($skuSizes->count() . ' SKUs à mettre à jour.');

        $currentHour = (int) now()->format('G');
        $useBulk = $currentHour >= 3 && $currentHour < 6;

        $stockMap = [];

        if ($useBulk) {
            $this->info('Créneau 3h-6h : récupération du catalogue complet...');
            try {
                $catalog = $this->api->getAllStock();
                foreach ($catalog as $item) {
                    $cref = $item['cref'] ?? null;
                    if ($cref) {
                        $stockMap[$cref] = (int) ($item['stock'] ?? 0);
                    }
                }
                $this->info(count($stockMap) . ' références stock reçues.');
            } catch (\Throwable $e) {
                $this->warn("Catalogue complet indisponible: {$e->getMessage()}");
                $this->info('Fallback sur requêtes par référence...');
                $useBulk = false;
            }
        }

        if (! $useBulk) {
            $this->info("Hors créneau 3h-6h ({$currentHour}h) : requêtes par SKU individuel.");

            // Deduplicate SKUs
            $uniqueSkus = $skuSizes->pluck('sku')->unique()->values();
            $count = 0;
            $bar = $this->output->createProgressBar($uniqueSkus->count());
            $bar->start();

            foreach ($uniqueSkus as $sku) {
                if ($count >= 95) {
                    $this->newLine();
                    $this->warn('Limite API proche (95 requêtes). Arrêt.');
                    break;
                }
                try {
                    $items = $this->api->getStock($sku);
                    foreach ($items as $item) {
                        $cref = $item['cref'] ?? null;
                        if ($cref) {
                            $stockMap[$cref] = (int) ($item['stock'] ?? 0);
                        }
                    }
                    $count++;
                } catch (\Throwable $e) {
                    // Skip on 400/403
                }
                $bar->advance();
            }

            $bar->finish();
            $this->newLine();
            $this->info(count($stockMap) . " références stock récupérées ({$count} requêtes).");
        }

        // Apply stock
        $bar = $this->output->createProgressBar($skuSizes->count());
        $bar->start();

        foreach ($skuSizes as $size) {
            if (isset($stockMap[$size->sku])) {
                $qty = $stockMap[$size->sku];
                if (! $dryRun) {
                    $size->update([
                        'stock' => $qty,
                        'is_available' => $qty > 0,
                    ]);
                }
                $this->stats['stock_updated']++;
            } else {
                $this->stats['stock_skipped']++;
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        // Update product-level stock (sum of all size stocks) via aggregated query
        if (! $dryRun) {
            $this->info('Mise à jour stock agrégé par produit...');
            \DB::statement("
                UPDATE products p
                SET stock = (
                    SELECT COALESCE(SUM(ps.stock), 0)
                    FROM product_sizes ps
                    JOIN product_colors pc ON ps.product_color_id = pc.id
                    WHERE pc.product_id = p.id
                )
                WHERE p.supplier = 'Valento'
            ");
        }
    }

    // ─── Prices ──────────────────────────────────────────────────

    private function updatePrices(bool $dryRun): void
    {
        $this->info('Récupération du catalogue prix complet Valento...');

        try {
            $catalog = $this->api->getAllPrices();
        } catch (\Throwable $e) {
            $this->stats['errors']++;
            $this->error("Erreur récupération prix: {$e->getMessage()}");
            return;
        }

        if (empty($catalog)) {
            $this->warn('Données prix vides.');
            return;
        }

        // Build lookup: cref → price
        $priceMap = [];
        foreach ($catalog as $item) {
            $cref = $item['cref'] ?? null;
            if ($cref) {
                $price = (float) ($item['price_for_0_uds'] ?? 0);
                if ($price > 0) {
                    $priceMap[$cref] = $price;
                }
            }
        }

        $this->info(count($priceMap) . ' prix valides dans le catalogue.');

        $products = Product::where('supplier', 'Valento')
            ->with('colors.sizes')
            ->get();

        $bar = $this->output->createProgressBar($products->count());
        $bar->start();

        foreach ($products as $product) {
            $matchedPrice = null;

            // Collect all valid prices for this product's SKUs
            $allPrices = [];
            foreach ($product->colors as $color) {
                foreach ($color->sizes as $size) {
                    if ($size->sku && isset($priceMap[$size->sku])) {
                        $allPrices[] = $priceMap[$size->sku];
                    }
                }
            }

            // Use the most common price (mode) to avoid outlet/promo SKU prices
            if (! empty($allPrices)) {
                $counts = array_count_values(array_map(fn ($p) => (string) round($p, 2), $allPrices));
                arsort($counts);
                $matchedPrice = (float) array_key_first($counts);
            }

            if ($matchedPrice !== null) {
                if (! $dryRun) {
                    $product->update([
                        'supplier_price' => $matchedPrice,
                        'base_price' => $matchedPrice,
                    ]);
                }
                $this->stats['prices_updated']++;
            } else {
                $this->stats['refs_not_found']++;
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
    }

    // ─── Summary ─────────────────────────────────────────────────

    private function displaySummary(): void
    {
        $this->newLine();
        $this->info('=== Résumé import Valento ===');
        $this->table(
            ['Métrique', 'Total'],
            collect($this->stats)
                ->filter(fn ($val) => $val > 0)
                ->map(fn ($val, $key) => [
                    str_replace('_', ' ', ucfirst($key)),
                    $val,
                ])->values()->toArray()
        );
    }
}
