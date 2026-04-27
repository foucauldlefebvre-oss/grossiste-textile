<?php

namespace Database\Seeders;

use App\Models\MarkingTechnique;
use App\Models\SerigraphiePricing;
use Illuminate\Database\Seeder;

class SerigraphiePricingSeeder extends Seeder
{
    public function run(): void
    {
        SerigraphiePricing::truncate();

        $qtyTiers = [
            ['min' => 20, 'max' => 49],
            ['min' => 50, 'max' => 99],
            ['min' => 100, 'max' => 249],
            ['min' => 250, 'max' => 499],
            ['min' => 500, 'max' => 999],
            ['min' => 1000, 'max' => 4999],
            ['min' => 5000, 'max' => null],
        ];

        // Sur clair (blanc, beige, naturel, gris chine, ash, blanc chine)
        $lightPrices = [
            1 => [3.10, 1.50, 1.10, 0.80, 0.70, 0.60, 0.50],
            2 => [4.50, 2.40, 1.70, 1.20, 1.00, 0.90, 0.70],
            3 => [5.50, 2.70, 1.90, 1.30, 1.10, 1.00, 0.90],
        ];

        // Sur fonce (toutes les autres couleurs)
        $darkPrices = [
            1 => [4.50, 2.30, 1.70, 1.30, 1.20, 1.00, 0.90],
            2 => [5.50, 3.00, 2.50, 1.90, 1.70, 1.40, 1.30],
            3 => [7.00, 3.60, 2.70, 2.10, 1.90, 1.50, 1.40],
        ];

        $sort = 0;

        foreach (['light' => $lightPrices, 'dark' => $darkPrices] as $type => $pricesByColors) {
            foreach ($pricesByColors as $colors => $prices) {
                foreach ($qtyTiers as $qi => $qty) {
                    SerigraphiePricing::create([
                        'textile_type' => $type,
                        'num_colors' => $colors,
                        'quantity_min' => $qty['min'],
                        'quantity_max' => $qty['max'],
                        'unit_price' => $prices[$qi],
                        'sort_order' => $sort++,
                    ]);
                }
            }
        }

        // Update serigraphie technique: quality rating + constraints
        $serigraphie = MarkingTechnique::where('slug', 'serigraphie')->first();

        if ($serigraphie) {
            $serigraphie->update(['quality_rating' => 4]);

            $serigraphie->constraint()->updateOrCreate(
                ['marking_technique_id' => $serigraphie->id],
                [
                    'min_quantity' => 20,
                    'max_colors' => 3,
                    'notes' => 'Encre fluo/metal = +30%. Commande mixte clair+fonce = tarif fonce sur toute la commande. Couleurs claires : blanc, beige, naturel, gris chine, ash, blanc chine.',
                ]
            );
        }
    }
}
