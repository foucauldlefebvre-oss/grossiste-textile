<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Product;
use App\Services\SoloApiService;
use Illuminate\Console\Command;

class FixSoloCategories extends Command
{
    protected $signature = 'solo:fix-categories
        {--dry-run : Show changes without applying them}';

    protected $description = 'Re-categorize Sol\'s products using the improved mapping logic and API ProductCategory data';

    private array $categoryCache = [];
    private array $stats = ['updated' => 0, 'unchanged' => 0, 'errors' => 0];

    public function handle(SoloApiService $api): int
    {
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->info('=== DRY RUN ===');
        }

        $this->categoryCache = Category::pluck('id', 'slug')->toArray();

        // Fetch catalogue from API to get ProductCategory per model
        $this->info('Fetching Sol\'s catalogue from API...');
        try {
            $data = $api->getCatalogue();
            $models = $this->extractItems($data);
        } catch (\Throwable $e) {
            $this->error("API error: {$e->getMessage()}");

            return self::FAILURE;
        }

        $this->info(count($models) . ' models fetched.');

        // Index by model code (Sku field)
        $modelMap = [];
        foreach ($models as $m) {
            $sku = $m['Sku'] ?? null;
            if ($sku) {
                $modelMap[$sku] = $m;
            }
        }

        // Process each Sol's product
        $suppliers = ["Sol's", 'NEOBLU', 'ATF', 'RTP Apparel', 'Solo'];
        $products = Product::whereIn('supplier', $suppliers)->get();
        $this->info($products->count() . ' products to check.');

        $changes = [];
        foreach ($products as $product) {
            $model = $modelMap[$product->reference] ?? null;
            if (! $model) {
                continue;
            }

            $soloCategory = $model['ProductCategory'] ?? '';
            $newSlug = $this->mapCategory($soloCategory, $model);
            $newCategoryId = $this->categoryCache[$newSlug] ?? null;

            if (! $newCategoryId) {
                $this->stats['errors']++;
                $this->warn("Slug '{$newSlug}' not found in categories for {$product->reference}");

                continue;
            }

            if ($product->category_id === $newCategoryId) {
                $this->stats['unchanged']++;

                continue;
            }

            $oldCat = $product->category ? $product->category->name : 'NULL';
            $newCat = Category::find($newCategoryId)->name;

            $changes[] = [
                $product->reference,
                $product->name,
                $model['ShortDescription'] ?? '',
                $soloCategory,
                $oldCat,
                $newCat,
            ];

            if (! $dryRun) {
                $product->update(['category_id' => $newCategoryId]);
            }
            $this->stats['updated']++;
        }

        if (count($changes)) {
            $this->table(
                ['Ref', 'Nom', 'Description', 'API Cat', 'Avant', 'Après'],
                $changes
            );
        }

        $this->newLine();
        $this->info("Updated: {$this->stats['updated']}");
        $this->info("Unchanged: {$this->stats['unchanged']}");
        if ($this->stats['errors']) {
            $this->warn("Errors: {$this->stats['errors']}");
        }

        if ($dryRun) {
            $this->warn('Dry run — no changes applied.');
        }

        return self::SUCCESS;
    }

    private function extractItems(array $data): array
    {
        foreach (['data', 'items', 'products', 'results'] as $key) {
            if (isset($data[$key]) && is_array($data[$key])) {
                return $data[$key];
            }
        }

        return isset($data[0]) ? $data : $data;
    }

    /**
     * Same mapping logic as ImportSolo — kept in sync.
     */
    private function mapCategory(string $soloCategory, array $model = []): string
    {
        $cat = strtoupper(trim($soloCategory));
        $desc = mb_strtolower($model['ShortDescription'] ?? '');
        $name = mb_strtolower($model['ProductName'] ?? '');
        $quality = mb_strtolower($model['Quality'] ?? '');
        $genderAttrs = array_filter($model['caracteristiques'] ?? [], fn ($a) => ($a['Attributes'] ?? '') === 'Gender');
        $gender = array_map(fn ($a) => strtolower($a['MultivalueValue'] ?? ''), $genderAttrs);
        $isBaby = in_array('bébé', $gender) || str_contains($desc, 'bébé') || str_contains($desc, 'body bébé') || str_contains($name, 'bambino') || str_contains($name, 'mosquito');
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
}
