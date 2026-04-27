<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class MarkingTechnique extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'meta_title',
        'meta_description',
        'seo_content',
        'seo_url',
        'icon',
        'is_active',
        'sort_order',
        'quality_rating',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'quality_rating' => 'float',
    ];

    public function pricings(): HasMany
    {
        return $this->hasMany(TechniquePricing::class)->orderBy('quantity_min');
    }

    public function constraint(): HasOne
    {
        return $this->hasOne(TechniqueConstraint::class);
    }

    public function productRules(): HasMany
    {
        return $this->hasMany(ProductTechniqueRule::class);
    }

    public function compatibleProducts()
    {
        return $this->belongsToMany(Product::class, 'product_technique_rules')
            ->wherePivot('is_compatible', true)
            ->withPivot(['is_compatible', 'incompatibility_reason', 'marking_zones']);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
