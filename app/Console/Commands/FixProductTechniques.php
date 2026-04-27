<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\MarkingTechnique;
use App\Models\Product;
use App\Models\ProductTechniqueRule;
use Illuminate\Console\Command;

class FixProductTechniques extends Command
{
    protected $signature = 'products:fix-techniques {--dry-run : Show what would be done without making changes}';

    protected $description = 'Add missing technique compatibility rules to products based on their category';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        $techniques = MarkingTechnique::pluck('id', 'slug')->toArray();

        $added = 0;
        $products = Product::with(['category', 'techniqueRules'])->get();

        foreach ($products as $product) {
            $catSlug = $product->category?->slug ?? '';
            $expectedSlugs = $this->getTechniques($catSlug);

            foreach ($expectedSlugs as $slug) {
                if (! isset($techniques[$slug])) {
                    continue;
                }

                $techId = $techniques[$slug];
                $existingRule = $product->techniqueRules->firstWhere('marking_technique_id', $techId);

                if (! $existingRule) {
                    if (! $dryRun) {
                        ProductTechniqueRule::create([
                            'product_id' => $product->id,
                            'marking_technique_id' => $techId,
                            'is_compatible' => true,
                            'marking_zones' => $this->getZones($catSlug),
                        ]);
                    }
                    $added++;
                    $this->line(($dryRun ? '[DRY] ' : '') . "Added {$slug} to {$product->name} ({$catSlug})");
                }
            }
        }

        $this->info(($dryRun ? '[DRY RUN] Would add' : 'Added') . " {$added} technique rules.");

        return self::SUCCESS;
    }

    /**
     * Determine which techniques a product category should support.
     * This is the corrected version using actual technique slugs.
     */
    private function getTechniques(string $slug): array
    {
        return match (true) {
            str_contains($slug, 'casquette'),
            in_array($slug, ['bonnets', 'bobs', 'chapeaux', 'chapkas', 'casquettes-anglaises-berets'])
                => ['broderie'],

            $slug === 'sacs-coton'
                => ['serigraphie', 'impression-dtg', 'transfert-offset', 'dtf'],

            str_contains($slug, 'sac'), str_contains($slug, 'sacoche'), str_contains($slug, 'valise')
                => ['transfert-offset', 'broderie'],

            in_array($slug, ['echarpes-gants', 'bandanas', 'ceintures-parapluies'])
                => ['broderie', 'serigraphie'],

            $slug === 't-shirts-polyester',
            str_contains($slug, 'chasubles'),
            str_contains($slug, 'maillots-t-shirts-sport')
                => ['serigraphie', 'transfert-offset', 'dtf', 'flocage-flex'],

            str_contains($slug, 't-shirt'), str_contains($slug, 'debardeur')
                => ['broderie', 'serigraphie', 'impression-dtg', 'transfert-offset', 'flocage-flex', 'dtf'],

            str_contains($slug, 'polo')
                => ['broderie', 'serigraphie', 'transfert-offset'],

            str_contains($slug, 'sweat')
                => ['broderie', 'serigraphie', 'impression-dtg', 'transfert-offset', 'dtf'],

            str_contains($slug, 'chemise')
                => ['broderie', 'serigraphie', 'transfert-offset'],

            str_contains($slug, 'pull'), str_contains($slug, 'polaire'),
            str_contains($slug, 'gilet'), str_contains($slug, 'softshell'),
            str_contains($slug, 'coupe-vent'), str_contains($slug, 'doudoune'),
            str_contains($slug, 'parka'), str_contains($slug, 'veste'),
            str_contains($slug, 'manteau'), str_contains($slug, 'teddy'),
            str_contains($slug, 'bodywarmer')
                => ['broderie', 'transfert-offset'],

            str_contains($slug, 'tablier')
                => ['broderie', 'serigraphie', 'transfert-offset'],

            str_contains($slug, 'serviette'), str_contains($slug, 'nappe'),
            str_contains($slug, 'couverture'), str_contains($slug, 'taie')
                => ['broderie', 'serigraphie'],

            str_contains($slug, 'pantalon'), str_contains($slug, 'short'),
            str_contains($slug, 'jupe'), str_contains($slug, 'jogging')
                => ['broderie', 'serigraphie', 'transfert-offset'],

            str_contains($slug, 'body'), str_contains($slug, 'bebe'),
            str_contains($slug, 'pyjama'), str_contains($slug, 'bavoir')
                => ['broderie', 'serigraphie', 'impression-dtg', 'transfert-offset'],

            default => ['broderie', 'serigraphie', 'transfert-offset'],
        };
    }

    private function getZones(string $slug): array
    {
        return match (true) {
            str_contains($slug, 't-shirt'), str_contains($slug, 'debardeur'),
            str_contains($slug, 'polo'), str_contains($slug, 'chemise'),
            str_contains($slug, 'maillot'), str_contains($slug, 'body')
                => ['poitrine_gauche', 'poitrine_droite', 'dos', 'manche_gauche', 'manche_droite'],

            str_contains($slug, 'sweat'), str_contains($slug, 'pull'), str_contains($slug, 'polaire')
                => ['poitrine_gauche', 'dos', 'manche_gauche', 'manche_droite'],

            str_contains($slug, 'casquette'),
            in_array($slug, ['bonnets', 'bobs', 'chapeaux', 'chapkas', 'casquettes-anglaises-berets'])
                => ['face', 'cote'],

            str_contains($slug, 'sac'), str_contains($slug, 'sacoche'),
            str_contains($slug, 'valise')
                => ['face', 'dos'],

            str_contains($slug, 'tablier')
                => ['poitrine', 'poche'],

            default => ['poitrine_gauche', 'dos'],
        };
    }
}
