<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BrandSize extends Model
{
    protected $fillable = [
        'brand',
        'size',
    ];

    /**
     * Get sizes for a specific brand, sorted by SIZE_ORDER.
     */
    public static function forBrand(string $brand): array
    {
        return static::where('brand', $brand)
            ->pluck('size')
            ->sort(fn ($a, $b) => ProductSize::sortPosition($a) <=> ProductSize::sortPosition($b))
            ->values()
            ->toArray();
    }
}
