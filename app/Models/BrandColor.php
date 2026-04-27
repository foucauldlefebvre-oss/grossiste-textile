<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BrandColor extends Model
{
    protected $fillable = [
        'brand',
        'name',
        'hex_code',
        'color_code',
    ];

    /**
     * Get all distinct brands that have colors.
     */
    public static function brands(): array
    {
        return static::distinct()->pluck('brand')->sort()->values()->toArray();
    }

    /**
     * Get colors for a specific brand, ordered by name.
     */
    public static function forBrand(string $brand): \Illuminate\Database\Eloquent\Collection
    {
        return static::where('brand', $brand)->orderBy('name')->get();
    }
}
