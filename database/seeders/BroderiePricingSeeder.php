<?php

namespace Database\Seeders;

use App\Models\MarkingTechnique;
use App\Models\TransferPricing;
use Illuminate\Database\Seeder;

class BroderiePricingSeeder extends Seeder
{
    public function run(): void
    {
        TransferPricing::where('technique', 'broderie')->delete();

        $qty = [20, 50, 100, 250, 500, 1000, 5000];
        $prices = [
            'A7' => [4.95, 3.86, 3.22, 2.72, 2.48, 2.23, 2.23],
            'A6' => [6.00, 4.68, 3.90, 3.30, 3.00, 2.70, 2.70],
            'A5' => [8.00, 6.50, 6.00, 5.00, 4.00, 3.80, 3.80],
            'A4' => [12.00, 8.80, 7.80, 6.60, 5.00, 4.50, 4.50],
        ];

        $sort = TransferPricing::max('sort_order') + 1;

        foreach ($prices as $format => $unitPrices) {
            foreach ($qty as $i => $q) {
                TransferPricing::create([
                    'technique' => 'broderie',
                    'format' => $format,
                    'quantity' => $q,
                    'unit_price' => $unitPrices[$i],
                    'sort_order' => $sort++,
                ]);
            }
        }

        // Update broderie technique
        $broderie = MarkingTechnique::updateOrCreate(
            ['slug' => 'broderie'],
            [
                'name' => 'Broderie',
                'is_active' => true,
                'quality_rating' => 5.0,
            ]
        );
        $broderie->constraint()->updateOrCreate(
            ['marking_technique_id' => $broderie->id],
            [
                'min_quantity' => 20,
                'max_colors' => 6,
                'notes' => 'Prix selon format (A7-A4) et quantite. Interpolation lineaire.',
            ]
        );
    }
}
