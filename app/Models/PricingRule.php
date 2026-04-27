<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PricingRule extends Model
{
    protected $fillable = [
        'label',
        'min_price',
        'max_price',
        'min_quantity',
        'max_quantity',
        'coefficient',
        'sort_order',
    ];

    protected $casts = [
        'min_price' => 'decimal:2',
        'max_price' => 'decimal:2',
        'coefficient' => 'decimal:3',
        'min_quantity' => 'integer',
        'max_quantity' => 'integer',
    ];

    /**
     * Get the coefficient for a given supplier price and quantity.
     * Returns null if quantity is below minimum (marked "x" in grid).
     * Returns 0 if "sur devis" (large qty + high price).
     */
    public static function getCoefficient(float $supplierPrice, int $quantity): ?float
    {
        $rule = static::where('min_price', '<=', $supplierPrice)
            ->where(function ($q) use ($supplierPrice) {
                $q->whereNull('max_price')
                  ->orWhere('max_price', '>=', $supplierPrice);
            })
            ->where('min_quantity', '<=', $quantity)
            ->where(function ($q) use ($quantity) {
                $q->whereNull('max_quantity')
                  ->orWhere('max_quantity', '>=', $quantity);
            })
            ->orderBy('min_quantity', 'desc')
            ->first();

        if (! $rule) {
            return null; // No rule = "x" = quantity too low for this price tier
        }

        return (float) $rule->coefficient; // 0 = "sur devis"
    }

    /**
     * Calculate selling price HT from supplier price and quantity.
     * Returns null if no sale at this qty, 0 if "sur devis".
     */
    public static function calculate(float $supplierPrice, int $quantity): ?float
    {
        $coefficient = static::getCoefficient($supplierPrice, $quantity);

        if ($coefficient === null) {
            return null; // Below minimum quantity
        }

        if ($coefficient == 0) {
            return 0; // Sur devis
        }

        return round($supplierPrice * $coefficient, 2);
    }

    /**
     * Get the minimum quantity that has a valid coefficient for a given supplier price.
     */
    public static function getMinQuantity(float $supplierPrice): ?int
    {
        $rule = static::where('min_price', '<=', $supplierPrice)
            ->where(function ($q) use ($supplierPrice) {
                $q->whereNull('max_price')
                  ->orWhere('max_price', '>=', $supplierPrice);
            })
            ->where('coefficient', '>', 0)
            ->orderBy('min_quantity', 'asc')
            ->first();

        return $rule?->min_quantity;
    }

    /**
     * Get the lowest possible selling price for a given supplier price (highest qty, lowest coeff > 0).
     */
    public static function getLowestPrice(float $supplierPrice): float
    {
        $rule = static::where('min_price', '<=', $supplierPrice)
            ->where(function ($q) use ($supplierPrice) {
                $q->whereNull('max_price')
                  ->orWhere('max_price', '>=', $supplierPrice);
            })
            ->where('coefficient', '>', 0)
            ->orderBy('coefficient', 'asc')
            ->first();

        if (! $rule) {
            return 0;
        }

        return round($supplierPrice * (float) $rule->coefficient, 2);
    }

    /**
     * Get all distinct price tiers.
     */
    public static function priceTiers(): array
    {
        return static::select('label', 'min_price', 'max_price')
            ->distinct()
            ->orderBy('min_price')
            ->get()
            ->unique('label')
            ->values()
            ->toArray();
    }
}
