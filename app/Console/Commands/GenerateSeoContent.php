<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class GenerateSeoContent extends Command
{
    protected $signature = 'seo:generate
        {--supplier= : Filtrer par fournisseur}
        {--force : Ecraser les contenus existants}
        {--dry-run : Compter sans modifier}';

    protected $description = 'Genere des meta titres, descriptions et textes SEO uniques pour chaque produit';

    /** Mots-cles strategiques par categorie */
    private const CATEGORY_KEYWORDS = [
        't-shirts' => ['marquage t-shirt', 'personnalisation t-shirt', 'impression t-shirt', 'serigraphie t-shirt'],
        'polos' => ['broderie polo', 'polo personnalise', 'marquage polo', 'polo professionnel'],
        'sweats' => ['broderie sweat', 'transfert sweat', 'sweat personnalise', 'marquage sweat'],
        'chemises' => ['broderie chemise', 'chemise personnalisee', 'chemise entreprise'],
        'coupe-vent-softshell' => ['broderie softshell', 'marquage softshell', 'softshell personnalisee'],
        'manteaux-parkas' => ['broderie parka', 'marquage manteau', 'parka personnalisee'],
        'doudounes' => ['broderie doudoune', 'doudoune personnalisee', 'marquage doudoune'],
        'pantalons' => ['marquage pantalon', 'pantalon professionnel', 'pantalon personnalise'],
        'polaires' => ['broderie polaire', 'polaire personnalisee', 'marquage polaire'],
        'sport' => ['marquage t-shirt de sport', 'serigraphie t-shirt de sport', 'maillot personnalise', 'tenue sport personnalisee'],
        'vetements-de-travail' => ['vetement de travail', 'marquage vetement de travail', 'marquage textile de travail', 'vetement professionnel personnalise'],
        'accessoires' => ['marquage pochon', 'sac personnalise', 'accessoire textile personnalise'],
        'bio' => ['textile bio personnalise', 'coton bio brode', 'vetement eco-responsable'],
        'made-in-france' => ['textile made in france', 'vetement francais personnalise', 'broderie made in france'],
        'bebe' => ['textile bebe personnalise', 'body bebe brode', 'vetement bebe personnalise'],
    ];

    /** Techniques de marquage pour varier les descriptions */
    private const TECHNIQUES = [
        'broderie', 'serigraphie', 'impression numerique DTG', 'transfert DTF',
        'flocage', 'sublimation', 'transfert offset', 'marquage textile',
    ];

    /** Caracteristiques produit pour enrichir */
    private const QUALITIES = [
        'confortable', 'resistant', 'durable', 'respirant', 'leger',
        'premium', 'professionnel', 'moderne', 'elegante', 'pratique',
    ];

    private array $stats = ['meta_title' => 0, 'meta_description' => 0, 'seo_description' => 0, 'skipped' => 0];

    public function handle(): int
    {
        $supplier = $this->option('supplier');
        $force = $this->option('force');
        $dryRun = $this->option('dry-run');

        $query = Product::where('is_active', true)->with(['category', 'colors']);
        if ($supplier) {
            $query->where('supplier', $supplier);
        }

        $products = $query->get();
        $this->info($products->count() . ' produits a traiter');

        if ($dryRun) {
            $needsMeta = $products->filter(fn ($p) => ! $p->meta_title || ! $p->meta_description)->count();
            $this->info($needsMeta . ' produits sans meta complet');

            return self::SUCCESS;
        }

        $bar = $this->output->createProgressBar($products->count());
        $bar->start();

        foreach ($products as $product) {
            $bar->advance();
            $this->generateForProduct($product, $force);
        }

        $bar->finish();
        $this->newLine(2);

        $this->table(['Metrique', 'Total'], collect($this->stats)->map(fn ($v, $k) => [str_replace('_', ' ', ucfirst($k)), $v])->values()->toArray());

        return self::SUCCESS;
    }

    private function generateForProduct(Product $product, bool $force): void
    {
        $catSlug = $product->category?->slug ?? '';
        $catName = $product->category?->name ?? 'Textile';
        $parentCat = $product->category?->parent;
        $parentSlug = $parentCat?->slug ?? $catSlug;
        $parentName = $parentCat?->name ?? $catName;

        $keywords = self::CATEGORY_KEYWORDS[$parentSlug] ?? self::CATEGORY_KEYWORDS[$catSlug] ?? ['marquage textile', 'personnalisation textile'];
        $keyword = $keywords[crc32($product->name) % count($keywords)];
        $technique = self::TECHNIQUES[crc32($product->reference ?? '') % count(self::TECHNIQUES)];
        $quality = self::QUALITIES[crc32($product->id . $product->name) % count(self::QUALITIES)];

        $productType = $this->detectProductType($product->name, $catName);
        $colorCount = $product->colors->count();
        $colorText = $colorCount > 1 ? "Disponible en {$colorCount} coloris" : '';
        $materialText = $product->material ? "en {$product->material}" : '';
        $grammageText = $product->grammage ? "{$product->grammage}g/m²" : '';
        $supplierText = $product->supplier ?? '';

        // Meta title (max 60 chars)
        if (! $product->meta_title || $force) {
            $title = $this->generateMetaTitle($product, $productType, $keyword);
            $product->meta_title = Str::limit($title, 58, '');
            $this->stats['meta_title']++;
        }

        // Meta description (max 160 chars, unique)
        if (! $product->meta_description || $force) {
            $product->meta_description = $this->generateMetaDescription(
                $product, $productType, $keyword, $technique, $quality,
                $colorText, $materialText, $grammageText, $supplierText
            );
            $this->stats['meta_description']++;
        }

        // Description SEO enrichie (si description vide ou generique)
        $desc = strip_tags($product->description ?? '');
        if (strlen($desc) < 80 || $force) {
            $product->description = $this->generateSeoDescription(
                $product, $productType, $keyword, $technique, $quality,
                $colorText, $materialText, $grammageText, $catName, $supplierText
            );
            $this->stats['seo_description']++;
        }

        $product->save();
    }

    private function generateMetaTitle(Product $product, string $type, string $keyword): string
    {
        $name = $product->name;
        $templates = [
            "{$name} - {$type} personnalisable | Marquage Textile",
            "{$name} | {$keyword} | Marquage Textile",
            "{$type} {$name} personnalisable - Marquage Textile",
            "{$name} - {$keyword} professionnel",
        ];

        return $templates[crc32($product->id . 'title') % count($templates)];
    }

    private function generateMetaDescription(
        Product $product, string $type, string $keyword, string $technique,
        string $quality, string $colorText, string $materialText, string $grammageText, string $supplier
    ): string {
        $name = $product->name;
        $priceText = $product->display_price > 0 ? 'Des ' . number_format($product->display_price, 2, ',', '') . ' EUR HT' : '';

        $templates = [
            "Decouvrez le {$type} {$name} {$materialText} personnalisable par {$technique}. {$colorText}. {$priceText}. Livraison rapide en France.",
            "{$name} : {$type} {$quality} {$materialText} ideal pour le {$keyword}. {$colorText}. Devis gratuit sous 24h.",
            "{$type} {$name} par {$supplier}. Personnalisation par {$technique} {$materialText}. {$priceText}. {$colorText}.",
            "Achetez le {$name} {$materialText} {$grammageText}. {$keyword} de qualite. {$colorText}. Devis en ligne gratuit.",
            "{$name} - {$type} {$quality} pour professionnels. {$technique} disponible. {$colorText}. Livraison France.",
        ];

        $desc = $templates[crc32($product->id . 'desc') % count($templates)];

        return Str::limit(preg_replace('/\s+/', ' ', trim($desc)), 158, '');
    }

    private function generateSeoDescription(
        Product $product, string $type, string $keyword, string $technique,
        string $quality, string $colorText, string $materialText, string $grammageText,
        string $catName, string $supplier
    ): string {
        $name = $product->name;
        $existingDesc = strip_tags($product->description ?? '');

        $intro = "Le {$name} est un {$type} {$quality} {$materialText}";
        if ($grammageText) {
            $intro .= " d'un grammage de {$grammageText}";
        }
        $intro .= ", signe {$supplier}.";

        $marking = "Ce produit est personnalisable par {$technique}, ainsi que par d'autres techniques de marquage textile (broderie, serigraphie, transfert DTF, flocage). Ideal pour le {$keyword}, il convient parfaitement aux entreprises, associations et collectivites.";

        $specs = '';
        if ($colorText) {
            $specs .= "{$colorText}. ";
        }
        if ($product->cut) {
            $coupe = match ($product->cut) {
                'homme' => 'Coupe homme',
                'femme' => 'Coupe femme',
                'mixte' => 'Coupe mixte unisexe',
                'enfant' => 'Coupe enfant',
                default => '',
            };
            if ($coupe) {
                $specs .= "{$coupe}. ";
            }
        }

        $cta = "Demandez votre devis gratuit en ligne et recevez une reponse sous 24h. Livraison rapide partout en France.";

        // Si description existante, l'enrichir plutot que la remplacer
        if (strlen($existingDesc) > 80) {
            return "<p>{$existingDesc}</p><p>{$marking}</p><p>{$specs}{$cta}</p>";
        }

        return "<p>{$intro}</p><p>{$marking}</p><p>{$specs}{$cta}</p>";
    }

    private function detectProductType(string $name, string $catName): string
    {
        $n = strtolower($name . ' ' . $catName);

        $types = [
            'sweat a capuche' => '/hoodie|capuche|hooded/i',
            'sweat' => '/sweat|sweater/i',
            'polo' => '/polo/i',
            't-shirt' => '/t[\-\s]?shirt|tee\b|camiseta/i',
            'chemise' => '/chemise|camisa|shirt/i',
            'veste' => '/veste|jacket|softshell/i',
            'doudoune' => '/doudoune|puffer|matelass/i',
            'pantalon' => '/pantalon|pant\b/i',
            'short' => '/short|bermuda/i',
            'casquette' => '/casquette|cap\b/i',
            'bonnet' => '/bonnet|beanie/i',
            'sac' => '/sac|bag|tote/i',
            'tablier' => '/tablier|apron/i',
            'polaire' => '/polaire|fleece/i',
            'gilet' => '/gilet|bodywarmer/i',
            'blouse' => '/blouse|tunique/i',
            'combinaison' => '/combinaison/i',
            'serviette' => '/serviette|towel/i',
        ];

        foreach ($types as $type => $pattern) {
            if (preg_match($pattern, $n)) {
                return $type;
            }
        }

        return 'textile';
    }
}
