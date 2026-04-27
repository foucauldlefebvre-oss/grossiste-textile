<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GroupShopOrder extends Model
{
    protected $fillable = [
        'group_shop_id',
        'group_shop_product_id',
        'order_id',
        'first_name',
        'last_name',
        'email',
        'department',
        'size',
        'quantity',
        'unit_price',
        'total',
        'payment_status',
        'stripe_payment_intent_id',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function groupShop(): BelongsTo
    {
        return $this->belongsTo(GroupShop::class);
    }

    public function groupShopProduct(): BelongsTo
    {
        return $this->belongsTo(GroupShopProduct::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
