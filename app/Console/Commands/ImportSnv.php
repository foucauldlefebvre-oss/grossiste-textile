<?php

namespace App\Console\Commands;

use App\Helpers\ColorHelper;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductColor;
use App\Models\ProductSize;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportSnv extends Command
{
    protected $signature = 'import:snv
        {--dry-run : Montre ce qui serait fait sans modifier}
        {--limit=0 : Limiter le nombre de produits}
        {--dump-structure : Afficher la structure du fichier et stopper}';

    protected $description = 'Import SNV product catalog from Excel + local photos';

    private const DEFAULT_STOCK = 1000;

    /**
     * Mapping categorie SNV → ID categorie en BDD.
     */
    private const CATEGORY_MAP = [
        // blouses, tuniques, chasubles
        'blouses' => 79,       // Blouses et vestes
        'tuniques' => 215,     // Tuniques
        'chasubles' => 89,     // Chasubles et dossards

        // workwear
        'pantalon workwear' => 78,      // Pantalons de travail
        'veste workwear' => 79,         // Blouses et vestes
        'combinaison workwear' => 80,   // Combinaisons

        // cuisine, hotellerie et restauration
        'veste de cuisines manches longues' => 86,  // Hotellerie-restauration
        'veste de cuisines manches courtes' => 86,
        'tabliers' => 117,              // Tabliers
        'vetements de service' => 86,
        'accessoires' => 86,

        // pantalons
        'pantalon taille élastiquées' => 78,
        'pantalon braguette' => 78,

        // chaussures
        'suecos' => 216,       // Chaussures de travail
        'sabot' => 216,
        'sabots sécurité' => 216,
        'mocassin' => 216,

        // agro
        'blouses agro' => 217,        // Agroalimentaire
        'combinaison agro' => 217,
        'veste' => 217,

        // polos
        'polos et gilets magasin' => 77, // Polos pro
    ];

    /**
     * Fallback par categorie principale.
     */
    private const CATEGORY_FALLBACK = [
        'blouses, tuniques, chasubles' => 79,
        'workwear' => 78,
        'cuisine, hotelerie et restauration' => 86,
        'pantalons' => 78,
        'chaussures' => 216,
        'agro' => 217,
        'polos et gilets magasin' => 77,
    ];

    private string $photoBasePath = '';
    private array $photoDirMap = [];

    private array $stats = [
        'products_created' => 0,
        'products_updated' => 0,
        'colors_created' => 0,
        'sizes_created' => 0,
        'photos_linked' => 0,
        'skipped' => 0,
        'errors' => 0,
    ];

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        $limit = (int) $this->option('limit');

        $excelPath = storage_path('app/public/import-snv/TOUTES INFOS PRODUITS.xlsx');
        $this->photoBasePath = storage_path('app/public/import-snv/photo snv');

        if (! file_exists($excelPath)) {
            $this->error("Fichier Excel non trouve : {$excelPath}");
            return self::FAILURE;
        }

        // Index des dossiers photos (insensible a la casse)
        $this->buildPhotoDirMap();
        $this->info(count($this->photoDirMap) . ' dossiers photos indexes.');

        // Lire le fichier Excel
        $this->info('Lecture du fichier Excel...');
        $reader = IOFactory::createReaderForFile($excelPath);
        $reader->setReadDataOnly(true);
        $sheet = $reader->load($excelPath)->getActiveSheet();
        $totalRows = $sheet->getHighestRow();
        $this->info(($totalRows - 1) . ' lignes trouvees.');

        if ($this->option('dump-structure')) {
            $this->dumpStructure($sheet);
            return self::SUCCESS;
        }

        // Grouper par reference (= modele + couleur)
        $groups = $this->groupByModel($sheet, $totalRows);
        $this->info(count($groups) . ' modeles uniques trouves.');

        if ($dryRun) {
            $this->info('=== DRY RUN ===');
        }

        $bar = $this->output->createProgressBar(count($groups));
        $bar->start();

        $count = 0;
        foreach ($groups as $modelKey => $data) {
            $bar->advance();

            if ($limit > 0 && $count >= $limit) {
                break;
            }

            try {
                $this->importModel($data, $dryRun);
            } catch (\Throwable $e) {
                $this->stats['errors']++;
                $this->newLine();
                $this->error("Erreur {$modelKey}: {$e->getMessage()}");
            }

            $count++;
        }

        $bar->finish();
        $this->newLine(2);
        $this->displaySummary();

        return self::SUCCESS;
    }

    private function buildPhotoDirMap(): void
    {
        if (! is_dir($this->photoBasePath)) {
            return;
        }

        $dirs = scandir($this->photoBasePath);
        foreach ($dirs as $dir) {
            if ($dir === '.' || $dir === '..') {
                continue;
            }
            if (is_dir($this->photoBasePath . '/' . $dir)) {
                $this->photoDirMap[Str::lower($dir)] = $dir;
            }
        }
    }

    private function groupByModel($sheet, int $totalRows): array
    {
        $groups = [];

        for ($row = 2; $row <= $totalRows; $row++) {
            $ref = trim($sheet->getCell('A' . $row)->getValue() ?? '');
            if (! $ref) {
                continue;
            }

            $model = trim($sheet->getCell('C' . $row)->getValue() ?? '');
            $color = trim($sheet->getCell('D' . $row)->getValue() ?? '');
            $size = trim($sheet->getCell('E' . $row)->getValue() ?? '');

            // Cle = modele (pas reference car ref inclut la couleur)
            $modelKey = $model ?: $ref;

            if (! isset($groups[$modelKey])) {
                $groups[$modelKey] = [
                    'ref' => $ref,
                    'model' => $model,
                    'designation' => trim($sheet->getCell('B' . $row)->getValue() ?? ''),
                    'composition' => trim($sheet->getCell('F' . $row)->getValue() ?? ''),
                    'grammage' => trim($sheet->getCell('G' . $row)->getValue() ?? ''),
                    'description' => trim($sheet->getCell('K' . $row)->getValue() ?? ''),
                    'category' => trim($sheet->getCell('P' . $row)->getValue() ?? ''),
                    'subcategory' => trim($sheet->getCell('Q' . $row)->getValue() ?? ''),
                    'origin' => trim($sheet->getCell('O' . $row)->getValue() ?? ''),
                    'colors' => [],
                ];
            }

            $colorKey = Str::lower($color ?: 'default');
            if (! isset($groups[$modelKey]['colors'][$colorKey])) {
                $groups[$modelKey]['colors'][$colorKey] = [
                    'name' => $color ?: 'Standard',
                    'sizes' => [],
                ];
            }

            $groups[$modelKey]['colors'][$colorKey]['sizes'][] = [
                'size' => $size,
                'ean' => trim($sheet->getCell('L' . $row)->getValue() ?? ''),
                'weight' => $sheet->getCell('M' . $row)->getValue(),
                'ref' => $ref,
            ];
        }

        return $groups;
    }

    private function importModel(array $data, bool $dryRun): void
    {
        $modelName = $data['model'] ?: $data['designation'];
        $slug = Str::slug($modelName . ' snv');
        $categoryId = $this->resolveCategory($data['category'], $data['subcategory']);

        // Extraire le nom du modele sans le grammage et la couleur
        $productName = $this->cleanProductName($modelName);

        // Trouver la photo principale
        $mainImage = $this->findMainPhoto($productName);

        if ($dryRun) {
            $this->stats['products_created']++;
            return;
        }

        $product = Product::updateOrCreate(
            ['reference' => $data['ref'], 'supplier' => 'SNV'],
            [
                'name' => $productName,
                'slug' => $slug,
                'category_id' => $categoryId,
                'description' => nl2br(e($data['description'])),
                'short_description' => Str::limit($data['designation'], 150),
                'material' => $data['composition'],
                'grammage' => is_numeric($data['grammage']) ? $data['grammage'] : null,
                'main_image' => $mainImage,
                'is_active' => true,
                'meta_title' => $productName . ' - SNV',
            ]
        );

        if ($product->wasRecentlyCreated) {
            $this->stats['products_created']++;
        } else {
            $this->stats['products_updated']++;
        }

        // Couleurs et tailles
        $sortOrder = 0;
        foreach ($data['colors'] as $colorKey => $colorData) {
            $colorImage = $this->findColorPhoto($productName, $colorData['name']);
            $hexCode = ColorHelper::resolve($colorData['name'], null);

            $color = ProductColor::updateOrCreate(
                ['product_id' => $product->id, 'name' => $colorData['name']],
                [
                    'hex_code' => $hexCode,
                    'image' => $colorImage,
                    'sort_order' => $sortOrder++,
                    'is_active' => true,
                    'stock' => self::DEFAULT_STOCK,
                ]
            );
            $this->stats['colors_created']++;

            foreach ($colorData['sizes'] as $sizeData) {
                ProductSize::updateOrCreate(
                    ['product_color_id' => $color->id, 'size' => $sizeData['size']],
                    [
                        'sku' => $sizeData['ref'] . '-' . Str::slug($sizeData['size']),
                        'ean' => $sizeData['ean'],
                        'stock' => self::DEFAULT_STOCK,
                        'is_available' => true,
                    ]
                );
                $this->stats['sizes_created']++;
            }
        }
    }

    private function resolveCategory(string $category, string $subcategory): int
    {
        $subLower = Str::lower($subcategory);
        $catLower = Str::lower($category);

        // Match sous-categorie d'abord
        if ($subcategory && isset(self::CATEGORY_MAP[$subLower])) {
            return self::CATEGORY_MAP[$subLower];
        }

        // Fallback categorie principale
        if (isset(self::CATEGORY_FALLBACK[$catLower])) {
            return self::CATEGORY_FALLBACK[$catLower];
        }

        // Default : Vetements de travail
        return 75;
    }

    private function cleanProductName(string $raw): string
    {
        // Retirer grammage et poids du nom
        $name = preg_replace('/\s+\d+\s*[Gg]($|\s)/', ' ', $raw);
        // Retirer PC, PES, etc. en fin de nom
        $name = preg_replace('/\s+(PC|PES|PET)\s+\d+/', '', $name);
        return trim($name);
    }

    private function findMainPhoto(string $productName): ?string
    {
        $dirKey = $this->findPhotoDir($productName);
        if (! $dirKey) {
            return null;
        }

        $dir = $this->photoBasePath . '/' . $this->photoDirMap[$dirKey];
        $files = scandir($dir);

        // Chercher une photo face (pas dos, pas zoom, pas ambiance)
        foreach ($files as $file) {
            if (! $this->isImage($file)) {
                continue;
            }
            $lower = Str::lower($file);
            if (Str::contains($lower, ['dos', 'back', 'zoom', 'ambiance', 'detail'])) {
                continue;
            }
            // Copier dans storage public
            return $this->copyPhoto($dir . '/' . $file, $productName, $file);
        }

        // Fallback : prendre la premiere image
        foreach ($files as $file) {
            if ($this->isImage($file)) {
                return $this->copyPhoto($dir . '/' . $file, $productName, $file);
            }
        }

        return null;
    }

    private function findColorPhoto(string $productName, string $colorName): ?string
    {
        $dirKey = $this->findPhotoDir($productName);
        if (! $dirKey) {
            return null;
        }

        $dir = $this->photoBasePath . '/' . $this->photoDirMap[$dirKey];
        $files = scandir($dir);
        $colorLower = Str::lower($colorName);

        // Chercher une photo contenant le nom de la couleur
        foreach ($files as $file) {
            if (! $this->isImage($file)) {
                continue;
            }
            $fileLower = Str::lower($file);
            if (Str::contains($fileLower, $colorLower) && ! Str::contains($fileLower, ['dos', 'back', 'zoom'])) {
                return $this->copyPhoto($dir . '/' . $file, $productName, $file);
            }
        }

        return null;
    }

    private function findPhotoDir(string $productName): ?string
    {
        // Extraire le nom du modele (premier mot significatif)
        $name = preg_replace('/^(PANTALON|BLOUSE|TUNIQUE|VESTE|COMBINAISON|TABLIER|POLO|CHASUBLE|SABOT)\s+/i', '', $productName);
        $name = explode(' ', trim($name))[0]; // Premier mot
        $key = Str::lower($name);

        if (isset($this->photoDirMap[$key])) {
            return $key;
        }

        // Essayer avec 2 mots
        $words = explode(' ', trim(Str::lower($productName)));
        foreach ($words as $word) {
            $word = trim($word);
            if (strlen($word) >= 3 && isset($this->photoDirMap[$word])) {
                return $word;
            }
        }

        // Match partiel
        foreach (array_keys($this->photoDirMap) as $dirKey) {
            if (Str::contains($key, $dirKey) || Str::contains($dirKey, $key)) {
                return $dirKey;
            }
        }

        return null;
    }

    private function copyPhoto(string $sourcePath, string $productName, string $filename): string
    {
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        $slug = Str::slug($productName);
        $destDir = 'products/snv/' . $slug;
        $destFile = $destDir . '/' . Str::slug(pathinfo($filename, PATHINFO_FILENAME)) . '.' . $ext;

        if (! Storage::disk('public')->exists($destFile)) {
            Storage::disk('public')->makeDirectory($destDir);
            copy($sourcePath, Storage::disk('public')->path($destFile));
            $this->stats['photos_linked']++;
        }

        return $destFile;
    }

    private function isImage(string $filename): bool
    {
        return preg_match('/\.(jpg|jpeg|png|webp)$/i', $filename);
    }

    private function dumpStructure($sheet): void
    {
        $this->info('Headers:');
        for ($col = 'A'; $col <= 'S'; $col++) {
            $val = $sheet->getCell($col . '1')->getValue();
            if ($val) {
                $this->line("  {$col}: {$val}");
            }
        }

        $this->newLine();
        $this->info('Sample (row 2):');
        for ($col = 'A'; $col <= 'S'; $col++) {
            $header = $sheet->getCell($col . '1')->getValue();
            $val = $sheet->getCell($col . '2')->getValue();
            if ($val !== null) {
                $this->line("  {$col} ({$header}): " . Str::limit($val, 80));
            }
        }
    }

    private function displaySummary(): void
    {
        $this->info('=== Resume import SNV ===');
        $this->table(
            ['Metrique', 'Total'],
            collect($this->stats)
                ->filter(fn ($val) => $val > 0)
                ->map(fn ($val, $key) => [str_replace('_', ' ', ucfirst($key)), $val])
                ->values()
                ->toArray()
        );
    }
}
