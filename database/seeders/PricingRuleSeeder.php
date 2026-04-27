<?php

namespace Database\Seeders;

use App\Models\PricingRule;
use Illuminate\Database\Seeder;

class PricingRuleSeeder extends Seeder
{
    public function run(): void
    {
        // Vider la table avant re-seed
        PricingRule::truncate();

        // Tranches de prix d'achat fournisseur HT
        $priceTiers = [
            ['label' => '0,00 - 0,99€', 'min' => 0.00, 'max' => 0.99],
            ['label' => '1,00 - 4,99€', 'min' => 1.00, 'max' => 4.99],
            ['label' => '5,00 - 9,99€', 'min' => 5.00, 'max' => 9.99],
            ['label' => '10,00 - 19,99€', 'min' => 10.00, 'max' => 19.99],
            ['label' => '20,00€ et +', 'min' => 20.00, 'max' => null],
        ];

        // Tranches de quantité (min => max)
        $qtyTiers = [
            ['min' => 1, 'max' => 19],
            ['min' => 20, 'max' => 49],
            ['min' => 50, 'max' => 99],
            ['min' => 100, 'max' => 199],
            ['min' => 200, 'max' => 499],
            ['min' => 500, 'max' => 999],
            ['min' => 1000, 'max' => 1999],
            ['min' => 2000, 'max' => 4999],
            ['min' => 5000, 'max' => null],
        ];

        // Grille de coefficients [tranche_prix][tranche_qty]
        // null = pas de coefficient (combinaison non proposée)
        $coefficients = [
            // 0,00 - 0,99€
            [3.0, 2.5, 2.2, 2.0, 1.9, 1.8, 1.7, 1.5, 1.3],
            // 1,00 - 4,99€
            [2.5, 2.2, 1.61, 1.5, 1.41, 1.3, 1.1, 1.1, 1.05],
            // 5,00 - 9,99€
            [1.7, 1.6, 1.5, 1.4, 1.3, 1.2, 1.1, 1.1, null],
            // 10,00 - 19,99€
            [1.6, 1.4, 1.3, 1.2, 1.15, 1.12, null, null, null],
            // 20,00€ et +
            [1.5, 1.4, 1.3, 1.2, 1.15, 1.12, null, null, null],
        ];

        $sort = 0;
        foreach ($priceTiers as $pi => $price) {
            foreach ($qtyTiers as $qi => $qty) {
                $coeff = $coefficients[$pi][$qi] ?? null;
                if ($coeff === null) {
                    continue; // Pas de règle pour cette combinaison
                }

                PricingRule::create([
                    'label' => $price['label'],
                    'min_price' => $price['min'],
                    'max_price' => $price['max'],
                    'min_quantity' => $qty['min'],
                    'max_quantity' => $qty['max'],
                    'coefficient' => $coeff,
                    'sort_order' => $sort++,
                ]);
            }
        }
    }
}
