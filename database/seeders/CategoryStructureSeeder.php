<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\MarkingTechnique;
use Illuminate\Database\Seeder;

class CategoryStructureSeeder extends Seeder
{
    /**
     * Seeder complet de l'arborescence catégories.
     * Utilise updateOrCreate sur le slug → re-exécutable sans doublon.
     * Les catégories ajoutées manuellement via Filament sont préservées.
     */
    public function run(): void
    {
        // Supprime les anciennes catégories racine obsolètes (ancien seeder)
        $legacySlugs = ['vetements', 'sweats-hoodies', 'vestes-softshells', 'pantalons-shorts', 'casquettes-bonnets', 'tabliers-workwear'];
        Category::whereIn('slug', $legacySlugs)->delete();

        $techniques = MarkingTechnique::pluck('id', 'slug')->toArray();

        foreach ($this->getStructure() as $i => $main) {
            $parent = $this->upsertCategory($main, null, $i);

            foreach (($main['children'] ?? []) as $j => $sub) {
                $child = $this->upsertCategory($sub, $parent->id, $j);

                foreach (($sub['children'] ?? []) as $k => $subsub) {
                    $this->upsertCategory($subsub, $child->id, $k);
                }
            }
        }
    }

    private function upsertCategory(array $data, ?int $parentId, int $sortOrder): Category
    {
        return Category::updateOrCreate(
            ['slug' => $data['slug']],
            [
                'parent_id' => $parentId,
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'meta_description' => $data['meta_description'] ?? null,
                'seo_keywords' => $data['seo_keywords'] ?? null,
                'default_techniques' => $data['default_techniques'] ?? null,
                'sort_order' => $sortOrder,
                'is_active' => true,
            ]
        );
    }

    // ──────────────────────────────────────────────
    // Helper : résout les slugs techniques en IDs
    // ──────────────────────────────────────────────
    private function techniqueIds(array $slugs): array
    {
        static $map = null;
        if ($map === null) {
            $map = MarkingTechnique::pluck('id', 'slug')->toArray();
        }

        return array_values(array_filter(
            array_map(fn ($s) => $map[$s] ?? null, $slugs)
        ));
    }

