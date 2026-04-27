<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Console\Command;

class GenerateCategoryDescriptions extends Command
{
    protected $signature = 'seo:category-descriptions
        {--force : Overwrite existing descriptions}
        {--dry-run : Show descriptions without saving}
        {--category= : Only process a specific category ID}';

    protected $description = 'Generate rich SEO descriptions for all product categories';

    public function handle(): int
    {
        $query = Category::query()->orderBy('parent_id')->orderBy('sort_order');

        if ($catId = $this->option('category')) {
            $query->where('id', $catId);
        }

        $categories = $query->get();
        $force = $this->option('force');
        $dryRun = $this->option('dry-run');
        $updated = 0;
        $skipped = 0;

        foreach ($categories as $category) {
            // Skip categories without products
            $directCount = Product::where('category_id', $category->id)->where('is_active', true)->count();
            $childIds = Category::where('parent_id', $category->id)->pluck('id');
            $childCount = $childIds->count() > 0
                ? Product::whereIn('category_id', $childIds)->where('is_active', true)->count()
                : 0;
            $totalProducts = $directCount + $childCount;

            if ($totalProducts === 0) {
                continue;
            }

            // Skip if already has a long description
            if (! $force && $category->description && strlen($category->description) > 100) {
                $skipped++;
                continue;
            }

            $description = $this->buildDescription($category, $directCount, $childCount);

            if ($dryRun) {
                $this->newLine();
                $this->info("=== {$category->name} (ID {$category->id}) ===");
                $this->line($description);
                $this->newLine();
            } else {
                $category->update(['description' => $description]);
                $this->info("✓ {$category->name} — " . strlen($description) . " chars");
            }

            $updated++;
        }

        $this->newLine();
        $this->info("Terminé : {$updated} descriptions générées, {$skipped} ignorées.");

        return self::SUCCESS;
    }

    private function buildDescription(Category $category, int $directCount, int $childCount): string
    {
        $name = $category->name;
        $slug = $category->slug;
        $parent = $category->parent;
        $isParent = Category::where('parent_id', $category->id)->exists();
        $totalProducts = $directCount + $childCount;

        // Gather product data for this category
        $productIds = Product::where('is_active', true);
        if ($isParent) {
            $childIds = Category::where('parent_id', $category->id)->pluck('id')->push($category->id);
            $productIds = $productIds->whereIn('category_id', $childIds);
        } else {
            $productIds = $productIds->where('category_id', $category->id);
        }

        $products = $productIds->get(['id', 'name', 'supplier', 'material', 'compatible_techniques', 'grammage', 'supplier_price']);

        // Brands
        $brands = $products->pluck('supplier')->filter()->unique()->sort()->values();

        // Materials
        $materials = $products->pluck('material')->filter()->unique()
            ->map(fn ($m) => strtolower(trim($m)))
            ->unique()->sort()->values();

        // Grammage range
        $grammages = $products->pluck('grammage')->filter()->sort();
        $gramMin = $grammages->first();
        $gramMax = $grammages->last();

        // Price range
        $prices = $products->pluck('supplier_price')->filter()->map(fn ($p) => (float) $p)->filter(fn ($p) => $p > 0)->sort();
        $priceMin = $prices->first();
        $priceMax = $prices->last();

        // Techniques
        $allTechniques = [];
        foreach ($products as $p) {
            if (is_array($p->compatible_techniques)) {
                foreach ($p->compatible_techniques as $t) {
                    $allTechniques[$t] = ($allTechniques[$t] ?? 0) + 1;
                }
            }
        }
        arsort($allTechniques);
        $techniqueIds = array_keys(array_slice($allTechniques, 0, 6, true));
        $techniqueNames = \App\Models\MarkingTechnique::whereIn('id', $techniqueIds)
            ->where('is_active', true)
            ->pluck('name')->toArray();

        // Sub-categories
        $subCats = $isParent
            ? Category::where('parent_id', $category->id)->orderBy('sort_order')->pluck('name')->toArray()
            : [];

        // Build HTML
        $html = '';

        // Title H2
        $titleKeyword = $this->getTitleKeyword($name, $parent);
        $html .= "<h2>{$titleKeyword}</h2>\n";

        // Intro paragraph
        $html .= "<p>" . $this->getIntroParagraph($name, $parent, $totalProducts, $brands) . "</p>\n\n";

        // Sub-categories section
        if (! empty($subCats)) {
            $html .= "<h3>Nos gammes de " . mb_strtolower($name) . "</h3>\n";
            $html .= "<p>Explorez notre selection de " . mb_strtolower($name) . " a travers nos differentes gammes : ";
            $html .= implode(', ', array_map(fn ($s) => "<strong>{$s}</strong>", $subCats));
            $html .= ". Chaque gamme repond a des besoins specifiques, que ce soit pour un usage quotidien, professionnel ou evenementiel.</p>\n\n";
        }

        // Brands section
        if ($brands->count() > 1) {
            $html .= "<h3>Les marques disponibles</h3>\n";
            $html .= "<p>Nous travaillons avec les meilleurs fabricants du marche pour vous garantir qualite et durabilite : ";
            $html .= implode(', ', $brands->map(fn ($b) => "<strong>{$b}</strong>")->toArray());
            $html .= ". " . $this->getBrandQualityNote($name) . "</p>\n\n";
        }

        // Materials section
        if ($materials->count() > 0) {
            $html .= "<h3>Matieres et compositions</h3>\n";
            $html .= "<p>" . $this->getMaterialParagraph($name, $materials, $gramMin, $gramMax) . "</p>\n\n";
        }

        // Techniques section
        if (! empty($techniqueNames)) {
            $html .= "<h3>Techniques de marquage disponibles</h3>\n";
            $html .= "<p>Nos " . mb_strtolower($name) . " sont personnalisables par ";
            $html .= $this->formatTechniqueList($techniqueNames);
            $html .= ". " . $this->getTechniqueNote($name, $techniqueNames) . "</p>\n\n";
        }

        // Usage section
        $html .= "<h3>Pour quels usages ?</h3>\n";
        $html .= "<p>" . $this->getUsageParagraph($name, $parent) . "</p>\n\n";

        // CTA
        $html .= "<p><strong>Besoin de " . mb_strtolower($name) . " personnalises ?</strong> ";
        $html .= "Demandez un devis gratuit en ligne. Notre equipe vous accompagne dans le choix des produits et des techniques de marquage les plus adaptees a votre projet. ";
        $html .= "Livraison rapide partout en France et en Europe.</p>";

        return $html;
    }

