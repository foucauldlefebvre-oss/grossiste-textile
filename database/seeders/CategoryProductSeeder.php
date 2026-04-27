<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\MarkingTechnique;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CategoryProductSeeder extends Seeder
{
    private array $categories;
    private array $techniques;

    /**
     * Seed ~870 produits réels issus de marquage-textile.fr
     * Les données produit sont dans database/seeders/data/products.php
     */
    public function run(): void
    {
        $this->categories = Category::pluck('id', 'slug')->toArray();
        $this->techniques = MarkingTechnique::pluck('id', 'slug')->toArray();

        $allProducts = require __DIR__ . '/data/products.php';

        DB::transaction(function () use ($allProducts) {
            $count = 0;
            foreach ($allProducts as $categorySlug => $items) {
                $catId = $this->categories[$categorySlug] ?? null;
                if (! $catId) {
                    $this->command?->warn("Catégorie introuvable : {$categorySlug}");
                    continue;
                }
                foreach ($items as $p) {
                    $this->seedProduct($catId, $categorySlug, $p);
                    $count++;
                }
            }
            $this->command?->info("{$count} produits créés.");
        });
    }

    private function seedProduct(int $catId, string $catSlug, array $p): void
    {
        [$name, $ref, $supplier, $material, $grammage, $cut, $price] = $p;

        $slug = Str::slug($name);
        if (Product::where('slug', $slug)->exists()) {
            $slug .= '-' . Str::slug($ref ?: Str::random(4));
        }

        $reference = $ref ?: strtoupper(Str::random(6));
        if (Product::where('reference', $reference)->exists()) {
            $reference .= '-' . strtoupper(Str::random(3));
        }

        $product = Product::create([
            'category_id' => $catId,
            'name' => $name,
            'slug' => $slug,
            'reference' => $reference,
            'supplier' => $supplier ?: null,
            'material' => $material,
            'grammage' => $grammage ?: null,
            'cut' => $cut,
            'base_price' => $price,
            'weight' => $grammage ? round($grammage * 0.003, 2) : 0.20,
            'is_active' => true,
            'short_description' => "{$name} — {$material}",
        ]);

        // Palette de couleurs déterministe par produit (4-8 couleurs)
        $palette = $this->getColorPalette($catSlug);
        $hash = crc32($name . $ref);
        $n = min(($hash % 5) + 4, count($palette));
        mt_srand($hash);
        $shuffled = $palette;
        shuffle($shuffled);
        mt_srand();
        $colors = array_slice($shuffled, 0, $n);

        $sizes = $this->getSizes($cut, $catSlug);

        foreach ($colors as $i => [$colorName, $hex]) {
            $color = $product->colors()->create([
                'name' => $colorName,
                'hex_code' => $hex,
                'sort_order' => $i,
                'is_active' => true,
                'stock' => rand(50, 500),
            ]);

            foreach ($sizes as $size) {
                $color->sizes()->create([
                    'size' => $size,
                    'stock' => rand(10, 100),
                    'is_available' => true,
                ]);
            }
        }

        foreach ($this->getTechniques($catSlug) as $techSlug) {
            if (isset($this->techniques[$techSlug])) {
                $product->techniqueRules()->create([
                    'marking_technique_id' => $this->techniques[$techSlug],
                    'is_compatible' => true,
                    'marking_zones' => $this->getZones($catSlug),
                ]);
            }
        }
    }

    private function getSizes(string $cut, string $catSlug): array
    {
        $noSize = [
            'casquettes-classiques', 'casquettes-snapback', 'casquettes-anglaises-berets',
            'chapeaux', 'bobs', 'bonnets', 'chapkas',
            'sacs-coton', 'sacoches-polyester', 'sacs-a-dos', 'sacs-polyester',
            'valises', 'portefeuilles-trousses',
            'echarpes-gants', 'bandanas', 'ceintures-parapluies',
            'couvertures', 'chiffons', 'paniers-rangement', 'taies-oreiller',
            'tabliers', 'nappes-serviettes', 'serviettes-bain-peignoirs',
            'accessoires-pro', 'accessoires-sport', 'accessoires-bebe', 'bonnets-bavoirs-bebe',
            'chasubles-dossards', 'slips-calecons', 'chaussettes', 'maillots-de-bain',
        ];
        if (in_array($catSlug, $noSize)) {
            return ['TU'];
        }

        return match ($cut) {
            'bebe' => ['3M', '6M', '12M', '18M', '24M'],
            'enfant' => ['2ans', '4ans', '6ans', '8ans', '10ans', '12ans', '14ans'],
            default => ['XS', 'S', 'M', 'L', 'XL', '2XL', '3XL'],
        };
    }

    private function getColorPalette(string $slug): array
    {
        if (str_contains($slug, 'bebe') || str_contains($slug, 'bodies') || str_contains($slug, 'pyjama') || str_contains($slug, 'bavoir')) {
            return [
                ['Blanc', '#FFFFFF'], ['Rose', '#FFC0CB'], ['Bleu ciel', '#87CEEB'],
                ['Jaune', '#EAB308'], ['Vert pomme', '#76FF03'], ['Naturel', '#F5F0E1'],
                ['Gris chiné', '#9CA3AF'], ['Rouge', '#DC2626'],
            ];
        }
        if (str_contains($slug, 'haute-visibilite')) {
            return [
                ['Jaune HV', '#FFFF00'], ['Orange HV', '#FF6600'], ['Rouge', '#DC2626'],
                ['Noir', '#000000'], ['Bleu Marine', '#1B2A4A'],
            ];
        }
        if (str_contains($slug, 'pro') || str_contains($slug, 'travail') || str_contains($slug, 'blouse') || str_contains($slug, 'combinaison') || str_contains($slug, 'medical') || str_contains($slug, 'hotellerie') || str_contains($slug, 'ignifuge')) {
            return [
                ['Noir', '#000000'], ['Bleu Marine', '#1B2A4A'], ['Blanc', '#FFFFFF'],
                ['Gris', '#6B7280'], ['Bleu Royal', '#1E40AF'], ['Rouge', '#DC2626'],
                ['Vert bouteille', '#006400'], ['Beige', '#D2B48C'],
            ];
        }
        if ($slug === 't-shirts-polyester' || str_contains($slug, 'sport') || str_contains($slug, 'chasubles') || str_contains($slug, 'maillots')) {
            return [
                ['Blanc', '#FFFFFF'], ['Noir', '#000000'], ['Bleu Royal', '#1E40AF'],
                ['Bleu Marine', '#1B2A4A'], ['Rouge', '#DC2626'], ['Jaune fluo', '#CCFF00'],
                ['Orange', '#EA580C'], ['Vert pomme', '#76FF03'], ['Turquoise', '#40E0D0'],
                ['Rose', '#EC4899'], ['Gris', '#6B7280'], ['Bleu ciel', '#87CEEB'],
            ];
        }
        if (str_contains($slug, 'sac') || str_contains($slug, 'sacoche') || str_contains($slug, 'valise') || str_contains($slug, 'portefeuille')) {
            return [
                ['Noir', '#000000'], ['Bleu Marine', '#1B2A4A'], ['Naturel', '#F5F0E1'],
                ['Rouge', '#DC2626'], ['Bleu Royal', '#1E40AF'], ['Vert', '#16A34A'],
                ['Gris', '#6B7280'], ['Orange', '#EA580C'],
            ];
        }

        // Palette standard textile (coton)
        return [
            ['Blanc', '#FFFFFF'], ['Noir', '#000000'], ['Gris chiné', '#9CA3AF'],
            ['Bleu Marine', '#1B2A4A'], ['Bleu Royal', '#1E40AF'], ['Bleu ciel', '#87CEEB'],
            ['Rouge', '#DC2626'], ['Burgundy', '#800020'], ['Orange', '#EA580C'],
            ['Jaune', '#EAB308'], ['Vert bouteille', '#006400'], ['Vert pomme', '#76FF03'],
            ['Turquoise', '#40E0D0'], ['Rose', '#EC4899'], ['Violet', '#7C3AED'],
            ['Kaki', '#6B7040'], ['Sable', '#C2B280'], ['Chocolat', '#7B3F00'],
        ];
    }

    private function getTechniques(string $slug): array
    {
        return match (true) {
            str_contains($slug, 'casquette'), in_array($slug, ['bonnets', 'bobs', 'chapeaux', 'chapkas', 'casquettes-anglaises-berets']) => ['broderie'],
            $slug === 'sacs-coton' => ['serigraphie', 'impression-dtg', 'transfert-offset', 'dtf'],
            str_contains($slug, 'sac'), str_contains($slug, 'sacoche'), str_contains($slug, 'valise') => ['transfert-offset', 'broderie'],
            in_array($slug, ['echarpes-gants', 'bandanas', 'ceintures-parapluies']) => ['broderie', 'serigraphie'],
            $slug === 't-shirts-polyester', str_contains($slug, 'chasubles'), str_contains($slug, 'maillots-t-shirts-sport') => ['serigraphie', 'transfert-offset', 'dtf', 'flocage-flex'],
            str_contains($slug, 't-shirt'), str_contains($slug, 'debardeur') => ['broderie', 'serigraphie', 'impression-dtg', 'transfert-offset', 'flocage-flex', 'dtf'],
            str_contains($slug, 'polo') => ['broderie', 'serigraphie', 'transfert-offset'],
            str_contains($slug, 'sweat') => ['broderie', 'serigraphie', 'impression-dtg', 'transfert-offset', 'dtf'],
            str_contains($slug, 'chemise') => ['broderie', 'serigraphie', 'transfert-offset'],
            str_contains($slug, 'pull'), str_contains($slug, 'polaire'), str_contains($slug, 'gilet'), str_contains($slug, 'softshell'), str_contains($slug, 'coupe-vent'), str_contains($slug, 'doudoune'), str_contains($slug, 'parka'), str_contains($slug, 'veste'), str_contains($slug, 'manteau'), str_contains($slug, 'teddy'), str_contains($slug, 'bodywarmer') => ['broderie', 'transfert-offset'],
            str_contains($slug, 'tablier') => ['broderie', 'serigraphie', 'transfert-offset'],
            str_contains($slug, 'serviette'), str_contains($slug, 'nappe'), str_contains($slug, 'couverture'), str_contains($slug, 'taie') => ['broderie', 'serigraphie'],
            str_contains($slug, 'pantalon'), str_contains($slug, 'short'), str_contains($slug, 'jupe'), str_contains($slug, 'jogging') => ['broderie', 'serigraphie'],
            str_contains($slug, 'body'), str_contains($slug, 'bebe'), str_contains($slug, 'pyjama'), str_contains($slug, 'bavoir') => ['broderie', 'serigraphie', 'impression-dtg', 'transfert-offset'],
            default => ['broderie', 'serigraphie', 'transfert-offset'],
        };
    }

    private function getZones(string $slug): array
    {
        return match (true) {
            str_contains($slug, 't-shirt'), str_contains($slug, 'debardeur'), str_contains($slug, 'polo'), str_contains($slug, 'chemise'), str_contains($slug, 'maillot'), str_contains($slug, 'body') => ['poitrine_gauche', 'poitrine_droite', 'dos', 'manche_gauche', 'manche_droite'],
            str_contains($slug, 'sweat'), str_contains($slug, 'pull'), str_contains($slug, 'polaire') => ['poitrine_gauche', 'dos', 'manche_gauche', 'manche_droite'],
            str_contains($slug, 'casquette'), in_array($slug, ['bonnets', 'bobs', 'chapeaux', 'chapkas', 'casquettes-anglaises-berets']) => ['face', 'cote'],
            str_contains($slug, 'sac'), str_contains($slug, 'sacoche'), str_contains($slug, 'portefeuille'), str_contains($slug, 'valise') => ['face', 'dos'],
            str_contains($slug, 'tablier') => ['poitrine', 'poche'],
            str_contains($slug, 'serviette'), str_contains($slug, 'nappe'), str_contains($slug, 'couverture'), str_contains($slug, 'taie') => ['centre', 'coin'],
            str_contains($slug, 'echarpe'), str_contains($slug, 'bandana'), str_contains($slug, 'ceinture') => ['centre'],
            str_contains($slug, 'pantalon'), str_contains($slug, 'short'), str_contains($slug, 'jupe'), str_contains($slug, 'jogging') => ['cuisse_gauche', 'cuisse_droite'],
            default => ['poitrine_gauche', 'dos'],
        };
    }
}
