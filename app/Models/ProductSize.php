<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductSize extends Model
{
    public const SIZE_ORDER = [
        // Enfant AWDis / Toptex
        '1/2' => -20, '2/3' => -19, '3/4' => -18, '4/5' => -17, '5/6' => -16,
        '6/7' => -15, '7/8' => -14, '8/9' => -13, '9/10' => -12, '10/11' => -11,
        '11/12' => -10, '12/13' => -9, '13/14' => -8,
        // Bébé
        '3M' => -7, '6M' => -6, '12M' => -5, '18M' => -4, '24M' => -3,
        '2ans' => -2, '4ans' => -1,
        // Adulte
        'XXS' => 0, 'XS' => 1, 'S' => 2, 'M' => 3, 'L' => 4, 'XL' => 5,
        '2XL' => 6, 'XXL' => 6, '3XL' => 7, '4XL' => 8, '5XL' => 9, '6XL' => 10, '7XL' => 11, '8XL' => 12,
        '6ans' => 13, '8ans' => 14, '10ans' => 15, '12ans' => 16, '14ans' => 17,
        'TU' => 25, 'UNIQUE' => 25,
    ];

    public static function sortPosition(string $size): int
    {
        return self::SIZE_ORDER[$size] ?? 999;
    }

    protected $fillable = [
        'product_color_id',
        'size',
        'sku',
        'ean',
        'price_supplement',
        'stock',
        'is_available',
    ];

    protected $casts = [
        'is_available' => 'boolean',
        'price_supplement' => 'decimal:2',
    ];

    /**
     * Sizes considered "large" that typically have a price supplement.
     */
    public const LARGE_SIZES = ['3XL', '4XL', '5XL', '6XL', '7XL', '8XL'];

    public function color(): BelongsTo
    {
        return $this->belongsTo(ProductColor::class, 'product_color_id');
    }

    public function scopeAvailable($query)
    {
        return $query->where('is_available', true);
    }
}
