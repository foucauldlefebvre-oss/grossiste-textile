<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\MarkingTechnique;
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
        $urls->push(['loc' => route('devis'), 'priority' => '0.7', 'changefreq' => 'monthly']);

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

        // Technique SEO pages
        MarkingTechnique::active()->whereNotNull('seo_url')->get()->each(function ($technique) use ($urls) {
            $urls->push([
                'loc' => url('/' . $technique->seo_url),
                'lastmod' => $technique->updated_at->toW3cString(),
                'priority' => '0.8',
                'changefreq' => 'monthly',
            ]);
        });

        // Legal pages
        $urls->push(['loc' => url('/conditions-generales-de-vente'), 'priority' => '0.3', 'changefreq' => 'yearly']);
        $urls->push(['loc' => url('/politique-de-confidentialite'), 'priority' => '0.3', 'changefreq' => 'yearly']);
        $urls->push(['loc' => url('/mentions-legales'), 'priority' => '0.3', 'changefreq' => 'yearly']);

        $xml = view('sitemap', compact('urls'))->render();

        return response($xml, 200, ['Content-Type' => 'application/xml']);
    }
}
