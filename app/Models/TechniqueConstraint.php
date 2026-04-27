<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TechniqueConstraint extends Model
{
    protected $fillable = [
        'marking_technique_id',
        'min_quantity',
        'max_colors',
        'max_format',
        'max_visual_width',
        'max_visual_height',
        'production_days_min',
        'production_days_max',
        'compatible_materials',
        'incompatible_materials',
        'notes',
    ];

    protected $casts = [
        'compatible_materials' => 'array',
        'incompatible_materials' => 'array',
    ];

    public function technique(): BelongsTo
    {
        return $this->belongsTo(MarkingTechnique::class, 'marking_technique_id');
    }
}
