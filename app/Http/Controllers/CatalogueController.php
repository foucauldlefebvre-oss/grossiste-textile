<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;

class CatalogueController extends Controller
{
    public function index()
    {
        $categories = Category::active()->roots()
            ->with(['children' => fn ($q) => $q->active()->with(['children' => fn ($q2) => $q2->active()])])
            ->orderBy('sort_order')
            ->get();

        // Pre-compute recursive product counts in a single query
        $counts = Product::where('is_active', true)
            ->selectRaw('category_id, count(*) as cnt')
            ->groupBy('category_id')
            ->pluck('cnt', 'category_id');

        foreach ($categories as $cat) {
            $ids = collect([$cat->id]);
            foreach ($cat->children as $child) {
                $ids->push($child->id);
                foreach ($child->children as $gc) {
                    $ids->push($gc->id);
                }
            }
            $cat->setAttribute('recursive_products_count', $ids->sum(fn ($id) => $counts[$id] ?? 0));
        }

        return view('catalogue.index', ['categories' => $categories]);
    }

    public function category(Category $category)
    {
        abort_unless($category->is_active, 404);

        $subcategories = $category->children()->active()
            ->withCount(['products' => fn ($q) => $q->where('is_active', true)])
            ->orderBy('sort_order')
            ->get();

        // Collect all descendant category IDs so parent page shows all products
        $categoryIds = collect([$category->id]);
        if ($subcategories->isNotEmpty()) {
            $childIds = $subcategories->pluck('id');
            $categoryIds = $categoryIds->merge($childIds);
            $grandchildIds = Category::active()->whereIn('parent_id', $childIds)->pluck('id');
            $categoryIds = $categoryIds->merge($grandchildIds);
        }

        return view('catalogue.category', [
            'category' => $category,
            'subcategories' => $subcategories,
            'categoryIds' => $categoryIds->all(),
        ]);
    }

    public function product(Category $category, Product $product)
    {
        abort_unless($product->is_active, 404);

        // Redirect si mauvaise categorie dans l'URL
        if ($product->category_id !== $category->id && $product->category?->parent_id !== $category->id) {
            $realCategory = $product->category?->parent ?? $product->category;
            if ($realCategory) {
                return redirect()->route('catalogue.product', [$realCategory, $product], 301);
            }
        }

        // TODO 2b: relations techniqueRules supprimées (Q4)
        $product->load([
            'category',
            'colors.sizes' => fn ($q) => $q->where('is_available', true),
        ]);

        return view('catalogue.product', [
            'product' => $product,
            'relatedProducts' => Product::active()
                ->where('category_id', $product->category_id)
                ->where('id', '!=', $product->id)
                ->with('colors')
                ->limit(4)
                ->get(),
        ]);
    }
}
