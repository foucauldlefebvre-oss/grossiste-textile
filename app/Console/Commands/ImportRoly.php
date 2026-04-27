<?php

namespace App\Console\Commands;

use App\Models\BrandColor;
use App\Models\BrandSize;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductColor;
use App\Models\ProductSize;
use App\Services\RolyApiService;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class ImportRoly extends Command
{
    protected $signature = 'import:roly
        {--dry-run : Show what would be done without modifying anything}
        {--update-stock-only : Only update prices and stock for existing Roly products}
        {--dump-structure : Dump the raw JSON structure of the first catalog item and stop}';

    protected $description = 'Import Roly product catalog via GorFactory REST API';

    private RolyApiService $api;
    private array $categoryMap = [];
    private ?int $rolyParentCategoryId = null;

    private array $stats = [
        'products_created' => 0,
        'products_updated' => 0,
        'colors_created' => 0,
        'colors_updated' => 0,
        'sizes_created' => 0,
        'sizes_updated' => 0,
        'prices_updated' => 0,
        'stock_updated' => 0,
        'skus_total' => 0,
        'errors' => 0,
    ];

    public function handle(RolyApiService $api): int
    {
        $this->api = $api;
        $dryRun = $this->option('dry-run');
        $stockOnly = $this->option('update-stock-only');

        if ($dryRun) {
            $this->info('=== DRY RUN MODE ===');
        }

        // Step 1: Login
        $this->info('Connexion a l\'API GorFactory (Roly)...');
        try {
            $this->api->login();
            $this->info('Connecte avec succes.');
        } catch (\Throwable $e) {
            $this->error("Echec de connexion : {$e->getMessage()}");
            return self::FAILURE;
        }

        // Stock-only mode
        if ($stockOnly) {
            return $this->updateStockOnly($dryRun);
        }

        // Step 2: Fetch catalog (returns flat array of SKUs)
        $this->info('Recuperation du catalogue (brand: ' . $this->api->getBrand() . ')...');
        try {
            $skus = $this->api->getCatalog();
        } catch (\Throwable $e) {
            $this->error("Echec recuperation catalogue : {$e->getMessage()}");
            return self::FAILURE;
        }

        $this->stats['skus_total'] = count($skus);
        $this->info(count($skus) . ' SKUs trouves dans le catalogue.');

        if (empty($skus)) {
            $this->warn('Catalogue vide.');
            return self::SUCCESS;
        }

        // Dump structure mode
        if ($this->option('dump-structure')) {
            $first = reset($skus);
            $this->info('Structure du premier SKU :');
            $this->line(json_encode($first, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            $this->newLine();
            $this->info('Cles disponibles : ' . implode(', ', array_keys($first)));
            return self::SUCCESS;
        }

        // Step 3: Group SKUs by modelcode → color → sizes
        $this->info('Regroupement par modele...');
        $models = $this->groupSkusByModel($skus);
        $this->info(count($models) . ' modeles distincts trouves.');

        // Step 4: Fetch categories
        $this->info('Recuperation des categories...');
        try {
            $rolyCategories = $this->api->getCategories();
            $this->info(count($rolyCategories) . ' categories trouvees.');
        } catch (\Throwable $e) {
            $this->warn("Categories non recuperees ({$e->getMessage()}), mapping par defaut.");
            $rolyCategories = [];
        }
        $this->buildCategoryMap($rolyCategories);

        // Step 5: Import products
        $this->info('Import des produits...');
        $bar = $this->output->createProgressBar(count($models));
        $bar->start();

        foreach ($models as $modelCode => $modelData) {
            try {
                $this->importModel($modelCode, $modelData, $dryRun);
            } catch (\Throwable $e) {
                $this->stats['errors']++;
                $this->newLine();
                $this->error("Erreur modele {$modelCode}: {$e->getMessage()}");
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        // Step 6: Prices
        $this->updatePrices($dryRun);

        // Step 7: Stock
        $this->updateStock($dryRun);

        $this->displaySummary();

        if ($dryRun) {
            $this->warn('Dry run termine. Aucune modification effectuee.');
        }

        return self::SUCCESS;
    }

    /**
     * Group flat SKU array by modelcode.
     * Each SKU has: modelcode, modelname, colorcode, colorname, sizecode, sizename,
     * description, composition, gender, weight, productimage, categorytree, etc.
     *
     * Result: [modelcode => ['info' => firstSku, 'colors' => [colorname => ['info' => sku, 'sizes' => [...]]]]]
     */
    private function groupSkusByModel(array $skus): array
    {
        $models = [];

        foreach ($skus as $sku) {
            $modelCode = $sku['modelcode'] ?? $sku['modelid'] ?? null;
            if (! $modelCode) {
                continue;
            }

            if (! isset($models[$modelCode])) {
                $models[$modelCode] = [
                    'info' => $sku, // Use first SKU for model-level info
                    'colors' => [],
                ];
            }

            $colorName = $sku['colorname'] ?? $sku['colorcode'] ?? 'Default';
            $colorCode = $sku['colorcode'] ?? '';

            if (! isset($models[$modelCode]['colors'][$colorName])) {
                $models[$modelCode]['colors'][$colorName] = [
                    'info' => $sku,
                    'colorcode' => $colorCode,
                    'sizes' => [],
                ];
            }

            $sizeName = $sku['sizename'] ?? $sku['sizecode'] ?? null;
            if ($sizeName) {
                $models[$modelCode]['colors'][$colorName]['sizes'][] = [
                    'sizename' => $sizeName,
                    'sizecode' => $sku['sizecode'] ?? $sizeName,
                    'itemcode' => $sku['itemcode'] ?? '',
                    'eancode' => $sku['eancode'] ?? '',
                    'weight' => $sku['weight'] ?? null,
                ];
            }
        }

        return $models;
    }

    private function importModel(string $modelCode, array $modelData, bool $dryRun): void
    {
        $info = $modelData['info'];

        $name = $info['modelname'] ?? $info['itemname'] ?? $modelCode;
        $reference = $modelCode;
        $slug = $this->generateUniqueSlug($name, $reference);
        $categoryId = $this->mapCategory($info);

        // Extract grammage from composition or observations (e.g. "180 g/m²")
        $grammage = null;
        $composition = $info['composition'] ?? '';
        $observations = $info['observations'] ?? '';
        if (preg_match('/(\d+)\s*g\/?m/i', $composition . ' ' . $observations, $m)) {
            $grammage = (int) $m[1];
        }

        $weight = $this->parseNumeric($info['weight'] ?? null);

        $productData = [
            'name' => $name,
            'slug' => $slug,
            'supplier' => 'Roly',
            'category_id' => $categoryId,
            'description' => $info['description'] ?? '',
            'short_description' => $info['observations'] ?? '',
            'material' => $composition ?: null,
            'grammage' => $grammage,
            'weight' => $weight,
            'cut' => $this->mapGender($info['gender'] ?? $info['gendercode'] ?? null),
            'main_image' => $info['modelimage'] ?? $info['productimage'] ?? null,
            'is_active' => true,
        ];

        if ($dryRun) {
            $existing = Product::where('reference', $reference)->where('supplier', 'Roly')->first();
            $this->stats[$existing ? 'products_updated' : 'products_created']++;

            foreach ($modelData['colors'] as $colorName => $colorData) {
                $this->stats['colors_created']++;
                $this->stats['sizes_created'] += count($colorData['sizes']);
            }

            return;
        }

        $product = Product::updateOrCreate(
            ['reference' => $reference, 'supplier' => 'Roly'],
            $productData
        );
        $this->stats[$product->wasRecentlyCreated ? 'products_created' : 'products_updated']++;

        // Import colors and sizes
        $sortOrder = 0;
        foreach ($modelData['colors'] as $colorName => $colorData) {
            $colorInfo = $colorData['info'];

            // Hex code from colordata (v2) or try to parse from colorcode
            $hexCode = $colorInfo['colordata']['hexcode']
                ?? $colorInfo['hexcode']
                ?? null;
            if ($hexCode && ! Str::startsWith($hexCode, '#')) {
                $hexCode = '#' . $hexCode;
            }
            if ($hexCode && strlen($hexCode) > 7) {
                $hexCode = substr($hexCode, 0, 7);
            }

            $colorImage = $colorInfo['childimage'] ?? $colorInfo['productimage'] ?? null;

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

            // Feed brand color catalog
            BrandColor::updateOrCreate(
                ['brand' => 'Roly', 'name' => $colorName],
                ['hex_code' => $hexCode, 'color_code' => $colorData['colorcode'] ?? null]
            );

            // Sizes
            foreach ($colorData['sizes'] as $sizeData) {
                $sizeName = $this->normalizeSizeName($sizeData['sizename']);

                $size = ProductSize::updateOrCreate(
                    ['product_color_id' => $color->id, 'size' => $sizeName],
                    ['stock' => 0, 'is_available' => true]
                );
                $this->stats[$size->wasRecentlyCreated ? 'sizes_created' : 'sizes_updated']++;

                // Feed brand size catalog
                BrandSize::firstOrCreate(
                    ['brand' => 'Roly', 'size' => $sizeName]
                );
            }
        }
    }

    // ─── Prices ──────────────────────────────────────────────────

    private function updatePrices(bool $dryRun): void
    {
        $this->info('Recuperation des prix...');

        try {
            $priceData = $this->api->getPriceList();

            if (empty($priceData) || ! is_array($priceData)) {
                $this->warn('Aucune donnee de prix retournee.');
                return;
            }

            $this->info(count($priceData) . ' lignes de prix recues.');

            if ($dryRun) {
                return;
            }

            // Group prices by modelcode to get min price as supplier_price
            $modelPrices = [];
            foreach ($priceData as $priceItem) {
                if (! is_array($priceItem)) {
                    continue;
                }

                $modelCode = $priceItem['modelcode'] ?? $priceItem['modelid'] ?? null;
                $price = $this->parseNumeric($priceItem['price'] ?? $priceItem['pvp'] ?? $priceItem['baseprice'] ?? null);

                if ($modelCode && $price && $price > 0) {
                    if (! isset($modelPrices[$modelCode]) || $price < $modelPrices[$modelCode]) {
                        $modelPrices[$modelCode] = $price;
                    }
                }
            }

            foreach ($modelPrices as $modelCode => $price) {
                $updated = Product::where('reference', $modelCode)
                    ->where('supplier', 'Roly')
                    ->update(['supplier_price' => $price]);
                if ($updated) {
                    $this->stats['prices_updated']++;
                }
            }
        } catch (\Throwable $e) {
            $this->warn("Erreur recuperation prix: {$e->getMessage()}");
        }
    }

    // ─── Stock ───────────────────────────────────────────────────

    private function updateStock(bool $dryRun): void
    {
        $this->info('Recuperation des stocks (nouvelle API)...');

        try {
            $stockData = $this->api->getStock();

            if (empty($stockData) || ! is_array($stockData)) {
                $this->warn('Aucune donnee de stock retournee.');
                return;
            }

            // Filtrer uniquement ROL (pas Stamina)
            $rolyRefs = [];
            foreach ($stockData as $ref => $sizes) {
                $firstSize = reset($sizes);
                $brand = $firstSize['BRAND_ORG'] ?? '?';
                if ($brand === 'ROL') {
                    $rolyRefs[$ref] = $sizes;
                }
            }

            $this->info(count($rolyRefs) . ' references Roly (filtre ROL, ' . count($stockData) . ' total).');

            if ($dryRun) {
                return;
            }

            // Charger tous les SKUs des produits Roly
            $skuSizes = ProductSize::whereHas('color.product', fn ($q) => $q->where('supplier', 'Roly'))
                ->whereNotNull('sku')
                ->where('sku', '!=', '')
                ->get();

            $this->info($skuSizes->count() . ' SKUs Roly en base.');

            // Construire un map SKU → stock
            $stockMap = [];
            foreach ($rolyRefs as $ref => $sizes) {
                foreach ($sizes as $sizeCode => $info) {
                    $qty = (int) ($info['STOCK'] ?? 0);
                    // Le SKU en base peut etre la ref complete ou ref+size
                    $stockMap[$ref] = ($stockMap[$ref] ?? 0) + $qty;
                    $stockMap[$ref . $sizeCode] = $qty;
                }
            }

            // Appliquer les stocks
            foreach ($skuSizes as $size) {
                $sku = $size->sku;
                if (isset($stockMap[$sku])) {
                    $qty = $stockMap[$sku];
                    $size->update([
                        'stock' => $qty,
                        'is_available' => $qty > 0,
                    ]);
                    $this->stats['stock_updated']++;
                }
            }

            // Mettre a jour le stock agrege par couleur
            \DB::statement("
                UPDATE product_colors pc
                SET stock = (
                    SELECT COALESCE(SUM(ps.stock), 0)
                    FROM product_sizes ps
                    WHERE ps.product_color_id = pc.id
                )
                WHERE pc.product_id IN (SELECT id FROM products WHERE supplier = 'Roly')
            ");

            // Mettre a jour le stock agrege par produit
            \DB::statement("
                UPDATE products p
                SET stock = (
                    SELECT COALESCE(SUM(pc.stock), 0)
                    FROM product_colors pc
                    WHERE pc.product_id = p.id
                )
                WHERE p.supplier = 'Roly'
            ");

        } catch (\Throwable $e) {
            $this->warn("Erreur recuperation stocks: {$e->getMessage()}");
        }
    }

    // ─── Stock-only mode ─────────────────────────────────────────

    private function updateStockOnly(bool $dryRun): int
    {
        $count = Product::where('supplier', 'Roly')->count();
        $this->info("Mode MAJ stocks/prix — {$count} produits Roly en base.");

        if ($count === 0) {
            $this->warn('Aucun produit Roly. Lancez d\'abord un import complet.');
            return self::SUCCESS;
        }

        // Prix non disponibles sur la nouvelle API (api.gorfactory.es)
        // $this->updatePrices($dryRun);
        $this->updateStock($dryRun);
        $this->displaySummary();

        if ($dryRun) {
            $this->warn('Dry run termine. Aucune modification effectuee.');
        }

        return self::SUCCESS;
    }

    // ─── Category mapping ────────────────────────────────────────

    private function buildCategoryMap(array $rolyCategories): void
    {
        $dbCategories = Category::all();
        $this->categoryMap = [];

        $flat = $this->flattenCategoryTree($rolyCategories);

        foreach ($flat as $rolyCat) {
            $match = $this->findCategoryMatch($rolyCat['name'], $dbCategories);
            if ($match) {
                if (isset($rolyCat['id'])) {
                    $this->categoryMap['id:' . $rolyCat['id']] = $match->id;
                }
                $this->categoryMap[Str::lower($rolyCat['name'])] = $match->id;
            }
        }

        $this->info(count($this->categoryMap) . ' categories mappees.');
    }

    private function flattenCategoryTree(array $tree, array &$result = []): array
    {
        foreach ($tree as $node) {
            if (! is_array($node)) {
                continue;
            }

            $name = $node['category'] ?? $node['name'] ?? null;
            $id = $node['id'] ?? null;

            if ($name) {
                $result[] = ['name' => $name, 'id' => $id];
            }

            $children = $node['subcategories'] ?? $node['subcategory'] ?? $node['children'] ?? [];
            if (is_array($children) && ! empty($children)) {
                $this->flattenCategoryTree($children, $result);
            }
        }

        return $result;
    }

    private function findCategoryMatch(string $rolyName, $dbCategories): ?Category
    {
        $lower = Str::lower(trim($rolyName));
        $slug = Str::slug($rolyName);

        // Exact name match
        $match = $dbCategories->first(fn ($c) => Str::lower($c->name) === $lower);
        if ($match) {
            return $match;
        }

        // Slug match
        $match = $dbCategories->first(fn ($c) => $c->slug === $slug);
        if ($match) {
            return $match;
        }

        // Partial match (Roly name contained in DB name or vice versa)
        $match = $dbCategories->first(fn ($c) => Str::contains(Str::lower($c->name), $lower)
            || Str::contains($lower, Str::lower($c->name)));
        if ($match) {
            return $match;
        }

        return null;
    }

    /**
     * Mapping par description du produit vers nos categories.
     * Patterns testes dans l'ordre (le premier match gagne).
     */
    private const DESCRIPTION_CATEGORY_MAP = [
        // Sweats
        '/sweat.*capuche|hoodie|hooded|sudadera.*capucha/i' => 23,  // Sweats a capuche
        '/sweatshirt|sweater|sudadera/i' => 22,                      // Sweats col rond
        // Polos
        '/polo.*long/i' => 17,                                       // Polos manches longues
        '/polo/i' => 16,                                              // Polos manches courtes
        // T-shirts
        '/debardeur|tank|camiseta.*tirantes|sans.manche/i' => 15,    // Debardeurs
        '/t.shirt.*long|camiseta.*manga.*larga/i' => 13,             // T-shirts manches longues
        '/t.shirt|tee.shirt|camiseta/i' => 11,                       // T-shirts col rond coton
        // Chemises
        '/chemise|camisa|shirt.*col/i' => 29,                         // Chemises manches longues
        // Vestes / manteaux
        '/doudoune|puffer|matelass|acolchad/i' => 47,                // Parkas
        '/parka/i' => 47,
        '/softshell/i' => 42,                                         // Softshells
        '/coupe.vent|cortaviento|windbreaker/i' => 41,                // Coupe-vent
        '/veste|jacket|chaqueta/i' => 44,                             // Vestes ete
        '/polaire|fleece|micropolar/i' => 38,                         // Pulls polaire
        '/gilet|bodywarmer|chaleco/i' => 76,                          // Bodywarmer
        // Pantalons
        '/short|bermuda/i' => 50,                                     // Shorts
        '/pantalon|pant\b|jogger/i' => 49,                           // Pantalons
        '/legging/i' => 49,
        // Travail
        '/tablier|delantal|apron/i' => 117,                           // Tabliers
        '/haute.visib|alta.visib/i' => 83,                            // Haute visibilite
        '/blouse|bata/i' => 79,                                       // Blouses
        '/chaussure|botte|zapato|boot/i' => 7,                       // Accessoires (chaussures)
        // Sport
        '/maillot.*sport|camiseta.*deport/i' => 90,                  // Maillots sport
        '/jogging|survetement/i' => 92,                               // Jogging
        '/chasuble|dossard|peto/i' => 89,                             // Chasubles
        // Accessoires tete
        '/casquette|gorra|cap\b/i' => 58,                            // Casquettes
        '/bonnet|gorro|beanie/i' => 63,                               // Bonnets
        '/chapeau|sombrero|hat\b/i' => 61,                           // Chapeaux
        // Sacs
        '/sac.*dos|mochila|backpack/i' => 68,                        // Sacs a dos
        '/valise|maleta|trolley/i' => 70,                             // Valises
        '/sacoche|bandolera/i' => 67,                                 // Sacoches
        '/trousse|neceser|portefeuille|monedero/i' => 71,             // Portefeuilles
        '/sac\b|bolsa|bag\b|tote/i' => 69,                           // Sacs polyester
        // Textile maison
        '/serviette|toalla|towel|peignoir/i' => 119,                  // Serviettes et peignoirs
        '/couverture|manta|blanket/i' => 112,                         // Couvertures
        // Sous-vetements
        '/slip|calec|boxer/i' => 53,                                  // Slips et calecons
        '/maillot.*bain|bana?dor/i' => 55,                           // Maillots de bain
        // Bebe
        '/bebe|baby/i' => 105,                                        // Bebes
        // Echarpes
        '/echarpe|bufanda|gant|guante/i' => 72,                      // Echarpes et gants
        '/ceinture|cintur|parapluie|paraguas/i' => 74,               // Ceintures et parapluies
    ];

    private function mapCategory(array $sku): int
    {
        // 1. Essayer le mapping API categorytree vers nos categories existantes
        $categoryTree = $sku['categorytree'] ?? null;
        if (is_array($categoryTree)) {
            foreach (array_reverse($categoryTree) as $cat) {
                $catName = is_array($cat) ? ($cat['category'] ?? $cat['name'] ?? null) : $cat;
                $catId = is_array($cat) ? ($cat['id'] ?? null) : null;

                if ($catId && isset($this->categoryMap['id:' . $catId])) {
                    return $this->categoryMap['id:' . $catId];
                }
                if ($catName) {
                    $key = Str::lower($catName);
                    if (isset($this->categoryMap[$key])) {
                        return $this->categoryMap[$key];
                    }
                }
            }
        }

        // 2. Mapping par description du produit (plus fiable)
        $text = strtolower(
            ($sku['modelname'] ?? '') . ' ' .
            ($sku['description'] ?? '') . ' ' .
            ($sku['family'] ?? '')
        );

        foreach (self::DESCRIPTION_CATEGORY_MAP as $pattern => $categoryId) {
            if (preg_match($pattern, $text)) {
                return $categoryId;
            }
        }

        // 3. Fallback : Accessoires
        return 7;
    }

    // ─── Helpers ─────────────────────────────────────────────────

    private function mapGender(?string $gender): string
    {
        if (! $gender) {
            return 'mixte';
        }

        $lower = Str::lower(trim($gender));

        return match (true) {
            Str::contains($lower, ['homme', 'man', 'male', 'hombre', 'men', 'caballero']) => 'homme',
            Str::contains($lower, ['femme', 'woman', 'female', 'mujer', 'women', 'señora']) => 'femme',
            Str::contains($lower, ['enfant', 'child', 'kid', 'niño', 'junior', 'infantil']) => 'enfant',
            Str::contains($lower, ['bebe', 'bébé', 'baby', 'bebé']) => 'enfant',
            Str::contains($lower, ['unisex', 'mixte']) => 'mixte',
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
        ];

        return $aliases[$size] ?? $size;
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
        $this->info('=== Resume import Roly ===');
        $this->table(
            ['Metrique', 'Total'],
            collect($this->stats)->map(fn ($val, $key) => [
                str_replace('_', ' ', ucfirst($key)),
                $val,
            ])->values()->toArray()
        );
    }
}