    private function getTitleKeyword(string $name, ?Category $parent): string
    {
        $lower = mb_strtolower($name);

        if (str_contains($lower, 'personnalis')) {
            return $name;
        }

        $suffix = match (true) {
            str_contains($lower, 'casquette'), str_contains($lower, 'bonnet'),
            str_contains($lower, 'bob'), str_contains($lower, 'chapeau') => 'personnalisables',

            str_contains($lower, 'sac'), str_contains($lower, 'valise'),
            str_contains($lower, 'trousse'), str_contains($lower, 'portefeuille') => 'personnalisables',

            str_contains($lower, 'tablier'), str_contains($lower, 'serviette'),
            str_contains($lower, 'couverture'), str_contains($lower, 'nappe') => 'personnalisables',

            str_contains($lower, 'body'), str_contains($lower, 'bavoir'),
            str_contains($lower, 'pyjama') => 'personnalisables',

            str_contains($lower, 'chasuble'), str_contains($lower, 'dossard') => 'personnalisables',

            str_contains($lower, 'chaussure'), str_contains($lower, 'chaussette') => 'personnalisables',

            default => 'personnalisables',
        };

        // Avoid "T-shirts col rond coton personnalisables" being too long
        if (strlen($name) > 30) {
            return $name . " — personnalisation textile";
        }

        return $name . " {$suffix}";
    }

    private function getIntroParagraph(string $name, ?Category $parent, int $totalProducts, $brands): string
    {
        $lower = mb_strtolower($name);
        $parentName = $parent ? mb_strtolower($parent->name) : '';

        $intro = "Decouvrez notre catalogue de <strong>{$totalProducts} " . mb_strtolower($name) . "</strong> ";
        $intro .= "disponibles a la personnalisation. ";

        if ($parent) {
            $intro .= "Dans notre gamme " . $parentName . ", ";
            $intro .= "les " . $lower . " sont parmi les produits les plus demandes pour la personnalisation textile. ";
        } else {
            $intro .= "Que vous soyez une entreprise, une association ou un organisateur d'evenements, ";
            $intro .= "nos " . $lower . " sont concus pour etre personnalises selon vos besoins. ";
        }

        if ($brands->count() >= 3) {
            $intro .= "Nous proposons les meilleures marques du marche, dont " . $brands->take(3)->implode(', ') . " et bien d'autres.";
        } elseif ($brands->count() > 0) {
            $intro .= "Des produits de qualite professionnelle signes " . $brands->implode(' et ') . ".";
        }

        return $intro;
    }

