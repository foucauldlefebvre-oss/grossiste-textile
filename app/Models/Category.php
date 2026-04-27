<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    protected $fillable = [
        'parent_id',
        'prestashop_id',
        'name',
        'slug',
        'description',
        'meta_title',
        'meta_description',
        'seo_keywords',
        'default_techniques',
        'available_filters',
        'image',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'default_techniques' => 'array',
        'available_filters' => 'array',
    ];

    /**
     * Get filters for this category, falling back to parent, then defaults.
     */
    public function getEffectiveFilters(): array
    {
        if (! empty($this->available_filters)) {
            return $this->available_filters;
        }

        if ($this->parent_id && $this->parent) {
            return $this->parent->getEffectiveFilters();
        }

        return [];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id')->orderBy('sort_order');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function secondaryProducts(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_categories')->withTimestamps();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeRoots($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Count products in this category AND all descendant categories.
     */
    public function getRecursiveProductsCountAttribute(): int
    {
        $ids = collect([$this->id]);

        $childIds = Category::where('parent_id', $this->id)->pluck('id');
        $ids = $ids->merge($childIds);

        if ($childIds->isNotEmpty()) {
            $grandchildIds = Category::whereIn('parent_id', $childIds)->pluck('id');
            $ids = $ids->merge($grandchildIds);
        }

        return Product::where(function ($q) use ($ids) {
            $q->whereIn('category_id', $ids)
              ->orWhereHas('secondaryCategories', fn ($sq) => $sq->whereIn('categories.id', $ids->all()));
        })->count();
    }
}
