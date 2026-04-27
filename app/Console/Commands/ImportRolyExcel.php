<?php

namespace App\Console\Commands;

use App\Helpers\ColorHelper;
use App\Models\BrandColor;
use App\Models\BrandSize;
use App\Models\Category;
use App\Models\PricingRule;
use App\Models\Product;
use App\Models\ProductColor;
use App\Models\ProductSize;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class ImportRolyExcel extends Command
{
    protected $signature = 'import:roly-excel
        {--dry-run : Show what would be done without modifying anything}
        {--file= : Import a single file instead of scanning storage/app/roly/}
        {--dump-columns : Show column headers from the first file and stop}';

    protected $description = 'Import Roly/Stamina products from Excel files (ROL + STA)';

    private array $categoryCache = [];

    private array $stats = [
        'products_created' => 0,
        'products_updated' => 0,
        'colors_created' => 0,
        'colors_updated' => 0,
        'sizes_created' => 0,
        'sizes_updated' => 0,
        'prices_updated' => 0,
        'skus_total' => 0,
        'errors' => 0,
    ];

    /** Excel CATEGORIES (first value, uppercased) → our category slug */
    private array $excelCategoryMap = [
        // ─── ROL: T-shirts ───
        'T-SHIRTS À MANCHES COURTES' => 't-shirts-col-rond-coton',
        'T-SHIRTS À MANCHES LONGUES' => 't-shirts-manches-longues',
        'T-SHIRTS DE SPORT' => 't-shirts-polyester',
        'T-SHIRTS HAUTE VISIBILITÉ' => 'haute-visibilite',
        'T-SHIRT INDUSTRIEL' => 'hauts-de-travail',
        'DÉBARDEURS' => 'debardeurs',
        'T-SHIRTS' => 't-shirts-col-rond-coton',
        'T-SHIRTS ET POLOS' => 't-shirts-col-rond-coton',
        'BASICS' => 't-shirts-col-rond-coton',
        'ROLY CONTROL DRY' => 't-shirts-polyester',
        // ─── ROL: Polos ───
        'POLOS À MANCHES COURTES' => 'polos-manches-courtes',
        'POLOS À MANCHES LONGUES' => 'polos-manches-longues',
        'POLOS DE SPORT' => 'polos-sport',
        'POLOS HAUTE VISIBILITÉ' => 'haute-visibilite',
        'POLOS - IGNIFUGES' => 'haute-visibilite',
        'POLOS' => 'polos-manches-courtes',
        // ─── ROL: Sweats ───
        'SWEATS À CAPUCHE' => 'sweats-capuche',
        'SWEATS SANS CAPUCHE' => 'sweats-col-rond',
        'SWEATS' => 'sweats-col-rond',
        'SWEATS ET VESTES' => 'sweats-col-rond',
        'SWEAT DE SERVICES  INDUSTRIE' => 'sweats-pulls-pro',
        // ─── ROL: Vestes / Manteaux ───
        'VESTES' => 'vestes-ete',
        'SOFTSHELL' => 'softshells',
        'COUPE-VENT' => 'coupe-vent',
        'PARKA' => 'parkas',
        'GILETS' => 'bodywarmer-pro',
        'VESTE HORECA' => 'hotellerie-restauration',
        'VESTES POLAIRES HAUTE VISIBILITÉ' => 'haute-visibilite',
        'VESTES IGNIFUGES' => 'vetements-ignifuges',
        'IMPERMÉABLES' => 'coupe-vent',
        'POLAIRES' => 'pulls-polaire',
        'VÊTEMENTS THERMIQUES' => 'pulls-polaire',
        // ─── ROL: Pantalons ───
        'PANTALONS' => 'pantalons-longs',
        'PANTALONES LARGOS' => 'pantalons-longs',
        'PANTALON HAUTE VISIBILITÉ' => 'haute-visibilite',
        'PANTALON - SANTÉ ET ESTHÉTIQUE' => 'pantalons-de-travail',
        'PANTALON - HORECA' => 'pantalons-de-travail',
        'PANTALON IGNIFUGE' => 'vetements-ignifuges',
        'PANTALONS DE SPORT' => 'jogging-sport',
        'SHORTS' => 'shorts-bermudas',
        'BERMUDAS' => 'shorts-bermudas',
        'LEGGINGS ET COLLANTS' => 'pantalons-longs',
        'SURVÊTEMENTS' => 'jogging-sport',
        // ─── ROL: Sport ───
        'KITS DE SPORT' => 'tenues-completes-sport',
        'SPORT COLLECTION' => 'maillots-t-shirts-sport',
        'WINTER SPORT' => 'vestes-sport',
        // ─── ROL: Workwear / Pro ───
        'SANTÉ ET ESTHÉTIQUE' => 'medical-sante',
        'INDUSTRIE - SERVICES' => 'hauts-de-travail',
        'HAUTE VISIBILITÉ' => 'haute-visibilite',
        'HORECA' => 'hotellerie-restauration',
        'INDUSTRIE ALIMENTAIRE' => 'hotellerie-restauration',
        'INDUSTRIE - IGNIFUGE' => 'vetements-ignifuges',
        'BLOUSE' => 'blouses-vestes-travail',
        'BLOUSE ET CHASUBLE' => 'blouses-vestes-travail',
        // ─── ROL: Chemises ───
        'CHEMISES' => 'chemises-manches-longues',
        // ─── ROL: Sous-vêtements ───
        'SOUS-VÊTEMENTS' => 'slips-calecons',
        'MAILLOTS DE BAIN' => 'maillots-de-bain',
        // ─── ROL: Accessoires ───
        'BONNET' => 'bonnets',
        'ACCESSOIRES' => 'accessoires',
        'TEXTILES' => 'accessoires',
        // ─── ROL: Chaussures ───
        'FOOTWEAR' => 'accessoires',
        'WRK FOOTWEAR' => 'accessoires',
        'PHARES' => 'accessoires',
        // ─── ROL: Divers ───
        'TABLIER' => 'tabliers',
        'ECO' => 'bio',
        'NEW ITEMS' => null,  // skip — real category from other values
        'OUTLET' => null,     // skip — modifier, not a real category
        // ─── STA: Sacs ───
        'SACS' => 'sacs-polyester',
        'SAC À DOS' => 'sacs-a-dos',
        'SACS À DOS' => 'sacs-a-dos',
        'SAC À DOS À CORDON' => 'sacs-polyester',
        'SACS À DOS' => 'sacs-a-dos',
        'PORTES MONNAIES ET SACS BANANE' => 'sacoches-polyester',
        'GLACIÈRE' => 'sacs-polyester',
        'SACS THERMIQUES ET GLACIÈRES' => 'sacs-polyester',
        'SACS ÉTANCHES' => 'sacs-polyester',
        'VALISES & TROLLEY' => 'valises',
        'NECESSAIRE' => 'portefeuilles-trousses',
        'TROUSSE' => 'portefeuilles-trousses',
        'PORTE DOCUMENT' => 'portefeuilles-trousses',
        // ─── STA: Casquettes / Bonnets ───
        'CASQUETTES' => 'casquettes-classiques',
        'BONNETS' => 'bonnets',
        'CHAPEAUX & RUBANS' => 'chapeaux',
        'TONGS ET CHAPEAUX' => 'chapeaux',
        // ─── STA: Textile ───
        'TABLIERS ET MITAINES' => 'tabliers',
        'SERVIETTES ET PARÉOS' => 'serviettes-bain-peignoirs',
        'COUVERTURES' => 'couvertures',
        'TOUR DE COU' => 'echarpes-gants',
        'GANTS' => 'echarpes-gants',
        'PARAPLUIE' => 'ceintures-parapluies',
        // ─── STA: Promo / Objets pub (→ accessoires) ───
        'STYLOS' => 'accessoires',
        'TASSES' => 'accessoires',
        'BIDONS' => 'accessoires',
        'BIDONS THERMIQUES' => 'accessoires',
        'BIDONS DE SPORT' => 'accessoires',
        'BIDONS EN VERRE' => 'accessoires',
        'BLOC NOTES' => 'accessoires',
        'PORTE-CLÉS' => 'accessoires',
        'OUTILS' => 'accessoires',
        'VERRES' => 'accessoires',
        'LANIÈRE ET IDENTIFIANTS' => 'accessoires',
        'ÉVENTAILS ET FOULARDS' => 'accessoires',
        'CHARGEUR SANS FIL' => 'accessoires',
        'POWER BANK' => 'accessoires',
        'HAUT-PARLEURS' => 'accessoires',
        'LUNETTES DE SOLEIL' => 'accessoires',
        'BRACELETS' => 'accessoires',
        'ACCESSOIRES DE VOYAGE' => 'accessoires',
        'JEUX' => 'accessoires',
        'ORNEMENTS' => 'accessoires',
        'CRAYONS' => 'accessoires',
        'AMUSEMENT' => 'accessoires',
        'USB' => 'accessoires',
        'BOUGIES' => 'accessoires',
        'AGENDA & CALENDRIER' => 'accessoires',
        'BOÎTES À LUNCH' => 'accessoires',
        'ÉCOUTEURS' => 'accessoires',
        'OUTDOOR ACCESORIES' => 'accessoires',
        'APPLAUDISSEUR ET SIFFLETS' => 'accessoires',
        'DÉCORATION' => 'accessoires',
        'SUBLIMATION' => 'accessoires',
        'N/D' => null,
    ];

    /** FAMILIE column → our category slug (fallback when CATEGORIES doesn't match) */
    private array $familieMap = [
        'T-SHIRTS' => 't-shirts-col-rond-coton',
        'POLOS' => 'polos-manches-courtes',
        'SWEATS-SHIRTS' => 'sweats-col-rond',
        'PANTALONS' => 'pantalons-longs',
        'VESTES' => 'vestes-ete',
        'HAUTE VISIBILITÉS' => 'haute-visibilite',
        'VÊTEMENT D\'HIVER' => 'pulls-polaire',
        'SOFTSHELLS' => 'softshells',
        'BERMUDAS' => 'shorts-bermudas',
        'PARKAS' => 'parkas',
        'KITS SPORT' => 'tenues-completes-sport',
        'SURVÊTEMENTS' => 'jogging-sport',
        'LEGGINS' => 'pantalons-longs',
        'GILETS' => 'bodywarmer-pro',
        'IMPERMÉABLES' => 'coupe-vent',
        'CHEMISES' => 'chemises-manches-longues',
        'MAILLOTS DE BAIN' => 'maillots-de-bain',
        'POLAIRES' => 'pulls-polaire',
        'TABLIERS' => 'tabliers',
        'COUPES-VENT' => 'coupe-vent',
        'BLOUSES' => 'blouses-vestes-travail',
        'TENUE DE SPORT' => 'maillots-t-shirts-sport',
        'DOSSARDS' => 'chasubles-dossards',
        'FIREPROOF' => 'vetements-ignifuges',
        'OVERALLS' => 'combinaisons-travail',
        'PULLS OVER' => 'pulls-col-rond',
        'CHAUSETTES' => 'chaussettes',
        'PANTALONS COURT' => 'shorts-bermudas',
        'SOUS-VÊTEMENTS' => 'slips-calecons',
        'SACS ET SACS A DOS' => 'sacs-polyester',
        'CARDIGAN SWEATER' => 'gilets-cardigans',
        'BONNETS' => 'bonnets',
        'CASQUETTES' => 'casquettes-classiques',
        'JUPES' => 'jupes-robes',
        'BACKPACKS' => 'sacs-a-dos',
        'FOULARDS' => 'echarpes-gants',
        'PADDLE' => 'maillots-t-shirts-sport',
        'CHAUSSURES' => 'accessoires',
        'BODYS' => 'bodies-bebe',
        'ZAPATO LABORAL' => 'accessoires',
        // STA families
        'PARAPLUIES' => 'ceintures-parapluies',
        'SERVIETTES' => 'serviettes-bain-peignoirs',
        'PLAIDS' => 'couvertures',
        'TOURS DE COU' => 'echarpes-gants',
    ];

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->info('=== DRY RUN MODE ===');
        }

        // Find Excel files
        $files = $this->findExcelFiles();
        if (empty($files)) {
            $this->error('Aucun fichier Excel trouve dans storage/app/roly/. Placez vos fichiers ROL*.xlsx et STA*.xlsx dans ce dossier.');

            return self::FAILURE;
        }

        $this->info(count($files) . ' fichier(s) trouve(s) : ' . implode(', ', array_map('basename', $files)));

        // Load category cache
        $this->categoryCache = Category::pluck('id', 'slug')->toArray();

        foreach ($files as $file) {
            $this->importFile($file, $dryRun);
        }

        $this->displaySummary();

        if ($dryRun) {
            $this->warn('Dry run termine. Aucune modification effectuee.');
        }

        return self::SUCCESS;
    }

    private function findExcelFiles(): array
    {
        $singleFile = $this->option('file');
        if ($singleFile) {
            if (! file_exists($singleFile)) {
                $this->error("Fichier introuvable : {$singleFile}");

                return [];
            }

            return [$singleFile];
        }

        $dir = storage_path('app/roly');
        if (! is_dir($dir)) {
            return [];
        }

        $files = glob($dir . '/*.{xlsx,xls,csv}', GLOB_BRACE);

        return $files ?: [];
    }

    private function importFile(string $filePath, bool $dryRun): void
    {
        $filename = basename($filePath);
        $this->info("Import de {$filename}...");

        // Detect brand from filename
        $brand = $this->detectBrand($filename);
        $this->info("  Marque detectee : {$brand}");

        try {
            $rows = Excel::toArray(null, $filePath);
        } catch (\Throwable $e) {
            $this->error("  Erreur lecture fichier : {$e->getMessage()}");
            $this->stats['errors']++;

            return;
        }

        if (empty($rows) || empty($rows[0])) {
            $this->warn("  Fichier vide.");

            return;
        }

        $sheet = $rows[0];
        $headers = array_map(fn ($h) => trim(strtoupper((string) $h)), $sheet[0]);

        if ($this->option('dump-columns')) {
            $this->info('Colonnes : ' . implode(' | ', $headers));
            $this->info('Premiere ligne de donnees :');
            if (isset($sheet[1])) {
                foreach ($headers as $i => $h) {
                    $this->line("  {$h} = " . ($sheet[1][$i] ?? ''));
                }
            }

            return;
        }

        // Build header index
        $colIndex = [];
        foreach ($headers as $i => $h) {
            $colIndex[$h] = $i;
        }

        // Validate required columns
        $required = ['MODELCODE', 'MODELNAME', 'COLOR', 'SIZE'];
        foreach ($required as $col) {
            if (! isset($colIndex[$col])) {
                $this->error("  Colonne requise manquante : {$col}");
                $this->stats['errors']++;

                return;
            }
        }

        // Detect price column
        $priceCol = $this->detectPriceColumn($colIndex);
        $this->info("  Colonne prix : {$priceCol}");

        // Group rows by MODELCODE
        $models = [];
        $dataRows = array_slice($sheet, 1); // skip header

        foreach ($dataRows as $rowIdx => $row) {
            $modelCode = trim((string) ($row[$colIndex['MODELCODE']] ?? ''));
            if (! $modelCode) {
                continue;
            }

            $this->stats['skus_total']++;

            if (! isset($models[$modelCode])) {
                $models[$modelCode] = [
                    'info' => $row,
                    'colors' => [],
                ];
            }

            $colorName = trim((string) ($row[$colIndex['COLOR']] ?? 'Default'));
            $colorCode = trim((string) ($row[$colIndex['COLORCODE'] ?? -1] ?? ''));

            if (! isset($models[$modelCode]['colors'][$colorName])) {
                $models[$modelCode]['colors'][$colorName] = [
                    'colorcode' => $colorCode,
                    'sizes' => [],
                ];
            }

            $sizeName = trim((string) ($row[$colIndex['SIZE']] ?? 'Unique'));
            $productCode = trim((string) ($row[$colIndex['PRODUCTCODE'] ?? $colIndex['MODELCODE']] ?? ''));
            $ean = trim((string) ($row[$colIndex['EANCODE'] ?? -1] ?? ''));
            $productImage = trim((string) ($row[$colIndex['PRODUCTIMAGE'] ?? -1] ?? ''));

            // Price
            $price = null;
            if ($priceCol && isset($colIndex[$priceCol])) {
                $price = $this->parseNumeric($row[$colIndex[$priceCol]] ?? null);
            }

            $models[$modelCode]['colors'][$colorName]['sizes'][] = [
                'sizename' => $sizeName,
                'sizecode' => trim((string) ($row[$colIndex['SIZECODE'] ?? -1] ?? $sizeName)),
                'productcode' => $productCode,
                'ean' => $ean,
                'image' => $productImage,
                'price' => $price,
            ];
        }

        $this->info('  ' . count($models) . ' modeles, ' . $this->stats['skus_total'] . ' SKUs.');

        // Import models
        $bar = $this->output->createProgressBar(count($models));
        $bar->start();

        foreach ($models as $modelCode => $modelData) {
            try {
                $this->importModel($modelCode, $modelData, $brand, $colIndex, $dryRun);
            } catch (\Throwable $e) {
                $this->stats['errors']++;
                $this->newLine();
                $this->warn("  Erreur {$modelCode}: " . Str::limit($e->getMessage(), 120));
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
    }

    private function importModel(string $modelCode, array $modelData, string $brand, array $colIndex, bool $dryRun): void
    {
        $row = $modelData['info'];
        $supplier = $brand === 'Stamina' ? 'Roly' : 'Roly';

        $name = trim((string) ($row[$colIndex['MODELNAME']] ?? $modelCode));
        $description = trim((string) ($row[$colIndex['DESCRIPTION'] ?? -1] ?? ''));
        $composition = trim((string) ($row[$colIndex['COMPOSITION'] ?? -1] ?? ''));
        $observation = trim((string) ($row[$colIndex['OBSERVATION'] ?? -1] ?? ''));
        $gender = trim((string) ($row[$colIndex['GENDER'] ?? -1] ?? ''));
        $modelImage = trim((string) ($row[$colIndex['MODELIMAGE'] ?? -1] ?? ''));
        $categories = trim((string) ($row[$colIndex['CATEGORIES'] ?? -1] ?? ''));
        $familie = trim((string) ($row[$colIndex['FAMILIE'] ?? -1] ?? ''));
        $minOrderQty = (int) ($row[$colIndex['MIN_ORDER_QTY'] ?? -1] ?? 0);

        // Grammage from composition
        $grammage = null;
        if (preg_match('/(\d+)\s*g\/?m/i', $composition . ' ' . $description, $m)) {
            $grammage = (int) $m[1];
        }

        $categoryId = $this->resolveCategory($categories, $familie, $name, $composition, $gender);
        $slug = $this->generateUniqueSlug($name, $modelCode);

        // Compute per-color base price (most frequent price = standard adult sizes)
        // and overall product base price (min of color base prices)
        $colorBasePrices = [];
        foreach ($modelData['colors'] as $colorName => $colorData) {
            $prices = [];
            foreach ($colorData['sizes'] as $sizeData) {
                if ($sizeData['price'] && $sizeData['price'] > 0) {
                    $prices[] = round((float) $sizeData['price'], 4);
                }
            }
            if (! empty($prices)) {
                // Mode (most frequent price) = standard adult price for this color
                $freq = array_count_values(array_map(fn ($p) => (string) $p, $prices));
                arsort($freq);
                $colorBasePrices[$colorName] = (float) array_key_first($freq);
            }
        }

        $productBaseSupplierPrice = ! empty($colorBasePrices) ? min($colorBasePrices) : null;
        $basePrice = ($productBaseSupplierPrice && $productBaseSupplierPrice > 0)
            ? PricingRule::calculate($productBaseSupplierPrice, 1)
            : null;

        $productData = [
            'name' => $name,
            'slug' => $slug,
            'supplier' => $supplier,
            'category_id' => $categoryId,
            'description' => $description,
            'short_description' => $observation ?: null,
            'material' => $composition ?: null,
            'grammage' => $grammage,
            'cut' => $this->mapGender($gender),
            'main_image' => $modelImage ?: null,
            'is_active' => true,
            'min_order_quantity' => $minOrderQty > 0 ? $minOrderQty : null,
        ];

        if ($productBaseSupplierPrice) {
            $productData['supplier_price'] = $productBaseSupplierPrice;
        }
        if ($basePrice) {
            $productData['base_price'] = $basePrice;
        }

        if ($dryRun) {
            $existing = Product::where('reference', $modelCode)->where('supplier', $supplier)->first();
            $this->stats[$existing ? 'products_updated' : 'products_created']++;

            foreach ($modelData['colors'] as $colorData) {
                $this->stats['colors_created']++;
                $this->stats['sizes_created'] += count($colorData['sizes']);
            }

            return;
        }

        $product = Product::updateOrCreate(
            ['reference' => $modelCode, 'supplier' => $supplier],
            $productData
        );
        $this->stats[$product->wasRecentlyCreated ? 'products_created' : 'products_updated']++;

        // Colors & sizes
        $sortOrder = 0;
        foreach ($modelData['colors'] as $colorName => $colorData) {
            $colorCode = $colorData['colorcode'] ?? '';

            // Color image = first variant image for this color
            $colorImage = null;
            foreach ($colorData['sizes'] as $sizeData) {
                if (! empty($sizeData['image'])) {
                    $colorImage = $sizeData['image'];
                    break;
                }
            }

            $colorSupplierPrice = $colorBasePrices[$colorName] ?? null;

            $hexCode = ColorHelper::resolve($colorName);

            $colorFields = [
                'image' => $colorImage,
                'sort_order' => $sortOrder++,
                'is_active' => true,
            ];
            if ($hexCode) {
                $colorFields['hex_code'] = $hexCode;
            }
            if ($colorSupplierPrice) {
                $colorFields['supplier_price'] = $colorSupplierPrice;
            }

            $color = ProductColor::updateOrCreate(
                ['product_id' => $product->id, 'name' => $colorName],
                $colorFields
            );
            $this->stats[$color->wasRecentlyCreated ? 'colors_created' : 'colors_updated']++;

            // Brand color catalog
            BrandColor::updateOrCreate(
                ['brand' => $supplier, 'name' => $colorName],
                ['color_code' => $colorCode]
            );

            // Sizes
            foreach ($colorData['sizes'] as $sizeData) {
                $sizeName = $this->normalizeSizeName($sizeData['sizename']);

                $sizeFields = [
                    'is_available' => true,
                ];
                if (! empty($sizeData['productcode'])) {
                    $sizeFields['sku'] = $sizeData['productcode'];
                }
                if (! empty($sizeData['ean'])) {
                    $sizeFields['ean'] = $sizeData['ean'];
                }

                // Price supplement = variant price - color base price
                $variantPrice = ($sizeData['price'] && $sizeData['price'] > 0) ? (float) $sizeData['price'] : null;
                if ($variantPrice && $colorSupplierPrice) {
                    $supplement = round($variantPrice - $colorSupplierPrice, 4);
                    $sizeFields['price_supplement'] = $supplement;
                }

                $size = ProductSize::updateOrCreate(
                    ['product_color_id' => $color->id, 'size' => $sizeName],
                    $sizeFields
                );
                $this->stats[$size->wasRecentlyCreated ? 'sizes_created' : 'sizes_updated']++;

                // Brand size catalog
                BrandSize::firstOrCreate(
                    ['brand' => $supplier, 'size' => $sizeName]
                );
            }
        }

        if ($productBaseSupplierPrice && $productBaseSupplierPrice > 0) {
            $this->stats['prices_updated']++;
        }
    }

    // ─── Category mapping ────────────────────────────────────────

    private function resolveCategory(string $categories, string $familie, string $name, string $material, string $gender): int
    {
        // CATEGORIES column may contain comma-separated values; use first
        $catParts = array_map('trim', explode(',', $categories));
        $mainCat = mb_strtoupper(trim($catParts[0] ?? ''));
        $titleLower = mb_strtolower($name);
        $materialLower = mb_strtolower($material);
        $famUpper = mb_strtoupper(trim($familie));

        // 1. Try direct match on first CATEGORIES value
        if ($mainCat && isset($this->excelCategoryMap[$mainCat])) {
            $slug = $this->excelCategoryMap[$mainCat];
            if ($slug !== null) {
                return $this->catId($slug);
            }
            // null means skip this value (modifier like NEW ITEMS / OUTLET)
        }

        // 2. Try secondary CATEGORIES values
        foreach (array_slice($catParts, 1) as $part) {
            $upper = mb_strtoupper(trim($part));
            if (isset($this->excelCategoryMap[$upper]) && $this->excelCategoryMap[$upper] !== null) {
                return $this->catId($this->excelCategoryMap[$upper]);
            }
        }

        // 3. Try FAMILIE column
        if ($famUpper && isset($this->familieMap[$famUpper])) {
            return $this->catId($this->familieMap[$famUpper]);
        }

        // 4. Context-aware refinement by title/material for generic families
        if ($famUpper === 'T-SHIRTS') {
            if (str_contains($titleLower, 'débardeur') || str_contains($titleLower, 'tank') || str_contains($titleLower, 'sans manche')) {
                return $this->catId('debardeurs');
            }
            if (str_contains($titleLower, 'col v') || str_contains($titleLower, 'v-neck')) {
                return $this->catId('t-shirts-col-v');
            }
            if (str_contains($titleLower, 'manches longues') || str_contains($titleLower, 'manga larga')) {
                return $this->catId('t-shirts-manches-longues');
            }
            if (str_contains($materialLower, 'polyester') && ! str_contains($materialLower, 'coton')) {
                return $this->catId('t-shirts-polyester');
            }

            return $this->catId('t-shirts-col-rond-coton');
        }

        if ($famUpper === 'SWEATS-SHIRTS') {
            if (str_contains($titleLower, 'zip') && str_contains($titleLower, 'capuche')) {
                return $this->catId('sweats-zippes-capuche');
            }
            if (str_contains($titleLower, 'zip') || str_contains($titleLower, 'cremallera')) {
                return $this->catId('sweats-zippes');
            }
            if (str_contains($titleLower, 'capuche') || str_contains($titleLower, 'hoodie')) {
                return $this->catId('sweats-capuche');
            }

            return $this->catId('sweats-col-rond');
        }

        // 5. Fallback: slug match on first category name
        $slug = Str::slug($catParts[0] ?? '');
        if ($slug && isset($this->categoryCache[$slug])) {
            return $this->categoryCache[$slug];
        }

        // 6. Create Roly subcategory
        return $this->getOrCreateRolyCategory($catParts[0] ?: ($familie ?: null));
    }

    private function catId(string $slug): int
    {
        return $this->categoryCache[$slug] ?? $this->getOrCreateRolyCategory($slug);
    }

    private ?int $rolyParentCategoryId = null;

    private function getOrCreateRolyCategory(?string $subcategoryName = null): int
    {
        if (! $this->rolyParentCategoryId) {
            $parent = Category::firstOrCreate(
                ['slug' => 'roly'],
                ['name' => 'Roly', 'is_active' => true, 'sort_order' => 999]
            );
            $this->rolyParentCategoryId = $parent->id;
            $this->categoryCache['roly'] = $parent->id;
        }

        if (! $subcategoryName) {
            $sub = Category::firstOrCreate(
                ['slug' => 'roly-non-classe'],
                [
                    'name' => 'Roly - Non classé',
                    'parent_id' => $this->rolyParentCategoryId,
                    'is_active' => true,
                    'sort_order' => 0,
                ]
            );
            $this->categoryCache['roly-non-classe'] = $sub->id;

            return $sub->id;
        }

        $slug = 'roly-' . Str::slug($subcategoryName);
        $sub = Category::firstOrCreate(
            ['slug' => $slug],
            [
                'name' => 'Roly - ' . $subcategoryName,
                'parent_id' => $this->rolyParentCategoryId,
                'is_active' => true,
                'sort_order' => 0,
            ]
        );
        $this->categoryCache[$slug] = $sub->id;

        return $sub->id;
    }

    // ─── Helpers ─────────────────────────────────────────────────

    private function detectBrand(string $filename): string
    {
        $upper = strtoupper($filename);
        if (str_contains($upper, 'STA') || str_contains($upper, 'STAMINA')) {
            return 'Stamina';
        }

        return 'Roly';
    }

    private function detectPriceColumn(array $colIndex): string
    {
        // ROL: PRICE UNIT
        if (isset($colIndex['PRICE UNIT'])) {
            return 'PRICE UNIT';
        }
        // STA: PRICE < 500 UNITS
        if (isset($colIndex['PRICE < 500 UNITS'])) {
            return 'PRICE < 500 UNITS';
        }
        // Fallback patterns
        foreach (array_keys($colIndex) as $col) {
            if (str_contains($col, 'PRICE') && (str_contains($col, 'UNIT') || str_contains($col, '< 500'))) {
                return $col;
            }
        }
        // Any price column
        foreach (array_keys($colIndex) as $col) {
            if (str_contains($col, 'PRICE')) {
                return $col;
            }
        }

        return '';
    }

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
        $this->info('=== Resume import Roly Excel ===');
        $this->table(
            ['Metrique', 'Total'],
            collect($this->stats)->map(fn ($val, $key) => [
                str_replace('_', ' ', ucfirst($key)),
                $val,
            ])->values()->toArray()
        );
    }
}
