<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TechniquePricing extends Model
{
    protected $fillable = [
        'marking_technique_id',
        'quantity_min',
        'quantity_max',
        'unit_price',
        'setup_cost',
        'num_colors',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'setup_cost' => 'decimal:2',
    ];

    public function technique(): BelongsTo
    {
        return $this->belongsTo(MarkingTechnique::class, 'marking_technique_id');
    }
}
