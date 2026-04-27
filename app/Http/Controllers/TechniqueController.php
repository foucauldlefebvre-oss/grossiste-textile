<?php

namespace App\Http\Controllers;

use App\Models\MarkingTechnique;
use App\Models\Product;

class TechniqueController extends Controller
{
    public function index()
    {
        $techniques = MarkingTechnique::where('is_active', true)
            ->orderByDesc('quality_rating')
            ->get();

        return view('technique.index', [
            'techniques' => $techniques,
        ]);
    }

    public function show(string $seoUrl)
    {
        $technique = MarkingTechnique::where('seo_url', $seoUrl)
            ->where('is_active', true)
            ->firstOrFail();

        $products = $technique->compatibleProducts()
            ->active()
            ->with('colors')
            ->limit(8)
            ->get();

        return view('technique.show', [
            'technique' => $technique,
            'products' => $products,
        ]);
    }
}
