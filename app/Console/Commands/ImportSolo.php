<?php

namespace App\Console\Commands;

use App\Helpers\ColorHelper;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductColor;
use App\Models\ProductSize;
use App\Services\SoloApiService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ImportSolo extends Command
{
    protected $signature = 'import:solo
        {--dry-run : Show what would be done without modifying anything}
        {--brand= : Brand filter (SOL\'S, NEOBLU, ATF, RTP APPAREL, all)}
        {--limit=0 : Limit number of products to import (0 = all)}
        {--stock-only : Only update stock for existing Solo products}
        {--prices-only : Only update prices (currently unavailable)}
        {--dump-structure : Dump the raw JSON structure and stop}';

    protected $description = 'Import/update Solo Paris (SOL\'S, NEOBLU, ATF) products via Gravitee API (OAuth2)';

    private SoloApiService $api;
    private array $categoryCache = [];

    private array $stats = [
        'products_created' => 0,
        'products_updated' => 0,
        'colors_created' => 0,
        'sizes_created' => 0,
        'stock_updated' => 0,
        'prices_updated' => 0,
        'skipped' => 0,
        'errors' => 0,
    ];

    public function handle(SoloApiService $api): int
    {
        $this->api = $api;
        $dryRun = $this->option('dry-run');

        if (! $this->api->isConfigured()) {
            $this->error('Solo API non configurée. Vérifiez SOLO_CLIENT_ID, SOLO_CLIENT_SECRET et SOLO_BASE_URL dans .env');

            return self::FAILURE;
        }

        if ($dryRun) {
            $this->info('=== DRY RUN MODE ===');
        }

        $this->info('Test de connexion OAuth2 Solo...');
        $result = $this->api->testConnection();
        if (! $result['success']) {
            $this->warn($result['message']);
            if (! empty($result['details'])) {
                $this->table(
                    ['Endpoint', 'Path', 'HTTP', 'Status'],
                    collect($result['details'])->map(fn ($r, $name) => [
                        str_replace('_', ' ', $name),
                        $r['path'],
                        $r['status'],
                        $r['ok'] ? '✓ Existe (auth requise)' : '✗ Introuvable',
                    ])->values()->toArray()
                );
            }

            return self::FAILURE;
        }
        $this->info("Connexion OAuth2 OK. Token: {$result['token_preview']}");

        if ($this->option('dump-structure')) {
            return $this->dumpStructure();
        }

        if ($this->option('stock-only')) {
            $this->updateStock($dryRun);
            $this->displaySummary();

            return self::SUCCESS;
        }

        if ($this->option('prices-only')) {
            $this->updatePrices($dryRun);
            $this->displaySummary();

            return self::SUCCESS;
        }

        $this->importCatalogue($dryRun);
        $this->displaySummary();

        if ($dryRun) {
            $this->warn('Dry run terminé. Aucune modification effectuée.');
        }

        return self::SUCCESS;
    }

    // ─── Dump ─────────────────────────────────────────────────────

    private function dumpStructure(): int
    {
        $this->info('Récupération du catalogue par modèle...');
        try {
            $data = $this->api->getCatalogue();
            $items = $this->extractItems($data);
            $this->info(count($items) . ' modèles trouvés.');

            if (! empty($items)) {
                $sample = array_slice($items, 0, 2);
                $this->line(json_encode($sample, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            }

            // Show brand distribution
            $brands = [];
            foreach ($items as $item) {
                $b = $item['Brand'] ?? 'unknown';
                $brands[$b] = ($brands[$b] ?? 0) + 1;
            }
            $this->newLine();
            $this->info('Marques :');
            foreach ($brands as $b => $c) {
                $this->line("  {$b}: {$c} produits");
            }
        } catch (\Throwable $e) {
            $this->error("Erreur catalogue: {$e->getMessage()}");
        }

        $this->newLine();
        $this->info('Récupération des SKUs (sample model 00548)...');
        try {
            $data = $this->api->getProductBySku('00548');
            $items = $this->extractItems($data);
            $this->info(count($items) . ' SKUs pour ce modèle.');
            if (! empty($items)) {
                $sample = array_slice($items, 0, 2);
                $this->line(json_encode($sample, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            }
        } catch (\Throwable $e) {
            $this->error("Erreur SKU: {$e->getMessage()}");
        }

        $this->newLine();
        $this->info('Récupération des stocks (entrepôt France 001, model 00548)...');
        try {
            $stockItems = $this->api->getStock(warehouse: '001', model: '00548');
            $this->info(count($stockItems) . ' entrées stock.');
            if (! empty($stockItems)) {
                $this->line(json_encode(array_slice($stockItems, 0, 5), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            }
        } catch (\Throwable $e) {
            $this->error("Erreur stock: {$e->getMessage()}");
        }

        $this->newLine();
        $this->info('Test endpoint prix client...');
        try {
            $this->api->getPrices();
        } catch (\Throwable $e) {
            $this->warn($e->getMessage());
        }

        return self::SUCCESS;
    }

    // ─── Import ───────────────────────────────────────────────────

    private function importCatalogue(bool $dryRun): void
    {
        $brandFilter = $this->option('brand');
        $limit = (int) $this->option('limit');

        // Load category cache
        $this->categoryCache = Category::pluck('id', 'slug')->toArray();

        // Phase 1: Fetch all models (product-level data)
        $this->info('Phase 1: Récupération du catalogue par modèle...');
        try {
            $models = $this->extractItems($this->api->getCatalogue());
        } catch (\Throwable $e) {
            $this->error("Erreur catalogue: {$e->getMessage()}");
            $this->stats['errors']++;

            return;
        }

        // Filter by brand if specified
        if ($brandFilter) {
            $models = array_values(array_filter($models, function ($item) use ($brandFilter) {
                if (strtolower($brandFilter) === 'all') {
                    return true;
                }
                $brand = $item['Brand'] ?? '';

                return stripos($brand, $brandFilter) !== false;
            }));
            $this->info(count($models) . " modèles pour la marque '{$brandFilter}'.");
        } else {
            $this->info(count($models) . ' modèles dans le catalogue.');
        }

        if ($limit > 0) {
            $models = array_slice($models, 0, $limit);
            $this->info("Limité à {$limit} produits.");
        }

        // Phase 2: Fetch all SKUs (color/size data) — large dataset, ~18k entries
        $this->info('Phase 2: Récupération des SKUs (couleurs/tailles)...');
        try {
            $allSkus = $this->extractItems($this->api->getAllSkus());
        } catch (\Throwable $e) {
            $this->error("Erreur SKUs: {$e->getMessage()}");
            $this->warn('Import sans couleurs/tailles — seules les fiches produit seront créées.');
            $allSkus = [];
        }

        // Index SKUs by modelCode for fast lookup
        $skusByModel = [];
        foreach ($allSkus as $sku) {
            $modelCode = $sku['modelCode'] ?? '';
            if ($modelCode) {
                $skusByModel[$modelCode][] = $sku;
            }
        }
        $this->info(count($allSkus) . ' SKUs indexés pour ' . count($skusByModel) . ' modèles.');
        unset($allSkus); // free memory

        // Phase 3: Create/update products with colors and sizes
        $this->info('Phase 3: Import des produits...');
        $bar = $this->output->createProgressBar(count($models));
        $bar->start();

        foreach ($models as $item) {
            try {
                $modelCode = $item['Sku'] ?? null;
                $modelSkus = $skusByModel[$modelCode] ?? [];
                $this->processProduct($item, $modelSkus, $dryRun);
            } catch (\Throwable $e) {
                $this->stats['errors']++;
                $this->newLine();
                $this->error("Erreur produit {$modelCode}: {$e->getMessage()}");
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
    }

    private function processProduct(array $model, array $skus, bool $dryRun): void
    {
        $reference = $model['Sku'] ?? null;
        $name = $model['ProductName'] ?? null;

        if (! $reference || ! $name) {
            $this->stats['skipped']++;

            return;
        }

        $supplier = $this->mapSupplier($model['Brand'] ?? '');
        $slug = Str::slug($name . '-' . $reference);
        $categorySlug = $this->mapCategory($model['ProductCategory'] ?? '', $model);
        $categoryId = $this->categoryCache[$categorySlug] ?? null;

        // Extract data from caracteristiques
        $certifications = $this->extractAttribute($model, 'certification');
        $gender = $this->extractAttribute($model, 'Gender');

        // Build description from Style field (multi-line bullet points)
        $style = $model['Style'] ?? '';
        $description = $style ? '<ul><li>' . implode('</li><li>', array_filter(explode("\n", $style))) . '</li></ul>' : '';

        if ($dryRun) {
            $this->stats['products_created']++;

            return;
        }

        $product = Product::updateOrCreate(
            ['reference' => $reference, 'supplier' => $supplier],
            [
                'name' => $name,
                'slug' => $slug,
                'category_id' => $categoryId,
                'description' => $description,
                'short_description' => $model['ShortDescription'] ?? Str::limit($name, 100),
                'material' => $model['Quality'] ?? '',
                'grammage' => $this->extractGrammage($model['Grammage'] ?? ''),
                'cut' => $this->mapCut($gender),
                'certifications' => $certifications ?: null,
                'main_image' => $model['MainVisual'] ?? '',
                'gallery' => array_filter([
                    $model['MainVisual'] ?? null,
                    $model['AlternativeMainVisual'] ?? null,
                ]),
                'is_active' => true,
                'meta_title' => $name,
                'meta_description' => $model['ShortDescription'] ?? '',
            ]
        );

        if ($product->wasRecentlyCreated) {
            $this->stats['products_created']++;
        } else {
            $this->stats['products_updated']++;
        }

        // Group SKUs by color
        $colorGroups = [];
        foreach ($skus as $sku) {
            $colorName = $sku['color'] ?? 'Default';
            $colorGroups[$colorName][] = $sku;
        }

        $sortOrder = 0;
        foreach ($colorGroups as $colorName => $colorSkus) {
            $firstSku = $colorSkus[0];
            $colorImage = $firstSku['images']['mannequinA']
                ?? $firstSku['images']['mainImageA']
                ?? '';

            $color = ProductColor::updateOrCreate(
                ['product_id' => $product->id, 'name' => $colorName],
                [
                    'image' => $colorImage,
                    'hex_code' => ColorHelper::resolve($colorName, $firstSku['pantoneCode'] ?? null),
                    'sort_order' => $sortOrder++,
                    'is_active' => true,
                ]
            );
            $this->stats['colors_created']++;

            foreach ($colorSkus as $skuData) {
                $sizeName = $skuData['size'] ?? 'TU';
                $eanPiece = '';
                if (isset($skuData['eanCodes'])) {
                    $ean = $skuData['eanCodes']['piece'] ?? '';
                    $eanPiece = is_array($ean) ? ($ean[0] ?? '') : (string) $ean;
                }

                ProductSize::updateOrCreate(
                    ['product_color_id' => $color->id, 'size' => $sizeName],
                    [
                        'sku' => $skuData['sku'] ?? '',
                        'ean' => $eanPiece,
                        'stock' => 0,
                        'is_available' => (bool) ($skuData['IsEnabled'] ?? true),
                    ]
                );
                $this->stats['sizes_created']++;
            }
        }
    }

    // ─── Prices ───────────────────────────────────────────────────

    private function updatePrices(bool $dryRun): void
    {
        $this->info('Recuperation des prix client Sol\'s (Customer Prices)...');

        $brandFilter = $this->option('brand') ?: 'all';

        try {
            $data = $this->api->getPrices('001');
        } catch (\Throwable $e) {
            $this->error('Echec recuperation prix : ' . $e->getMessage());

            return;
        }

        $products = $data['Products'] ?? [];
        $this->info(count($products) . ' SKUs avec prix recus.');

        if (empty($products)) {
            $this->warn('Aucun prix recu.');

            return;
        }

        // Group all prices by model code (first 5 chars of ProductCode)
        $pricesByModelAll = [];
        foreach ($products as $item) {
            $code = $item['ProductCode'] ?? '';
            if (strlen($code) < 5) {
                continue;
            }

            // Extract CustomerPrice
            $customerPrice = null;
            foreach ($item['PriceDetails'] ?? [] as $detail) {
                if (($detail['PriceType'] ?? '') === 'CustomerPrice' && ! empty($detail['Price']) && ! is_array($detail['Price'])) {
                    $customerPrice = (float) $detail['Price'];
                    break;
                }
            }

            if ($customerPrice === null || $customerPrice <= 0) {
                continue;
            }

            $modelCode = substr($code, 0, 5);
            $pricesByModelAll[$modelCode][] = $customerPrice;
        }

        // Use the most common price (mode) per model to avoid outlet/promo prices
        $pricesByModel = [];
        // Also track outlet SKUs: price < 60% of modal price
        $outletSkus = [];
        foreach ($pricesByModelAll as $model => $prices) {
            $counts = array_count_values(array_map(fn ($p) => (string) round($p, 2), $prices));
            arsort($counts);
            $modalPrice = (float) array_key_first($counts);
            $pricesByModel[$model] = $modalPrice;

            // Identify outlet SKUs by price
            foreach ($prices as $p) {
                if ($modalPrice > 0 && $p < $modalPrice * 0.60) {
                    $outletSkus[(string) round($p, 2)] = $model;
                }
            }
        }

        // Build map: ProductCode → is_outlet (based on price)
        $outletByProductCode = [];
        foreach ($products as $item) {
            $code = $item['ProductCode'] ?? '';
            if (strlen($code) < 5) continue;
            $model = substr($code, 0, 5);
            $modal = $pricesByModel[$model] ?? 0;
            if ($modal <= 0) continue;

            $customerPrice = null;
            foreach ($item['PriceDetails'] ?? [] as $detail) {
                if (($detail['PriceType'] ?? '') === 'CustomerPrice' && ! empty($detail['Price']) && ! is_array($detail['Price'])) {
                    $customerPrice = (float) $detail['Price'];
                    break;
                }
            }
            if ($customerPrice !== null && $customerPrice > 0 && $customerPrice < $modal * 0.60) {
                $outletByProductCode[$code] = true;
            }
        }

        $this->info(count($pricesByModel) . ' modeles avec prix CustomerPrice.');

        // Match with products in DB by reference
        $soloProducts = Product::whereIn('supplier', ['Sol\'s', 'NEOBLU', 'ATF', 'RTP APPAREL'])
            ->get(['id', 'name', 'reference', 'supplier', 'supplier_price']);

        $bar = $this->output->createProgressBar($soloProducts->count());
        $bar->start();

        foreach ($soloProducts as $product) {
            $bar->advance();

            $ref = $product->reference;
            if (! $ref || ! isset($pricesByModel[$ref])) {
                continue;
            }

            // Filter by brand if specified
            if ($brandFilter !== 'all') {
                $normalizedBrand = strtolower($brandFilter);
                $normalizedSupplier = strtolower($product->supplier);
                if (! str_contains($normalizedSupplier, $normalizedBrand)) {
                    continue;
                }
            }

            $newPrice = $pricesByModel[$ref];
            $currentPrice = (float) ($product->supplier_price ?? 0);

            if (abs($currentPrice - $newPrice) < 0.01) {
                // Price unchanged, but still check outlet colors
            } else {
                if ($dryRun) {
                    $this->newLine();
                    $this->line("  [DRY] {$product->name} ({$ref}): {$currentPrice} -> {$newPrice}");
                } else {
                    $product->update(['supplier_price' => $newPrice]);
                }
                $this->stats['prices_updated']++;
            }

            // Mark outlet colors: colors whose SKUs all have outlet prices
            if (! $dryRun) {
                $colors = ProductColor::where('product_id', $product->id)->with('sizes')->get();
                foreach ($colors as $color) {
                    $allSkusOutlet = true;
                    $anySkuFound = false;
                    foreach ($color->sizes as $size) {
                        if ($size->sku) {
                            // Solo SKU codes start with model ref
                            $fullCode = $size->sku;
                            if (isset($outletByProductCode[$fullCode])) {
                                $anySkuFound = true;
                            } else {
                                $allSkusOutlet = false;
                            }
                        }
                    }
                    if ($anySkuFound && $allSkusOutlet) {
                        $color->update(['is_outlet' => true]);
                    } elseif (! $allSkusOutlet) {
                        $color->update(['is_outlet' => false]);
                    }
                }

                // If ALL colors outlet → product outlet
                $activeColors = ProductColor::where('product_id', $product->id)->where('is_outlet', false)->count();
                $totalColors = ProductColor::where('product_id', $product->id)->count();
                if ($totalColors > 0 && $activeColors === 0) {
                    $product->update(['is_outlet' => true, 'is_active' => false]);
                }
            }
        }

        $bar->finish();
        $this->newLine(2);
    }

    // ─── Stock ────────────────────────────────────────────────────

    private function updateStock(bool $dryRun): void
    {
        $this->info('Récupération des stocks Solo (entrepôt France 001)...');

        try {
            $stockItems = $this->api->getStock(warehouse: '001');
        } catch (\Throwable $e) {
            $this->error("Erreur stock: {$e->getMessage()}");
            $this->stats['errors']++;

            return;
        }

        $this->info(count($stockItems) . ' entrées stock reçues.');

        // Build stock map: SKU → quantity
        $stockMap = [];
        foreach ($stockItems as $item) {
            $sku = $item['SKU'] ?? null;
            if ($sku) {
                $stockMap[$sku] = (int) ($item['StockAvailable'] ?? 0);
            }
        }
        $this->info(count($stockMap) . ' SKUs avec stock indexés.');

        // Apply to existing Solo products
        $soloSuppliers = ["Sol's", 'Solo', 'NEOBLU', 'ATF', 'RTP Apparel'];
        $sizes = ProductSize::whereHas('color.product', fn ($q) => $q->whereIn('supplier', $soloSuppliers))
            ->whereNotNull('sku')
            ->where('sku', '!=', '')
            ->get();

        $this->info($sizes->count() . ' tailles en base avec SKU.');

        $matched = 0;
        $notFound = 0;
        foreach ($sizes as $size) {
            if (isset($stockMap[$size->sku])) {
                $qty = $stockMap[$size->sku];
                if (! $dryRun) {
                    $size->update(['stock' => $qty, 'is_available' => $qty > 0]);
                }
                $this->stats['stock_updated']++;
                $matched++;
            } else {
                $notFound++;
            }
        }

        $this->info("Stock mis à jour: {$matched} SKUs matchés, {$notFound} non trouvés dans l'API.");

        // Aggregate stock at product level
        if (! $dryRun) {
            $products = Product::whereIn('supplier', $soloSuppliers)
                ->with('colors.sizes')
                ->get();

            foreach ($products as $product) {
                $totalStock = 0;
                foreach ($product->colors as $color) {
                    $colorStock = $color->sizes->sum('stock');
                    $color->update(['stock' => $colorStock]);
                    $totalStock += $colorStock;
                }
                $product->update(['stock' => $totalStock]);
            }
            $this->info($products->count() . ' produits Solo avec stock agrégé.');
        }
    }

    // ─── Helpers ──────────────────────────────────────────────────

    private function extractItems(array $data): array
    {
        foreach (['data', 'items', 'products', 'results'] as $key) {
            if (isset($data[$key]) && is_array($data[$key])) {
                return $data[$key];
            }
        }

        if (isset($data[0])) {
            return $data;
        }

        return $data;
    }

    private function extractAttribute(array $model, string $attributeName): array
    {
        $values = [];
        foreach ($model['caracteristiques'] ?? [] as $attr) {
            if (($attr['Attributes'] ?? '') === $attributeName) {
                $values[] = $attr['MultivalueValue'] ?? $attr['MultivalueCode'] ?? '';
            }
        }

        return array_filter($values);
    }

    private function mapCategory(string $soloCategory, array $model = []): string
    {
        $cat = strtoupper(trim($soloCategory));
        $desc = mb_strtolower($model['ShortDescription'] ?? '');
        $name = mb_strtolower($model['ProductName'] ?? '');
        $quality = mb_strtolower($model['Quality'] ?? '');
        $genderAttrs = array_filter($model['caracteristiques'] ?? [], fn ($a) => ($a['Attributes'] ?? '') === 'Gender');
        $gender = array_map(fn ($a) => strtolower($a['MultivalueValue'] ?? ''), $genderAttrs);
        $isBaby = in_array('bébé', $gender) || str_contains($desc, 'bébé') || str_contains($desc, 'body bébé') || str_contains($name, 'bambino') || str_contains($name, 'mosquito');
        $isKid = in_array('enfant', $gender) || in_array('kids', $gender) || str_contains($desc, 'enfant') || str_contains($desc, 'kids');
        $isSport = str_contains($desc, 'sport') || str_contains($quality, 'interlock') || str_contains($quality, 'mesh');

        return match ($cat) {
            'TEE SHIRT' => $this->mapTeeShirt($desc, $name, $quality, $isBaby, $isSport),
            'POLO' => $isSport ? 'polos-sport' : (str_contains($desc, 'manches longues') ? 'polos-manches-longues' : 'polos-manches-courtes'),
            'CHEMISE' => str_contains($desc, 'manches longues') ? 'chemises-manches-longues' : 'chemises-manches-courtes',
            'SWEAT' => $this->mapSweat($desc, $name),
            'PULL' => $this->mapPull($desc),
            'CARDIGAN' => 'gilets-cardigans',
            'VESTE / GILET' => 'gilets-cardigans',
            'VESTE / BLAZER' => 'blazers',
            'BODYWARMER' => 'bodywarmer-pro',
            'COUPE VENT' => str_contains($desc, 'softshell') ? 'softshells' : 'coupe-vent',
            'PARKA ET BLOUSON' => $this->mapParka($desc),
            'PANTALON' => str_contains($desc, 'jogging') || str_contains($desc, 'molleton') ? 'jogging-sport' : 'pantalons-longs',
            'BERMUDA SHORT' => $isSport ? 'shorts-sport' : 'shorts-bermudas',
            'COMBINAISON' => 'combinaisons-travail',
            'SURVETEMENT' => 'jogging-sport',
            'ROBE/JUPE' => 'jupes-robes',
            'CASQUETTE' => $this->mapCasquette($desc, $name),
            'BONNET' => 'bonnets',
            'BANDEAU' => 'bandanas',
            'ECHARPE' => 'echarpes-gants',
            'FOULARD' => 'echarpes-gants',
            'CRAVATE' => 'ceintures-parapluies',
            'CHAUSSETTES' => 'chaussettes',
            'SAC SHOPPING' => 'sacs-coton',
            'SAC A DOS' => 'sacs-a-dos',
            'SAC DE VOYAGE' => 'valises',
            'SAC DE SPORT' => 'sacs-polyester',
            'SACOCHE' => 'sacoches-polyester',
            'TROUSSE' => 'portefeuilles-trousses',
            'TABLIER' => 'tabliers',
            'SERVIETTE' => 'serviettes-bain-peignoirs',
            'PEIGNOIR' => 'serviettes-bain-peignoirs',
            'PLAID' => 'couvertures',
            'BAVOIR' => 'bonnets-bavoirs-bebe',
            'HOUSSE COSTUME' => 'accessoires-pro',
            'GENOUILLERE' => 'accessoires-pro',
            default => $this->mapDefault($desc, $name),
        };
    }

    private function mapTeeShirt(string $desc, string $name, string $quality, bool $isBaby, bool $isSport): string
    {
        if ($isBaby) {
            return str_contains($desc, 'body') || str_contains($name, 'body') ? 'bodies-bebe' : 't-shirts-bebe';
        }
        if (str_contains($desc, 'débardeur') || str_contains($desc, 'dos nageur') || str_contains($desc, 'sans manche')) {
            return 'debardeurs';
        }
        if ($isSport || str_contains($quality, 'polyester') && ! str_contains($quality, 'coton')) {
            return 't-shirts-polyester';
        }
        if (str_contains($desc, 'manches longues')) {
            return 't-shirts-manches-longues';
        }
        if (str_contains($desc, 'col v') || str_contains($desc, 'col en v')) {
            return 't-shirts-col-v';
        }
        if (str_contains($desc, 'bicolor') || str_contains($desc, 'bicolore') || str_contains($desc, 'contrasté')) {
            return 't-shirts-bicolor';
        }

        return 't-shirts-col-rond-coton';
    }

    private function mapSweat(string $desc, string $name): string
    {
        if (str_contains($desc, 'zippé') || str_contains($desc, 'zipée') || str_contains($desc, 'zip')) {
            return str_contains($desc, 'capuche') ? 'sweats-zippes-capuche' : 'sweats-zippes';
        }
        if (str_contains($desc, 'capuche') || str_contains($desc, 'hoodie')) {
            return 'sweats-capuche';
        }
        if (str_contains($desc, 'sans manche') || str_contains($desc, 'sans manches')) {
            return 'sweats-sans-manches';
        }

        return 'sweats-col-rond';
    }

    private function mapPull(string $desc): string
    {
        if (str_contains($desc, 'sans manche') || str_contains($desc, 'sans manches')) {
            return 'pulls-sans-manches';
        }
        if (str_contains($desc, 'col v') || str_contains($desc, 'col en v')) {
            return 'pulls-col-v';
        }

        return 'pulls-col-rond';
    }

    private function mapParka(string $desc): string
    {
        if (str_contains($desc, 'doudoune') || str_contains($desc, 'matelassé')) {
            return 'doudounes';
        }
        if (str_contains($desc, 'parka')) {
            return 'parkas';
        }
        if (str_contains($desc, 'bodywarmer') || str_contains($desc, 'sans manche')) {
            return 'bodywarmer-pro';
        }

        return 'vestes-ete';
    }

    private function mapCasquette(string $desc, string $name): string
    {
        if (str_contains($desc, 'bob') || str_contains($name, 'bucket')) {
            return 'bobs';
        }
        if (str_contains($desc, 'visière') || str_contains($desc, 'chapeau')) {
            return 'chapeaux';
        }
        if (str_contains($desc, 'snapback')) {
            return 'casquettes-snapback';
        }
        if (str_contains($desc, 'bonnet')) {
            return 'bonnets';
        }

        return 'casquettes-classiques';
    }

    private function mapDefault(string $desc, string $name): string
    {
        if (str_contains($desc, 'tour de cou') || str_contains($desc, 'snood')) {
            return 'echarpes-gants';
        }
        if (str_contains($desc, 'gilet de sécurité') || str_contains($desc, 'gilet de securité') || str_contains($desc, 'haute visibilité')) {
            return 'haute-visibilite';
        }
        if (str_contains($desc, 'tire-zip')) {
            return 'accessoires-pro';
        }
        if (str_contains($desc, 'bandana')) {
            return 'bandanas';
        }

        return 'accessoires';
    }

    private function mapCut(array $gender): string
    {
        $genderLower = array_map('strtolower', $gender);
        if (in_array('enfant', $genderLower) || in_array('kids', $genderLower) || in_array('bébé', $genderLower)) {
            return 'enfant';
        }
        $hasHomme = in_array('homme', $genderLower) || in_array('men', $genderLower);
        $hasFemme = in_array('femme', $genderLower) || in_array('women', $genderLower);
        if ($hasHomme && $hasFemme) {
            return 'mixte';
        }
        if ($hasHomme) {
            return 'homme';
        }
        if ($hasFemme) {
            return 'femme';
        }

        return 'mixte';
    }

    private function mapSupplier(string $brand): string
    {
        return match (strtolower(trim($brand))) {
            "sol's" => "Sol's",
            'neoblu' => 'NEOBLU',
            'atf' => 'ATF',
            'rtp apparel' => 'RTP Apparel',
            'unbranded selection' => 'Solo',
            default => 'Solo',
        };
    }

    private function extractGrammage(mixed $raw): ?int
    {
        if (is_array($raw)) {
            $raw = $raw[0] ?? '';
        }
        $raw = (string) $raw;
        if (empty($raw)) {
            return null;
        }
        // "Tricot 380g/pc" → 380, "Jersey 150" → 150
        if (preg_match('/(\d+)\s*g/', $raw, $m)) {
            return (int) $m[1];
        }
        if (preg_match('/(\d+)/', $raw, $m)) {
            return (int) $m[1];
        }

        return null;
    }

    private function pantoneToHex(mixed $pantone): ?string
    {
        if (is_array($pantone)) {
            $pantone = $pantone[0] ?? '';
        }
        $pantone = (string) $pantone;
        if (empty($pantone)) {
            return null;
        }

        // Common Pantone TCX → hex approximations for textile colors
        $map = [
            '19-4007 TCX' => '#2B2B2B', // Noir
            '19-4010 TCX' => '#1C1C3A', // Marine foncé
            '19-1664 TCX' => '#C62828', // Rouge
            '11-0601 TCX' => '#F5F5F0', // Blanc
            '19-3921 TCX' => '#2C3E8C', // Bleu royal
            '14-4112 TCX' => '#87CEEB', // Bleu ciel
            '17-4041 TCX' => '#4682B4', // French blue
            '19-3952 TCX' => '#3949AB', // Bleu roi
            '18-1662 TCX' => '#E53935', // Rouge vif
            '18-0130 TCX' => '#2E7D32', // Vert
            '16-1054 TCX' => '#FFB300', // Jaune doré
            '14-0837 TCX' => '#FDD835', // Jaune
            '19-1101 TCX' => '#3E2723', // Marron
            '15-1040 TCX' => '#FF8F00', // Orange
            '17-3826 TCX' => '#7E57C2', // Violet
            '15-3817 TCX' => '#CE93D8', // Lavande
            '14-1318 TCX' => '#FFAB91', // Corail
            '12-0720 TCX' => '#FFF9C4', // Crème
            '15-6437 TCX' => '#66BB6A', // Vert pomme
            '18-4051 TCX' => '#1976D2', // Bleu bugatti
            '19-0509 TCX' => '#455A64', // Gris foncé
            '17-5104 TCX' => '#9E9E9E', // Gris
            '14-4002 TCX' => '#CFD8DC', // Gris clair
            '19-1725 TCX' => '#880E4F', // Bordeaux
            '16-1546 TCX' => '#FF7043', // Corail vif
        ];

        return $map[$pantone] ?? null;
    }

    private function displaySummary(): void
    {
        $this->newLine();
        $this->info('=== Résumé import Solo ===');
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
