<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function __invoke(): Response
    {
        $urls = collect();

        // Static pages
        $urls->push(['loc' => route('home'), 'priority' => '1.0', 'changefreq' => 'weekly']);
        $urls->push(['loc' => route('catalogue.index'), 'priority' => '0.9', 'changefreq' => 'weekly']);
        // TODO 2b: route('devis') retirée (système devis supprimé)

        // Categories
        Category::active()->get()->each(function ($category) use ($urls) {
            $urls->push([
                'loc' => route('catalogue.category', $category),
                'lastmod' => $category->updated_at->toW3cString(),
                'priority' => '0.8',
                'changefreq' => 'weekly',
            ]);
        });

        // Products
        Product::active()->with('category.parent')->get()->each(function ($product) use ($urls) {
            $urls->push([
                'loc' => $product->url,
                'lastmod' => $product->updated_at->toW3cString(),
                'priority' => '0.8',
                'changefreq' => 'weekly',
            ]);
        });

        // TODO 2b: bloc Technique SEO pages supprimé (techniques dégagées)

        // Legal pages
        $urls->push(['loc' => url('/conditions-generales-de-vente'), 'priority' => '0.3', 'changefreq' => 'yearly']);
        $urls->push(['loc' => url('/politique-de-confidentialite'), 'priority' => '0.3', 'changefreq' => 'yearly']);
        $urls->push(['loc' => url('/mentions-legales'), 'priority' => '0.3', 'changefreq' => 'yearly']);

        $xml = view('sitemap', compact('urls'))->render();

        return response($xml, 200, ['Content-Type' => 'application/xml']);
    }
}
