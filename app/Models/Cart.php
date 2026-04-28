<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cart extends Model
{
    protected $fillable = [
        'user_id',
        'status',
        'subtotal_ht',
        'shipping_ht',
        'shipping_parcels',
        'shipping_zone',
        'shipping_per_parcel',
        'total_ht',
        'total_tva',
        'total_ttc',
        'vat_exemption',
        'converted_at',
        'abandoned_at',
    ];

    protected $casts = [
        'subtotal_ht' => 'decimal:2',
        'shipping_ht' => 'decimal:2',
        'shipping_per_parcel' => 'decimal:2',
        'total_ht' => 'decimal:2',
        'total_tva' => 'decimal:2',
        'total_ttc' => 'decimal:2',
        'shipping_parcels' => 'integer',
        'converted_at' => 'datetime',
        'abandoned_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeAbandoned($query)
    {
        return $query->where('status', 'abandoned');
    }

    public function scopeConverted($query)
    {
        return $query->where('status', 'converted');
    }

    public function getItemCountAttribute(): int
    {
        return (int) $this->items()->sum('quantity');
    }

    public function isEmpty(): bool
    {
        return $this->items()->count() === 0;
    }
}