    private function getBrandQualityNote(string $name): string
    {
        $lower = mb_strtolower($name);

        return match (true) {
            str_contains($lower, 't-shirt'), str_contains($lower, 'polo') =>
                "Chaque marque propose des coupes et grammages differents pour repondre a tous les budgets et exigences de confort.",
            str_contains($lower, 'sweat'), str_contains($lower, 'polaire') =>
                "Des modeles robustes et confortables, ideaux pour le froid et la mi-saison.",
            str_contains($lower, 'sac'), str_contains($lower, 'valise') =>
                "Des produits resistants, disponibles dans une grande variete de formats et de styles.",
            str_contains($lower, 'travail'), str_contains($lower, 'pro') =>
                "Des vetements conformes aux normes professionnelles, alliant confort et securite.",
            default =>
                "Des produits selectionnes pour leur qualite de fabrication et leur durabilite.",
        };
    }

    private function getMaterialParagraph(string $name, $materials, ?int $gramMin, ?int $gramMax): string
    {
        $lower = mb_strtolower($name);
        $text = "Nos " . $lower . " sont disponibles en ";

        // Simplify material list
        $simplified = $materials->map(function ($m) {
            if (str_contains($m, 'coton')) return 'coton';
            if (str_contains($m, 'polyester')) return 'polyester';
            if (str_contains($m, 'nylon')) return 'nylon';
            if (str_contains($m, 'laine')) return 'laine';
            if (str_contains($m, 'acrylique')) return 'acrylique';
            if (str_contains($m, 'viscose')) return 'viscose';
            if (str_contains($m, 'bambou')) return 'bambou';
            if (str_contains($m, 'recyclé') || str_contains($m, 'recycle')) return 'matieres recyclees';
            if (str_contains($m, 'bio') || str_contains($m, 'organique')) return 'coton biologique';
            return null;
        })->filter()->unique()->values();

        if ($simplified->count() > 0) {
            $text .= "differentes matieres : <strong>" . $simplified->implode('</strong>, <strong>') . "</strong>";
        } else {
            $text .= "plusieurs compositions et matieres";
        }

        if ($gramMin && $gramMax && $gramMin !== $gramMax) {
            $text .= ", avec des grammages allant de <strong>{$gramMin} g/m²</strong> a <strong>{$gramMax} g/m²</strong>";
        } elseif ($gramMin) {
            $text .= " (grammage {$gramMin} g/m²)";
        }

        $text .= ". ";

        // Add material-specific note
        if ($simplified->contains('coton') && $simplified->contains('polyester')) {
            $text .= "Le coton offre douceur et respirabilite, tandis que le polyester apporte resistance et sechage rapide. Les melanges coton-polyester combinent le meilleur des deux matieres.";
        } elseif ($simplified->contains('coton')) {
            $text .= "Le coton garantit un confort optimal au porter et offre un excellent rendu pour les techniques d'impression et de broderie.";
        } elseif ($simplified->contains('polyester')) {
            $text .= "Le polyester est ideal pour les usages sportifs et professionnels grace a ses proprietes de sechage rapide et de resistance.";
        }

        return $text;
    }

    private function formatTechniqueList(array $techniques): string
    {
        if (count($techniques) <= 2) {
            return implode(' et ', array_map(fn ($t) => "<strong>" . mb_strtolower($t) . "</strong>", $techniques));
        }

        $last = array_pop($techniques);
        return implode(', ', array_map(fn ($t) => "<strong>" . mb_strtolower($t) . "</strong>", $techniques))
            . " et <strong>" . mb_strtolower($last) . "</strong>";
    }

    private function getTechniqueNote(string $name, array $techniques): string
    {
        $lower = mb_strtolower($name);
        $techLower = array_map('mb_strtolower', $techniques);

        if (in_array('broderie', $techLower)) {
            return "La broderie est particulierement recommandee pour un rendu haut de gamme et durable dans le temps.";
        }

        if (in_array('sérigraphie', $techLower)) {
            return "La serigraphie offre un excellent rapport qualite/prix pour les grandes series.";
        }

        return "Notre equipe vous conseille sur la technique la plus adaptee a votre projet et votre budget.";
    }

