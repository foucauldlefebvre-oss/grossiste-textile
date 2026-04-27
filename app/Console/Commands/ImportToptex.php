<?php

namespace App\Console\Commands;

use App\Models\BrandColor;
use App\Models\BrandSize;
use App\Models\Category;
use App\Models\PricingRule;
use App\Models\Product;
use App\Models\ProductColor;
use App\Models\ProductSize;
use App\Services\ToptexApiService;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class ImportToptex extends Command
{
    protected $signature = 'import:toptex
        {--dry-run : Show what would be done without modifying anything}
        {--stock-only : Only update stocks and prices for existing Toptex products}
        {--dump-structure : Dump the raw JSON structure of the first product and stop}
        {--brand= : Filter by brand name (e.g. Kariban, Proact)}
        {--limit=0 : Limit the number of products to import (0 = no limit)}';

    protected $description = 'Import Toptex product catalog (Fruit of the Loom, Gildan, B&C, Sol\'s, etc.)';

    private ToptexApiService $api;

    /**
     * Allowed Toptex-distributed brands.
     * API brand name (normalized, no ® etc.) → our supplier name.
     */
    private array $brandMap = [
        'Kariban' => 'Kariban',
        'Front Row' => 'Front Row',
        'Kariban Premium' => 'Kariban Premium',
        'Kimood' => 'Kimood',
        'PROACT' => 'Proact',
        'K-up' => 'Kimood',
    ];

    /**
     * Explicit mapping: Toptex sub_family/family (lowercase) → category slug.
     * Based on actual API values for Kariban, Front Row, Kariban Premium, Kimood, Proact, K-up.
     */
    private array $categoryMapping = [
        // ── Vêtements ────────────────────────────────────
        't-shirts' => 't-shirts',
        't-shirt' => 't-shirts',
        'débardeurs' => 'debardeurs',
        'polos' => 'polos',
        'sweat-shirts' => 'sweats',
        'chemises / surchemises' => 'chemises',
        'pullovers / cardigans' => 'pulls-gilets',
        'gilets' => 'gilets-cardigans',
        'vestes' => 'vestes-ete',
        'bodywarmers' => 'bodywarmer-pro',
        'bermudas / shorts' => 'shorts-bermudas',
        'pantalons / pantacourts' => 'pantalons-longs',
        'jupes' => 'jupes-robes',
        'robes' => 'jupes-robes',
        'tabliers' => 'tabliers',
        'ponchos' => 'coupe-vent',
        'bodys' => 'bodies-bebe',
        'chasubles' => 'chasubles-dossards',
        'maillots de sport' => 'maillots-t-shirts-sport',
        'maillots de bain' => 'maillots-de-bain',
        'pyjamas' => 'sous-vetements',
        // ── Sous-vêtements ───────────────────────────────
        'boxers / caleçons' => 'slips-calecons',
        'slips' => 'slips-calecons',
        'shortys' => 'slips-calecons',
        'brassières' => 'sous-vetements',
        'maillots de corps' => 'sous-vetements',
        'chaussettes' => 'chaussettes',
        // ── Headwear & Accessoires ───────────────────────
        'casquettes' => 'casquettes-classiques',
        'bonnets' => 'bonnets',
        'bobs' => 'bobs',
        'chapeaux & accessoires' => 'chapeaux',
        'bérets' => 'casquettes-anglaises-berets',
        'cagoules' => 'bonnets',
        'bandanas' => 'bandanas',
        'bandeaux' => 'bandanas',
        'gants' => 'echarpes-gants',
        'écharpes / étoles / tours de cou' => 'echarpes-gants',
        // ── Accessoires Vêtements ────────────────────────
        'ceintures' => 'ceintures-parapluies',
        'cravates' => 'accessoires',
        'foulards' => 'echarpes-gants',
        'tire-zips' => 'accessoires',
        'brassards' => 'accessoires-sport',
        // ── Bagagerie ────────────────────────────────────
        'sacs' => 'sacs',
        'valises' => 'valises',
        'paniers' => 'sacs',
        'housses' => 'sacs',
        'accessoires bagagerie' => 'sacs',
        // ── Accessoires ──────────────────────────────────
        'parapluies' => 'ceintures-parapluies',
        // ── Linge de maison ──────────────────────────────
        'bain' => 'serviettes-bain-peignoirs',
        'table / cuisine' => 'nappes-serviettes',
        'décoration' => 'maison',
        // ── Drinkwear ────────────────────────────────────
        'gourdes / bouteilles' => 'accessoires-sport',
        'porte-gourdes / porte-bouteilles' => 'accessoires-sport',
        // ── Equipements sportifs ─────────────────────────
        'accessoires de sport' => 'accessoires-sport',
        'matériel d\'entraînement et de terrain' => 'accessoires-sport',
        'matériel d\'arbitrage' => 'accessoires-sport',
        'matériel de gonflage' => 'accessoires-sport',
        'ballons & accessoires' => 'accessoires-sport',
        'crampons & accessoires' => 'accessoires-sport',
        // ── Chaussures ──────────────────────────────────
        'chaussures lifestyle / loisir' => 'accessoires',
        'accessoires chaussures' => 'accessoires',
        // ── Family-level fallbacks ──────────────────────
        'vêtements' => 't-shirts',
        'headwear & accessoires' => 'casquettes',
        'bagagerie' => 'sacs',
        'linge de maison' => 'maison',
        'equipements sportifs' => 'sport',
        'sous-vêtements' => 'sous-vetements',
        'accessoires vêtements' => 'accessoires',
        'chaussures & accessoires' => 'accessoires',
        'drinkwear' => 'accessoires-sport',
        'produits' => 'accessoires',
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
        'errors' => 0,
    ];

    public function handle(ToptexApiService $api): int
    {
        $this->api = $api;
        $dryRun = $this->option('dry-run');
        $stockOnly = $this->option('stock-only');
        $brandFilter = $this->option('brand');
        $limit = (int) $this->option('limit');

        if ($brandFilter) {
            $found = false;
            foreach ($this->brandMap as $key => $val) {
                if (Str::lower($key) === Str::lower($brandFilter) || Str::lower($val) === Str::lower($brandFilter)) {
                    $found = true;
                    break;
                }
            }
            if (! $found) {
                $this->error("Marque '{$brandFilter}' non autorisee. Marques disponibles : " . implode(', ', array_unique(array_values($this->brandMap))));

                return self::FAILURE;
            }
            $this->info("Filtre marque : {$brandFilter}");
        }

        if ($limit > 0) {
            $this->info("Limite : {$limit} produits");
        }

        if (! $this->api->isConfigured()) {
            $this->error('Toptex API non configuree. Verifiez TOPTEX_URL et TOPTEX_API_KEY dans .env');

            return self::FAILURE;
        }

        if ($dryRun) {
            $this->info('=== DRY RUN MODE ===');
        }

        // Authenticate
        $this->info('Authentification a l\'API Toptex...');
        try {
            $this->api->authenticate();
            $this->info('Authentifie avec succes (JWT obtenu).');
        } catch (\Throwable $e) {
            $this->error("Echec d'authentification : {$e->getMessage()}");

            return self::FAILURE;
        }

        if ($stockOnly) {
            return $this->updateStockOnly($dryRun);
        }

        // Dump structure: fetch just 1 product
        if ($this->option('dump-structure')) {
            $this->info('Recuperation d\'un produit (v3)...');
            try {
                $data = $this->api->getProductPage(1, 1);
                $products = $data['items'] ?? [];
                $totalCount = $data['total_count'] ?? 0;
                $this->info("Total catalogue: {$totalCount} produits.");

                if (empty($products)) {
                    $this->warn('Catalogue vide.');

                    return self::SUCCESS;
                }

                $first = $products[0];
                $this->info('Structure du premier produit :');
                $this->line(json_encode($first, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                $this->newLine();
                $this->info('Cles : ' . implode(', ', array_keys($first)));
                $this->info('Marque : ' . ($first['brand'] ?? '?'));
                $this->info('Ref : ' . ($first['catalogReference'] ?? '?'));
                $this->info('Nom : ' . ($this->tr($first['designation'] ?? null) ?: '?'));
                $this->info('Couleurs : ' . count($first['colors'] ?? []));
            } catch (\Throwable $e) {
                $this->error("Echec : {$e->getMessage()}");

                return self::FAILURE;
            }

            return self::SUCCESS;
        }

        // Import — paginate through all products (50 per page to avoid Lambda payload limit)
        $this->info('Import des produits (v3, page par page)...');
        $imported = 0;
        $skipped = 0;
        $stopEarly = false;
        try {
            $this->api->eachProductPage(function (array $products, int $totalCount, int $page) use ($dryRun, $brandFilter, $limit, &$imported, &$skipped, &$stopEarly) {
                if ($stopEarly) {
                    return true;
                }

                if ($page === 1) {
                    $this->stats['products_total'] = $totalCount;
                    $this->info("{$totalCount} produits au catalogue.");
                }

                foreach ($products as $product) {
                    // Brand filtering — normalize by stripping ® and trimming
                    $rawBrand = trim($product['brand'] ?? '');
                    $cleanBrand = str_replace(['®', '™'], '', $rawBrand);
                    $supplier = $this->brandMap[$cleanBrand] ?? null;

                    if (! $supplier) {
                        $skipped++;
                        continue;
                    }

                    if ($brandFilter && ! Str::contains(Str::lower($cleanBrand), Str::lower($brandFilter))) {
                        $skipped++;
                        continue;
                    }

                    // Limit check
                    if ($limit > 0 && $imported >= $limit) {
                        $stopEarly = true;

                        return true;
                    }

                    try {
                        $this->importProduct($product, $dryRun, $supplier);
                        $imported++;
                    } catch (\Throwable $e) {
                        $this->stats['errors']++;
                        $ref = $product['catalogReference'] ?? '?';
                        $this->warn("Erreur {$ref}: " . substr($e->getMessage(), 0, 100));
                    }
                }

                $this->info("  Page {$page}: {$imported} importes, {$skipped} ignores");
            });
        } catch (\Throwable $e) {
            $this->error("Echec recuperation catalogue : {$e->getMessage()}");

            return self::FAILURE;
        }

        $this->info("Total: {$imported} produits traites, {$skipped} ignores (marque non autorisee)");

        $this->displaySummary();

        if ($dryRun) {
            $this->warn('Dry run termine. Aucune modification effectuee.');
        }

        return self::SUCCESS;
    }

    /**
     * Import a single Toptex v3 product (already grouped with colors[]/sizes[]).
     *
     * v3 structure:
     * - catalogReference, brand, designation.fr, description.fr, composition.fr
     * - family.fr, sub_family.fr, gender[0].fr, averageWeight, images[].url_image
     * - colors[]: colors.fr, colorsHexa[0], packshots.FACE_SIDE.url_packshot, sizes[]
     * - sizes[]: size, sku, publicUnitPrice, ean, coatingWeight_fr
     */
    private function importProduct(array $product, bool $dryRun, ?string $supplier = null): void
    {
        $modelRef = $product['catalogReference'] ?? null;
        if (! $modelRef) {
            return;
        }

        if (! $supplier) {
            $cleanBrand = str_replace(['®', '™'], '', trim($product['brand'] ?? ''));
            $supplier = $this->brandMap[$cleanBrand] ?? $cleanBrand;
        }

        $name = $this->tr($product['designation'] ?? null) ?: $modelRef;
        $description = $this->tr($product['description'] ?? null);
        $material = $this->tr($product['composition'] ?? null);
        $grammage = $this->extractGrammage($product);
        $gender = $this->mapGender($product['gender'] ?? null);
        $categoryId = $this->mapCategory($product);
        $mainImage = $product['images'][0]['url_image'] ?? $product['images'][0]['url'] ?? null;
        $slug = $this->generateUniqueSlug($name, $modelRef);

        $productData = [
            'name' => $name,
            'slug' => $slug,
            'supplier' => $supplier,
            'category_id' => $categoryId,
            'description' => $description,
            'material' => $material,
            'grammage' => $grammage,
            'cut' => $gender,
            'main_image' => $mainImage,
            'is_active' => true,
        ];

        $colors = $product['colors'] ?? [];

        if ($dryRun) {
            $existing = Product::where('reference', $modelRef)->where('supplier', $supplier)->first();
            $this->stats[$existing ? 'products_updated' : 'products_created']++;
            foreach ($colors as $c) {
                $this->stats['colors_created']++;
                $this->stats['sizes_created'] += count($c['sizes'] ?? []);
            }

            return;
        }

        $dbProduct = Product::updateOrCreate(
            ['reference' => $modelRef, 'supplier' => $supplier],
            $productData
        );
        $this->stats[$dbProduct->wasRecentlyCreated ? 'products_created' : 'products_updated']++;

        $sortOrder = 0;
        $allPrices = [];

        foreach ($colors as $colorData) {
            $colorName = $this->tr($colorData['colors'] ?? null) ?: 'Default';
            $hexCode = $this->cleanHex($colorData['colorsHexa'][0] ?? null);
            $colorImage = $colorData['packshots']['FACE SIDE']['url_packshot']
                ?? $colorData['packshots']['FACE_SIDE']['url_packshot']
                ?? null;

            // Detect outlet: saleState not active at color level
            $colorSaleState = $colorData['saleState'] ?? 'active';
            $colorIsOutlet = $colorSaleState !== 'active';

            // Also check if ALL sizes in this color are discontinued
            $allSizesDiscontinued = true;
            foreach ($colorData['sizes'] ?? [] as $sd) {
                if (($sd['saleState'] ?? 'active') === 'active' && ! ($sd['isDiscontinued'] ?? false)) {
                    $allSizesDiscontinued = false;
                    break;
                }
            }
            if (! empty($colorData['sizes']) && $allSizesDiscontinued) {
                $colorIsOutlet = true;
            }

            $color = ProductColor::updateOrCreate(
                ['product_id' => $dbProduct->id, 'name' => $colorName],
                [
                    'hex_code' => $hexCode,
                    'image' => $colorImage,
                    'sort_order' => $sortOrder++,
                    'is_active' => ! $colorIsOutlet,
                    'is_outlet' => $colorIsOutlet,
                ]
            );
            $this->stats[$color->wasRecentlyCreated ? 'colors_created' : 'colors_updated']++;

            BrandColor::updateOrCreate(
                ['brand' => $supplier, 'name' => $colorName],
                ['hex_code' => $hexCode]
            );

            foreach ($colorData['sizes'] ?? [] as $sizeData) {
                $sizeName = $this->normalizeSizeName($sizeData['size'] ?? 'Unique');
                // publicUnitPrice is the recommended retail price (≈2× wholesale HT)
                $publicPrice = $this->parseEuroPrice($sizeData['publicUnitPrice'] ?? null);
                $price = $publicPrice ? round($publicPrice / 2, 2) : null;

                if ($price && $price > 0) {
                    $allPrices[] = $price;
                }

                $size = ProductSize::updateOrCreate(
                    ['product_color_id' => $color->id, 'size' => $sizeName],
                    [
                        'sku' => $sizeData['sku'] ?? null,
                        'ean' => $sizeData['ean'] ?? null,
                        'is_available' => ($sizeData['saleState'] ?? 'active') === 'active'
                            && ! ($sizeData['isDiscontinued'] ?? false),
                    ]
                );
                $this->stats[$size->wasRecentlyCreated ? 'sizes_created' : 'sizes_updated']++;

                BrandSize::firstOrCreate(['brand' => $supplier, 'size' => $sizeName]);
            }
        }

        // Use the most common price (mode) to avoid outlet/promo SKU prices
        if (! empty($allPrices)) {
            $counts = array_count_values(array_map(fn ($p) => (string) round($p, 2), $allPrices));
            arsort($counts);
            $unitPrice = (float) array_key_first($counts);

            $basePrice = PricingRule::calculate($unitPrice, 1);
            $dbProduct->update([
                'supplier_price' => $unitPrice,
                'base_price' => $basePrice ?: $unitPrice,
            ]);
            $this->stats['prices_updated']++;
        }

        // If ALL colors are outlet → mark entire product as outlet
        $activeColors = ProductColor::where('product_id', $dbProduct->id)->where('is_outlet', false)->count();
        $totalColors = ProductColor::where('product_id', $dbProduct->id)->count();
        if ($totalColors > 0 && $activeColors === 0) {
            $dbProduct->update(['is_outlet' => true, 'is_active' => false]);
        } else {
            $dbProduct->update(['is_outlet' => false]);
        }
    }

    // ─── Stock & Prices ──────────────────────────────────────────

    private function updateStockOnly(bool $dryRun): int
    {
        $suppliers = array_unique(array_values($this->brandMap));
        $count = Product::whereIn('supplier', $suppliers)->count();
        $this->info("Mode MAJ stocks — {$count} produits Toptex en base.");

        if ($count === 0) {
            $this->warn('Aucun produit Toptex. Lancez d\'abord un import complet.');

            return self::SUCCESS;
        }

        // Load all our SKUs into a lookup set for fast matching
        $knownSkus = ProductSize::whereNotNull('sku')
            ->where('sku', '!=', '')
            ->pluck('id', 'sku')
            ->toArray();

        $this->info(count($knownSkus) . " SKUs en base.");

        if (empty($knownSkus)) {
            $this->warn('Aucun SKU en base. Relancez un import complet pour enregistrer les SKUs.');

            return self::SUCCESS;
        }

        $this->info('Recuperation de l\'inventaire Toptex (page par page)...');
        $updated = 0;
        $matched = 0;
        $skipped = 0;

        $this->api->eachInventoryPage(function (array $items, int $totalCount, int $page) use ($dryRun, &$knownSkus, &$updated, &$matched, &$skipped) {
            if ($page === 1) {
                $this->info("{$totalCount} entrees d'inventaire au total.");
            }

            foreach ($items as $entry) {
                $sku = $entry['sku'] ?? null;
                if (! $sku || ! isset($knownSkus[$sku])) {
                    $skipped++;
                    continue;
                }

                $matched++;
                $stock = $this->sumWarehouseStock($entry);

                if (! $dryRun) {
                    ProductSize::where('id', $knownSkus[$sku])->update([
                        'stock' => $stock,
                        'is_available' => $stock > 0,
                    ]);
                }
                $updated++;
            }

            if ($page % 10 === 0 || $page === 1) {
                $this->info("  Page {$page}: {$matched} trouves, {$updated} MAJ, {$skipped} ignores");
            }
        }, 50);

        $this->stats['stock_updated'] = $updated;
        $this->info("Stock mis a jour: {$updated} tailles ({$matched} correspondances, {$skipped} ignores)");
        $this->displaySummary();

        if ($dryRun) {
            $this->warn('Dry run termine. Aucune modification effectuee.');
        }

        return self::SUCCESS;
    }

    private function sumWarehouseStock(array $entry): int
    {
        $stock = 0;
        if (isset($entry['warehouses']) && is_array($entry['warehouses'])) {
            foreach ($entry['warehouses'] as $warehouse) {
                $stock += (int) ($warehouse['stock'] ?? 0);
            }
        } else {
            $stock = (int) ($entry['stock'] ?? $entry['quantity'] ?? 0);
        }

        return $stock;
    }

    // ─── Category mapping ────────────────────────────────────────

    private ?int $toptexParentCategoryId = null;

    private function mapCategory(array $product): int
    {
        $family = Str::lower(trim($this->tr($product['family'] ?? null)));
        $subFamily = Str::lower(trim($this->tr($product['sub_family'] ?? $product['subfamily'] ?? null)));

        // Try exact match on sub_family first, then family
        foreach ([$subFamily, $family] as $term) {
            if (! $term) {
                continue;
            }

            // Exact match in mapping
            if (isset($this->categoryMapping[$term])) {
                $cat = Category::where('slug', $this->categoryMapping[$term])->first();
                if ($cat) {
                    return $cat->id;
                }
            }

            // Partial match: check if any mapping key is contained in the term
            foreach ($this->categoryMapping as $keyword => $slug) {
                if (Str::contains($term, $keyword)) {
                    $cat = Category::where('slug', $slug)->first();
                    if ($cat) {
                        return $cat->id;
                    }
                }
            }
        }

        // Fallback: create under Toptex parent
        return $this->getOrCreateToptexCategory($subFamily ?: $family);
    }

    private function getOrCreateToptexCategory(?string $subcategoryName = null): int
    {
        if (! $this->toptexParentCategoryId) {
            $parent = Category::firstOrCreate(
                ['slug' => 'toptex'],
                ['name' => 'Toptex', 'is_active' => true, 'sort_order' => 997]
            );
            $this->toptexParentCategoryId = $parent->id;
        }

        if (! $subcategoryName) {
            $sub = Category::firstOrCreate(
                ['slug' => 'toptex-non-classe'],
                [
                    'name' => 'Toptex - Non classé',
                    'parent_id' => $this->toptexParentCategoryId,
                    'is_active' => true,
                    'sort_order' => 0,
                ]
            );

            return $sub->id;
        }

        $slug = 'toptex-' . Str::slug($subcategoryName);
        $sub = Category::firstOrCreate(
            ['slug' => $slug],
            [
                'name' => 'Toptex - ' . $subcategoryName,
                'parent_id' => $this->toptexParentCategoryId,
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

        return $this->brandMap[$upper] ?? trim($brandName);
    }

    /**
     * Extract French text from a translated field (v3 uses {fr, en, de, ...} objects).
     */
    private function tr(mixed $field): string
    {
        if (is_string($field)) {
            return $field;
        }
        if (is_array($field)) {
            // Translated object
            if (isset($field['fr'])) {
                return $field['fr'];
            }
            // Array of translated objects (e.g. gender)
            if (isset($field[0]) && is_array($field[0]) && isset($field[0]['fr'])) {
                return $field[0]['fr'];
            }
        }

        return '';
    }

    private function mapGender(mixed $gender): string
    {
        $text = Str::lower(trim($this->tr($gender)));

        if (! $text) {
            return 'mixte';
        }

        return match (true) {
            Str::contains($text, ['homme', 'man', 'male', 'men']) => 'homme',
            Str::contains($text, ['femme', 'woman', 'female', 'women', 'ladies']) => 'femme',
            Str::contains($text, ['enfant', 'child', 'kid', 'junior', 'bébé', 'baby']) => 'enfant',
            Str::contains($text, ['unisex', 'mixte']) => 'mixte',
            default => 'mixte',
        };
    }

    private function extractGrammage(array $data): ?int
    {
        // v3: averageWeight = "80 g" or coatingWeight_fr = "89 g/m²"
        $weight = $data['averageWeight'] ?? '';
        if (preg_match('/(\d+)\s*g/i', $weight, $m)) {
            return (int) $m[1];
        }

        // Try description
        $text = $this->tr($data['composition'] ?? '') . ' ' . $this->tr($data['description'] ?? '');
        if (preg_match('/(\d+)\s*g\/?m/i', $text, $m)) {
            return (int) $m[1];
        }

        return null;
    }

    /**
     * Parse euro price string like "11,02 €" to float.
     */
    private function parseEuroPrice(?string $price): ?float
    {
        if (! $price) {
            return null;
        }

        $clean = preg_replace('/[^0-9,.]/', '', $price);
        $clean = str_replace(',', '.', $clean);

        return is_numeric($clean) ? (float) $clean : null;
    }

    private function normalizeSizeName(string $size): string
    {
        $size = trim(strtoupper($size));

        $aliases = [
            'XXXS' => 'XXS',
            'XXXL' => '3XL',
            'XXXXL' => '4XL',
            'XXXXXL' => '5XL',
        ];

        return $aliases[$size] ?? $size;
    }

    private function cleanHex(?string $hex): ?string
    {
        if (! $hex || $hex === '' || $hex === '#') {
            return null;
        }

        if (! Str::startsWith($hex, '#')) {
            $hex = '#' . $hex;
        }

        if (strlen($hex) > 7) {
            $hex = substr($hex, 0, 7);
        }

        return $hex;
    }

    private function parseNumeric(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_string($value)) {
            $value = str_replace(',', '.', trim($value));
        }

        return is_numeric($value) ? (float) $value : null;
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
        $this->info('=== Resume import Toptex ===');
        $this->table(
            ['Metrique', 'Total'],
            collect($this->stats)->map(fn ($val, $key) => [
                str_replace('_', ' ', ucfirst($key)),
                $val,
            ])->values()->toArray()
        );
    }
}
