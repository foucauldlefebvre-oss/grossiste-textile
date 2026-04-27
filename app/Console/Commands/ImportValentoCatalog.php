<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductColor;
use App\Models\ProductSize;
use App\Models\SlugRedirect;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportValentoCatalog extends Command
{
    protected $signature = 'import:valento-catalog
        {file? : Path to the Valento XLSX catalog file}
        {--dry-run : Show what would be done without modifying anything}';

    protected $description = 'Import Valento product catalog from official XLSX file';

    private array $stats = [
        'products_created' => 0,
        'products_updated' => 0,
        'colors_created' => 0,
        'sizes_created' => 0,
        'skus_set' => 0,
        'redirects_created' => 0,
        'errors' => 0,
    ];

    /** Category slug cache: slug → id */
    private array $categoryCache = [];

    public function handle(): int
    {
        ini_set('memory_limit', '512M');

        $file = $this->argument('file') ?: base_path('valento_catalog.xlsx');
        $dryRun = $this->option('dry-run');

        if (! file_exists($file)) {
            $this->error("Fichier non trouvé : {$file}");
            return self::FAILURE;
        }

        if ($dryRun) {
            $this->info('=== DRY RUN MODE ===');
        }

        // Load categories cache
        $this->categoryCache = Category::pluck('id', 'slug')->toArray();

        // Parse Excel
        $this->info('Lecture du fichier Excel...');
        $articles = $this->parseExcel($file);
        $this->info(count($articles) . ' articles Valento trouvés.');

        // Import
        $this->info('Import des produits...');
        $bar = $this->output->createProgressBar(count($articles));
        $bar->start();

        foreach ($articles as $articleName => $data) {
            try {
                $this->importArticle($articleName, $data, $dryRun);
            } catch (\Throwable $e) {
                $this->stats['errors']++;
                $this->newLine();
                $this->error("Erreur [{$articleName}]: {$e->getMessage()}");
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->displaySummary();

        if ($dryRun) {
            $this->warn('Dry run terminé. Aucune modification effectuée.');
        }

        return self::SUCCESS;
    }

    // ─── Excel parsing ───────────────────────────────────────────

    private function parseExcel(string $file): array
    {
        $reader = IOFactory::createReaderForFile($file);
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($file);
        $sheet = $spreadsheet->getSheet(0);
        $highestRow = $sheet->getHighestRow();

        $articles = [];

        for ($row = 2; $row <= $highestRow; $row++) {
            $articleName = trim($sheet->getCell('A' . $row)->getValue() ?? '');
            if ($articleName === '') {
                continue;
            }

            $color = trim($sheet->getCell('B' . $row)->getValue() ?? '');
            $size = $this->normalizeSize(trim($sheet->getCell('C' . $row)->getValue() ?? ''));
            $cref = trim($sheet->getCell('D' . $row)->getValue() ?? '');
            $techDesc = trim($sheet->getCell('F' . $row)->getValue() ?? '');
            $price = (float) ($sheet->getCell('H' . $row)->getValue() ?? 0);
            $image = $this->fixImageUrl(trim($sheet->getCell('M' . $row)->getValue() ?? ''));

            if (! isset($articles[$articleName])) {
                $articles[$articleName] = [
                    'description' => $techDesc,
                    'min_price' => $price > 0 ? $price : 999,
                    'image' => $image,
                    'colors' => [],
                ];
            }

            if ($price > 0 && $price < $articles[$articleName]['min_price']) {
                $articles[$articleName]['min_price'] = $price;
            }

            if (! isset($articles[$articleName]['colors'][$color])) {
                $articles[$articleName]['colors'][$color] = [
                    'image' => $image,
                    'sizes' => [],
                ];
            }

            $articles[$articleName]['colors'][$color]['sizes'][$size] = [
                'cref' => $cref,
                'price' => $price,
            ];
        }

        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);

        return $articles;
    }

    // ─── Import single article ───────────────────────────────────

    private function importArticle(string $name, array $data, bool $dryRun): void
    {
        $categorySlug = $this->guessCategory($name);
        $categoryId = $this->categoryCache[$categorySlug] ?? null;

        if (! $categoryId) {
            $this->stats['errors']++;
            return;
        }

        // Extract a stable reference from the first cref (model part without color+size suffix)
        $firstCref = null;
        foreach ($data['colors'] as $colorData) {
            foreach ($colorData['sizes'] as $sizeData) {
                $firstCref = $sizeData['cref'];
                break 2;
            }
        }

        $reference = $firstCref ? $this->extractModelRef($firstCref) : Str::upper(Str::slug($name, ''));
        $slug = $this->generateSlug($name, $reference);

        // Extract material and grammage from description
        $material = $this->extractMaterial($data['description']);
        $grammage = $this->extractGrammage($data['description']);
        $cut = $this->guessCut($name, $data);

        if ($dryRun) {
            $this->stats['products_created']++;
            foreach ($data['colors'] as $colorName => $colorData) {
                $this->stats['colors_created']++;
                $this->stats['sizes_created'] += count($colorData['sizes']);
                $this->stats['skus_set'] += count($colorData['sizes']);
            }
            return;
        }

        // Find existing Valento product to overwrite
        $existing = Product::where('supplier', 'Valento')
            ->where(function ($q) use ($name, $reference) {
                $q->where('name', $name)
                  ->orWhere('reference', $reference);
            })
            ->first();

        // Save old slug for redirect
        $oldSlug = $existing?->slug;

        $productData = [
            'name' => $name,
            'slug' => $slug,
            'description' => $data['description'],
            'short_description' => $name,
            'material' => $material,
            'grammage' => $grammage,
            'cut' => $cut,
            'base_price' => $data['min_price'] < 999 ? $data['min_price'] : null,
            'supplier_price' => $data['min_price'] < 999 ? $data['min_price'] : null,
            'main_image' => $this->getMainImage($reference, $data['image']),
            'is_active' => $existing ? $existing->is_active : true,
        ];

        // Only set guessed category for NEW products — preserve manual recategorization
        if (! $existing) {
            $productData['category_id'] = $categoryId;
        }

        $product = Product::updateOrCreate(
            ['reference' => $reference, 'supplier' => 'Valento'],
            $productData
        );

        $this->stats[$product->wasRecentlyCreated ? 'products_created' : 'products_updated']++;

        // Create 301 redirect if slug changed
        if ($oldSlug && $oldSlug !== $slug) {
            SlugRedirect::updateOrCreate(
                ['old_slug' => $oldSlug, 'type' => 'product'],
                ['new_slug' => $slug]
            );
            $this->stats['redirects_created']++;
        }

        // Remove old colors/sizes and recreate from Excel (complete overwrite)
        $product->colors()->delete();

        $sortOrder = 0;
        foreach ($data['colors'] as $colorName => $colorData) {
            $color = ProductColor::create([
                'product_id' => $product->id,
                'name' => $colorName,
                'hex_code' => $this->guessHexCode($colorName),
                'image' => $colorData['image'],
                'sort_order' => $sortOrder++,
                'is_active' => true,
            ]);
            $this->stats['colors_created']++;

            foreach ($colorData['sizes'] as $sizeName => $sizeData) {
                ProductSize::create([
                    'product_color_id' => $color->id,
                    'size' => $sizeName,
                    'sku' => $sizeData['cref'],
                    'stock' => 0,
                    'is_available' => true,
                ]);
                $this->stats['sizes_created']++;
                $this->stats['skus_set']++;
            }
        }
    }

    // ─── Category mapping ────────────────────────────────────────

    private function guessCategory(string $name): string
    {
        // Exact keyword matching, most specific first
        $rules = [
            // T-shirts
            ['/^t-shirt\s+(enfant|bébé|bebe)/i', 't-shirts-bebe'],
            ['/^t-shirt\s+technique/i', 'maillots-t-shirts-sport'],
            ['/^t-shirt/i', 't-shirts-col-rond-coton'],
            ['/^mini t-shirt/i', 'accessoires-pro'],
            // Polos
            ['/^polo\s+m\/l/i', 'polos-manches-longues'],
            ['/^polo\s+(sport|technique)/i', 'polos-sport'],
            ['/^polo/i', 'polos-manches-courtes'],
            // Sweats
            ['/^sweat.*(capuche|hoodie)/i', 'sweats-a-capuche'],
            ['/^sweat.*zipp/i', 'sweats-zippes'],
            ['/^sweat/i', 'sweats-col-rond'],
            // Chemises
            ['/^chemise.*m\/l|^chemise.*manches?\s*longues?/i', 'chemises-manches-longues'],
            ['/^surchemise/i', 'chemises-manches-courtes'],
            ['/^chemise/i', 'chemises-manches-courtes'],
            // Polaires
            ['/^gilet.*polaire/i', 'polaires-sans-manches'],
            ['/^polaire|^veste\s+polaire|^fleece/i', 'gilets-polaire'],
            // Gilets / Bodywarmer
            ['/^gilet|^bodywarmer/i', 'bodywarmer-pro'],
            // Softshell
            ['/^softshell|^veste.*softshell/i', 'softshells'],
            // Coupe-vent / K-way / Cape pluie / Couvre pantalon pluie
            ['/^coupe.?vent|^k-way|^cape\s+pluie|^couvre\s+pantalon/i', 'coupe-vent'],
            // Parka / Doudoune / Manteau
            ['/^parka/i', 'parkas'],
            ['/^doudoune/i', 'doudounes'],
            ['/^manteau/i', 'manteaux-hiver'],
            // Vestes
            ['/^veste.*(sport|survêt)/i', 'vestes-sport'],
            ['/^veste.*(travail|pro)/i', 'blouses-vestes-travail'],
            ['/^blouson|^blouse/i', 'blouses-vestes-travail'],
            ['/^veste/i', 'vestes-ete'],
            // Pantalons
            ['/^pantalon.*(sport|survêt|jogging)/i', 'jogging-sport'],
            ['/^pantalon/i', 'pantalons-longs'],
            ['/^leggings|^collant/i', 'pantalons-longs'],
            ['/^culotte\s+cyclisme/i', 'slips-calecons'],
            // Shorts
            ['/^short|^bermuda/i', 'shorts-bermudas'],
            // Combinaisons / Salopettes
            ['/^combinaison|^salopette/i', 'combinaisons-travail'],
            // Ensemble sport
            ['/^ensemble\s+(sport|de\s+sport)/i', 'tenues-completes-sport'],
            ['/^ensemble\s+médical/i', 'hotellerie-restauration'],
            // Débardeurs / Maillots sport
            ['/^maillot|^débardeur.*sport|^chasuble|^dossard/i', 'maillots-t-shirts-sport'],
            ['/^débardeur/i', 'debardeurs'],
            // Pulls
            ['/^pull/i', 'pulls-col-rond'],
            // Jupes / Robes
            ['/^jupe|^robe/i', 'jupes-robes'],
            // Bébé
            ['/^body|^bodies/i', 'bodies-bebe'],
            ['/^bavoir/i', 'bonnets-bavoirs-bebe'],
            // Casquettes / Couvre-chef
            ['/^casquette/i', 'casquettes-classiques'],
            ['/^bob[^a-z]/i', 'bobs'],
            ['/^bonnet/i', 'bonnets'],
            ['/^chapeau|^chapka/i', 'chapeaux'],
            ['/^béret/i', 'casquettes-anglaises-berets'],
            ['/^balaclava|^cache\s+cou/i', 'echarpes-gants'],
            // Écharpes / Gants
            ['/^écharpe|^foulard|^gant/i', 'echarpes-gants'],
            ['/^bandana/i', 'bandanas'],
            // Ceintures / Cravates / Accessoires vestimentaires
            ['/^ceinture|^boucle\s+ceinture|^passants\s+ceinture/i', 'ceintures-parapluies'],
            ['/^cravate|^noeud\s+papillon|^bretelles/i', 'ceintures-parapluies'],
            // Tabliers / Cuisine / Restauration
            ['/^tablier/i', 'tabliers'],
            ['/^toque|^tunique|^tabard/i', 'hotellerie-restauration'],
            // Sacs / Porte-documents
            ['/^sac|^valise|^sacoche/i', 'sacs-polyester'],
            ['/^housse\s+de\s+vêtements/i', 'sacs-polyester'],
            ['/^porte\s+documents/i', 'portefeuilles-trousses'],
            ['/^trousse|^pochette/i', 'portefeuilles-trousses'],
            // Sous-vêtements
            ['/^boxer|^slip/i', 'slips-calecons'],
            ['/^chaussett/i', 'chaussettes'],
            // Maison
            ['/^serviette|^peignoir|^tapis\s+de\s+bain/i', 'serviettes-bain-peignoirs'],
            ['/^couverture|^plaid/i', 'couvertures'],
            ['/^chiffon|^torchon/i', 'chiffons'],
            ['/^nappe|^chemin\s+de\s+table|^set\s+de\s+table/i', 'nappes-serviettes'],
            ['/^manique/i', 'accessoires-pro'],
            // Sécurité / Haute visibilité
            ['/^haute.?visib/i', 'haute-visibilite'],
            ['/^bande\s+rétro|^épaulette/i', 'haute-visibilite'],
            ['/^Échantillon.*bandes/i', 'haute-visibilite'],
            // Parapluie
            ['/^parapluie/i', 'ceintures-parapluies'],
            // Accessoires pro catch-all
            ['/^bracelet|^cordon|^porte-gants|^porte-objets/i', 'accessoires-pro'],
            ['/^housse|^ruban|^autocollant|^carton|^avec\s+ventouse/i', 'accessoires-pro'],
        ];

        foreach ($rules as [$pattern, $slug]) {
            if (preg_match($pattern, $name)) {
                return $slug;
            }
        }

        return 'accessoires-pro'; // fallback
    }

    // ─── Helpers ─────────────────────────────────────────────────

    private function normalizeSize(string $size): string
    {
        $size = ltrim($size, '_');
        $size = trim($size);

        $map = [
            'unica' => 'TU',
            'adulto' => 'TU',
            'niño' => 'Enfant',
        ];

        return $map[mb_strtolower($size)] ?? $size;
    }

    /**
     * Extract model reference from a cref.
     * e.g. "CAVABASBL20" → strip last 4 chars (color 2 + size 2) → "CAVABAS"
     * But some crefs have 3-char color codes. Use a heuristic:
     * strip trailing digits then strip 2-char color code.
     */
    private function extractModelRef(string $cref): string
    {
        // Strip trailing size digits (2-3 chars)
        $ref = preg_replace('/\d{2,3}$/', '', $cref);
        // Strip trailing 2-char color code (uppercase letters)
        $ref = preg_replace('/[A-Z]{2}$/', '', $ref);

        return $ref ?: $cref;
    }

    private function generateSlug(string $name, string $reference): string
    {
        $baseSlug = Str::slug($name . '-valento');
        if (! $baseSlug) {
            $baseSlug = Str::slug($reference);
        }

        $slug = $baseSlug;
        $counter = 1;

        while (Product::where('slug', $slug)->where('reference', '!=', $reference)->where('supplier', '!=', 'Valento')->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    private function extractMaterial(string $description): ?string
    {
        if (preg_match('/100%\s+[^.,:;]+/i', $description, $m)) {
            return trim($m[0]);
        }
        if (preg_match('/(\d+%\s+\w+[\s,]+\d+%\s+\w+)/i', $description, $m)) {
            return trim($m[1]);
        }

        return null;
    }

    private function extractGrammage(string $description): ?int
    {
        if (preg_match('/(\d+)\s*g\/m[²2]/i', $description, $m)) {
            return (int) $m[1];
        }

        return null;
    }

    private function guessCut(string $name, array $data): string
    {
        $lower = mb_strtolower($name);

        if (str_contains($lower, 'femme') || str_contains($lower, 'fille')) {
            return 'femme';
        }
        if (str_contains($lower, 'homme')) {
            return 'homme';
        }
        if (str_contains($lower, 'enfant') || str_contains($lower, 'bébé') || str_contains($lower, 'bebe')) {
            return 'enfant';
        }

        // Check sizes for children indicators
        foreach ($data['colors'] as $colorData) {
            foreach (array_keys($colorData['sizes']) as $size) {
                if (preg_match('/^(4\/5|6\/8|10\/12|3|4|6|8|10|12|14)$/', $size)) {
                    return 'enfant';
                }
            }
            break; // Only check first color
        }

        return 'mixte';
    }

    private function displaySummary(): void
    {
        $this->newLine();
        $this->info('=== Résumé import catalogue Valento ===');
        $this->table(
            ['Métrique', 'Total'],
            collect($this->stats)->map(fn ($val, $key) => [
                str_replace('_', ' ', ucfirst($key)),
                $val,
            ])->values()->toArray()
        );
    }

    /**
     * Fix image URL from Excel: replace valentocatalog.eu domain and remove category subfolder.
     *
     * Excel:   http://valentocatalog.eu/digital_content/images_valento/articles/CU/CAVABAS/CAVABASBL.jpg
     * Correct: https://www.valento.es/images_valento/articles/CAVABAS/CAVABASBL.jpg
     */
    private function fixImageUrl(string $url): string
    {
        if (empty($url)) {
            return $url;
        }

        // Replace domain and remove category subfolder
        if (preg_match('#/articles/\w+/(\w+)/(\w+\.jpg)$#i', $url, $m)) {
            return "https://www.valento.es/images_valento/articles/{$m[1]}/{$m[2]}";
        }

        return $url;
    }

    /**
     * Get the best main image for a product: prefer _presentacion.jpg, fallback to color image.
     */
    private function getMainImage(string $modelRef, string $colorImageUrl): string
    {
        if (empty($colorImageUrl)) {
            return '';
        }

        // Extract model ref folder from the color image URL
        if (preg_match('#/articles/(\w+)/#', $colorImageUrl, $m)) {
            $folder = $m[1];
            $presentacionUrl = "https://www.valento.es/images_valento/articles/{$folder}/{$folder}_presentacion.jpg";

            // Check if presentacion image exists (with a short timeout)
            $headers = @get_headers($presentacionUrl, true);
            if ($headers && str_contains($headers[0], '200')) {
                return $presentacionUrl;
            }

            // Fallback: try {MODEL_REF}.jpg (without color suffix)
            $directUrl = "https://www.valento.es/images_valento/articles/{$folder}/{$folder}.jpg";
            $headers = @get_headers($directUrl, true);
            if ($headers && str_contains($headers[0], '200')) {
                return $directUrl;
            }
        }

        return $colorImageUrl;
    }

    private function guessHexCode(string $colorName): ?string
    {
        static $map = null;
        if ($map === null) {
            $map = [
                'blanc' => '#FFFFFF', 'blanc chiné' => '#F5F5F0', 'blanc crème' => '#FFFDD0',
                'écru naturel' => '#F5F0E0', 'écru' => '#F5F0E0',
                'noir' => '#000000',
                'gris charbon' => '#4A4A4A', 'gris vigore' => '#808080', 'gris vigore foncé' => '#5C5C5C',
                'gris chiné' => '#A0A0A0', 'gris perle' => '#C0C0C0', 'gris clair' => '#D3D3D3',
                'gris ciment' => '#A5A5A5', 'gris fumée' => '#6E6E6E',
                'argent' => '#C0C0C0', 'platine' => '#E5E4E2', 'anthracite chiné' => '#383838',
                'bleu ciel' => '#87CEEB', 'bleu royal' => '#4169E1',
                'bleu marine orion' => '#1B1F3B', 'bleu marine nuit' => '#191970',
                'bleu marine océan' => '#003153', 'bleu marine' => '#000080',
                'bleu tropical' => '#0099CC', 'bleu ville' => '#336699', 'bleu jean' => '#5B7DB1',
                'bleu électrique' => '#0066FF', 'bleu denim' => '#1560BD', 'bleu turquoise' => '#40E0D0',
                'bleu pétrole' => '#004953', 'bleu cobalt' => '#0047AB', 'bleu azur' => '#007FFF',
                'bleu dauphin' => '#007FBB', 'bleu hôtesse' => '#4472C4', 'bleu acier' => '#4682B4',
                'bleu chiné' => '#6699CC', 'bleu france' => '#0072BB', 'bleu indigo' => '#4B0082',
                'rouge lotus' => '#CC0000', 'rouge' => '#FF0000', 'rouge cerise' => '#DE3163',
                'rouge carmin' => '#960018', 'rouge vermillon' => '#E34234',
                'rose' => '#FF69B4', 'rose bonbon' => '#FF1493', 'rose pâle' => '#FFB6C1',
                'rose fuchsia' => '#FF00FF', 'framboise' => '#E30B5D', 'corail' => '#FF7F50',
                'saumon' => '#FA8072',
                'orange fête' => '#FF6600', 'orange' => '#FF8C00', 'orange fluo' => '#FF4500',
                'mandarine' => '#FF8800', 'pêche' => '#FFCBA4',
                'jaune citron' => '#FFF44F', 'jaune' => '#FFD700', 'jaune d\'or' => '#FFD700',
                'jaune fluo' => '#CCFF00', 'jaune miel' => '#EB9605', 'jaune soleil' => '#FFD300',
                'moutarde' => '#E1AD01',
                'vert kelly' => '#4CBB17', 'vert pomme' => '#8DB600', 'vert prairie' => '#57A639',
                'vert bouteille' => '#006A4E', 'vert forêt' => '#228B22', 'vert sapin' => '#01796F',
                'vert fluo' => '#39FF14', 'vert menthe' => '#98FF98', 'vert olive' => '#808000',
                'vert militaire' => '#4B5320', 'vert émeraude' => '#50C878', 'vert amazone' => '#3B7A57',
                'vert chirurgien' => '#009B76', 'vert pistache' => '#93C572', 'vert printemps' => '#00FF7F',
                'kaki' => '#6B6B47',
                'beige sable' => '#D2B48C', 'beige' => '#F5F5DC', 'camel' => '#C19A6B',
                'chocolat' => '#7B3F00', 'marron' => '#8B4513', 'marron tabac' => '#7C4700',
                'noisette' => '#A67B5B', 'taupe' => '#483C32', 'sable' => '#C2B280',
                'violet' => '#8B00FF', 'mauve' => '#E0B0FF', 'lavande' => '#E6E6FA',
                'lilas' => '#C8A2C8', 'prune' => '#8E4585', 'aubergine' => '#3D0C11',
                'bordeaux acajou' => '#800020', 'bordeaux' => '#800020', 'grenat' => '#6C3461',
                'transparent' => '#FFFFFF',
            ];
        }

        $name = mb_strtolower(trim($colorName));

        if (isset($map[$name])) {
            return $map[$name];
        }

        // Partial match
        foreach ($map as $key => $hex) {
            if (str_contains($name, $key) || str_contains($key, $name)) {
                return $hex;
            }
        }

        return null;
    }
}
