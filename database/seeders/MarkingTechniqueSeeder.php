<?php

namespace Database\Seeders;

use App\Models\MarkingTechnique;
use App\Models\TechniqueConstraint;
use App\Models\TechniquePricing;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class MarkingTechniqueSeeder extends Seeder
{
    public function run(): void
    {
        $techniques = [
            [
                'name' => 'Broderie',
                'description' => 'Marquage haut de gamme par fil brodé, rendu premium et durable.',
                'constraint' => [
                    'min_quantity' => 20,
                    'max_colors' => 6,
                    'max_visual_width' => 300,
                    'max_visual_height' => 300,
                    'production_days_min' => 5,
                    'production_days_max' => 7,
                    'incompatible_materials' => ['polyester fin', 'maille fine'],
                    'notes' => 'Incompatible avec textiles très fins',
                ],
            ],
            [
                'name' => 'Sérigraphie',
                'description' => 'Impression à plat par pochoir, idéale pour grandes séries.',
                'constraint' => [
                    'min_quantity' => 20,
                    'max_colors' => 6,
                    'max_visual_width' => 400,
                    'max_visual_height' => 500,
                    'production_days_min' => 5,
                    'production_days_max' => 7,
                    'incompatible_materials' => ['softshell', 'polaire épaisse'],
                    'notes' => 'Incompatible softshell',
                ],
            ],
            [
                'name' => 'Impression DTG',
                'description' => 'Impression numérique directe sur textile, quadrichromie sans limite de couleurs.',
                'constraint' => [
                    'min_quantity' => 10,
                    'max_colors' => null,
                    'max_visual_width' => 350,
                    'max_visual_height' => 450,
                    'production_days_min' => 3,
                    'production_days_max' => 5,
                    'compatible_materials' => ['coton', 'coton/polyester'],
                    'notes' => 'Coton ou mixte uniquement',
                ],
            ],
            [
                'name' => 'Transfert',
                'description' => 'Marquage par transfert thermique, adapté aux visuels complexes.',
                'constraint' => [
                    'min_quantity' => 50,
                    'max_colors' => null,
                    'max_visual_width' => 350,
                    'max_visual_height' => 450,
                    'production_days_min' => 5,
                    'production_days_max' => 7,
                ],
            ],
            [
                'name' => 'Flocage / Flex',
                'description' => 'Découpe vinyle thermocollé, idéal pour textes et logos simples.',
                'constraint' => [
                    'min_quantity' => 10,
                    'max_colors' => 1,
                    'max_visual_width' => 350,
                    'max_visual_height' => 450,
                    'production_days_min' => 3,
                    'production_days_max' => 5,
                ],
            ],
            [
                'name' => 'DTF',
                'description' => 'Direct To Film, transfert numérique compatible polyester.',
                'constraint' => [
                    'min_quantity' => 1,
                    'max_colors' => null,
                    'max_visual_width' => 350,
                    'max_visual_height' => 450,
                    'production_days_min' => 3,
                    'production_days_max' => 5,
                    'notes' => 'Compatible polyester',
                ],
            ],
            [
                'name' => 'Tissage',
                'description' => 'Création de badges et étiquettes tissées, rendu luxueux.',
                'constraint' => [
                    'min_quantity' => 100,
                    'max_colors' => 6,
                    'max_visual_width' => 100,
                    'max_visual_height' => 100,
                    'production_days_min' => 10,
                    'production_days_max' => 15,
                    'notes' => 'Badges et étiquettes',
                ],
            ],
            [
                'name' => 'Badge PVC',
                'description' => 'Badges en PVC souple, relief 3D, résistant à l\'eau.',
                'constraint' => [
                    'min_quantity' => 1,
                    'max_colors' => null,
                    'max_visual_width' => 100,
                    'max_visual_height' => 100,
                    'production_days_min' => 7,
                    'production_days_max' => 10,
                    'notes' => 'Accessoires',
                ],
            ],
            [
                'name' => 'Full Print',
                'description' => 'Sublimation intégrale all-over, impression bord à bord.',
                'constraint' => [
                    'min_quantity' => 1,
                    'max_colors' => null,
                    'production_days_min' => 7,
                    'production_days_max' => 10,
                    'compatible_materials' => ['polyester'],
                    'notes' => 'Sublimation totale, polyester uniquement',
                ],
            ],
        ];

        foreach ($techniques as $index => $data) {
            $technique = MarkingTechnique::create([
                'name' => $data['name'],
                'slug' => Str::slug($data['name']),
                'description' => $data['description'],
                'is_active' => true,
                'sort_order' => $index,
            ]);

            TechniqueConstraint::create(array_merge(
                ['marking_technique_id' => $technique->id],
                $data['constraint']
            ));
        }

        $this->seedPricings();
    }

    private function seedPricings(): void
    {
        $pricingGrids = [
            'broderie' => [
                ['quantity_min' => 20, 'quantity_max' => 49, 'unit_price' => 5.50, 'setup_cost' => 60, 'num_colors' => 1],
                ['quantity_min' => 50, 'quantity_max' => 99, 'unit_price' => 4.50, 'setup_cost' => 60, 'num_colors' => 1],
                ['quantity_min' => 100, 'quantity_max' => 249, 'unit_price' => 3.80, 'setup_cost' => 60, 'num_colors' => 1],
                ['quantity_min' => 250, 'quantity_max' => 499, 'unit_price' => 3.20, 'setup_cost' => 0, 'num_colors' => 1],
                ['quantity_min' => 500, 'quantity_max' => null, 'unit_price' => 2.80, 'setup_cost' => 0, 'num_colors' => 1],
            ],
            'serigraphie' => [
                ['quantity_min' => 20, 'quantity_max' => 49, 'unit_price' => 4.00, 'setup_cost' => 35, 'num_colors' => 1],
                ['quantity_min' => 50, 'quantity_max' => 99, 'unit_price' => 3.00, 'setup_cost' => 35, 'num_colors' => 1],
                ['quantity_min' => 100, 'quantity_max' => 249, 'unit_price' => 2.20, 'setup_cost' => 35, 'num_colors' => 1],
                ['quantity_min' => 250, 'quantity_max' => 499, 'unit_price' => 1.60, 'setup_cost' => 0, 'num_colors' => 1],
                ['quantity_min' => 500, 'quantity_max' => null, 'unit_price' => 1.20, 'setup_cost' => 0, 'num_colors' => 1],
                ['quantity_min' => 20, 'quantity_max' => 49, 'unit_price' => 5.50, 'setup_cost' => 70, 'num_colors' => 2],
                ['quantity_min' => 50, 'quantity_max' => 99, 'unit_price' => 4.20, 'setup_cost' => 70, 'num_colors' => 2],
                ['quantity_min' => 100, 'quantity_max' => 249, 'unit_price' => 3.20, 'setup_cost' => 70, 'num_colors' => 2],
                ['quantity_min' => 250, 'quantity_max' => null, 'unit_price' => 2.40, 'setup_cost' => 0, 'num_colors' => 2],
            ],
            'impression-dtg' => [
                ['quantity_min' => 10, 'quantity_max' => 24, 'unit_price' => 8.00, 'setup_cost' => 0, 'num_colors' => 1],
                ['quantity_min' => 25, 'quantity_max' => 49, 'unit_price' => 6.50, 'setup_cost' => 0, 'num_colors' => 1],
                ['quantity_min' => 50, 'quantity_max' => 99, 'unit_price' => 5.00, 'setup_cost' => 0, 'num_colors' => 1],
                ['quantity_min' => 100, 'quantity_max' => 249, 'unit_price' => 4.00, 'setup_cost' => 0, 'num_colors' => 1],
                ['quantity_min' => 250, 'quantity_max' => null, 'unit_price' => 3.50, 'setup_cost' => 0, 'num_colors' => 1],
            ],
            'transfert' => [
                ['quantity_min' => 50, 'quantity_max' => 99, 'unit_price' => 3.50, 'setup_cost' => 45, 'num_colors' => 1],
                ['quantity_min' => 100, 'quantity_max' => 249, 'unit_price' => 2.80, 'setup_cost' => 45, 'num_colors' => 1],
                ['quantity_min' => 250, 'quantity_max' => 499, 'unit_price' => 2.20, 'setup_cost' => 0, 'num_colors' => 1],
                ['quantity_min' => 500, 'quantity_max' => null, 'unit_price' => 1.80, 'setup_cost' => 0, 'num_colors' => 1],
            ],
            'flocage-flex' => [
                ['quantity_min' => 10, 'quantity_max' => 24, 'unit_price' => 6.00, 'setup_cost' => 25, 'num_colors' => 1],
                ['quantity_min' => 25, 'quantity_max' => 49, 'unit_price' => 5.00, 'setup_cost' => 25, 'num_colors' => 1],
                ['quantity_min' => 50, 'quantity_max' => 99, 'unit_price' => 4.00, 'setup_cost' => 0, 'num_colors' => 1],
                ['quantity_min' => 100, 'quantity_max' => null, 'unit_price' => 3.50, 'setup_cost' => 0, 'num_colors' => 1],
            ],
            'dtf' => [
                ['quantity_min' => 1, 'quantity_max' => 9, 'unit_price' => 9.00, 'setup_cost' => 0, 'num_colors' => 1],
                ['quantity_min' => 10, 'quantity_max' => 24, 'unit_price' => 7.00, 'setup_cost' => 0, 'num_colors' => 1],
                ['quantity_min' => 25, 'quantity_max' => 49, 'unit_price' => 5.50, 'setup_cost' => 0, 'num_colors' => 1],
                ['quantity_min' => 50, 'quantity_max' => 99, 'unit_price' => 4.50, 'setup_cost' => 0, 'num_colors' => 1],
                ['quantity_min' => 100, 'quantity_max' => null, 'unit_price' => 3.50, 'setup_cost' => 0, 'num_colors' => 1],
            ],
            'tissage' => [
                ['quantity_min' => 100, 'quantity_max' => 249, 'unit_price' => 3.00, 'setup_cost' => 80, 'num_colors' => 1],
                ['quantity_min' => 250, 'quantity_max' => 499, 'unit_price' => 2.20, 'setup_cost' => 80, 'num_colors' => 1],
                ['quantity_min' => 500, 'quantity_max' => null, 'unit_price' => 1.50, 'setup_cost' => 0, 'num_colors' => 1],
            ],
            'badge-pvc' => [
                ['quantity_min' => 1, 'quantity_max' => 49, 'unit_price' => 5.00, 'setup_cost' => 50, 'num_colors' => 1],
                ['quantity_min' => 50, 'quantity_max' => 99, 'unit_price' => 4.00, 'setup_cost' => 50, 'num_colors' => 1],
                ['quantity_min' => 100, 'quantity_max' => null, 'unit_price' => 3.00, 'setup_cost' => 0, 'num_colors' => 1],
            ],
            'full-print' => [
                ['quantity_min' => 1, 'quantity_max' => 9, 'unit_price' => 15.00, 'setup_cost' => 0, 'num_colors' => 1],
                ['quantity_min' => 10, 'quantity_max' => 24, 'unit_price' => 12.00, 'setup_cost' => 0, 'num_colors' => 1],
                ['quantity_min' => 25, 'quantity_max' => 49, 'unit_price' => 9.00, 'setup_cost' => 0, 'num_colors' => 1],
                ['quantity_min' => 50, 'quantity_max' => null, 'unit_price' => 7.00, 'setup_cost' => 0, 'num_colors' => 1],
            ],
        ];

        foreach ($pricingGrids as $slug => $pricings) {
            $technique = MarkingTechnique::where('slug', $slug)->first();
            if (! $technique) {
                continue;
            }

            foreach ($pricings as $pricing) {
                TechniquePricing::create(array_merge(
                    ['marking_technique_id' => $technique->id],
                    $pricing
                ));
            }
        }
    }
}
