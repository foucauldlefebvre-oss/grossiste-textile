<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\MarkingTechnique;
use App\Models\Product;

class HomeController extends Controller
{
    public function __invoke()
    {
        return view('home', [
            'categories' => Category::active()->roots()
                ->withCount('products')
                ->orderBy('sort_order')
                ->get(),
            'featuredProducts' => Product::active()
                ->featured()
                ->with('colors')
                ->orderBy('sort_order')
                ->limit(8)
                ->get(),
            'techniques' => MarkingTechnique::active()
                ->with('constraint')
                ->orderBy('sort_order')
                ->get(),
        ]);
    }
}
