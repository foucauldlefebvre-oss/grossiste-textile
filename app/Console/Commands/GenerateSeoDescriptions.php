<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\MarkingTechnique;
use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class GenerateSeoDescriptions extends Command
{
    protected $signature = 'seo:descriptions
        {--category= : Only process a specific category ID}
        {--limit=0 : Limit number of products}
        {--dry-run : Show without saving}';

    protected $description = 'Generate unique SEO-optimized descriptions for all products';

    private array $techniqueNames = [];
    private array $categoryKeywords = [];

    // Target SEO keywords to weave in naturally
    private array $globalKeywords = [
        'marquage textile', 'personnalisation textile', 'marquage en série',
        'textile professionnel', 'vêtement personnalisé', 'broderie',
        'sérigraphie', 'transfert', 'impression textile',
    ];

    // Category-specific keyword mapping
    private array $catKeywordMap = [
        't-shirt' => ['marquage t-shirt', 'sérigraphie t-shirt', 'personnalisation t-shirt', 't-shirt personnalisé'],
        'polo' => ['polo personnalisé', 'polo brodé', 'polo entreprise', 'marquage polo'],
        'sweat' => ['sweat brodé', 'sweat personnalisé', 'broderie sweat', 'transfert sweat', 'sweat d\'entreprise'],
        'chemise' => ['chemise brodée', 'chemise entreprise', 'broderie chemise'],
        'doudoune' => ['broderie doudoune', 'doudoune personnalisée', 'doudoune entreprise'],
        'softshell' => ['broderie softshell', 'softshell personnalisée', 'softshell entreprise'],
        'polaire' => ['polaire brodée', 'polaire personnalisée', 'broderie polaire'],
        'pantalon' => ['pantalon de travail', 'vêtement de travail', 'marquage vêtement de travail'],
        'sport' => ['marquage t-shirt de sport', 'maillot personnalisé', 'tenue de sport personnalisée', 't-shirt polyester'],
        'sac' => ['sac personnalisé', 'marquage pochon', 'tote bag personnalisé', 'sac publicitaire'],
        'casquette' => ['casquette brodée', 'casquette personnalisée', 'broderie casquette'],
        'tablier' => ['tablier personnalisé', 'tablier brodé', 'tablier professionnel'],
        'serviette' => ['serviette brodée', 'serviette personnalisée', 'peignoir brodé'],
        'travail' => ['vêtement de travail', 'marquage textile de travail', 'textile professionnel', 'vêtement professionnel personnalisé'],
        'haute_visibilite' => ['gilet haute visibilité', 'vêtement haute visibilité personnalisé'],
        'ignifuge' => ['vêtement ignifuge', 'vêtement de protection'],
        'bio' => ['textile bio', 'coton bio personnalisé', 'vêtement écoresponsable'],
        'bebe' => ['body bébé personnalisé', 'vêtement bébé brodé'],
    ];

    public function handle(): int
    {
        ini_set('memory_limit', '1G');

        $this->techniqueNames = MarkingTechnique::where('is_active', true)
            ->pluck('name', 'id')->toArray();

        $dryRun = $this->option('dry-run');
        $limit = (int) $this->option('limit');
        $catId = $this->option('category');

        if ($dryRun) {
            $this->warn('=== DRY RUN ===');
        }

        $query = Product::where('is_active', true)->with(['category', 'colors']);

        if ($catId) {
            $query->where('category_id', $catId);
        }

        if ($limit > 0) {
            $query->limit($limit);
        }

        $products = $query->get();
        $this->info("Processing {$products->count()} products...");

        $bar = $this->output->createProgressBar($products->count());
        $bar->start();

        $updated = 0;

        foreach ($products as $product) {
            $newDesc = $this->generateDescription($product);
            $metaTitle = $this->generateMetaTitle($product);
            $metaDesc = $this->generateMetaDescription($product);

            if ($dryRun) {
                if ($updated < 3) {
                    $this->newLine();
                    $this->line("--- {$product->name} ({$product->reference}) ---");
                    $this->line("META TITLE: {$metaTitle}");
                    $this->line("META DESC: {$metaDesc}");
                    $this->line(Str::limit(strip_tags($newDesc), 300));
                }
            } else {
                $product->update([
                    'description' => $newDesc,
                    'meta_title' => $metaTitle,
                    'meta_description' => $metaDesc,
                ]);
            }

            $updated++;
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        $this->info("Updated: {$updated} products");

        if ($dryRun) {
            $this->warn('Dry run — no changes saved.');
        }

        return 0;
    }

    private function generateDescription(Product $product): string
    {
        $name = $product->name;
        $supplier = $product->supplier;
        $material = $product->material ?? '';
        $grammage = $product->grammage;
        $cut = $product->cut;
        $cat = $product->category;
        $catName = $cat?->name ?? '';
        $parentCatName = $cat?->parent?->name ?? '';
        $existingDesc = strip_tags($product->description ?? '');
        $techniques = $product->compatible_techniques ?? [];
        $techNames = array_filter(array_map(fn ($id) => $this->techniqueNames[$id] ?? null, $techniques));
        $colors = $product->colors;
        $colorCount = $colors->count();
        $price = (float) $product->supplier_price;

        // Determine price positioning
        $priceLevel = $this->getPriceLevel($product);

        // Get relevant keywords
        $keywords = $this->getRelevantKeywords($catName, $parentCatName);

        // Extract key info from existing description
        $features = $this->extractFeatures($existingDesc);

        $type = $this->getProductTypeLabel($product);
        $cleanName = $this->cleanProductName($product);
        $title = $this->buildTitle($type, $cleanName);

        $parts = [];

        // ── H2: descriptive product title ──
        $h2 = $this->generateH2($product, $keywords[0] ?? 'textile personnalisé', $priceLevel);
        $parts[] = "<h2>{$h2}</h2>";

        // ── Intro paragraph ──
        $parts[] = '<p>' . $this->generateIntroParagraph($product, $catName, $parentCatName, $priceLevel, $keywords) . '</p>';

        // ── H3: Caractéristiques & composition ──
        if ($material || $grammage || ! empty($features)) {
            $parts[] = '<h3>Caractéristiques et composition du ' . mb_strtolower($type) . ' ' . $cleanName . '</h3>';
            $parts[] = '<p>' . $this->generateFeaturesParagraph($product, $material, $grammage, $features, $cut) . '</p>';

            // Extra technical details from original description
            if (! empty($features)) {
                $parts[] = '<ul>';
                foreach (array_slice($features, 0, 5) as $feat) {
                    $parts[] = '<li>' . ucfirst(trim($feat)) . '</li>';
                }
                $parts[] = '</ul>';
            }
        }

        // ── H3: Coloris disponibles ──
        if ($colorCount > 0) {
            $parts[] = '<h3>Les coloris du ' . mb_strtolower($title) . '</h3>';
            if ($colorCount > 8) {
                $colorSample = $colors->take(10)->pluck('name')->implode(', ');
                $parts[] = "<p>Ce modèle est décliné en <strong>{$colorCount} coloris</strong> pour s'adapter à toutes vos chartes graphiques : {$colorSample}, et bien d'autres. Que vous recherchiez des teintes classiques (noir, blanc, marine) ou des couleurs vives pour vous démarquer, ce {$type} répond à tous les besoins de <strong>personnalisation textile</strong>.</p>";
            } else {
                $colorList = $colors->pluck('name')->implode(', ');
                $parts[] = "<p>Disponible en <strong>{$colorCount} coloris</strong> : {$colorList}. Chaque couleur est pensée pour mettre en valeur votre logo ou visuel lors du <strong>marquage</strong>.</p>";
            }
        }

        // ── H3: Techniques de marquage ──
        if (! empty($techNames)) {
            $keyword = $keywords[array_rand($keywords)] ?? 'marquage textile';
            $parts[] = "<h3>Personnalisation et {$keyword}</h3>";
            $parts[] = '<p>' . $this->generateMarkingParagraph($product, $techNames, $catName, $keywords) . '</p>';

            // Detail per technique
            $techDetails = [];
            if (in_array(1, $techniques)) {
                $techDetails[] = 'La <strong>broderie</strong> offre un rendu haut de gamme avec un relief élégant, résistant à des centaines de lavages industriels.';
            }
            if (in_array(2, $techniques)) {
                $techDetails[] = 'La <strong>sérigraphie</strong> est la technique idéale pour les grandes séries à budget maîtrisé, avec un rendu net et des couleurs éclatantes.';
            }
            if (in_array(11, $techniques)) {
                $techDetails[] = 'Le <strong>transfert sérigraphique</strong> associe qualité premium et toucher léger, parfait pour un logo monochrome avec un rendu professionnel.';
            }
            if (in_array(10, $techniques)) {
                $techDetails[] = 'Le <strong>transfert offset</strong> permet de reproduire des visuels multicolores ou en quadrichromie avec une grande fidélité.';
            }
            if (in_array(6, $techniques)) {
                $techDetails[] = 'Le <strong>transfert DTF</strong> s\'adapte à tous les supports et permet des impressions détaillées à petit prix.';
            }
            if (! empty($techDetails)) {
                $parts[] = '<p>' . implode(' ', array_slice($techDetails, 0, 3)) . '</p>';
            }
        }

        // ── H3: Tailles disponibles ──
        $sizes = $product->sizes ?? collect();
        $uniqueSizes = $sizes->pluck('size')->unique();
        if ($uniqueSizes->count() > 0) {
            $sizeList = $uniqueSizes->sort(function ($a, $b) {
                $order = ['XXS' => 1, 'XS' => 2, 'S' => 3, 'M' => 4, 'L' => 5, 'XL' => 6, '2XL' => 7, 'XXL' => 7, '3XL' => 8, '4XL' => 9, '5XL' => 10];
                return ($order[$a] ?? 99) <=> ($order[$b] ?? 99);
            })->values();
            $parts[] = '<h3>Tailles disponibles</h3>';
            $parts[] = "<p>Le {$title} est disponible du <strong>{$sizeList->first()}</strong> au <strong>{$sizeList->last()}</strong> ({$sizeList->implode(', ')}). Notre gamme de tailles étendue permet d'habiller toutes vos équipes, du plus petit au plus grand.</p>";
        }

        // ── H3: À qui s'adresse ce produit ──
        $parts[] = '<h3>Pour qui ?</h3>';
        $parts[] = '<p>' . $this->generateUsageParagraph($product, $catName, $parentCatName, $priceLevel) . '</p>';

        // ── Price positioning paragraph ──
        $parts[] = '<p>' . match ($priceLevel) {
            'budget' => "Avec son <strong>prix grossiste</strong> compétitif, le {$title} est le choix malin pour les commandes en volume. Idéal pour le <strong>marquage textile pas cher</strong> sans compromis sur la qualité. Nos tarifs dégressifs rendent la personnalisation accessible dès les petites séries.",
            'premium' => "Produit premium, le {$title} justifie son positionnement par une <strong>qualité de confection supérieure</strong> et des finitions soignées. Un investissement durable pour l'image de votre marque.",
            default => "Le {$title} offre un <strong>excellent rapport qualité-prix</strong> pour la personnalisation en série. Tarifs dégressifs disponibles sur devis.",
        } . '</p>';

        // ── CTA ──
        $parts[] = '<p><strong>Commandez votre ' . mb_strtolower($title) . ' personnalisé</strong> : sélectionnez vos coloris et tailles ci-dessus, puis configurez votre marquage dans le panier devis. Notre équipe vous recontacte sous 24h avec une offre détaillée. <strong>Livraison rapide</strong> en France et en Europe.</p>';

        return implode("\n", $parts);
    }

    /**
     * Build a descriptive product type label from category.
     * e.g. "T-shirt col rond", "Sweat à capuche", "Polo manches longues"
     */
    private function getProductTypeLabel(Product $product): string
    {
        $catName = $product->category?->name ?? '';
        $parentName = $product->category?->parent?->name ?? '';
        $lower = mb_strtolower($catName);
        $parentLower = mb_strtolower($parentName);

        // Map category to product type description
        $typeMap = [
            't-shirts polyester' => 'T-shirt polyester',
            't-shirts col rond' => 'T-shirt col rond',
            't-shirts col v' => 'T-shirt col V',
            't-shirts manches longues' => 'T-shirt manches longues',
            't-shirts bi' => 'T-shirt bicolore',
            'débardeurs' => 'Débardeur',
            'polos manches courtes' => 'Polo',
            'polos manches longues' => 'Polo manches longues',
            'polos rugby' => 'Polo rugby',
            'polos sport' => 'Polo de sport',
            'polos bi' => 'Polo bicolore',
            'polos pro' => 'Polo professionnel',
            'sweats col rond' => 'Sweat col rond',
            'sweats à capuche' => 'Sweat à capuche',
            'sweats zippés à capuche' => 'Sweat zippé à capuche',
            'sweats zippés' => 'Sweat zippé',
            'teddys' => 'Teddy',
            'chemises manches courtes' => 'Chemise manches courtes',
            'chemises manches longues' => 'Chemise manches longues',
            'pulls sans manches' => 'Pull sans manches',
            'pulls col rond' => 'Pull col rond',
            'pulls col v' => 'Pull col V',
            'gilets cardigans' => 'Cardigan',
            'polaires sans manches' => 'Polaire sans manches',
            'pulls polaire' => 'Pull polaire',
            'gilets polaire' => 'Gilet polaire',
            'coupe-vent' => 'Coupe-vent',
            'softshells' => 'Softshell',
            'imperméables' => 'Imperméable',
            'vestes été' => 'Veste légère',
            'manteaux hiver' => 'Manteau',
            'parkas' => 'Parka',
            'doudounes sans manches' => 'Doudoune sans manches',
            'doudounes manches longues' => 'Doudoune',
            'doudounes à capuche' => 'Doudoune à capuche',
            'pantalons' => 'Pantalon',
            'shorts et bermudas' => 'Short',
            'jupes et robes' => 'Jupe',
            'pantalons de travail' => 'Pantalon de travail',
            'blouses et vestes' => 'Blouse de travail',
            'combinaisons' => 'Combinaison de travail',
            'bodywarmer' => 'Bodywarmer',
            'haute visibilité' => 'Vêtement haute visibilité',
            'tuniques' => 'Tunique professionnelle',
            'casquettes classiques' => 'Casquette',
            'casquettes snapback' => 'Casquette snapback',
            'bérets' => 'Béret',
            'bobs' => 'Bob',
            'bonnets' => 'Bonnet',
            'chapkas' => 'Chapka',
            'sacs coton' => 'Sac en coton',
            'sacs polyester' => 'Sac',
            'sacs à dos' => 'Sac à dos',
            'sacoches' => 'Sacoche',
            'tabliers' => 'Tablier',
            'serviettes' => 'Serviette',
            'couvertures' => 'Couverture',
            'maillots et t-shirts sport' => 'Maillot de sport',
            'shorts sport' => 'Short de sport',
            'joggings et leggings' => 'Jogging',
            'vestes de sport' => 'Veste de sport',
            'manches longues sport' => 'T-shirt de sport manches longues',
            'bodies' => 'Body bébé',
            't-shirts bébé' => 'T-shirt bébé',
            'chaussettes' => 'Chaussettes',
            'slips et caleçons' => 'Sous-vêtement',
            'maillots de bain' => 'Maillot de bain',
            'valises' => 'Valise',
            'portefeuilles' => 'Trousse',
            'écharpes et gants' => 'Écharpe',
            'cravates et bandanas' => 'Bandana',
            'ceintures' => 'Accessoire',
            'chapeaux' => 'Chapeau',
            'accessoires pro' => 'Accessoire professionnel',
            'accessoires sport' => 'Accessoire de sport',
            'accessoires bébé' => 'Accessoire bébé',
            'chiffons' => 'Chiffon',
            'nappes et serviettes' => 'Linge de table',
            'sweat-shirts bébé' => 'Sweat bébé',
            'bonnets et bavoirs' => 'Bavoir bébé',
            'pyjamas' => 'Pyjama bébé',
            'vêtements ignifuges' => 'Vêtement ignifuge',
            'hôtellerie' => 'Vêtement hôtellerie-restauration',
            'médical' => 'Vêtement médical',
            'agroalimentaire' => 'Vêtement agroalimentaire',
            'extérieur' => 'Vêtement de travail extérieur',
            'chasubles' => 'Chasuble de sport',
            'tenues complètes' => 'Tenue de sport complète',
            'chaussures de travail' => 'Chaussure de travail',
            'chaussures sport' => 'Chaussure de sport',
            't-shirts made in france' => 'T-shirt Made in France',
            'polos made in france' => 'Polo Made in France',
            'sweats made in france' => 'Sweat Made in France',
            'textiles made in france' => 'Textile Made in France',
            't-shirts bio' => 'T-shirt bio',
            'polos bio' => 'Polo bio',
            'sweats bio' => 'Sweat bio',
            'sacs et accessoires bio' => 'Sac bio',
        ];

        foreach ($typeMap as $key => $label) {
            if (str_contains($lower, $key)) {
                return $label;
            }
        }

        // Fallback: use parent category name
        if ($parentName) {
            return rtrim($parentName, 's');
        }

        return $catName;
    }

    /**
     * Clean a product name: remove brand prefix and redundant type words.
     */
    private function cleanProductName(Product $product): string
    {
        $name = $product->name;
        $supplier = $product->supplier ?? '';

        // Remove common brand prefixes from name (e.g. "SOL'S REGENT" -> "REGENT", "ATF LÉON" -> "LÉON")
        $brandPrefixes = [
            "SOL'S ", 'NEOBLU ', 'ATF ', 'RTP APPAREL ', 'B&C ', 'Unbranded Selection ',
        ];
        foreach ($brandPrefixes as $prefix) {
            if (str_starts_with($name, $prefix)) {
                $name = substr($name, strlen($prefix));
                break;
            }
        }

        return trim($name);
    }

    /**
     * Build title: "Type + Name" but avoid redundancy like "Chaussettes Chaussettes lot de 3"
     */
    private function buildTitle(string $type, string $cleanName): string
    {
        // If the name already starts with the type word, don't repeat
        $typeLower = mb_strtolower($type);
        $nameLower = mb_strtolower($cleanName);

        if (str_starts_with($nameLower, $typeLower) || str_starts_with($nameLower, rtrim($typeLower, 's'))) {
            return $cleanName;
        }

        // Also check common equivalences
        $equivalences = [
            'polo' => ['polo'],
            'parka' => ['parka'],
            'bob' => ['bob'],
            'bonnet' => ['bonnet', 'cagoule', 'balaclava'],
            'casquette' => ['casquette'],
            'béret' => ['béret'],
            'chapeau' => ['chapeau'],
            'tablier' => ['tablier'],
            'combinaison' => ['combinaison'],
            'cardigan' => ['cardigan'],
            'débardeur' => ['débardeur'],
            'serviette' => ['serviette', 'peignoir', 'drap'],
            'couverture' => ['couverture', 'plaid', 'coussin', 'drap', 'housse'],
            'pantalon' => ['pantalon', 'bermuda', 'short', 'legging'],
            'chaussettes' => ['chaussettes', 'socquettes', 'jambière'],
            'sac' => ['sac ', 'tote', 'pochon', 'trousse', 'porte-'],
            'polaire' => ['polaire', 'fleece'],
            'vêtement ignifuge' => ['veste ignifuge', 'pantalon ignifuge', 'chemise ignifuge', 'combinaison ignifuge', 'combinaison de travail'],
            'vêtement haute visibilité' => ['gilet', 'veste', 'pantalon', 'polo', 't-shirt', 'sweat', 'parka'],
            'vêtement' => ['veste', 'blouse', 'tunique', 'surblouse'],
            'softshell' => ['veste softshell', 'softshell'],
            'doudoune' => ['doudoune'],
            'imperméable' => ['imperméable', 'k-way', 'cape pluie'],
            'bodywarmer' => ['bodywarmer', 'gilet '],
            'coupe-vent' => ['coupe-vent', 'coupe vent'],
            'manteau' => ['manteau', 'anorak'],
        ];

        foreach ($equivalences as $typeKey => $nameStarts) {
            if (str_contains($typeLower, $typeKey)) {
                foreach ($nameStarts as $ns) {
                    if (str_starts_with($nameLower, $ns)) {
                        return $cleanName;
                    }
                }
            }
        }

        return "{$type} {$cleanName}";
    }

    private function generateH2(Product $product, string $keyword, string $priceLevel): string
    {
        $type = $this->getProductTypeLabel($product);
        $cleanName = $this->cleanProductName($product);
        $title = $this->buildTitle($type, $cleanName);

        return match ($priceLevel) {
            'budget' => "{$title} — meilleur rapport qualité-prix",
            'premium' => "{$title} — qualité premium",
            default => $title,
        };
    }

    private function generateIntroParagraph(Product $product, string $catName, string $parentCatName, string $priceLevel, array $keywords): string
    {
        $name = $product->name;
        $supplier = $product->supplier;
        $material = $product->material ? Str::limit($product->material, 60, '') : '';

        $phrases = [];

        // Opening - varies by price level
        $type = $this->getProductTypeLabel($product);
        $cleanName = $this->cleanProductName($product);

        $phrases[] = match ($priceLevel) {
            'budget' => "Le {$type} <strong>{$cleanName}</strong> de {$supplier} offre un excellent rapport qualité-prix pour la personnalisation textile en série.",
            'premium' => "Le {$type} <strong>{$cleanName}</strong> de {$supplier} est un modèle haut de gamme, parfait pour un <strong>{$catName} personnalisé</strong> de qualité.",
            default => "Le {$type} <strong>{$cleanName}</strong> de {$supplier} est un choix fiable pour la <strong>personnalisation textile professionnelle</strong>.",
        };

        // Material mention (clean up and limit length)
        if ($material) {
            $cleanMaterial = preg_replace('/\.\s*$/', '', Str::limit($material, 80, ''));
            $phrases[] = "Confectionné en {$cleanMaterial}, ce produit allie confort et durabilité.";
        }

        // Keyword integration
        if (! empty($keywords) && isset($keywords[1])) {
            $phrases[] = "Idéal pour le <strong>{$keywords[1]}</strong>, il répond aux exigences des professionnels du marquage en série.";
        }

        return implode(' ', $phrases);
    }

    private function generateFeaturesParagraph(Product $product, string $material, ?int $grammage, array $features, ?string $cut): string
    {
        $parts = [];

        if ($material) {
            $parts[] = "Composition : {$material}.";
        }

        if ($grammage && $grammage > 1) {
            $weight = $grammage < 150 ? 'léger' : ($grammage < 250 ? 'standard' : ($grammage < 400 ? 'résistant' : 'robuste'));
            $parts[] = "Grammage {$weight} de {$grammage} g/m² adapté à un usage professionnel intensif.";
        }

        if ($cut) {
            $cutLabel = match ($cut) {
                'homme' => 'Coupe homme, ajustée et confortable',
                'femme' => 'Coupe femme, cintrée et élégante',
                'enfant' => 'Coupe enfant, adaptée aux plus jeunes',
                'mixte' => 'Coupe unisexe, convient à tous',
                default => '',
            };
            if ($cutLabel) {
                $parts[] = "{$cutLabel}.";
            }
        }

        // Add unique features from original description
        foreach (array_slice($features, 0, 3) as $feature) {
            $parts[] = ucfirst(trim($feature)) . '.';
        }

        return implode(' ', $parts);
    }

    private function generateMarkingParagraph(Product $product, array $techNames, string $catName, array $keywords): string
    {
        $count = count($techNames);
        $techList = implode(', ', array_slice($techNames, 0, -1));
        if ($count > 1) {
            $techList .= ' et ' . end($techNames);
        } else {
            $techList = $techNames[0] ?? '';
        }

        $intro = match (true) {
            $count === 1 => "Ce produit est compatible avec la technique de <strong>{$techList}</strong>",
            $count <= 3 => "Compatible avec {$count} techniques de marquage : <strong>{$techList}</strong>",
            default => "Large choix de personnalisation avec {$count} techniques disponibles : <strong>{$techList}</strong>",
        };

        $keyword = $keywords[array_rand($keywords)] ?? 'marquage textile';
        $detail = match (true) {
            in_array(1, $product->compatible_techniques ?? []) && in_array(2, $product->compatible_techniques ?? [])
                => ". La broderie offre un rendu premium et durable, tandis que la sérigraphie est idéale pour les grandes séries à petit budget. Parfait pour le <strong>{$keyword}</strong>.",
            in_array(1, $product->compatible_techniques ?? [])
                => ". La broderie apporte un rendu haut de gamme, résistant à des centaines de lavages. Un choix de qualité pour le <strong>{$keyword}</strong>.",
            in_array(6, $product->compatible_techniques ?? []) || in_array(10, $product->compatible_techniques ?? [])
                => ". Les transferts permettent des visuels détaillés et multicolores à moindre coût. Solution économique pour le <strong>{$keyword}</strong> en série.",
            default => ". Une solution professionnelle pour le <strong>{$keyword}</strong> en série.",
        };

        return $intro . $detail;
    }

    private function generateUsageParagraph(Product $product, string $catName, string $parentCatName, string $priceLevel): string
    {
        $uses = match (true) {
            str_contains(mb_strtolower($parentCatName), 'travail') || str_contains(mb_strtolower($catName), 'travail')
                => 'les équipes de terrain, le BTP, l\'industrie, la logistique et les collectivités',
            str_contains(mb_strtolower($parentCatName), 'sport') || str_contains(mb_strtolower($catName), 'sport')
                => 'les clubs sportifs, les associations, les écoles et les événements sportifs',
            str_contains(mb_strtolower($catName), 'restauration') || str_contains(mb_strtolower($catName), 'hôtel')
                => 'les restaurants, hôtels, traiteurs et professionnels de la restauration',
            str_contains(mb_strtolower($catName), 'médical')
                => 'les cliniques, cabinets médicaux, EHPAD et professionnels de santé',
            str_contains(mb_strtolower($parentCatName), 'bébé')
                => 'les crèches, maternités, boutiques bébé et cadeaux de naissance personnalisés',
            str_contains(mb_strtolower($catName), 'sac') || str_contains(mb_strtolower($catName), 'tote')
                => 'les salons professionnels, événements d\'entreprise, goodies et cadeaux promotionnels',
            default => 'les entreprises, associations, événements et collectivités',
        };

        $minQty = match ($priceLevel) {
            'budget' => 'Commande possible dès 10 pièces avec des tarifs dégressifs avantageux pour les grandes quantités.',
            'premium' => 'Tarifs sur devis personnalisé. Quantités minimales selon la technique de marquage choisie.',
            default => 'Tarifs dégressifs à partir de 20 pièces. Devis gratuit sous 24h.',
        };

        return "Destiné aux {$uses}. {$minQty} <strong>Livraison rapide</strong> partout en France et en Europe.";
    }

    private function generateMetaTitle(Product $product): string
    {
        $type = $this->getProductTypeLabel($product);
        $cleanName = $this->cleanProductName($product);
        $supplier = $product->supplier;

        $productTitle = $this->buildTitle($type, $cleanName);
        $title = "{$productTitle} | {$supplier} | Marquage Textile";

        if (mb_strlen($title) > 60) {
            $title = "{$productTitle} | {$supplier}";
        }

        if (mb_strlen($title) > 60) {
            $title = $productTitle;
        }

        if (mb_strlen($title) > 60) {
            $title = mb_substr($title, 0, 57) . '...';
        }

        return $title;
    }

    private function generateMetaDescription(Product $product): string
    {
        $name = $product->name;
        $supplier = $product->supplier;
        $priceLevel = $this->getPriceLevel($product);
        $techniques = $product->compatible_techniques ?? [];
        $techNames = array_filter(array_map(fn ($id) => $this->techniqueNames[$id] ?? null, array_slice($techniques, 0, 3)));
        $material = $product->material ? Str::limit($product->material, 30, '') : '';

        $desc = match ($priceLevel) {
            'budget' => "{$name} {$supplier} à prix grossiste.",
            'premium' => "{$name} {$supplier}, qualité premium.",
            default => "{$name} {$supplier} personnalisable.",
        };

        if (! empty($techNames)) {
            $desc .= ' ' . implode(', ', $techNames) . '.';
        }

        if ($material) {
            $desc .= " {$material}.";
        }

        $desc .= ' Devis gratuit, livraison rapide.';

        if (mb_strlen($desc) > 160) {
            $desc = mb_substr($desc, 0, 157) . '...';
        }

        return $desc;
    }

    private function getPriceLevel(Product $product): string
    {
        $price = (float) ($product->supplier_price ?? 0);

        if ($price <= 0) {
            return 'standard';
        }

        // Relative to category average
        $catAvg = Product::where('category_id', $product->category_id)
            ->where('is_active', true)
            ->where('supplier_price', '>', 0)
            ->avg('supplier_price');

        if (! $catAvg || $catAvg <= 0) {
            return 'standard';
        }

        $ratio = $price / $catAvg;

        return match (true) {
            $ratio < 0.7 => 'budget',
            $ratio > 1.4 => 'premium',
            default => 'standard',
        };
    }

    private function getRelevantKeywords(string $catName, string $parentCatName): array
    {
        $keywords = [];
        $search = mb_strtolower($catName . ' ' . $parentCatName);

        foreach ($this->catKeywordMap as $key => $kws) {
            if (str_contains($search, $key)) {
                $keywords = array_merge($keywords, $kws);
            }
        }

        if (empty($keywords)) {
            $keywords = ['personnalisation textile', 'marquage textile', 'textile professionnel'];
        }

        return array_unique($keywords);
    }

    private function extractFeatures(string $description): array
    {
        $features = [];

        // Extract meaningful sentences (not too short, not too long)
        $sentences = preg_split('/[.!]\s+/', $description);
        foreach ($sentences as $sentence) {
            $sentence = trim($sentence);
            $len = mb_strlen($sentence);
            if ($len > 20 && $len < 150) {
                // Skip generic marketing fluff
                if (preg_match('/convient à|idéal|parfait pour|marquage|sérigraphie|transfert|broderie|vinyle|écusson/i', $sentence)) {
                    continue;
                }
                // Keep technical features
                if (preg_match('/col |manche|poche|fermeture|zip|capuche|couture|renfort|ceinture|fente|doublure|respirant|imperméable|déperlant|réfléchissant|élastiq|ajust/i', $sentence)) {
                    $features[] = $sentence;
                }
            }
        }

        return array_unique(array_slice($features, 0, 5));
    }
}
