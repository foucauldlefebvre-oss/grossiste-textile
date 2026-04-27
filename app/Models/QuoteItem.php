<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuoteItem extends Model
{
    // TODO 2b: tout le modèle QuoteItem sera supprimé / refactor en CartItem (Q1).
    protected $fillable = [
        'quote_id',
        // 'marking_group',           // TODO 2c: drop column
        'product_id',
        'product_color_id',
        'product_size_id',
        // 'marking_technique_id',    // TODO 2c: drop column
        'quantity',
        'unit_price_ht',
        // 'marking_price_ht',        // TODO 2c: drop column
        'line_total_ht',
        // 'visual_file',             // TODO 2c: drop column
        // 'marking_zone',            // TODO 2c: drop column
        // 'visual_colors',           // TODO 2c: drop column
        // 'options',                 // TODO 2c: drop column
    ];

    protected $casts = [
        'unit_price_ht' => 'decimal:2',
        // 'marking_price_ht' => 'decimal:2',
        'line_total_ht' => 'decimal:2',
        // 'options' => 'array',
    ];

    public function quote(): BelongsTo
    {
        return $this->belongsTo(Quote::class);
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

    // TODO 2b: relation technique() supprimée (MarkingTechnique dégagé)
}
