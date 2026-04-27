<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SerigraphiePricing extends Model
{
    protected $fillable = [
        'textile_type',
        'num_colors',
        'quantity_min',
        'quantity_max',
        'unit_price',
        'sort_order',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'num_colors' => 'integer',
        'quantity_min' => 'integer',
        'quantity_max' => 'integer',
    ];

    /**
     * Light textile color names (case-insensitive).
     */
    const LIGHT_COLORS = [
        'blanc',
        'beige',
        'naturel',
        'gris chine',
        'gris chiné',
        'ash',
        'blanc chine',
        'blanc chiné',
    ];

    /**
     * Check if a textile color name is considered "light".
     */
    public static function isLightColor(string $colorName): bool
    {
        $normalized = mb_strtolower(trim($colorName));

        foreach (self::LIGHT_COLORS as $light) {
            if ($normalized === $light || str_contains($normalized, $light)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine textile type from an array of color names.
     * Rule: if ANY color is dark, entire order uses dark pricing.
     */
    public static function determineTextileType(array $textileColors): string
    {
        if (empty($textileColors)) {
            return 'dark';
        }

        foreach ($textileColors as $color) {
            if (! self::isLightColor($color)) {
                return 'dark';
            }
        }

        return 'light';
    }

    /**
     * Calculate serigraphie unit price HT.
     *
     * @param  int    $quantity       Total quantity (min 20)
     * @param  int    $colors         Number of ink colors (1-3)
     * @param  string $inkType        'standard', 'fluo', or 'metal'
     * @param  array  $textileColors  Textile color names to determine light/dark
     */
    public static function calculate(int $quantity, int $colors, string $inkType = 'standard', array $textileColors = []): float
    {
        $colors = max(1, min($colors, 3));
        $textileType = self::determineTextileType($textileColors);

        $pricing = self::where('textile_type', $textileType)
            ->where('num_colors', $colors)
            ->where('quantity_min', '<=', $quantity)
            ->where(function ($q) use ($quantity) {
                $q->whereNull('quantity_max')
                  ->orWhere('quantity_max', '>=', $quantity);
            })
            ->orderBy('quantity_min', 'desc')
            ->first();

        if (! $pricing) {
            return 0;
        }

        $unitPrice = (float) $pricing->unit_price;

        // Fluo or metallic ink = +30%
        if (in_array(mb_strtolower($inkType), ['fluo', 'metal', 'métal', 'metallique', 'métallique'])) {
            $unitPrice = round($unitPrice * 1.30, 2);
        }

        return $unitPrice;
    }
}
