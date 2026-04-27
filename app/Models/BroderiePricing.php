<?php

namespace App\Models;

class BroderiePricing
{
    const FORMATS = ['A7', 'A6', 'A5', 'A4'];

    /**
     * Calculate broderie unit price HT with linear interpolation.
     */
    public static function calculate(string $format, int $quantity): float
    {
        return TransferPricing::calculate('broderie', $format, $quantity);
    }
}
