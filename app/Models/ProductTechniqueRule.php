<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductTechniqueRule extends Model
{
    protected $fillable = [
        'product_id',
        'marking_technique_id',
        'is_compatible',
        'incompatibility_reason',
        'marking_zones',
        'supplement_10',
        'supplement_20',
    ];

    protected $casts = [
        'is_compatible' => 'boolean',
        'marking_zones' => 'array',
        'supplement_10' => 'boolean',
        'supplement_20' => 'boolean',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function technique(): BelongsTo
    {
        return $this->belongsTo(MarkingTechnique::class, 'marking_technique_id');
    }
}
