<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShippingRate extends Model
{
    protected $fillable = [
        'carrier',
        'zone',
        'weight_min',
        'weight_max',
        'price_ht',
        'delivery_days_min',
        'delivery_days_max',
        'is_active',
    ];

    protected $casts = [
        'weight_min' => 'decimal:3',
        'weight_max' => 'decimal:3',
        'price_ht' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForWeight($query, float $weight)
    {
        // weight_min is exclusive (strict <), weight_max is inclusive (>=)
        // so 2kg matches tier [1, 2] not [2, 5]
        return $query->where('weight_min', '<', $weight)
            ->where('weight_max', '>=', $weight);
    }
}
