<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductColor extends Model
{
    protected $fillable = [
        'product_id',
        'name',
        'hex_code',
        'hex_code_secondary',
        'supplier_price',
        'image',
        'stock',
        'sort_order',
        'is_active',
        'is_outlet',
        'outlet_supplier_price',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_outlet' => 'boolean',
        'supplier_price' => 'decimal:2',
        'outlet_supplier_price' => 'decimal:2',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function sizes(): HasMany
    {
        return $this->hasMany(ProductSize::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
