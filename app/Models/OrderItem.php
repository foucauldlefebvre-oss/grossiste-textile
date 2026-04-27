<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'marking_group',
        'product_id',
        'product_color_id',
        'product_size_id',
        'marking_technique_id',
        'quantity',
        'unit_price_ht',
        'marking_price_ht',
        'line_total_ht',
        'visual_file',
        'marking_zone',
        'visual_colors',
        'bat_pdf',
        'bat_status',
    ];

    protected $casts = [
        'unit_price_ht' => 'decimal:2',
        'marking_price_ht' => 'decimal:2',
        'line_total_ht' => 'decimal:2',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function color(): BelongsTo
    {
        return $this->belongsTo(ProductColor::class, 'product_color_id');
    }

    public function size(): BelongsTo
    {
        return $this->belongsTo(ProductSize::class, 'product_size_id');
    }

    public function technique(): BelongsTo
    {
        return $this->belongsTo(MarkingTechnique::class, 'marking_technique_id');
    }
}
