<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GroupShopProduct extends Model
{
    protected $fillable = [
        'group_shop_id',
        'product_id',
        'allowed_colors',
        'allowed_sizes',
        'marking_technique_id',
        'visual_file',
        'marking_zone',
        'fixed_price',
        'sort_order',
    ];

    protected $casts = [
        'allowed_colors' => 'array',
        'allowed_sizes' => 'array',
        'fixed_price' => 'decimal:2',
    ];

    public function groupShop(): BelongsTo
    {
        return $this->belongsTo(GroupShop::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function technique(): BelongsTo
    {
        return $this->belongsTo(MarkingTechnique::class, 'marking_technique_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(GroupShopOrder::class);
    }
}