    private function getUsageParagraph(string $name, ?Category $parent): string
    {
        $lower = mb_strtolower($name);
        $parentLower = $parent ? mb_strtolower($parent->name) : '';

        return match (true) {
            str_contains($lower, 'sport') || str_contains($parentLower, 'sport') =>
                "Nos " . $lower . " sont parfaits pour les clubs sportifs, les associations, les equipes d'entreprise et les evenements sportifs. " .
                "Personnalisez-les avec votre logo, le nom de votre equipe ou un numero pour creer une tenue complete et professionnelle.",

            str_contains($lower, 'travail') || str_contains($parentLower, 'travail') || str_contains($lower, 'pro') =>
                "Destines aux professionnels, nos " . $lower . " sont concus pour resister aux conditions de travail exigeantes. " .
                "Personnalisez-les avec votre logo d'entreprise pour renforcer votre image de marque sur le terrain.",

            str_contains($lower, 'bébé') || str_contains($parentLower, 'bébé') || str_contains($lower, 'bebe') =>
                "Nos " . $lower . " sont ideaux pour les creches, les cadeaux de naissance personnalises, les evenements familiaux ou simplement pour offrir un vetement unique a votre enfant.",

            str_contains($lower, 'bio') || str_contains($parentLower, 'bio') =>
                "Nos " . $lower . " en coton biologique certifie repondent aux attentes des entreprises et associations engagees dans une demarche eco-responsable. " .
                "Un choix ethique sans compromis sur la qualite de personnalisation.",

            str_contains($lower, 'made in france') =>
                "Nos " . $lower . " fabriques en France garantissent une qualite premium et un circuit court. " .
                "Ideal pour les entreprises souhaitant valoriser le savoir-faire francais aupres de leurs clients et collaborateurs.",

            str_contains($lower, 'sac') || str_contains($lower, 'valise') || str_contains($lower, 'trousse') =>
                "Nos " . $lower . " personnalises sont des supports de communication puissants : salons professionnels, goodies d'entreprise, cadeaux clients ou boutiques en ligne. " .
                "Un investissement durable et visible au quotidien.",

            str_contains($lower, 'casquette') || str_contains($lower, 'bonnet') || str_contains($lower, 'bob') || str_contains($lower, 'chapeau') =>
                "Nos " . $lower . " personnalises sont ideaux comme goodies d'entreprise, accessoires d'equipe ou produits merchandising. " .
                "Un accessoire visible et apprecie qui met en valeur votre marque.",

            str_contains($lower, 'tablier') || str_contains($lower, 'cuisine') =>
                "Nos " . $lower . " personnalises conviennent parfaitement aux restaurants, hotels, ecoles de cuisine et aux evenements culinaires. " .
                "Brodez votre logo pour une image professionnelle soignee.",

            str_contains($lower, 'serviette') || str_contains($lower, 'bain') || str_contains($lower, 'peignoir') =>
                "Nos " . $lower . " personnalises sont prisees par les hotels, spas, clubs de sport et comme cadeaux d'entreprise haut de gamme. " .
                "La broderie offre un rendu luxueux et durable.",

            str_contains($lower, 'polo') =>
                "Nos " . $lower . " personnalises sont le choix privilegie des entreprises, des equipes commerciales et des clubs de golf. " .
                "Un vetement a la fois elegant et decontracte, parfait pour porter votre image de marque au quotidien.",

            str_contains($lower, 't-shirt') =>
                "Nos " . $lower . " personnalises sont les incontournables de la communication textile : evenements, team building, lancement de produit, festival ou uniforme d'equipe. " .
                "Un support efficace et economique pour faire connaitre votre marque.",

            str_contains($lower, 'sweat') =>
                "Nos " . $lower . " personnalises sont tres demandes pour les equipes d'entreprise, les promotions d'ecole, les clubs et associations. " .
                "Confortables et durables, ils sont portes au quotidien et offrent une visibilite maximale a votre marque.",

            str_contains($lower, 'chemise') =>
                "Nos " . $lower . " personnalisees apportent une touche professionnelle et elegante a vos equipes. " .
                "Ideales pour les hotesses d'accueil, les commerciaux, le personnel hotelier ou toute entreprise souhaitant une tenue soignee.",

            str_contains($lower, 'doudoune') || str_contains($lower, 'parka') || str_contains($lower, 'manteau') =>
                "Nos " . $lower . " personnalises sont parfaits pour les equipes travaillant en exterieur, les chantiers, la logistique ou simplement comme cadeau d'entreprise premium. " .
                "Un vetement chaud et personnalise qui renforce l'esprit d'equipe.",

            default =>
                "Nos " . $lower . " personnalises conviennent a de nombreux usages : " .
                "vetements d'entreprise, uniformes d'equipe, goodies evenementiels, cadeaux clients, merchandising ou tenues d'association. " .
                "Quel que soit votre projet, nous avons la solution adaptee.",
        };
    }
}
