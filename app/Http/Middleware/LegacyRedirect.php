<?php

namespace App\Http\Middleware;

use App\Models\Category;
use App\Models\Product;
use App\Models\SlugRedirect;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LegacyRedirect
{
    /** Language prefixes used by PrestaShop */
    private const LANG_PREFIXES = ['fr', 'en', 'es', 'de', 'it', 'pt', 'nl', 'pl', 'ro', 'ar', 'gb'];

    public function handle(Request $request, Closure $next): Response
    {
        $path = $request->getPathInfo();

        // Slug redirects (old product/category slugs → new ones)
        if (preg_match('#^/produit/(.+)$#', $path, $m)) {
            $redirect = SlugRedirect::where('old_slug', $m[1])->where('type', 'product')->first();
            if ($redirect) {
                $p = Product::where('slug', $redirect->new_slug)->first();
                return redirect($p ? $p->url : '/', 301);
            }
        }

        // ═══ PrestaShop product URLs (all languages) ═══
        // Pattern: /{lang}/accueil/{id}-{slug}.html or /{lang}/{category}/{id}-{slug}.html
        if (preg_match('#^/(?:' . implode('|', self::LANG_PREFIXES) . ')/(?:[^/]+/)*(\d+)-[^/]+\.html$#', $path, $m)) {
            $product = Product::where('prestashop_id', (int) $m[1])->first();
            if ($product) {
                return redirect($product->url, 301);
            }
            $redirect = SlugRedirect::where('old_slug', $m[1])->where('type', 'prestashop')->first();
            if ($redirect) {
                $p = Product::where('slug', $redirect->new_slug)->first();
                return redirect($p ? $p->url : '/', 301);
            }
        }

        // ═══ PrestaShop category URLs (all languages) ═══
        // Pattern: /{lang}/{id}-{slug} (no .html)
        if (preg_match('#^/(?:' . implode('|', self::LANG_PREFIXES) . ')/(\d+)-[^/]+$#', $path, $m)) {
            $category = Category::where('prestashop_id', (int) $m[1])->first();
            if ($category) {
                return redirect(route('catalogue.category', $category->slug), 301);
            }
            // Maybe it's a product ID in category format
            $product = Product::where('prestashop_id', (int) $m[1])->first();
            if ($product) {
                return redirect($product->url, 301);
            }
        }

        // ═══ PrestaShop homepage + language homepages ═══
        // /fr, /en, /fr/, /en/2-accueil, /fr/accueil, etc.
        if (preg_match('#^/(?:' . implode('|', self::LANG_PREFIXES) . ')(?:/(?:\d+-)?accueil)?/?$#', $path)) {
            return redirect('/', 301);
        }

        // ═══ PrestaShop CMS/content pages (all languages) ═══
        if (preg_match('#^/(?:' . implode('|', self::LANG_PREFIXES) . ')/content/#', $path)) {
            return redirect('/', 301);
        }

        // ═══ PrestaShop module URLs ═══
        if (preg_match('#^/(?:' . implode('|', self::LANG_PREFIXES) . ')/module/#', $path)) {
            return redirect('/', 301);
        }

        // ═══ PrestaShop contact/account pages ═══
        if (preg_match('#^/(?:' . implode('|', self::LANG_PREFIXES) . ')/(?:contactez-nous|contact-us|nous-contacter)#', $path)) {
            return redirect(route('devis'), 301);
        }
        if (preg_match('#^/(?:' . implode('|', self::LANG_PREFIXES) . ')/(?:mon-compte|my-account|connexion|login|commande|order)#', $path)) {
            return redirect(route('login'), 301);
        }

        // ═══ PrestaShop supplier/manufacturer pages ═══
        if (preg_match('#^/(?:' . implode('|', self::LANG_PREFIXES) . ')/(?:fournisseur|supplier|fabricant|manufacturer)#', $path)) {
            return redirect('/catalogue', 301);
        }

        // ═══ PrestaShop search ═══
        if (preg_match('#^/(?:' . implode('|', self::LANG_PREFIXES) . ')/recherche#', $path)) {
            return redirect('/catalogue', 301);
        }

        // ═══ PrestaShop promo/new/bestseller pages ═══
        if (preg_match('#^/(?:' . implode('|', self::LANG_PREFIXES) . ')/(?:promotions|soldes|nouveautes|meilleures-ventes|best-sales|new-products|prices-drop)#', $path)) {
            return redirect('/catalogue', 301);
        }

        // ═══ Catch-all: any remaining /{lang}/... URL → homepage ═══
        if (preg_match('#^/(?:' . implode('|', self::LANG_PREFIXES) . ')/.+#', $path)) {
            // Try to extract a PrestaShop ID from anywhere in the URL
            if (preg_match('#/(\d+)-#', $path, $m)) {
                $id = (int) $m[1];
                $product = Product::where('prestashop_id', $id)->first();
                if ($product) {
                    return redirect($product->url, 301);
                }
                $category = Category::where('prestashop_id', $id)->first();
                if ($category) {
                    return redirect(route('catalogue.category', $category->slug), 301);
                }
            }
            // Ultimate fallback: redirect to homepage
            return redirect('/', 301);
        }

        return $next($request);
    }
}
