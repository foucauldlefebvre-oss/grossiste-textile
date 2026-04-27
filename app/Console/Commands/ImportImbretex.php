<?php

namespace App\Console\Commands;

use App\Helpers\ColorHelper;
use App\Models\Category;
use App\Models\PricingRule;
use App\Models\Product;
use App\Models\ProductColor;
use App\Models\ProductSize;
use App\Services\ImbretexApiService;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class ImportImbretex extends Command
{
    protected $signature = 'import:imbretex
        {--dry-run : Show what would be done without modifying anything}
        {--brand= : Filter by brand (e.g. "B&C", "RESULT", "all")}
        {--limit=0 : Limit number of products to import (0 = all)}
        {--stock-only : Only update stocks and prices for existing Imbretex products}
        {--dump-structure : Dump the raw JSON structure of the first product and stop}';

    protected $description = 'Import Imbretex product catalog (B&C, Fruit of the Loom, Result, etc.)';

    private ImbretexApiService $api;

    /** Brand mapping: API brand name → our supplier name */
    private array $brandMap = [
        'B&C' => 'B&C',
        'JUST HOODS' => 'AWDis',
        'JUST COOL' => 'AWDis',
        'FRUIT OF THE LOOM' => 'Fruit of the Loom',
        'RESULT' => 'Result',
        'BAG BASE' => 'Bag Base',
        'BEECHFIELD' => 'Beechfield',
        'WESTFORD MILL' => 'Westford Mill',
        'BABYBUGZ' => 'BabyBugz',
    ];

    /** Only import these brands */
    private array $allowedBrands = [
        'B&C',
        'JUST HOODS',
        'JUST COOL',
        'FRUIT OF THE LOOM',
        'RESULT',
        'BAG BASE',
        'BEECHFIELD',
        'WESTFORD MILL',
        'BABYBUGZ',
    ];

    private array $stats = [
        'products_created' => 0,
        'products_updated' => 0,
        'colors_created' => 0,
        'colors_updated' => 0,
        'sizes_created' => 0,
        'sizes_updated' => 0,
        'prices_updated' => 0,
        'stock_updated' => 0,
        'products_total' => 0,
        'variants_total' => 0,
        'errors' => 0,
    ];

    public function handle(ImbretexApiService $api): int
    {
        $this->api = $api;
        $dryRun = $this->option('dry-run');
        $stockOnly = $this->option('stock-only');

        if (! $this->api->isConfigured()) {
            $this->error('Imbretex API non configuree. Verifiez IMBRETEX_URL et IMBRETEX_TOKEN dans .env');
            return self::FAILURE;
        }

        if ($dryRun) {
            $this->info('=== DRY RUN MODE ===');
        }

        // Test connection
        $this->info('Test de connexion a l\'API Imbretex...');
        try {
            if (! $this->api->testConnection()) {
                $this->error('Echec de connexion a l\'API Imbretex (HTTP non 2xx).');
                return self::FAILURE;
            }
            $this->info('Connexion OK.');
        } catch (\Throwable $e) {
            $this->error("Echec de connexion : {$e->getMessage()}");
            return self::FAILURE;
        }

        if ($stockOnly) {
            return $this->updateStockOnly($dryRun);
        }

        // Full catalog import — page by page to save memory
        $brandFilter = $this->option('brand');
        $limit = (int) $this->option('limit');
        $perPage = 50;
        $page = 1;
        $imported = 0;
        $importedRefs = [];

        $this->info('Recuperation et import du catalogue page par page...');

        do {
            try {
                $response = $this->api->getProductsPage($page, $perPage);
            } catch (\Throwable $e) {
                $this->error("Echec page {$page}: {$e->getMessage()}");
                break;
            }

            $totalPages = $response['totalNumberPage'] ?? 1;
            $products = $response['products'] ?? [];

            if ($page === 1) {
                $this->info(($response['productCount'] ?? '?') . ' produits au total, ' . $totalPages . ' pages.');
            }

            foreach ($products as $product) {
                $brand = strtoupper(trim($product['brands']['name'] ?? ''));

                if ($brandFilter && strtolower($brandFilter) !== 'all') {
                    if (strcasecmp($brand, $brandFilter) !== 0) {
                        continue;
                    }
                } elseif (! in_array($brand, $this->allowedBrands)) {
                    continue;
                }

                if ($limit > 0 && $imported >= $limit) {
                    break 2;
                }

                // Dump structure mode
                if ($this->option('dump-structure')) {
                    $this->info("Structure du produit ({$brand}):");
                    $this->line(json_encode($product, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                    return self::SUCCESS;
                }

                try {
                    $this->importProduct($product, $dryRun);
                    $importedRefs[] = $product['reference'] ?? '';
                    $imported++;
                } catch (\Throwable $e) {
                    $this->stats['errors']++;
                    $ref = $product['reference'] ?? '?';
                    $this->warn("Erreur {$ref}: " . Str::limit($e->getMessage(), 100));
                }
            }

            if ($page % 10 === 0) {
                $this->info("  Page {$page}/{$totalPages} — {$imported} produits importes...");
            }

            $page++;
        } while ($page <= $totalPages);

        $this->stats['products_total'] = $imported;
        $this->info("{$imported} produits importes.");

        if ($imported === 0) {
            $this->warn('Aucun produit importe.');
            $this->displaySummary();
            return self::SUCCESS;
        }

        // Stock & prices via /price-stock/{ref}
        $apiProducts = array_map(fn ($ref) => ['reference' => $ref], array_unique($importedRefs));
        $this->updateStockAndPrices($apiProducts, $dryRun);

        $this->displaySummary();

        if ($dryRun) {
            $this->warn('Dry run termine. Aucune modification effectuee.');
        }

        return self::SUCCESS;
    }

    /**
     * Import a single product from the API response.
     *
     * Product structure (from Swagger):
     * - reference: "CATART24"
     * - brands.name: "REFERENCE TEXTILE"
     * - variants[]: array of variant objects
     *   - variantReference: "BC01T00M"
     *   - title.fr: "T-shirt homme 150"
     *   - longTitle.fr: "T-shirt col rond homme 150g"
     *   - description.fr: "..."
     *   - attributes[]: [{type: "sizes", value: "M"}, {type: "color", value: "Noir", hex: "#000", ...}, {type: "material", value: "100% coton"}]
     *   - grammage: {unit: "g/m²", value: 150}
     *   - netWeight: {unit: "kg", value: 0.15}
     *   - characteristics.genders: ["Homme"]
     *   - categories[]: [{categories.fr: "...", families.fr: "T-SHIRTS"}]
     *   - images[]: [...]
     */
    private function importProduct(array $apiProduct, bool $dryRun): void
    {
        $reference = $apiProduct['reference'] ?? null;
        if (! $reference) {
            return;
        }

        $brandName = $apiProduct['brands']['name'] ?? 'Imbretex';
        $supplier = $this->mapBrand($brandName);
        $variants = $apiProduct['variants'] ?? [];

        if (empty($variants)) {
            return;
        }

        $this->stats['variants_total'] += count($variants);

        // Group variants by color to build product colors/sizes
        $colorGroups = $this->groupVariantsByColor($variants);

        // Use first variant for product-level info
        $firstVariant = $variants[0];
        $name = $firstVariant['title']['fr'] ?? $firstVariant['longTitle']['fr'] ?? $reference;
        $description = $firstVariant['description']['fr'] ?? $firstVariant['longDescription']['fr'] ?? '';
        $shortDescription = $firstVariant['longTitle']['fr'] ?? '';

        // Material from attributes
        $material = null;
        $grammage = null;
        foreach ($firstVariant['attributes'] ?? [] as $attr) {
            if (($attr['type'] ?? '') === 'material' && ! empty($attr['value'])) {
                $val = $attr['value'];
                $material = is_array($val) ? ($val['fr'] ?? reset($val)) : (string) $val;
            }
        }
        if (isset($firstVariant['grammage']['value']) && $firstVariant['grammage']['value']) {
            $grammage = (int) $firstVariant['grammage']['value'];
        }

        $weight = $firstVariant['netWeight']['value'] ?? $firstVariant['averageWeight']['value'] ?? null;
        if ($weight !== null) {
            $weight = (float) $weight;
        }

        $gender = $this->mapGender($firstVariant['characteristics']['genders'] ?? []);
        $categoryId = $this->mapCategory($firstVariant);

        // Product main image — priorite vue front
        $mainImage = null;
        $gallery = [];
        foreach ($variants as $v) {
            foreach ($v['images'] ?? [] as $img) {
                $url = $img['url'] ?? null;
                if (! $url) {
                    continue;
                }
                $gallery[] = $url;
                if (! $mainImage || str_contains($url, '_front') || str_contains($url, '/20/1/')) {
                    $mainImage = $url;
                }
            }
            if ($mainImage && str_contains($mainImage, '_front')) {
                break;
            }
        }
        // S'assurer que c'est la vue front
        if ($mainImage && str_contains($mainImage, 'imbretex')) {
            $mainImage = str_replace('_back', '_front', $mainImage);
            $mainImage = str_replace('_rightside', '_front', $mainImage);
            $mainImage = preg_replace('/\/20\/[23]\//', '/20/1/', $mainImage);
        }

        $slug = $this->generateUniqueSlug($name, $reference);

        $productData = [
            'name' => $name,
            'slug' => $slug,
            'supplier' => $supplier,
            'category_id' => $categoryId,
            'description' => $description,
            'short_description' => $shortDescription,
            'material' => $material,
            'grammage' => $grammage,
            'weight' => $weight,
            'cut' => $gender,
            'main_image' => $mainImage,
            'gallery' => array_unique(array_slice($gallery, 0, 6)),
            'is_active' => true,
            'meta_title' => $name,
            'meta_description' => $shortDescription,
        ];

        if ($dryRun) {
            $existing = Product::where('reference', $reference)->whereIn('supplier', array_values($this->brandMap))->first();
            $this->stats[$existing ? 'products_updated' : 'products_created']++;
            foreach ($colorGroups as $colorName => $sizes) {
                $this->stats['colors_created']++;
                $this->stats['sizes_created'] += count($sizes);
            }
            return;
        }

        // Match on reference alone to avoid duplicate key errors
        // (some products may already exist from previous imports with a different supplier)
        $product = Product::updateOrCreate(
            ['reference' => $reference],
            $productData
        );
        $this->stats[$product->wasRecentlyCreated ? 'products_created' : 'products_updated']++;

        // Import colors and sizes
        $sortOrder = 0;
        foreach ($colorGroups as $colorName => $sizes) {
            $colorInfo = $sizes[0]; // first variant with this color
            $hexCode = $colorInfo['hex'] ?? null;
            if ($hexCode && ! Str::startsWith($hexCode, '#')) {
                $hexCode = '#' . $hexCode;
            }
            if ($hexCode && strlen($hexCode) > 7) {
                $hexCode = substr($hexCode, 0, 7);
            }
            // Use empty string hex as null
            if ($hexCode === '#' || $hexCode === '') {
                $hexCode = null;
            }
            // Fallback: resolve hex from color name
            if (! $hexCode) {
                $hexCode = ColorHelper::resolve($colorName);
            }

            // Get color image — priorite front
            $colorImage = null;
            $colorImageFallback = null;
            foreach ($sizes as $sizeData) {
                foreach ($sizeData['images'] ?? [] as $img) {
                    $url = $img['url'] ?? null;
                    if (! $url) {
                        continue;
                    }
                    if (! $colorImageFallback) {
                        $colorImageFallback = $url;
                    }
                    if (str_contains($url, '_front') || str_contains($url, '/20/1/')) {
                        $colorImage = $url;
                        break 2;
                    }
                }
            }
            $colorImage = $colorImage ?? $colorImageFallback;

            $color = ProductColor::updateOrCreate(
                ['product_id' => $product->id, 'name' => $colorName],
                [
                    'hex_code' => $hexCode,
                    'image' => $colorImage,
                    'sort_order' => $sortOrder++,
                    'is_active' => true,
                ]
            );
            $this->stats[$color->wasRecentlyCreated ? 'colors_created' : 'colors_updated']++;

            // Sizes
            foreach ($sizes as $sizeData) {
                $sizeName = $this->normalizeSizeName($sizeData['size']);
                $variantRef = $sizeData['variantReference'] ?? '';

                $size = ProductSize::updateOrCreate(
                    ['product_color_id' => $color->id, 'size' => $sizeName],
                    [
                        'sku' => $variantRef,
                        'stock' => 0,
                        'is_available' => true,
                    ]
                );
                $this->stats[$size->wasRecentlyCreated ? 'sizes_created' : 'sizes_updated']++;
            }
        }
    }

    /**
     * Group variants by color name.
     * Returns: [colorName => [['size' => 'M', 'variantReference' => '...', 'hex' => '...', ...], ...]]
     */
    private function groupVariantsByColor(array $variants): array
    {
        $groups = [];

        foreach ($variants as $variant) {
            $colorName = 'Default';
            $colorCode = '';
            $hex = '';
            $size = 'Unique';

            foreach ($variant['attributes'] ?? [] as $attr) {
                $type = $attr['type'] ?? '';
                if ($type === 'color') {
                    $colorName = $attr['value'] ?? 'Default';
                    $colorCode = $attr['colorCode'] ?? '';
                    $hex = $attr['hex'] ?? '';
                } elseif ($type === 'sizes') {
                    $size = $attr['value'] ?? 'Unique';
                }
            }

            $groups[$colorName][] = [
                'size' => $size,
                'variantReference' => $variant['variantReference'] ?? '',
                'colorCode' => $colorCode,
                'hex' => $hex,
                'images' => $variant['images'] ?? [],
            ];
        }

        return $groups;
    }

    // ─── Stock & Prices ──────────────────────────────────────────

    /**
     * Update stock + prices via GET /products/price-stock/{productRef}
     * for each imported product. This endpoint returns all variants
     * with their price and stock in a single call.
     */
    private function updateStockAndPrices(array $apiProducts, bool $dryRun): void
    {
        $refs = array_unique(array_column($apiProducts, 'reference'));
        $this->info('MAJ prix/stocks pour ' . count($refs) . ' produits via /price-stock...');

        if ($dryRun) {
            return;
        }

        $suppliers = array_unique(array_values($this->brandMap));
        $sizePattern = '/(3XL|4XL|5XL|XXL|XL|XS|XXS|L|M|S)$/';

        $bar = $this->output->createProgressBar(count($refs));
        $bar->start();

        foreach ($refs as $ref) {
            try {
                $items = $this->api->getStockPriceByReference($ref);
                if (empty($items)) {
                    $bar->advance();
                    continue;
                }

                // Find our product
                $product = Product::where('reference', $ref)
                    ->whereIn('supplier', $suppliers)
                    ->first();
                if (! $product) {
                    $bar->advance();
                    continue;
                }

                // Build SKU map for this product only
                $dbSizes = ProductSize::whereHas('color', fn ($q) => $q->where('product_id', $product->id))
                    ->whereNotNull('sku')
                    ->where('sku', '!=', '')
                    ->get();

                // Try direct SKU match first
                $skuMap = [];
                foreach ($dbSizes as $ps) {
                    $skuMap[$ps->sku] = $ps;
                }

                $directMatches = 0;
                foreach ($items as $item) {
                    if (isset($skuMap[$item['code'] ?? ''])) {
                        $directMatches++;
                    }
                }

                $minPrice = null;

                if ($directMatches > 0 && $directMatches >= count($dbSizes) * 0.5) {
                    // Direct match works — use it
                    foreach ($items as $item) {
                        $code = $item['code'] ?? '';
                        $price = (float) ($item['price'] ?? 0);
                        $totalStock = (int) ($item['stock'] ?? 0) + (int) ($item['stock_supplier'] ?? 0);

                        if ($price > 0 && ($minPrice === null || $price < $minPrice)) {
                            $minPrice = $price;
                        }

                        if (isset($skuMap[$code])) {
                            $skuMap[$code]->update([
                                'stock' => $totalStock,
                                'is_available' => $totalStock > 0,
                            ]);
                            $this->stats['stock_updated']++;
                        }
                    }
                } else {
                    // SKU mismatch: catalog variantReference differs from stock API code.
                    // Match by grouping both by color code (prefix after ref, before size suffix),
                    // then map colors by size count and update SKUs.

                    // Group API items by color code
                    $apiByColor = [];
                    foreach ($items as $item) {
                        $code = $item['code'] ?? '';
                        $price = (float) ($item['price'] ?? 0);
                        if ($price > 0 && ($minPrice === null || $price < $minPrice)) {
                            $minPrice = $price;
                        }
                        $afterRef = substr($code, strlen($ref));
                        if (preg_match($sizePattern, $afterRef, $m)) {
                            $size = $m[0];
                            $colorPart = substr($afterRef, 0, strlen($afterRef) - strlen($size));
                            $apiByColor[$colorPart][$size] = $item;
                        }
                    }

                    // Group DB items by color code
                    $dbByColor = [];
                    foreach ($dbSizes as $ps) {
                        $afterRef = substr($ps->sku, strlen($ref));
                        if (preg_match($sizePattern, $afterRef, $m)) {
                            $size = $m[0];
                            $colorPart = substr($afterRef, 0, strlen($afterRef) - strlen($size));
                            $dbByColor[$colorPart][$size] = $ps;
                        }
                    }

                    // Match colors by identical size sets
                    $matched = [];
                    $usedApiColors = [];
                    foreach ($dbByColor as $dbCC => $dbSizeMap) {
                        $dbSizeNames = array_keys($dbSizeMap);
                        sort($dbSizeNames);
                        foreach ($apiByColor as $apiCC => $apiSizeMap) {
                            if (isset($usedApiColors[$apiCC])) {
                                continue;
                            }
                            $apiSizeNames = array_keys($apiSizeMap);
                            sort($apiSizeNames);
                            if ($dbSizeNames === $apiSizeNames) {
                                $matched[$dbCC] = $apiCC;
                                $usedApiColors[$apiCC] = true;
                                break;
                            }
                        }
                    }

                    // Fallback: match remaining by similar size count
                    $remainingDb = array_diff_key($dbByColor, $matched);
                    $remainingApi = array_diff_key($apiByColor, $usedApiColors);
                    if (! empty($remainingDb) && ! empty($remainingApi)) {
                        $dbKeys = array_keys($remainingDb);
                        $apiKeys = array_keys($remainingApi);
                        for ($i = 0; $i < min(count($dbKeys), count($apiKeys)); $i++) {
                            $matched[$dbKeys[$i]] = $apiKeys[$i];
                        }
                    }

                    // Update SKUs and stocks
                    foreach ($matched as $dbCC => $apiCC) {
                        foreach ($dbByColor[$dbCC] as $size => $ps) {
                            if (isset($apiByColor[$apiCC][$size])) {
                                $item = $apiByColor[$apiCC][$size];
                                $newSku = $ref . $apiCC . $size;
                                $totalStock = (int) ($item['stock'] ?? 0) + (int) ($item['stock_supplier'] ?? 0);
                                $ps->update([
                                    'sku' => $newSku,
                                    'stock' => $totalStock,
                                    'is_available' => $totalStock > 0,
                                ]);
                                $this->stats['stock_updated']++;
                            }
                        }
                    }
                }

                // Set supplier_price + base_price on the product
                if ($minPrice && $minPrice > 0) {
                    $basePrice = PricingRule::calculate($minPrice, 1);
                    $product->update([
                        'supplier_price' => $minPrice,
                        'base_price' => $basePrice,
                    ]);
                    $this->stats['prices_updated']++;
                }
            } catch (\Throwable $e) {
                // Silently skip — some refs may not exist in price-stock
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();

        // Aggregate stock at color/product level
        $this->aggregateStocks($suppliers);
    }

    /**
     * Roll up size-level stock to color and product level.
     */
    private function aggregateStocks(array $suppliers): void
    {
        $products = Product::whereIn('supplier', $suppliers)->with('colors.sizes')->get();

        foreach ($products as $product) {
            $totalStock = 0;
            foreach ($product->colors as $color) {
                $colorStock = $color->sizes->sum('stock');
                $color->update(['stock' => $colorStock]);
                $totalStock += $colorStock;
            }
            $product->update(['stock' => $totalStock]);
        }
    }

    private function updateStockOnly(bool $dryRun): int
    {
        $suppliers = array_unique(array_values($this->brandMap));
        $products = Product::whereIn('supplier', $suppliers)->get();
        $this->info("Mode MAJ stocks/prix — {$products->count()} produits en base.");

        if ($products->isEmpty()) {
            $this->warn('Aucun produit Imbretex. Lancez d\'abord un import complet.');
            return self::SUCCESS;
        }

        // Build fake apiProducts array with just references
        $apiProducts = $products->map(fn ($p) => ['reference' => $p->reference])->toArray();
        $this->updateStockAndPrices($apiProducts, $dryRun);
        $this->displaySummary();

        if ($dryRun) {
            $this->warn('Dry run termine. Aucune modification effectuee.');
        }

        return self::SUCCESS;
    }

    // ─── Category mapping ────────────────────────────────────────

    private array $categoryCache = [];

    /** Family (fr) → our category slug — most specific, checked first */
    private array $familyMap = [
        // T-shirts
        '100% COTON' => 't-shirts-col-rond-coton',
        'COL ROND' => 't-shirts-col-rond-coton',
        'COL V' => 't-shirts-col-v',
        'MANCHES LONGUES' => 't-shirts-manches-longues',
        'MANCHES RAGLAN' => 't-shirts-bicolor',
        'DÉBARDEUR' => 'debardeurs',
        '100% POLYESTER' => 't-shirts-polyester',
        'OVERSIZE' => 't-shirts-col-rond-coton',
        // Sweats
        'SWEAT CAPUCHE' => 'sweats-capuche',
        'SWEAT ZIPPÉ CAPUCHE' => 'sweats-zippes-capuche',
        'SWEAT ZIPPE CAPUCHE' => 'sweats-zippes-capuche',
        'SWEAT ZIPPÉ' => 'sweats-zippes',
        'SWEAT-SHIRT' => 'sweats-col-rond',
        'CAPUCHE' => 'sweats-capuche',
        '1/4 ZIP' => 'sweats-zippes',
        // Polos
        'POLO' => 'polos-manches-courtes',
        // Chemises
        'CHEMISE' => 'chemises-manches-longues',
        'POPELINE : POLYESTER - COTON' => 'chemises-manches-courtes',
        'OXFORD' => 'chemises-manches-longues',
        // Polaires
        'POLAIRE' => 'pulls-polaire',
        'MICROPOLAIRE' => 'gilets-polaire',
        // Vestes / Manteaux
        'SOFTSHELL' => 'softshells',
        'COUPE-VENT' => 'coupe-vent',
        'PARKA' => 'parkas',
        'DOUDOUNE' => 'doudounes',
        'MATELASSÉ' => 'doudounes',
        'BLOUSON' => 'vestes-ete',
        'VESTE - BLOUSON' => 'vestes-ete',
        'VESTE' => 'vestes-ete',
        'BOMBER' => 'vestes-ete',
        '3 EN 1' => 'parkas',
        'BODYWARMER' => 'bodywarmer-pro',
        // Pantalons
        'JOGGING' => 'jogging-sport',
        'PANTALON' => 'pantalons-longs',
        'PANTALONS' => 'pantalons-longs',
        'SHORT' => 'shorts-bermudas',
        'LEGGING' => 'pantalons-longs',
        'CHINO' => 'pantalons-longs',
        'PANTALONS COMPATIBLES GENOUILLERES' => 'pantalons-de-travail',
        'MULTI-POCHES' => 'pantalons-de-travail',
        'COMBINAISON' => 'combinaisons-travail',
        // Casquettes / Bonnets
        'CASQUETTE' => 'casquettes-classiques',
        'VISIERE INCURVEE' => 'casquettes-classiques',
        'VISIERE PLATE' => 'casquettes-snapback',
        'SNAPBACK' => 'casquettes-snapback',
        'TRUCKER' => 'casquettes-classiques',
        'BASEBALL' => 'casquettes-classiques',
        'DAD HAT' => 'casquettes-classiques',
        'ARMY' => 'casquettes-classiques',
        '5 PANS' => 'casquettes-classiques',
        '6 PANS' => 'casquettes-classiques',
        'RAPPER' => 'casquettes-snapback',
        'CHAPEAU' => 'chapeaux',
        'BONNET' => 'bonnets',
        'POMPOM' => 'bonnets',
        'POMPON' => 'bonnets',
        'ECHARPE' => 'echarpes-gants',
        'GANTS' => 'echarpes-gants',
        // Sacs
        'BAGAGERIE' => 'sacs-polyester',
        'SAC SHOPPING' => 'sacs-coton',
        'SAC À DOS' => 'sacs-a-dos',
        'SAC A DOS' => 'sacs-a-dos',
        'SAC BANDOULIERE' => 'sacoches-polyester',
        'SAC - POCHETTE ORDINATEUR' => 'sacoches-polyester',
        'SAC DE SPORT' => 'sacs-polyester',
        'SAC DE VOYAGE' => 'valises',
        'SAC A ROULETTES' => 'valises',
        'SAC GYM' => 'sacs-polyester',
        'SAC CORDON' => 'sacs-polyester',
        'SAC BANANE' => 'sacoches-polyester',
        'SAC ORGANIQUE' => 'sacs-coton',
        'SAC TOILE JUTE' => 'sacs-coton',
        'SAC TOILE DE JUTE' => 'sacs-coton',
        'SAC ISOTHERME' => 'sacs-polyester',
        'SAC IMPERMÉABLE' => 'sacs-polyester',
        'SAC CABAS' => 'sacs-coton',
        'SAC À CHAUSSURES' => 'sacs-polyester',
        'SAC' => 'sacs-polyester',
        'POCHETTE' => 'portefeuilles-trousses',
        'TROUSSE' => 'portefeuilles-trousses',
        'PORTEFEUILLE' => 'portefeuilles-trousses',
        'ORGANISER' => 'portefeuilles-trousses',
        'PORTE-CLE' => 'portefeuilles-trousses',
        // Sport
        'SPORT' => 'maillots-t-shirts-sport',
        'TECHNIQUE' => 'maillots-t-shirts-sport',
        // Workwear
        'WORKWEAR' => 'hauts-de-travail',
        'GILET SÉCURITÉ' => 'haute-visibilite',
        'GILET SECURITE' => 'haute-visibilite',
        'SECURITE' => 'haute-visibilite',
        'HAUTE-VISIBILITE' => 'haute-visibilite',
        'HAUTE-VISIBILITÉ' => 'haute-visibilite',
        'HAUTE VISIBILITE' => 'haute-visibilite',
        'VISIBILITE AUGMENTEE' => 'haute-visibilite',
        // Maison / Divers
        'TABLIER' => 'tabliers',
        'SERVIETTE' => 'serviettes-bain-peignoirs',
        'TORCHON' => 'cuisine-bain',
        'PLAID' => 'couvertures',
        'HOUSSE COUSSIN' => 'couvertures',
        'LINGE DE MAISON' => 'maison',
        'MARQUAGE' => 'accessoires',
        // Sous-vêtements
        'SLIP - BOXER' => 'slips-calecons',
        'BRASSIÈRE' => 'slips-calecons',
        'SOUS-VETEMENTS' => 'slips-calecons',
        // Chaussures
        'CHAUSSURES' => 'accessoires',
        'SANDALES' => 'accessoires',
        // Enfant
        'ENFANT' => 'accessoires-bebe',
    ];

    /** Category (fr) → our category slug — broader fallback */
    private array $apiCategoryMap = [
        'TEE-SHIRT' => 't-shirts-col-rond-coton',
        'SWEAT-SHIRT' => 'sweats-col-rond',
        'POLO' => 'polos-manches-courtes',
        'CHEMISE' => 'chemises-manches-longues',
        'CASQUETTE' => 'casquettes-classiques',
        'BONNET' => 'bonnets',
        'BAGAGERIE' => 'sacs-polyester',
        'SAC SHOPPING' => 'sacs-coton',
        'VESTE - BLOUSON' => 'vestes-ete',
        'SOFTSHELL' => 'softshells',
        'PANTALONS' => 'pantalons-longs',
        'POLAIRE' => 'pulls-polaire',
        'BODYWARMER' => 'bodywarmer-pro',
        'SPORT' => 'maillots-t-shirts-sport',
        'WORKWEAR' => 'hauts-de-travail',
        'HAUTE VISIBILITE' => 'haute-visibilite',
        'ACCESSOIRES HIVER' => 'echarpes-gants',
        'ACCESSOIRES' => 'accessoires',
        'ENFANT' => 'accessoires-bebe',
        'BIO' => 'bio',
        'RECYCLÉ' => 'bio',
        'LINGE DE MAISON' => 'maison',
        'TABLIER' => 'tabliers',
        'TENUE PROFESSIONNELLE' => 'vetements-de-travail',
        'SOUS-VETEMENTS' => 'slips-calecons',
        'CHAUSSURES' => 'accessoires',
        'CHASUBLE' => 'chasubles-dossards',
        'EPONGE' => 'serviettes-bain-peignoirs',
        // These are "modifier" categories — skip them and use other info
        'NO LABEL / TEAR AWAY' => null,
        'NO LABEL/TEAR AWAY' => null,
        'FIN DE SERIE' => null,
        'SCHOOLWEAR' => null,
        '24' => null,
        '60°C' => null,
    ];

    private function mapCategory(array $variant): int
    {
        if (empty($this->categoryCache)) {
            $this->categoryCache = Category::pluck('id', 'slug')->toArray();
        }

        $categories = $variant['categories'] ?? [];

        // Collect all API categories and families for this variant
        $apiCats = [];
        $apiFams = [];
        foreach ($categories as $catEntry) {
            $c = mb_strtoupper(trim($catEntry['categories']['fr'] ?? ''));
            $f = mb_strtoupper(trim($catEntry['families']['fr'] ?? ''));
            if ($c) $apiCats[] = $c;
            if ($f) $apiFams[] = $f;
        }

        // Extract material from attributes
        $material = '';
        foreach ($variant['attributes'] ?? [] as $attr) {
            if (($attr['type'] ?? '') === 'material') {
                $val = $attr['value'] ?? '';
                $material = mb_strtolower(is_array($val) ? ($val['fr'] ?? reset($val)) : (string) $val);
            }
        }

        $title = mb_strtolower($variant['title']['fr'] ?? $variant['longTitle']['fr'] ?? '');
        $isSport = in_array('SPORT', $apiCats) || str_contains($material, 'polyester') && !str_contains($material, 'coton');

        // 1. Context-aware mapping: combine category + family
        $slug = $this->resolveCategory($apiCats, $apiFams, $title, $material, $isSport);
        if ($slug && isset($this->categoryCache[$slug])) {
            return $this->categoryCache[$slug];
        }

        // 2. Fallback to uncategorized
        return $this->getOrCreateImbretexCategory();
    }

    private function resolveCategory(array $apiCats, array $apiFams, string $title, string $material, bool $isSport): ?string
    {
        // Filter out "modifier" categories
        $skipCats = ['NO LABEL / TEAR AWAY', 'NO LABEL/TEAR AWAY', 'FIN DE SERIE', 'SCHOOLWEAR', '24', '60°C', 'RECYCLÉ', 'BIO'];
        $mainCats = array_diff($apiCats, $skipCats);
        $mainCat = reset($mainCats) ?: '';

        // ─── T-shirts ───
        if ($mainCat === 'TEE-SHIRT' || in_array('TEE-SHIRT', $mainCats)) {
            if (in_array('DÉBARDEUR', $apiFams) || str_contains($title, 'débardeur') || str_contains($title, 'sans manche')) {
                return 'debardeurs';
            }
            if (in_array('COL V', $apiFams) || str_contains($title, 'col v')) {
                return 't-shirts-col-v';
            }
            if (in_array('MANCHES LONGUES', $apiFams) || str_contains($title, 'manches longues')) {
                return 't-shirts-manches-longues';
            }
            if ($isSport || in_array('100% POLYESTER', $apiFams) || in_array('POLYESTER', $apiFams) || in_array('TECHNIQUE', $apiFams)) {
                return 't-shirts-polyester';
            }
            if (in_array('MANCHES RAGLAN', $apiFams)) {
                return 't-shirts-bicolor';
            }
            return 't-shirts-col-rond-coton';
        }

        // ─── Sweats ───
        if ($mainCat === 'SWEAT-SHIRT' || in_array('SWEAT-SHIRT', $mainCats)) {
            if (in_array('SWEAT ZIPPÉ CAPUCHE', $apiFams) || in_array('SWEAT ZIPPE CAPUCHE', $apiFams) || in_array('SWEAT ZIPPE CAPUCHE', $apiFams)) {
                return 'sweats-zippes-capuche';
            }
            if (in_array('SWEAT ZIPPÉ', $apiFams) || in_array('ZIPPÉE', $apiFams) || in_array('SWEAT ZIPPÉ', $apiFams)) {
                return str_contains($title, 'capuche') ? 'sweats-zippes-capuche' : 'sweats-zippes';
            }
            if (in_array('SWEAT CAPUCHE', $apiFams) || in_array('CAPUCHE', $apiFams) || str_contains($title, 'hoodie') || str_contains($title, 'capuche')) {
                return 'sweats-capuche';
            }
            return 'sweats-col-rond';
        }

        // ─── Polos ───
        if ($mainCat === 'POLO' || in_array('POLO', $mainCats)) {
            if ($isSport) return 'polos-sport';
            if (str_contains($title, 'manches longues')) return 'polos-manches-longues';
            return 'polos-manches-courtes';
        }

        // ─── Chemises ───
        if ($mainCat === 'CHEMISE') {
            return str_contains($title, 'manches courtes') ? 'chemises-manches-courtes' : 'chemises-manches-longues';
        }

        // ─── Vestes / Manteaux ───
        if ($mainCat === 'VESTE - BLOUSON' || in_array('VESTE - BLOUSON', $mainCats)) {
            if (in_array('DOUDOUNE', $apiFams) || in_array('MATELASSÉ', $apiFams)) return 'doudounes';
            if (in_array('PARKA', $apiFams)) return 'parkas';
            if (in_array('BOMBER', $apiFams)) return 'vestes-ete';
            if (in_array('3 EN 1', $apiFams)) return 'parkas';
            if (in_array('BLOUSON', $apiFams)) return 'vestes-ete';
            return 'vestes-ete';
        }
        if ($mainCat === 'SOFTSHELL' || in_array('SOFTSHELL', $mainCats)) {
            if (in_array('BODYWARMER', $apiFams)) return 'bodywarmer-pro';
            return 'softshells';
        }
        if ($mainCat === 'BODYWARMER' || in_array('BODYWARMER', $mainCats)) return 'bodywarmer-pro';
        if (in_array('POLAIRE', $mainCats) || $mainCat === 'POLAIRE') {
            if (in_array('MICROPOLAIRE', $apiFams) || str_contains($title, 'sans manche')) return 'gilets-polaire';
            return 'pulls-polaire';
        }

        // ─── Pantalons ───
        if ($mainCat === 'PANTALONS' || in_array('PANTALONS', $mainCats)) {
            if (in_array('SHORT', $apiFams) || str_contains($title, 'short')) return $isSport ? 'shorts-sport' : 'shorts-bermudas';
            if (in_array('JOGGING', $apiFams) || str_contains($title, 'jogging')) return 'jogging-sport';
            if (in_array('MULTI-POCHES', $apiFams) || in_array('PANTALONS COMPATIBLES GENOUILLERES', $apiFams)) return 'pantalons-de-travail';
            if (in_array('COMBINAISON', $apiFams)) return 'combinaisons-travail';
            if (in_array('LEGGING', $apiFams)) return 'pantalons-longs';
            return 'pantalons-longs';
        }

        // ─── Casquettes ───
        if ($mainCat === 'CASQUETTE' || in_array('CASQUETTE', $mainCats)) {
            if (in_array('VISIERE PLATE', $apiFams) || in_array('SNAPBACK', $apiFams) || in_array('RAPPER', $apiFams)) return 'casquettes-snapback';
            if (in_array('CHAPEAU', $apiFams) || str_contains($title, 'bob') || str_contains($title, 'bucket')) return 'bobs';
            return 'casquettes-classiques';
        }

        // ─── Bonnets ───
        if ($mainCat === 'BONNET' || in_array('BONNET', $mainCats)) {
            if (in_array('ECHARPE', $apiFams) || in_array('GANTS', $apiFams)) return 'echarpes-gants';
            return 'bonnets';
        }

        // ─── Accessoires hiver ───
        if ($mainCat === 'ACCESSOIRES HIVER' || in_array('ACCESSOIRES HIVER', $mainCats)) {
            if (in_array('BONNET', $apiFams) || in_array('POMPON', $apiFams) || in_array('DOUBLÉ', $apiFams)) return 'bonnets';
            if (in_array('ECHARPE', $apiFams) || in_array('GANTS', $apiFams)) return 'echarpes-gants';
            return 'bonnets';
        }

        // ─── Bagagerie / Sacs ───
        if ($mainCat === 'BAGAGERIE' || in_array('BAGAGERIE', $mainCats) || $mainCat === 'SAC SHOPPING') {
            if (in_array('SAC SHOPPING', $apiFams) || in_array('SAC ORGANIQUE', $apiFams) || in_array('SAC TOILE JUTE', $apiFams) || in_array('SAC TOILE DE JUTE', $apiFams) || in_array('SAC CABAS', $apiFams)) {
                // Polyester/nylon bags go to sacs-polyester even if family says SHOPPING
                if (str_contains($material, 'polyester') || str_contains($material, 'nylon') || str_contains($material, 'feutrine')) {
                    return 'sacs-polyester';
                }
                return 'sacs-coton';
            }
            if (in_array('SAC À DOS', $apiFams) || in_array('SAC A DOS', $apiFams)) return 'sacs-a-dos';
            if (in_array('SAC BANDOULIERE', $apiFams) || in_array('SAC - POCHETTE ORDINATEUR', $apiFams) || in_array('SAC BANANE', $apiFams)) return 'sacoches-polyester';
            if (in_array('POCHETTE', $apiFams) || in_array('TROUSSE', $apiFams) || in_array('PORTEFEUILLE', $apiFams) || in_array('ORGANISER', $apiFams) || in_array('PORTE-CLE', $apiFams)) return 'portefeuilles-trousses';
            if (in_array('SAC DE VOYAGE', $apiFams) || in_array('SAC A ROULETTES', $apiFams)) return 'valises';
            return 'sacs-polyester';
        }

        // ─── Sport ───
        if ($mainCat === 'SPORT' || in_array('SPORT', $mainCats)) {
            if (str_contains($title, 'short')) return 'shorts-sport';
            if (str_contains($title, 'jogging') || str_contains($title, 'pantalon')) return 'jogging-sport';
            if (str_contains($title, 'veste')) return 'vestes-sport';
            return 'maillots-t-shirts-sport';
        }

        // ─── Workwear ───
        if ($mainCat === 'WORKWEAR' || in_array('WORKWEAR', $mainCats)) {
            if (in_array('GILET SÉCURITÉ', $apiFams) || in_array('GILET SECURITE', $apiFams) || in_array('HAUTE-VISIBILITE', $apiFams) || in_array('HAUTE-VISIBILITÉ', $apiFams)) return 'haute-visibilite';
            if (in_array('MULTI-POCHES', $apiFams) || in_array('PANTALONS COMPATIBLES GENOUILLERES', $apiFams)) return 'pantalons-de-travail';
            return 'hauts-de-travail';
        }
        if ($mainCat === 'HAUTE VISIBILITE' || in_array('HAUTE VISIBILITE', $mainCats)) return 'haute-visibilite';

        // ─── Enfant ───
        if ($mainCat === 'ENFANT' || in_array('ENFANT', $mainCats)) {
            // Categorize children's items by type using families
            foreach ($apiFams as $f) {
                if (isset($this->familyMap[$f])) return $this->familyMap[$f];
            }
            return 'accessoires-bebe';
        }

        // ─── Maison / Divers ───
        if ($mainCat === 'LINGE DE MAISON') return 'maison';
        if (in_array('TABLIER', $mainCats)) return 'tabliers';
        if (in_array('SOUS-VETEMENTS', $mainCats)) return 'slips-calecons';
        if (in_array('CHASUBLE', $mainCats)) return 'chasubles-dossards';
        if (in_array('EPONGE', $mainCats)) return 'serviettes-bain-peignoirs';
        if ($mainCat === 'ACCESSOIRES') return 'accessoires';

        // ─── Family-only fallback for products with only modifier categories ───
        foreach ($apiFams as $f) {
            if (isset($this->familyMap[$f])) {
                return $this->familyMap[$f];
            }
        }

        return null;
    }

    private ?int $imbretexParentCategoryId = null;

    private function getOrCreateImbretexCategory(): int
    {
        if (! $this->imbretexParentCategoryId) {
            $parent = Category::firstOrCreate(
                ['slug' => 'imbretex'],
                ['name' => 'Imbretex', 'is_active' => true, 'sort_order' => 998]
            );
            $this->imbretexParentCategoryId = $parent->id;
        }

        $sub = Category::firstOrCreate(
            ['slug' => 'imbretex-non-classe'],
            [
                'name' => 'Imbretex - Non classé',
                'parent_id' => $this->imbretexParentCategoryId,
                'is_active' => true,
                'sort_order' => 0,
            ]
        );

        return $sub->id;
    }

    // ─── Helpers ─────────────────────────────────────────────────

    private function mapBrand(string $brandName): string
    {
        $upper = Str::upper(trim($brandName));

        return $this->brandMap[$upper] ?? $this->brandMap[Str::title(trim($brandName))] ?? trim($brandName);
    }

    private function mapGender(array $genders): string
    {
        if (empty($genders)) {
            return 'mixte';
        }

        $first = Str::lower($genders[0] ?? '');

        return match (true) {
            Str::contains($first, ['homme', 'man', 'male', 'men']) => 'homme',
            Str::contains($first, ['femme', 'woman', 'female', 'women']) => 'femme',
            Str::contains($first, ['enfant', 'child', 'kid', 'junior']) => 'enfant',
            Str::contains($first, ['unisex', 'mixte']) => 'mixte',
            default => 'mixte',
        };
    }

    private function normalizeSizeName(string $size): string
    {
        $size = trim(strtoupper($size));

        $aliases = [
            'XXXS' => 'XXS',
            'XXXL' => '3XL',
            'XXXXL' => '4XL',
            'XXXXXL' => '5XL',
            '0' => 'Unique',
        ];

        return $aliases[$size] ?? $size;
    }

    private function generateUniqueSlug(string $name, string $reference): string
    {
        $baseSlug = Str::slug($name);
        if (! $baseSlug) {
            $baseSlug = Str::slug($reference);
        }

        $slug = $baseSlug;
        $counter = 1;

        while (Product::where('slug', $slug)->where('reference', '!=', $reference)->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    private function displaySummary(): void
    {
        $this->newLine();
        $this->info('=== Resume import Imbretex ===');
        $this->table(
            ['Metrique', 'Total'],
            collect($this->stats)->map(fn ($val, $key) => [
                str_replace('_', ' ', ucfirst($key)),
                $val,
            ])->values()->toArray()
        );
    }
}
