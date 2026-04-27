<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransferPricing extends Model
{
    protected $fillable = [
        'technique',
        'format',
        'quantity',
        'unit_price',
        'sort_order',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'quantity' => 'integer',
    ];

    const FORMATS = ['A7', 'A6', 'A5', 'A4', 'A3'];

    /**
     * Calculate transfer unit price HT with linear interpolation.
     *
     * @param  string $technique  'dtf' or 'transfert-offset'
     * @param  string $format     'A7', 'A6', 'A5', 'A4', 'A3'
     * @param  int    $quantity   Total quantity
     */
    public static function calculate(string $technique, string $format, int $quantity): float
    {
        $points = self::where('technique', $technique)
            ->where('format', $format)
            ->orderBy('quantity')
            ->get();

        if ($points->isEmpty()) {
            return 0;
        }

        $first = $points->first();
        $last = $points->last();

        // At or below minimum breakpoint
        if ($quantity <= $first->quantity) {
            return (float) $first->unit_price;
        }

        // At or above maximum breakpoint
        if ($quantity >= $last->quantity) {
            return (float) $last->unit_price;
        }

        // Linear interpolation between surrounding breakpoints
        for ($i = 0; $i < $points->count() - 1; $i++) {
            $lower = $points[$i];
            $upper = $points[$i + 1];

            if ($quantity >= $lower->quantity && $quantity <= $upper->quantity) {
                $ratio = ($quantity - $lower->quantity) / ($upper->quantity - $lower->quantity);
                $price = (float) $lower->unit_price + $ratio * ((float) $upper->unit_price - (float) $lower->unit_price);

                return round($price, 2);
            }
        }

        return (float) $last->unit_price;
    }
}