    // ──────────────────────────────────────────────
    // Arborescence complète
    // ──────────────────────────────────────────────
    private function getStructure(): array
    {
        return [

            // — T-shirts —
            [
                'name' => 'T-shirts',
                'slug' => 't-shirts',
                'description' => 'T-shirts personnalisables pour homme, femme et enfant. Coton, polyester et bio.',
                'meta_description' => 'T-shirts personnalisés pas cher. Col rond, col V, manches longues. Marquage sérigraphie, broderie, DTG.',
                'seo_keywords' => 't-shirt personnalisé, t-shirt imprimé, t-shirt entreprise, t-shirt logo',
                'default_techniques' => $this->techniqueIds(['serigraphie', 'impression-dtg', 'transfert', 'flocage-flex', 'dtf', 'full-print']),
                'children' => [
                    [
                        'name' => 'T-shirts polyester',
                        'slug' => 't-shirts-polyester',
                        'meta_description' => 'T-shirts polyester personnalisables. Idéals pour le sport et la sublimation full print.',
                        'seo_keywords' => 't-shirt polyester, t-shirt sport personnalisé, t-shirt sublimation',
                        'default_techniques' => $this->techniqueIds(['serigraphie', 'transfert', 'dtf', 'full-print']),
                    ],
                    [
                        'name' => 'T-shirts col rond coton',
                        'slug' => 't-shirts-col-rond-coton',
                        'meta_description' => 'T-shirts col rond 100% coton personnalisables. Le classique du textile promotionnel.',
                        'seo_keywords' => 't-shirt col rond, t-shirt coton, t-shirt classique personnalisé',
                        'default_techniques' => $this->techniqueIds(['serigraphie', 'impression-dtg', 'transfert', 'flocage-flex', 'dtf']),
                    ],
                    [
                        'name' => 'T-shirts col V',
                        'slug' => 't-shirts-col-v',
                        'meta_description' => 'T-shirts col V personnalisables pour un style élégant. Homme et femme.',
                        'seo_keywords' => 't-shirt col V, t-shirt col V personnalisé, t-shirt élégant',
                        'default_techniques' => $this->techniqueIds(['serigraphie', 'impression-dtg', 'transfert', 'flocage-flex', 'dtf']),
                    ],
                    [
                        'name' => 'T-shirts manches longues',
                        'slug' => 't-shirts-manches-longues',
                        'meta_description' => 'T-shirts manches longues personnalisables. Confort et style pour toutes les saisons.',
                        'seo_keywords' => 't-shirt manches longues, t-shirt ML personnalisé, t-shirt hiver',
                        'default_techniques' => $this->techniqueIds(['serigraphie', 'impression-dtg', 'transfert', 'flocage-flex', 'dtf']),
                    ],
                    [
                        'name' => 'T-shirts bi/multi color',
                        'slug' => 't-shirts-bicolor',
                        'meta_description' => 'T-shirts bicolores et multicolores personnalisables. Design original pour vos équipes.',
                        'seo_keywords' => 't-shirt bicolore, t-shirt deux couleurs, t-shirt multicolor personnalisé',
                        'default_techniques' => $this->techniqueIds(['serigraphie', 'impression-dtg', 'transfert', 'dtf']),
                    ],
                    [
                        'name' => 'Débardeurs',
                        'slug' => 'debardeurs',
                        'meta_description' => 'Débardeurs personnalisables homme et femme. Parfaits pour l\'été et le sport.',
                        'seo_keywords' => 'débardeur personnalisé, débardeur imprimé, marcel personnalisé',
                        'default_techniques' => $this->techniqueIds(['serigraphie', 'impression-dtg', 'transfert', 'dtf']),
                    ],
                ],
            ],

            // — Polos —
            [
                'name' => 'Polos',
                'slug' => 'polos',
                'description' => 'Polos personnalisables pour un look professionnel. Manches courtes, longues, rugby et sport.',
                'meta_description' => 'Polos personnalisés avec broderie ou sérigraphie. Manches courtes et longues, polos sport et rugby.',
                'seo_keywords' => 'polo personnalisé, polo brodé, polo entreprise, polo logo',
                'default_techniques' => $this->techniqueIds(['broderie', 'serigraphie', 'impression-dtg', 'transfert']),
                'children' => [
                    [
                        'name' => 'Polos manches courtes',
                        'slug' => 'polos-manches-courtes',
                        'meta_description' => 'Polos manches courtes personnalisables. Le classique professionnel avec broderie.',
                        'seo_keywords' => 'polo manches courtes, polo MC personnalisé, polo classique brodé',
                        'default_techniques' => $this->techniqueIds(['broderie', 'serigraphie', 'impression-dtg', 'transfert']),
                    ],
                    [
                        'name' => 'Polos manches longues',
                        'slug' => 'polos-manches-longues',
                        'meta_description' => 'Polos manches longues personnalisables. Élégance et confort toute l\'année.',
                        'seo_keywords' => 'polo manches longues, polo ML personnalisé, polo hiver brodé',
                        'default_techniques' => $this->techniqueIds(['broderie', 'serigraphie', 'transfert']),
                    ],
                    [
                        'name' => 'Polos rugby',
                        'slug' => 'polos-rugby',
                        'meta_description' => 'Polos rugby personnalisables. Style sportif et robuste pour clubs et entreprises.',
                        'seo_keywords' => 'polo rugby, polo rugby personnalisé, polo rugby brodé',
                        'default_techniques' => $this->techniqueIds(['broderie', 'serigraphie', 'transfert']),
                    ],
                    [
                        'name' => 'Polos sport',
                        'slug' => 'polos-sport',
                        'meta_description' => 'Polos sport personnalisables en polyester respirant. Idéals pour clubs et événements.',
                        'seo_keywords' => 'polo sport, polo technique personnalisé, polo respirant',
                        'default_techniques' => $this->techniqueIds(['broderie', 'transfert', 'dtf', 'full-print']),
                    ],
                    [
                        'name' => 'Polos bi/multicolor',
                        'slug' => 'polos-bicolor',
                        'meta_description' => 'Polos bicolores et multicolores personnalisables. Ajoutez du style à vos tenues.',
                        'seo_keywords' => 'polo bicolore, polo deux couleurs, polo contrasté personnalisé',
                        'default_techniques' => $this->techniqueIds(['broderie', 'serigraphie', 'transfert']),
                    ],
                ],
            ],

            // — Sweats —
            [
                'name' => 'Sweats',
                'slug' => 'sweats',
                'description' => 'Sweats personnalisables : col rond, à capuche, zippés. Confort et style pour vos équipes.',
                'meta_description' => 'Sweats personnalisés avec broderie ou sérigraphie. Col rond, capuche, zippés. Toutes marques.',
                'seo_keywords' => 'sweat personnalisé, sweat brodé, hoodie personnalisé, sweat entreprise',
                'default_techniques' => $this->techniqueIds(['broderie', 'serigraphie', 'impression-dtg', 'dtf']),
                'children' => [
                    [
                        'name' => 'Sweats col rond',
                        'slug' => 'sweats-col-rond',
                        'meta_description' => 'Sweats col rond personnalisables. Classique et confortable pour toutes les occasions.',
                        'seo_keywords' => 'sweat col rond, sweat classique personnalisé, sweat brodé col rond',
                        'default_techniques' => $this->techniqueIds(['broderie', 'serigraphie', 'impression-dtg', 'dtf']),
                    ],
                    [
                        'name' => 'Sweats à capuche',
                        'slug' => 'sweats-capuche',
                        'meta_description' => 'Hoodies personnalisables avec broderie ou impression. Le sweat tendance par excellence.',
                        'seo_keywords' => 'hoodie personnalisé, sweat capuche brodé, sweat à capuche imprimé',
                        'default_techniques' => $this->techniqueIds(['broderie', 'serigraphie', 'impression-dtg', 'dtf']),
                    ],
                    [
                        'name' => 'Sweats zippés',
                        'slug' => 'sweats-zippes',
                        'meta_description' => 'Sweats zippés personnalisables. Pratiques et élégants pour le quotidien professionnel.',
                        'seo_keywords' => 'sweat zippé personnalisé, sweat zip brodé, veste sweat personnalisée',
                        'default_techniques' => $this->techniqueIds(['broderie', 'serigraphie', 'transfert']),
                    ],
                    [
                        'name' => 'Sweats zippés à capuche',
                        'slug' => 'sweats-zippes-capuche',
                        'meta_description' => 'Sweats zippés à capuche personnalisables. Le combo zip + capuche pour un max de confort.',
                        'seo_keywords' => 'sweat zippé capuche, hoodie zippé personnalisé, sweat zip capuche brodé',
                        'default_techniques' => $this->techniqueIds(['broderie', 'serigraphie', 'impression-dtg', 'dtf']),
                    ],
                    [
                        'name' => 'Sweats sans manches',
                        'slug' => 'sweats-sans-manches',
                        'meta_description' => 'Sweats sans manches personnalisables. Style décontracté pour sport et loisirs.',
                        'seo_keywords' => 'sweat sans manches, gilet sweat personnalisé, débardeur sweat',
                        'default_techniques' => $this->techniqueIds(['broderie', 'serigraphie', 'transfert']),
                    ],
                ],
            ],

            // — Chemises —
            [
                'name' => 'Chemises',
                'slug' => 'chemises',
                'description' => 'Chemises personnalisables pour homme et femme. Look professionnel avec broderie.',
                'meta_description' => 'Chemises personnalisées avec broderie. Manches courtes et longues pour un look corporate.',
                'seo_keywords' => 'chemise personnalisée, chemise brodée, chemise entreprise, chemise logo',
                'default_techniques' => $this->techniqueIds(['broderie', 'serigraphie']),
                'children' => [
                    [
                        'name' => 'Chemises manches courtes',
                        'slug' => 'chemises-manches-courtes',
                        'meta_description' => 'Chemises manches courtes personnalisables avec broderie. Parfaites pour l\'été.',
                        'seo_keywords' => 'chemise manches courtes, chemise MC brodée, chemise été personnalisée',
                        'default_techniques' => $this->techniqueIds(['broderie', 'serigraphie']),
                    ],
                    [
                        'name' => 'Chemises manches longues',
                        'slug' => 'chemises-manches-longues',
                        'meta_description' => 'Chemises manches longues personnalisables. Élégance corporate avec votre logo brodé.',
                        'seo_keywords' => 'chemise manches longues, chemise ML brodée, chemise corporate personnalisée',
                        'default_techniques' => $this->techniqueIds(['broderie', 'serigraphie']),
                    ],
                ],
            ],

            // — Pulls & Gilets —
            [
                'name' => 'Pulls & Gilets',
                'slug' => 'pulls-gilets',
                'description' => 'Pulls et gilets personnalisables. Broderie pour un look soigné et professionnel.',
                'meta_description' => 'Pulls et gilets personnalisés avec broderie. Col rond, col V, cardigans et teddy.',
                'seo_keywords' => 'pull personnalisé, gilet brodé, pull entreprise, cardigan personnalisé',
                'default_techniques' => $this->techniqueIds(['broderie', 'transfert']),
                'children' => [
                    [
                        'name' => 'Pulls sans manches',
                        'slug' => 'pulls-sans-manches',
                        'meta_description' => 'Pulls sans manches personnalisables avec broderie. Look élégant pour le bureau.',
                        'seo_keywords' => 'pull sans manches, débardeur pull brodé, pull SM personnalisé',
                        'default_techniques' => $this->techniqueIds(['broderie', 'transfert']),
                    ],
                    [
                        'name' => 'Pulls col rond',
                        'slug' => 'pulls-col-rond',
                        'meta_description' => 'Pulls col rond personnalisables. Classique et intemporel avec votre logo brodé.',
                        'seo_keywords' => 'pull col rond, pull classique brodé, pull col rond personnalisé',
                        'default_techniques' => $this->techniqueIds(['broderie', 'transfert']),
                    ],
                    [
                        'name' => 'Pulls col V',
                        'slug' => 'pulls-col-v',
                        'meta_description' => 'Pulls col V personnalisables. Élégance professionnelle avec broderie.',
                        'seo_keywords' => 'pull col V, pull col V brodé, pull élégant personnalisé',
                        'default_techniques' => $this->techniqueIds(['broderie', 'transfert']),
                    ],
                    [
                        'name' => 'Gilets cardigans',
                        'slug' => 'gilets-cardigans',
                        'meta_description' => 'Gilets et cardigans personnalisables. Confort et élégance avec broderie.',
                        'seo_keywords' => 'gilet cardigan, cardigan brodé, gilet personnalisé, gilet entreprise',
                        'default_techniques' => $this->techniqueIds(['broderie', 'transfert']),
                    ],
                    [
                        'name' => 'Vestes Teddy',
                        'slug' => 'vestes-teddy',
                        'meta_description' => 'Vestes teddy personnalisables. Style vintage et moderne avec broderie.',
                        'seo_keywords' => 'veste teddy, teddy personnalisé, veste baseball brodée, teddy vintage',
                        'default_techniques' => $this->techniqueIds(['broderie', 'transfert', 'flocage-flex']),
                    ],
                ],
            ],

            // — Polaires —
            [
                'name' => 'Polaires',
                'slug' => 'polaires',
                'description' => 'Polaires personnalisables. Chaleur et confort pour l\'extérieur et le travail.',
                'meta_description' => 'Polaires personnalisées avec broderie. Pulls et gilets polaire pour entreprises et associations.',
                'seo_keywords' => 'polaire personnalisée, polaire brodée, polaire entreprise, polaire logo',
                'default_techniques' => $this->techniqueIds(['broderie', 'transfert']),
                'children' => [
                    [
                        'name' => 'Polaires sans manches',
                        'slug' => 'polaires-sans-manches',
                        'meta_description' => 'Polaires sans manches (bodywarmer) personnalisables. Chaleur sans encombrement.',
                        'seo_keywords' => 'polaire sans manches, bodywarmer polaire, gilet polaire personnalisé',
                        'default_techniques' => $this->techniqueIds(['broderie', 'transfert']),
                    ],
                    [
                        'name' => 'Pulls polaire',
                        'slug' => 'pulls-polaire',
                        'meta_description' => 'Pulls polaire personnalisables avec broderie. Chauds et confortables.',
                        'seo_keywords' => 'pull polaire, pull polaire brodé, pull polaire personnalisé',
                        'default_techniques' => $this->techniqueIds(['broderie', 'transfert']),
                    ],
                    [
                        'name' => 'Gilets polaire',
                        'slug' => 'gilets-polaire',
                        'meta_description' => 'Gilets polaire personnalisables. Idéals comme couche intermédiaire.',
                        'seo_keywords' => 'gilet polaire, gilet polaire brodé, gilet polaire personnalisé',
                        'default_techniques' => $this->techniqueIds(['broderie', 'transfert']),
                    ],
                ],
            ],

            // — Coupe-vent & Softshell —
            [
                'name' => 'Coupe-vent & Softshell',
                'slug' => 'coupe-vent-softshell',
                'description' => 'Coupe-vent et softshells personnalisables. Protection contre le vent et la pluie.',
                'meta_description' => 'Coupe-vent et softshells personnalisés avec broderie. Protection et style pour vos équipes.',
                'seo_keywords' => 'coupe-vent personnalisé, softshell brodé, veste coupe-vent logo, softshell entreprise',
                'default_techniques' => $this->techniqueIds(['broderie', 'transfert']),
                'children' => [
                    [
                        'name' => 'Coupe-vent',
                        'slug' => 'coupe-vent',
                        'meta_description' => 'Coupe-vent personnalisables avec broderie ou transfert. Légers et pratiques.',
                        'seo_keywords' => 'coupe-vent, coupe-vent personnalisé, coupe-vent brodé, k-way logo',
                        'default_techniques' => $this->techniqueIds(['broderie', 'transfert']),
                    ],
                    [
                        'name' => 'Softshells',
                        'slug' => 'softshells',
                        'meta_description' => 'Vestes softshell personnalisables. Souples, coupe-vent et déperlantes.',
                        'seo_keywords' => 'softshell, veste softshell personnalisée, softshell brodé, softshell entreprise',
                        'default_techniques' => $this->techniqueIds(['broderie', 'transfert']),
                    ],
                ],
            ],

            // — Manteaux & Parkas —
            [
                'name' => 'Manteaux & Parkas',
                'slug' => 'manteaux-parkas',
                'description' => 'Manteaux et parkas personnalisables pour l\'hiver. Doudounes, parkas et vestes d\'été.',
                'meta_description' => 'Manteaux et parkas personnalisés avec broderie. Doudounes, parkas et vestes pour vos équipes.',
                'seo_keywords' => 'manteau personnalisé, parka brodée, doudoune personnalisée, veste hiver logo',
                'default_techniques' => $this->techniqueIds(['broderie', 'transfert']),
                'children' => [
                    [
                        'name' => 'Vestes été',
                        'slug' => 'vestes-ete',
                        'meta_description' => 'Vestes légères d\'été personnalisables. Protection et style pour la belle saison.',
                        'seo_keywords' => 'veste été, veste légère personnalisée, veste été brodée',
                        'default_techniques' => $this->techniqueIds(['broderie', 'transfert']),
                    ],
                    [
                        'name' => 'Doudounes',
                        'slug' => 'doudounes',
                        'meta_description' => 'Doudounes personnalisables avec broderie. Chaleur et style corporate.',
                        'seo_keywords' => 'doudoune personnalisée, doudoune brodée, doudoune entreprise, doudoune logo',
                        'default_techniques' => $this->techniqueIds(['broderie', 'transfert']),
                    ],
                    [
                        'name' => 'Manteaux hiver',
                        'slug' => 'manteaux-hiver',
                        'meta_description' => 'Manteaux d\'hiver personnalisables. Protection maximale contre le froid.',
                        'seo_keywords' => 'manteau hiver, manteau personnalisé, manteau brodé, manteau entreprise',
                        'default_techniques' => $this->techniqueIds(['broderie', 'transfert']),
                    ],
                    [
                        'name' => 'Parkas',
                        'slug' => 'parkas',
                        'meta_description' => 'Parkas personnalisables avec broderie. Robustes et imperméables.',
                        'seo_keywords' => 'parka personnalisée, parka brodée, parka entreprise, parka imperméable',
                        'default_techniques' => $this->techniqueIds(['broderie', 'transfert']),
                    ],
                ],
            ],

            // — Pantalons —
            [
                'name' => 'Pantalons',
                'slug' => 'pantalons',
                'description' => 'Pantalons, shorts et jupes personnalisables. Broderie et sérigraphie.',
                'meta_description' => 'Pantalons et shorts personnalisés. Broderie et sérigraphie pour vos tenues d\'équipe.',
                'seo_keywords' => 'pantalon personnalisé, short personnalisé, bermuda brodé, jupe personnalisée',
                'default_techniques' => $this->techniqueIds(['broderie', 'serigraphie', 'transfert']),
                'children' => [
                    [
                        'name' => 'Pantalons',
                        'slug' => 'pantalons-longs',
                        'meta_description' => 'Pantalons personnalisables avec broderie. Chinos, joggings et pantalons classiques.',
                        'seo_keywords' => 'pantalon personnalisé, pantalon brodé, pantalon entreprise',
                        'default_techniques' => $this->techniqueIds(['broderie', 'serigraphie', 'transfert']),
                    ],
                    [
                        'name' => 'Shorts et bermudas',
                        'slug' => 'shorts-bermudas',
                        'meta_description' => 'Shorts et bermudas personnalisables. Confort estival avec votre logo.',
                        'seo_keywords' => 'short personnalisé, bermuda brodé, short entreprise, bermuda personnalisé',
                        'default_techniques' => $this->techniqueIds(['broderie', 'serigraphie', 'transfert']),
                    ],
                    [
                        'name' => 'Jupes et robes',
                        'slug' => 'jupes-robes',
                        'meta_description' => 'Jupes et robes personnalisables. Féminité et élégance pour vos équipes.',
                        'seo_keywords' => 'jupe personnalisée, robe personnalisée, jupe brodée, tenue femme entreprise',
                        'default_techniques' => $this->techniqueIds(['broderie', 'transfert']),
                    ],
                ],
            ],

            // — Sous-vêtements —
            [
                'name' => 'Sous-vêtements',
                'slug' => 'sous-vetements',
                'description' => 'Sous-vêtements et maillots de bain personnalisables.',
                'meta_description' => 'Sous-vêtements personnalisés : slips, caleçons, chaussettes et maillots de bain.',
                'seo_keywords' => 'sous-vêtement personnalisé, chaussette personnalisée, maillot de bain personnalisé',
                'default_techniques' => $this->techniqueIds(['impression-dtg', 'transfert', 'dtf']),
                'children' => [
                    [
                        'name' => 'Slips et caleçons',
                        'slug' => 'slips-calecons',
                        'meta_description' => 'Slips et caleçons personnalisables. Sous-vêtements avec marquage discret.',
                        'seo_keywords' => 'slip personnalisé, caleçon personnalisé, boxer personnalisé',
                        'default_techniques' => $this->techniqueIds(['impression-dtg', 'transfert', 'dtf']),
                    ],
                    [
                        'name' => 'Chaussettes',
                        'slug' => 'chaussettes',
                        'meta_description' => 'Chaussettes personnalisables avec votre logo. Idéales pour cadeaux d\'entreprise.',
                        'seo_keywords' => 'chaussette personnalisée, chaussette logo, chaussette entreprise',
                        'default_techniques' => $this->techniqueIds(['tissage', 'transfert']),
                    ],
                    [
                        'name' => 'Maillots de bain',
                        'slug' => 'maillots-de-bain',
                        'meta_description' => 'Maillots de bain personnalisables pour clubs et événements.',
                        'seo_keywords' => 'maillot de bain personnalisé, maillot club, maillot imprimé',
                        'default_techniques' => $this->techniqueIds(['transfert', 'dtf', 'full-print']),
                    ],
                ],
            ],

            // — Veste costume & blazer —
            [
                'name' => 'Veste costume & blazer',
                'slug' => 'blazers',
                'description' => 'Blazers et vestes de costume personnalisables avec broderie.',
                'meta_description' => 'Blazers et vestes de costume personnalisés. Broderie haut de gamme pour un look corporate.',
                'seo_keywords' => 'blazer personnalisé, veste costume brodée, blazer entreprise, veste corporate',
                'default_techniques' => $this->techniqueIds(['broderie']),
            ],

            // ═══════════════════════════════════════
            // 2. ACCESSOIRES
            // ═══════════════════════════════════════
            [
                'name' => 'Accessoires',
                'slug' => 'accessoires',
                'description' => 'Accessoires personnalisables : casquettes, sacs, écharpes, bandanas et plus encore.',
                'meta_description' => 'Accessoires personnalisés : casquettes, sacs, écharpes. Marquage broderie, sérigraphie, transfert.',
                'seo_keywords' => 'accessoire personnalisé, accessoire textile, accessoire logo, goodies textile',
                'default_techniques' => $this->techniqueIds(['broderie', 'serigraphie', 'transfert', 'dtf']),
                'children' => [

                    // — Casquettes & couvre-chef —
                    [
                        'name' => 'Casquettes & couvre-chef',
                        'slug' => 'casquettes',
                        'description' => 'Casquettes, bonnets, bobs et chapeaux personnalisables.',
                        'meta_description' => 'Casquettes et couvre-chef personnalisés avec broderie. Snapback, bérets, bonnets et bobs.',
                        'seo_keywords' => 'casquette personnalisée, casquette brodée, bonnet personnalisé, bob personnalisé',
                        'default_techniques' => $this->techniqueIds(['broderie', 'transfert', 'badge-pvc']),
                        'children' => [
                            [
                                'name' => 'Casquettes classiques',
                                'slug' => 'casquettes-classiques',
                                'meta_description' => 'Casquettes classiques personnalisables avec broderie. 5 et 6 panneaux.',
                                'seo_keywords' => 'casquette classique, casquette 6 panneaux, casquette brodée classique',
                                'default_techniques' => $this->techniqueIds(['broderie', 'transfert', 'badge-pvc']),
                            ],
                            [
                                'name' => 'Casquettes snapback',
                                'slug' => 'casquettes-snapback',
                                'meta_description' => 'Casquettes snapback personnalisables. Style urbain avec broderie ou patch.',
                                'seo_keywords' => 'snapback personnalisée, casquette snapback brodée, snapback logo',
                                'default_techniques' => $this->techniqueIds(['broderie', 'transfert', 'badge-pvc']),
                            ],
                            [
                                'name' => 'Casquettes anglaises et bérets',
                                'slug' => 'casquettes-anglaises-berets',
                                'meta_description' => 'Casquettes anglaises et bérets personnalisables. Style classique et élégant.',
                                'seo_keywords' => 'casquette anglaise, béret personnalisé, casquette gavroche, béret brodé',
                                'default_techniques' => $this->techniqueIds(['broderie', 'transfert']),
                            ],
                            [
                                'name' => 'Chapeaux',
                                'slug' => 'chapeaux',
                                'meta_description' => 'Chapeaux personnalisables avec broderie. Protection solaire avec votre logo.',
                                'seo_keywords' => 'chapeau personnalisé, chapeau brodé, chapeau soleil logo, chapeau entreprise',
                                'default_techniques' => $this->techniqueIds(['broderie', 'transfert']),
                            ],
                            [
                                'name' => 'Bobs',
                                'slug' => 'bobs',
                                'meta_description' => 'Bobs personnalisables avec broderie. Tendance et pratiques pour l\'été.',
                                'seo_keywords' => 'bob personnalisé, bob brodé, bob été logo, bucket hat personnalisé',
                                'default_techniques' => $this->techniqueIds(['broderie', 'transfert', 'badge-pvc']),
                            ],
                            [
                                'name' => 'Bonnets',
                                'slug' => 'bonnets',
                                'meta_description' => 'Bonnets personnalisables avec broderie. Chaleur et style pour l\'hiver.',
                                'seo_keywords' => 'bonnet personnalisé, bonnet brodé, bonnet logo, bonnet hiver entreprise',
                                'default_techniques' => $this->techniqueIds(['broderie', 'transfert', 'badge-pvc']),
                            ],
                            [
                                'name' => 'Chapkas',
                                'slug' => 'chapkas',
                                'meta_description' => 'Chapkas personnalisables. Protection grand froid avec broderie.',
                                'seo_keywords' => 'chapka personnalisée, chapka brodée, bonnet oreilles, chapka logo',
                                'default_techniques' => $this->techniqueIds(['broderie', 'transfert']),
                            ],
                        ],
                    ],

                    // — Sacs & valises —
                    [
                        'name' => 'Sacs & valises',
                        'slug' => 'sacs',
                        'description' => 'Sacs et valises personnalisables. Tote bags, sacs à dos, sacoches et valises.',
                        'meta_description' => 'Sacs personnalisés : tote bags, sacs à dos, sacoches. Sérigraphie, broderie et transfert.',
                        'seo_keywords' => 'sac personnalisé, tote bag personnalisé, sac à dos brodé, sacoche logo',
                        'default_techniques' => $this->techniqueIds(['serigraphie', 'impression-dtg', 'transfert', 'dtf', 'tissage']),
                        'children' => [
                            [
                                'name' => 'Sacs coton',
                                'slug' => 'sacs-coton',
                                'meta_description' => 'Sacs en coton et tote bags personnalisables. Écologiques et économiques.',
                                'seo_keywords' => 'tote bag coton, sac coton personnalisé, sac coton bio, tote bag logo',
                                'default_techniques' => $this->techniqueIds(['serigraphie', 'impression-dtg', 'transfert', 'dtf']),
                            ],
                            [
                                'name' => 'Sacoches polyester',
                                'slug' => 'sacoches-polyester',
                                'meta_description' => 'Sacoches en polyester personnalisables. Résistantes et pratiques au quotidien.',
                                'seo_keywords' => 'sacoche personnalisée, sacoche polyester, sacoche logo, sacoche entreprise',
                                'default_techniques' => $this->techniqueIds(['serigraphie', 'transfert', 'broderie']),
                            ],
                            [
                                'name' => 'Sacs à dos',
                                'slug' => 'sacs-a-dos',
                                'meta_description' => 'Sacs à dos personnalisables avec broderie ou sérigraphie. Pratiques pour tous.',
                                'seo_keywords' => 'sac à dos personnalisé, sac à dos brodé, sac à dos logo, backpack',
                                'default_techniques' => $this->techniqueIds(['broderie', 'serigraphie', 'transfert']),
                            ],
                            [
                                'name' => 'Sacs polyester',
                                'slug' => 'sacs-polyester',
                                'meta_description' => 'Sacs polyester personnalisables. Légers et résistants pour le sport et les loisirs.',
                                'seo_keywords' => 'sac polyester, sac sport personnalisé, sac polyester logo',
                                'default_techniques' => $this->techniqueIds(['serigraphie', 'transfert', 'dtf']),
                            ],
                            [
                                'name' => 'Valises',
                                'slug' => 'valises',
                                'meta_description' => 'Valises et bagages personnalisables pour voyages d\'entreprise et événements.',
                                'seo_keywords' => 'valise personnalisée, bagage logo, valise entreprise, trolley personnalisé',
                                'default_techniques' => $this->techniqueIds(['broderie', 'transfert']),
                            ],
                            [
                                'name' => 'Portefeuilles et trousses',
                                'slug' => 'portefeuilles-trousses',
                                'meta_description' => 'Portefeuilles et trousses personnalisables. Petits accessoires avec votre logo.',
                                'seo_keywords' => 'trousse personnalisée, portefeuille logo, trousse brodée, pochette personnalisée',
                                'default_techniques' => $this->techniqueIds(['broderie', 'transfert', 'serigraphie']),
                            ],
                        ],
                    ],

                    // — Écharpes et gants —
                    [
                        'name' => 'Écharpes et gants',
                        'slug' => 'echarpes-gants',
                        'description' => 'Écharpes et gants personnalisables pour l\'hiver.',
                        'meta_description' => 'Écharpes et gants personnalisés avec broderie. Accessoires hiver avec votre logo.',
                        'seo_keywords' => 'écharpe personnalisée, gant brodé, écharpe logo, gant personnalisé, accessoire hiver',
                        'default_techniques' => $this->techniqueIds(['broderie', 'tissage', 'transfert']),
                    ],

                    // — Bandanas —
                    [
                        'name' => 'Bandanas',
                        'slug' => 'bandanas',
                        'description' => 'Bandanas et foulards personnalisables.',
                        'meta_description' => 'Bandanas personnalisés avec impression. Idéals pour événements et associations.',
                        'seo_keywords' => 'bandana personnalisé, foulard imprimé, bandana logo, foulard personnalisé',
                        'default_techniques' => $this->techniqueIds(['serigraphie', 'impression-dtg', 'transfert', 'full-print']),
                    ],

                    // — Ceintures & parapluies —
                    [
                        'name' => 'Ceintures & parapluies',
                        'slug' => 'ceintures-parapluies',
                        'description' => 'Ceintures et parapluies personnalisables.',
                        'meta_description' => 'Ceintures et parapluies personnalisés. Accessoires pratiques avec votre logo.',
                        'seo_keywords' => 'parapluie personnalisé, ceinture logo, parapluie entreprise, ceinture personnalisée',
                        'default_techniques' => $this->techniqueIds(['serigraphie', 'transfert']),
                    ],
                ],
            ],

            // ═══════════════════════════════════════
            // 3. VÊTEMENTS DE TRAVAIL
            // ═══════════════════════════════════════
            [
                'name' => 'Vêtements de travail',
                'slug' => 'vetements-de-travail',
                'description' => 'Vêtements de travail personnalisables. Workwear professionnel avec marquage.',
                'meta_description' => 'Vêtements de travail personnalisés : blouses, pantalons pro, haute visibilité. Broderie et sérigraphie.',
                'seo_keywords' => 'vêtement de travail personnalisé, workwear, EPI personnalisé, vêtement pro logo',
                'default_techniques' => $this->techniqueIds(['broderie', 'serigraphie', 'transfert']),
                'children' => [
                    [
                        'name' => 'Bodywarmer',
                        'slug' => 'bodywarmer-pro',
                        'meta_description' => 'Bodywarmer professionnels personnalisables. Chaleur sans encombrement sur le terrain.',
                        'seo_keywords' => 'bodywarmer, gilet sans manches pro, bodywarmer brodé, gilet travail',
                        'default_techniques' => $this->techniqueIds(['broderie', 'transfert']),
                    ],
                    [
                        'name' => 'Polos pro',
                        'slug' => 'polos-pro',
                        'meta_description' => 'Polos professionnels personnalisables. Résistants et élégants pour le terrain.',
                        'seo_keywords' => 'polo pro, polo travail personnalisé, polo professionnel brodé',
                        'default_techniques' => $this->techniqueIds(['broderie', 'serigraphie', 'transfert']),
                    ],
                    [
                        'name' => 'Pantalons de travail',
                        'slug' => 'pantalons-de-travail',
                        'meta_description' => 'Pantalons de travail personnalisables. Robustes et fonctionnels pour les pros.',
                        'seo_keywords' => 'pantalon de travail, pantalon pro, pantalon chantier, pantalon artisan',
                        'default_techniques' => $this->techniqueIds(['broderie', 'transfert']),
                    ],
                    [
                        'name' => 'Blouses et vestes',
                        'slug' => 'blouses-vestes-travail',
                        'meta_description' => 'Blouses et vestes de travail personnalisables. Protection et image professionnelle.',
                        'seo_keywords' => 'blouse travail, veste de travail, blouse personnalisée, veste pro brodée',
                        'default_techniques' => $this->techniqueIds(['broderie', 'serigraphie', 'transfert']),
                    ],
                    [
                        'name' => 'Combinaisons',
                        'slug' => 'combinaisons-travail',
                        'meta_description' => 'Combinaisons de travail personnalisables. Protection intégrale avec votre logo.',
                        'seo_keywords' => 'combinaison travail, combinaison personnalisée, combinaison brodée, overall pro',
                        'default_techniques' => $this->techniqueIds(['broderie', 'serigraphie', 'transfert']),
                    ],
                    [
                        'name' => 'Hauts de travail',
                        'slug' => 'hauts-de-travail',
                        'meta_description' => 'T-shirts et hauts de travail personnalisables. Confort et résistance au quotidien.',
                        'seo_keywords' => 't-shirt travail, haut pro, t-shirt chantier, haut de travail personnalisé',
                        'default_techniques' => $this->techniqueIds(['broderie', 'serigraphie', 'transfert', 'dtf']),
                    ],
                    [
                        'name' => 'Sweats et pulls pro',
                        'slug' => 'sweats-pulls-pro',
                        'meta_description' => 'Sweats et pulls professionnels personnalisables. Chaleur et image de marque.',
                        'seo_keywords' => 'sweat pro, pull travail, sweat professionnel brodé, pull chantier',
                        'default_techniques' => $this->techniqueIds(['broderie', 'serigraphie', 'transfert']),
                    ],
                    [
                        'name' => 'Haute visibilité',
                        'slug' => 'haute-visibilite',
                        'meta_description' => 'Vêtements haute visibilité personnalisables. Conformes EN ISO 20471 avec votre logo.',
                        'seo_keywords' => 'haute visibilité, gilet fluo personnalisé, vêtement HV, veste fluo logo',
                        'default_techniques' => $this->techniqueIds(['serigraphie', 'transfert']),
                    ],
                    [
                        'name' => 'Accessoires pro',
                        'slug' => 'accessoires-pro',
                        'meta_description' => 'Accessoires professionnels personnalisables : casques, gants, lunettes et ceintures.',
                        'seo_keywords' => 'accessoire pro, EPI personnalisé, accessoire chantier, accessoire travail logo',
                        'default_techniques' => $this->techniqueIds(['serigraphie', 'transfert']),
                    ],
                    [
                        'name' => 'Vêtements ignifuges',
                        'slug' => 'vetements-ignifuges',
                        'meta_description' => 'Vêtements ignifuges personnalisables. Protection anti-flamme avec votre logo.',
                        'seo_keywords' => 'vêtement ignifuge, vêtement anti-feu, vêtement FR personnalisé, ignifugé logo',
                        'default_techniques' => $this->techniqueIds(['broderie', 'transfert']),
                    ],
                    [
                        'name' => 'Hôtellerie-restauration',
                        'slug' => 'hotellerie-restauration',
                        'meta_description' => 'Vêtements hôtellerie et restauration personnalisables. Tabliers, vestes de cuisine, toques.',
                        'seo_keywords' => 'vêtement restauration, tablier personnalisé, veste cuisine, toque brodée, hôtellerie',
                        'default_techniques' => $this->techniqueIds(['broderie', 'serigraphie', 'transfert']),
                    ],
                    [
                        'name' => 'Médical-santé',
                        'slug' => 'medical-sante',
                        'meta_description' => 'Vêtements médicaux personnalisables. Blouses, tuniques et pantalons pour la santé.',
                        'seo_keywords' => 'blouse médicale, tunique santé, vêtement médical personnalisé, blouse brodée',
                        'default_techniques' => $this->techniqueIds(['broderie', 'transfert']),
                    ],
                ],
            ],

            // ═══════════════════════════════════════
            // 4. SPORT
            // ═══════════════════════════════════════
            [
                'name' => 'Sport',
                'slug' => 'sport',
                'description' => 'Vêtements de sport personnalisables pour clubs, associations et événements sportifs.',
                'meta_description' => 'Vêtements de sport personnalisés : maillots, shorts, joggings. Sublimation et sérigraphie.',
                'seo_keywords' => 'vêtement sport personnalisé, maillot club, tenue sport logo, équipement sportif',
                'default_techniques' => $this->techniqueIds(['serigraphie', 'impression-dtg', 'transfert', 'dtf', 'full-print']),
                'children' => [
                    [
                        'name' => 'Chasubles et dossards',
                        'slug' => 'chasubles-dossards',
                        'meta_description' => 'Chasubles et dossards personnalisables. Indispensables pour entraînements et événements.',
                        'seo_keywords' => 'chasuble personnalisé, dossard personnalisé, chasuble sport, dossard imprimé',
                        'default_techniques' => $this->techniqueIds(['serigraphie', 'transfert', 'full-print']),
                    ],
                    [
                        'name' => 'Maillots et t-shirts sport',
                        'slug' => 'maillots-t-shirts-sport',
                        'meta_description' => 'Maillots et t-shirts sport personnalisables. Techniques respirantes pour la performance.',
                        'seo_keywords' => 'maillot sport, t-shirt sport personnalisé, maillot club, maillot technique',
                        'default_techniques' => $this->techniqueIds(['serigraphie', 'impression-dtg', 'transfert', 'dtf', 'full-print']),
                    ],
                    [
                        'name' => 'Shorts sport',
                        'slug' => 'shorts-sport',
                        'meta_description' => 'Shorts de sport personnalisables. Confort et liberté de mouvement pour vos équipes.',
                        'seo_keywords' => 'short sport, short personnalisé, short club, short sport imprimé',
                        'default_techniques' => $this->techniqueIds(['serigraphie', 'transfert', 'dtf']),
                    ],
                    [
                        'name' => 'Jogging',
                        'slug' => 'jogging-sport',
                        'meta_description' => 'Pantalons de jogging personnalisables. Entraînement et détente avec votre logo.',
                        'seo_keywords' => 'jogging personnalisé, pantalon jogging sport, jogging club, survêtement bas',
                        'default_techniques' => $this->techniqueIds(['broderie', 'serigraphie', 'transfert']),
                    ],
                    [
                        'name' => 'Tenues complètes',
                        'slug' => 'tenues-completes-sport',
                        'meta_description' => 'Tenues de sport complètes personnalisables. Maillot + short assortis pour vos équipes.',
                        'seo_keywords' => 'tenue sport complète, kit sport personnalisé, ensemble sport club',
                        'default_techniques' => $this->techniqueIds(['serigraphie', 'transfert', 'dtf', 'full-print']),
                    ],
                    [
                        'name' => 'Vestes de sport',
                        'slug' => 'vestes-sport',
                        'meta_description' => 'Vestes de sport personnalisables. Coupe-vent et vestes d\'entraînement avec votre logo.',
                        'seo_keywords' => 'veste sport, veste entraînement, veste club personnalisée, veste sport brodée',
                        'default_techniques' => $this->techniqueIds(['broderie', 'serigraphie', 'transfert']),
                    ],
                    [
                        'name' => 'Polos de sport',
                        'slug' => 'polos-de-sport',
                        'meta_description' => 'Polos de sport personnalisables en polyester respirant. Élégance sportive.',
                        'seo_keywords' => 'polo sport, polo technique personnalisé, polo sport brodé, polo club',
                        'default_techniques' => $this->techniqueIds(['broderie', 'transfert', 'dtf', 'full-print']),
                    ],
                    [
                        'name' => 'Accessoires sport',
                        'slug' => 'accessoires-sport',
                        'meta_description' => 'Accessoires de sport personnalisables : sacs, serviettes, bandeaux et brassards.',
                        'seo_keywords' => 'accessoire sport, sac sport personnalisé, serviette sport, bandeau personnalisé',
                        'default_techniques' => $this->techniqueIds(['serigraphie', 'broderie', 'transfert']),
                    ],
                ],
            ],

            // ═══════════════════════════════════════
            // 5. BIO
            // ═══════════════════════════════════════
            [
                'name' => 'Bio',
                'slug' => 'bio',
                'description' => 'Vêtements et accessoires bio et éco-responsables personnalisables. Coton biologique certifié.',
                'meta_description' => 'Textile bio personnalisé : t-shirts, sweats, sacs en coton biologique certifié GOTS et OEKO-TEX.',
                'seo_keywords' => 'textile bio, vêtement bio personnalisé, coton biologique, t-shirt bio, éco-responsable',
                'default_techniques' => $this->techniqueIds(['serigraphie', 'impression-dtg', 'transfert', 'dtf']),
                'children' => [
                    [
                        'name' => 'T-shirts bio',
                        'slug' => 't-shirts-bio',
                        'meta_description' => 'T-shirts en coton bio personnalisables. Éco-responsables et certifiés GOTS.',
                        'seo_keywords' => 't-shirt bio, t-shirt coton biologique, t-shirt éco-responsable personnalisé',
                        'default_techniques' => $this->techniqueIds(['serigraphie', 'impression-dtg', 'transfert', 'dtf']),
                    ],
                    [
                        'name' => 'Polos bio',
                        'slug' => 'polos-bio',
                        'meta_description' => 'Polos en coton bio personnalisables. Élégance durable avec broderie ou sérigraphie.',
                        'seo_keywords' => 'polo bio, polo coton biologique, polo éco-responsable, polo bio brodé',
                        'default_techniques' => $this->techniqueIds(['broderie', 'serigraphie', 'impression-dtg', 'transfert']),
                    ],
                    [
                        'name' => 'Sweats bio',
                        'slug' => 'sweats-bio',
                        'meta_description' => 'Sweats en coton bio personnalisables. Confort éthique avec marquage responsable.',
                        'seo_keywords' => 'sweat bio, hoodie bio personnalisé, sweat coton biologique, sweat éco-responsable',
                        'default_techniques' => $this->techniqueIds(['broderie', 'serigraphie', 'impression-dtg', 'dtf']),
                    ],
                    [
                        'name' => 'Sacs et accessoires bio',
                        'slug' => 'sacs-accessoires-bio',
                        'meta_description' => 'Sacs et accessoires en coton bio personnalisables. Tote bags et pochettes écologiques.',
                        'seo_keywords' => 'tote bag bio, sac coton bio, accessoire bio personnalisé, sac écologique',
                        'default_techniques' => $this->techniqueIds(['serigraphie', 'impression-dtg', 'transfert', 'dtf']),
                    ],
                    [
                        'name' => 'Bébé bio',
                        'slug' => 'bebe-bio',
                        'meta_description' => 'Vêtements bébé en coton bio personnalisables. Douceur et sécurité pour les tout-petits.',
                        'seo_keywords' => 'vêtement bébé bio, body bio, t-shirt bébé bio, layette bio personnalisée',
                        'default_techniques' => $this->techniqueIds(['impression-dtg', 'transfert', 'dtf']),
                    ],
                ],
            ],

            // ═══════════════════════════════════════
            // 6. BÉBÉ
            // ═══════════════════════════════════════
            [
                'name' => 'Bébé',
                'slug' => 'bebe',
                'description' => 'Vêtements et accessoires bébé personnalisables. Bodies, t-shirts, bonnets et pyjamas.',
                'meta_description' => 'Vêtements bébé personnalisés : bodies, t-shirts, bavoirs, pyjamas. Marquage doux et sûr.',
                'seo_keywords' => 'vêtement bébé personnalisé, body personnalisé, layette personnalisée, cadeau naissance',
                'default_techniques' => $this->techniqueIds(['impression-dtg', 'transfert', 'dtf', 'broderie']),
                'children' => [
                    [
                        'name' => 'Bodies',
                        'slug' => 'bodies-bebe',
                        'meta_description' => 'Bodies bébé personnalisables. Le basique de la garde-robe avec votre texte ou logo.',
                        'seo_keywords' => 'body bébé personnalisé, body imprimé, body naissance, body prénom',
                        'default_techniques' => $this->techniqueIds(['impression-dtg', 'transfert', 'dtf']),
                    ],
                    [
                        'name' => 'T-shirts bébé',
                        'slug' => 't-shirts-bebe',
                        'meta_description' => 'T-shirts bébé personnalisables. Coton doux et marquage adapté aux tout-petits.',
                        'seo_keywords' => 't-shirt bébé, t-shirt bébé personnalisé, t-shirt enfant petit',
                        'default_techniques' => $this->techniqueIds(['impression-dtg', 'transfert', 'dtf']),
                    ],
                    [
                        'name' => 'Bonnets et bavoirs',
                        'slug' => 'bonnets-bavoirs-bebe',
                        'meta_description' => 'Bonnets et bavoirs bébé personnalisables. Accessoires mignons avec broderie.',
                        'seo_keywords' => 'bonnet bébé personnalisé, bavoir brodé, bavoir personnalisé, bonnet naissance',
                        'default_techniques' => $this->techniqueIds(['broderie', 'transfert', 'dtf']),
                    ],
                    [
                        'name' => 'Pyjamas',
                        'slug' => 'pyjamas-bebe',
                        'meta_description' => 'Pyjamas bébé personnalisables. Douceur et confort pour les nuits de vos tout-petits.',
                        'seo_keywords' => 'pyjama bébé personnalisé, grenouillère personnalisée, dors-bien brodé',
                        'default_techniques' => $this->techniqueIds(['impression-dtg', 'transfert', 'dtf']),
                    ],
                    [
                        'name' => 'Accessoires bébé',
                        'slug' => 'accessoires-bebe',
                        'meta_description' => 'Accessoires bébé personnalisables : doudous, couvertures, sacs à langer.',
                        'seo_keywords' => 'accessoire bébé personnalisé, doudou brodé, couverture bébé, sac à langer',
                        'default_techniques' => $this->techniqueIds(['broderie', 'transfert', 'dtf']),
                    ],
                    [
                        'name' => 'Sweat-shirts bébé',
                        'slug' => 'sweats-bebe',
                        'meta_description' => 'Sweat-shirts bébé personnalisables. Chauds et doux pour les tout-petits.',
                        'seo_keywords' => 'sweat bébé, sweat bébé personnalisé, pull bébé brodé',
                        'default_techniques' => $this->techniqueIds(['broderie', 'impression-dtg', 'transfert', 'dtf']),
                    ],
                    [
                        'name' => 'Pantalons et joggings bébé',
                        'slug' => 'pantalons-joggings-bebe',
                        'meta_description' => 'Pantalons et joggings bébé personnalisables. Confort et liberté de mouvement.',
                        'seo_keywords' => 'pantalon bébé, jogging bébé personnalisé, legging bébé, bas bébé',
                        'default_techniques' => $this->techniqueIds(['impression-dtg', 'transfert', 'dtf']),
                    ],
                ],
            ],

            // ═══════════════════════════════════════
            // 7. MAISON
            // ═══════════════════════════════════════
            [
                'name' => 'Maison',
                'slug' => 'maison',
                'description' => 'Textile maison personnalisable : couvertures, serviettes, tabliers et linge de maison.',
                'meta_description' => 'Textile maison personnalisé : couvertures, serviettes, tabliers. Broderie et impression.',
                'seo_keywords' => 'textile maison personnalisé, linge maison logo, couverture personnalisée, serviette brodée',
                'default_techniques' => $this->techniqueIds(['broderie', 'serigraphie', 'impression-dtg', 'transfert']),
                'children' => [
                    [
                        'name' => 'Couvertures',
                        'slug' => 'couvertures',
                        'meta_description' => 'Couvertures et plaids personnalisables avec broderie. Cadeaux d\'entreprise haut de gamme.',
                        'seo_keywords' => 'couverture personnalisée, plaid brodé, couverture logo, plaid entreprise',
                        'default_techniques' => $this->techniqueIds(['broderie', 'transfert']),
                    ],
                    [
                        'name' => 'Chiffons',
                        'slug' => 'chiffons',
                        'meta_description' => 'Chiffons et lavettes personnalisables. Textile utilitaire avec votre logo.',
                        'seo_keywords' => 'chiffon personnalisé, lavette logo, chiffon microfibre personnalisé',
                        'default_techniques' => $this->techniqueIds(['serigraphie', 'transfert']),
                    ],
                    [
                        'name' => 'Paniers de rangement',
                        'slug' => 'paniers-rangement',
                        'meta_description' => 'Paniers de rangement en textile personnalisables. Organisation avec style.',
                        'seo_keywords' => 'panier rangement personnalisé, panier textile, panier brodé, rangement logo',
                        'default_techniques' => $this->techniqueIds(['broderie', 'serigraphie', 'transfert']),
                    ],
                    [
                        'name' => 'Taies d\'oreiller',
                        'slug' => 'taies-oreiller',
                        'meta_description' => 'Taies d\'oreiller personnalisables. Linge de lit avec impression ou broderie.',
                        'seo_keywords' => 'taie oreiller personnalisée, coussin personnalisé, taie brodée, coussin logo',
                        'default_techniques' => $this->techniqueIds(['broderie', 'impression-dtg', 'transfert', 'dtf']),
                    ],
                    [
                        'name' => 'Cuisine et bain',
                        'slug' => 'cuisine-bain',
                        'description' => 'Textile cuisine et salle de bain : tabliers, serviettes, nappes et peignoirs.',
                        'meta_description' => 'Textile cuisine et bain personnalisé : tabliers, serviettes, peignoirs. Broderie et impression.',
                        'seo_keywords' => 'tablier personnalisé, serviette brodée, peignoir logo, nappe personnalisée',
                        'default_techniques' => $this->techniqueIds(['broderie', 'serigraphie', 'transfert']),
                        'children' => [
                            [
                                'name' => 'Tabliers',
                                'slug' => 'tabliers',
                                'meta_description' => 'Tabliers de cuisine personnalisables. Broderie ou sérigraphie pour restaurants et pros.',
                                'seo_keywords' => 'tablier personnalisé, tablier brodé, tablier cuisine, tablier restaurant logo',
                                'default_techniques' => $this->techniqueIds(['broderie', 'serigraphie', 'transfert']),
                            ],
                            [
                                'name' => 'Nappes et serviettes',
                                'slug' => 'nappes-serviettes',
                                'meta_description' => 'Nappes et serviettes de table personnalisables. Linge de table avec votre logo.',
                                'seo_keywords' => 'nappe personnalisée, serviette table, linge table logo, nappe brodée',
                                'default_techniques' => $this->techniqueIds(['broderie', 'serigraphie', 'transfert']),
                            ],
                            [
                                'name' => 'Serviettes de bain et peignoirs',
                                'slug' => 'serviettes-bain-peignoirs',
                                'meta_description' => 'Serviettes de bain et peignoirs personnalisables. Broderie haut de gamme pour hôtels.',
                                'seo_keywords' => 'serviette bain brodée, peignoir personnalisé, drap de bain logo, peignoir hôtel',
                                'default_techniques' => $this->techniqueIds(['broderie', 'transfert']),
                            ],
                        ],
                    ],
                ],
            ],

            // ═══════════════════════════════════════
            // 8. TECHNIQUES DE MARQUAGE
            // ═══════════════════════════════════════
            [
                'name' => 'Techniques de marquage',
                'slug' => 'techniques-de-marquage',
                'description' => 'Découvrez toutes nos techniques de marquage textile : sérigraphie, broderie, DTG, transfert et plus.',
                'meta_description' => 'Guide des techniques de marquage textile : sérigraphie, broderie, DTG, DTF, flocage, transfert.',
                'seo_keywords' => 'technique marquage textile, impression textile, personnalisation vêtement, marquage logo',
                'default_techniques' => null,
                'children' => [
                    [
                        'name' => 'Sérigraphie',
                        'slug' => 'technique-serigraphie',
                        'meta_description' => 'La sérigraphie textile : technique idéale pour les grandes séries. Couleurs vives et durables.',
                        'seo_keywords' => 'sérigraphie textile, impression sérigraphie, sérigraphie t-shirt, sérigraphie prix',
                    ],
                    [
                        'name' => 'Impression directe DTG',
                        'slug' => 'technique-dtg',
                        'meta_description' => 'L\'impression DTG : impression numérique directe sur textile. Idéale pour les visuels complexes.',
                        'seo_keywords' => 'impression DTG, direct to garment, impression numérique textile, DTG prix',
                    ],
                    [
                        'name' => 'Broderie',
                        'slug' => 'technique-broderie',
                        'meta_description' => 'La broderie textile : marquage haut de gamme et durable. Idéale pour logos d\'entreprise.',
                        'seo_keywords' => 'broderie textile, broderie logo, broderie personnalisée, broderie vêtement',
                    ],
                    [
                        'name' => 'Transfert',
                        'slug' => 'technique-transfert',
                        'meta_description' => 'Le transfert textile : technique polyvalente pour tous types de visuels et supports.',
                        'seo_keywords' => 'transfert textile, transfert thermique, transfert sérigraphique, transfert logo',
                    ],
                    [
                        'name' => 'Flex / Flocage',
                        'slug' => 'technique-flocage',
                        'meta_description' => 'Le flex et flocage : marquage en relief idéal pour noms, numéros et textes.',
                        'seo_keywords' => 'flocage textile, flex textile, flocage t-shirt, flocage maillot, flex personnalisé',
                    ],
                    [
                        'name' => 'Tissage',
                        'slug' => 'technique-tissage',
                        'meta_description' => 'Le tissage textile : écussons et étiquettes tissées pour un rendu premium.',
                        'seo_keywords' => 'tissage textile, écusson tissé, étiquette tissée, badge tissé, patch tissé',
                    ],
                    [
                        'name' => 'DTF',
                        'slug' => 'technique-dtf',
                        'meta_description' => 'L\'impression DTF : transfert numérique polyvalent pour tous types de textiles.',
                        'seo_keywords' => 'impression DTF, direct to film, DTF textile, DTF prix, DTF personnalisation',
                    ],
                    [
                        'name' => 'Badge PVC',
                        'slug' => 'technique-badge-pvc',
                        'meta_description' => 'Les badges PVC : marquage en relief résistant. Idéal pour vêtements outdoor et sport.',
                        'seo_keywords' => 'badge PVC, patch PVC, badge caoutchouc, badge PVC personnalisé, patch 3D',
                    ],
                    [
                        'name' => 'Full Print',
                        'slug' => 'technique-full-print',
                        'meta_description' => 'Le full print : sublimation intégrale pour des impressions bord-à-bord spectaculaires.',
                        'seo_keywords' => 'full print, sublimation textile, impression intégrale, all-over print, sublimation',
                    ],
                ],
            ],
        ];
    }
}
