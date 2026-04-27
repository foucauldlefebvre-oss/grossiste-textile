<?php

namespace Database\Seeders;

use App\Models\MarkingTechnique;
use App\Models\TransferPricing;
use Illuminate\Database\Seeder;

class TransferPricingSeeder extends Seeder
{
    public function run(): void
    {
        TransferPricing::truncate();

        // ── DTF ──────────────────────────────────────────
        $dtfQty = [10, 20, 50, 100, 250, 500, 1000, 5000];
        $dtfPrices = [
            'A7' => [9.30, 5.50, 4.50, 4.00, 2.30, 2.00, 2.00, 2.00],
            'A6' => [9.30, 5.50, 4.50, 4.00, 2.30, 2.00, 2.00, 2.00],
            'A5' => [10.00, 5.80, 4.80, 4.30, 2.50, 2.50, 2.50, 2.50],
            'A4' => [10.50, 6.00, 5.00, 4.50, 3.00, 3.00, 3.00, 3.00],
            'A3' => [11.50, 7.00, 6.00, 5.00, 4.50, 4.50, 4.50, 4.50],
        ];

        // ── OFFSET ───────────────────────────────────────
        $offsetQty = [50, 100, 250, 500, 1000, 5000];
        $offsetPrices = [
            'A7' => [4.50, 3.50, 2.30, 1.30, 1.00, 0.45],
            'A6' => [4.90, 3.70, 2.40, 1.40, 1.10, 0.50],
            'A5' => [5.30, 3.80, 2.50, 1.50, 1.20, 0.55],
            'A4' => [5.50, 4.00, 2.90, 1.65, 1.30, 0.75],
            'A3' => [6.00, 4.60, 3.10, 2.01, 1.70, 0.75],
        ];

        $sort = 0;

        foreach ($dtfPrices as $format => $prices) {
            foreach ($dtfQty as $i => $qty) {
                TransferPricing::create([
                    'technique' => 'dtf',
                    'format' => $format,
                    'quantity' => $qty,
                    'unit_price' => $prices[$i],
                    'sort_order' => $sort++,
                ]);
            }
        }

        foreach ($offsetPrices as $format => $prices) {
            foreach ($offsetQty as $i => $qty) {
                TransferPricing::create([
                    'technique' => 'transfert-offset',
                    'format' => $format,
                    'quantity' => $qty,
                    'unit_price' => $prices[$i],
                    'sort_order' => $sort++,
                ]);
            }
        }

        // ── Update / create techniques ───────────────────
        $dtf = MarkingTechnique::updateOrCreate(
            ['slug' => 'dtf'],
            [
                'name' => 'Transfert DTF',
                'is_active' => true,
                'quality_rating' => 2.0,
            ]
        );
        $dtf->constraint()->updateOrCreate(
            ['marking_technique_id' => $dtf->id],
            [
                'min_quantity' => 10,
                'max_colors' => null,
                'notes' => '1 couleur a quadri. Format visuel A7 a A3. Couleur textile sans impact.',
            ]
        );

        $offset = MarkingTechnique::updateOrCreate(
            ['slug' => 'transfert-offset'],
            [
                'name' => 'Transfert Offset',
                'is_active' => true,
                'quality_rating' => 4.5,
            ]
        );
        $offset->constraint()->updateOrCreate(
            ['marking_technique_id' => $offset->id],
            [
                'min_quantity' => 50,
                'max_colors' => null,
                'notes' => '1 couleur a quadri. Format visuel A7 a A3. Couleur textile sans impact.',
            ]
        );
    }
}
