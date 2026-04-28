<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Laravel\Scout\Searchable;

class Product extends Model
{
    use Searchable;

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    protected $fillable = [
        'category_id',
        'prestashop_id',
        'name',
        'slug',
        'reference',
        'supplier',
        'description',
        'short_description',
        'material',
        'grammage',
        'cut',
        'certifications',
        'filter_tags',
        'base_price',
        'supplier_price',
        'weight',
        'package_weight',
        'main_image',
        'gallery',
        'is_active',
        'is_outlet',
        'is_featured',
        'sort_order',
        'stock',
        'min_order_quantity',
        'meta_title',
        'meta_description',
        'seo_keywords',
        'seo_url',
    ];

    protected $casts = [
        'certifications' => 'array',
        'filter_tags' => 'array',
        'gallery' => 'array',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'base_price' => 'decimal:2',
        'supplier_price' => 'decimal:2',
        'weight' => 'decimal:3',
        'grammage' => 'integer',
        'min_order_quantity' => 'integer',
        'package_weight' => 'integer',
    ];

    // TODO 2b: hook booted() supprimé — sync compatible_techniques ↔ techniqueRules dégagé (Q4)

    public static function generateReference(?string $supplier, ?string $categoryName, ?string $productName): string
    {
        $brandCodes = [
            'Kariban' => 'KA',
            'Stanley/Stella' => 'SS',
            'B&C' => 'BC',
            'Gildan' => 'GI',
            'Fruit of the Loom' => 'FL',
            "SOL'S" => 'SO',
            'Result' => 'RE',
            'Autre' => 'AU',
        ];

        $brand = $brandCodes[$supplier] ?? strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $supplier ?? 'XX'), 0, 2));
        $cat = strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $categoryName ?? ''), 0, 2)) ?: 'XX';
        $name = strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $productName ?? ''), 0, 3)) ?: 'XXX';

        return "{$brand}-{$cat}-{$name}";
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the SEO-friendly URL for this product: /{category-slug}/{product-slug}
     */
    public function getUrlAttribute(): string
    {
        $category = $this->relationLoaded('category') ? $this->category : $this->category()->first();
        // Use parent category for root-level URL
        $rootCategory = $category?->parent ?? $category;

        return $rootCategory
            ? route('catalogue.product', [$rootCategory->slug, $this->slug])
            : '/' . $this->slug;
    }

    public function colors(): HasMany
    {
        return $this->hasMany(ProductColor::class)->orderBy('sort_order');
    }

    public function sizes(): HasManyThrough
    {
        return $this->hasManyThrough(ProductSize::class, ProductColor::class);
    }

    // TODO 2b: relation techniqueRules() supprimée — ProductTechniqueRule dégagé (Q4)

    public function secondaryCategories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'product_categories')->withTimestamps();
    }

    public function allCategoryIds(): array
    {
        $ids = $this->secondaryCategories()->pluck('categories.id')->toArray();
        if ($this->category_id) {
            array_unshift($ids, $this->category_id);
        }

        return array_unique($ids);
    }

    // TODO 2b: relation compatibleTechniques() supprimée — MarkingTechnique dégagé

    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'reference' => $this->reference,
            'supplier' => $this->supplier,
            'material' => $this->material,
            'description' => strip_tags($this->description ?? ''),
            'short_description' => strip_tags($this->short_description ?? ''),
            'cut' => $this->cut,
            'base_price' => (float) $this->base_price,
            'category_id' => $this->category_id,
            'is_active' => $this->is_active,
            'is_featured' => $this->is_featured,
            'color_names' => $this->colors->pluck('name')->implode(' '),
        ];
    }

    /**
     * Prix d'affichage "A partir de" : prix le plus bas possible (coefficient le plus bas avec coeff > 0).
     */
    public function getDisplayPriceAttribute(): float
    {
        $supplierPrice = (float) ($this->supplier_price ?? 0);

        if ($supplierPrice > 0) {
            return \App\Models\PricingRule::getLowestPrice($supplierPrice);
        }

        if ($this->base_price && (float) $this->base_price > 0) {
            return (float) $this->base_price;
        }

        return 0;
    }

    public function shouldBeSearchable(): bool
    {
        return (bool) $this->is_active;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Get the URL for an image field, handling both local storage paths and external URLs.
     */
    public function imageUrl(?string $path = null): string
    {
        $path = $path ?? $this->main_image;
        if (! $path) {
            return '';
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return \Illuminate\Support\Facades\Storage::url($path);
    }
}
