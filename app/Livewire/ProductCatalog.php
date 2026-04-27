<?php

namespace App\Livewire;

use App\Models\Category;
use App\Models\Product;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class ProductCatalog extends Component
{
    use WithPagination;

    public ?int $categoryId = null;
    public array $categoryIds = [];

    // Common filters (always visible)
    #[Url]
    public string $search = '';

    #[Url]
    public string $grammage = '';

    #[Url]
    public string $material = '';

    #[Url]
    public string $cut = '';

    #[Url]
    public string $color = '';

    // Category-specific filters
    #[Url]
    public string $supplier = '';

    #[Url]
    public string $col = '';

    #[Url]
    public string $manches = '';

    #[Url]
    public string $certification = '';

    #[Url]
    public string $impermeabilite = '';

    #[Url]
    public string $haute_visibilite = '';

    #[Url]
    public string $doublure = '';

    #[Url]
    public string $poche = '';

    #[Url]
    public string $fermeture = '';

    #[Url]
    public string $duo_trio = '';

    #[Url]
    public string $elasthanne = '';

    #[Url]
    public string $composition = '';

    #[Url]
    public string $poche_toggle = '';

    #[Url]
    public string $capuche = '';

    #[Url]
    public string $sans_manches = '';

    #[Url]
    public string $multi_poche = '';

    #[Url]
    public string $jean = '';

    #[Url]
    public string $hauteur_chaussette = '';

    #[Url]
    public string $type_chaussette = '';

    #[Url]
    public string $panneaux = '';

    #[Url]
    public string $filet = '';

    #[Url]
    public string $visiere = '';

    #[Url]
    public string $patch = '';

    #[Url]
    public string $pompon = '';

    #[Url]
    public string $ourlet = '';

    #[Url]
    public string $bicolor = '';

    #[Url]
    public string $polaire_bonnet = '';

    #[Url]
    public string $tote_bag = '';
    #[Url]
    public string $shopping = '';
    #[Url]
    public string $sac_a_dos = '';
    #[Url]
    public string $pochon = '';
    #[Url]
    public string $jute = '';
    #[Url]
    public string $bio = '';
    #[Url]
    public string $made_in_france = '';

    #[Url]
    public string $volume = '';

    #[Url]
    public string $matelasse = '';
    #[Url]
    public string $fin = '';
    #[Url]
    public string $manches_longues = '';
    #[Url]
    public string $genouillere = '';
    #[Url]
    public string $agroindustrie = '';
    #[Url]
    public string $medical = '';
    #[Url]
    public string $type_hv = '';
    #[Url]
    public string $tunique = '';
    #[Url]
    public string $coiffe = '';
    #[Url]
    public string $gilet_serveur = '';
    #[Url]
    public string $securite = '';
    #[Url]
    public string $sabot = '';
    #[Url]
    public string $sandale = '';
    #[Url]
    public string $survetement = '';
    #[Url]
    public string $maillot_short = '';
    #[Url]
    public string $chaussette_sport = '';
    #[Url]
    public string $coiffe_sport = '';
    #[Url]
    public string $ballon = '';
    #[Url]
    public string $sweat = '';
    #[Url]
    public string $fermeture_zip = '';
    #[Url]
    public string $sac_bio = '';
    #[Url]
    public string $linge = '';
    #[Url]
    public string $bavette = '';
    #[Url]
    public string $court = '';
    #[Url]
    public string $veste_travail = '';
    #[Url]
    public string $blouse = '';
    #[Url]
    public string $manche_elastique = '';

    #[Url]
    public string $sort = 'newest';

    /** All possible specific filter definitions */
    public const SPECIFIC_FILTERS = [
        'supplier' => [
            'label' => 'Marque',
            'type' => 'dynamic_select', // options built from products in category
            'field' => 'supplier',
        ],
        'col' => [
            'label' => 'Col',
            'type' => 'select',
            'options' => [
                'rond' => 'Col rond',
                'v' => 'Col V',
                'polo' => 'Col polo',
                'montant' => 'Col montant',
                'capuche' => 'Capuche',
                'zip' => 'Col zippé',
            ],
            'search_fields' => ['name', 'description', 'short_description', 'cut'],
        ],
        'manches' => [
            'label' => 'Manches',
            'type' => 'select',
            'options' => [
                'courtes' => 'Manches courtes',
                'longues' => 'Manches longues',
                'sans' => 'Sans manches',
                '3/4' => 'Manches 3/4',
            ],
            'search_fields' => ['name', 'description', 'short_description'],
        ],
        'certification' => [
            'label' => 'Certification',
            'type' => 'select',
            'options' => [
                'oeko-tex' => 'OEKO-TEX',
                'gots' => 'GOTS',
                'grs' => 'GRS',
                'fairwear' => 'Fair Wear',
            ],
            'search_fields' => ['certifications', 'description'],
        ],
        'impermeabilite' => [
            'label' => 'Imperméabilité',
            'type' => 'select',
            'options' => [
                'oui' => 'Imperméable',
                'deperlant' => 'Déperlant',
            ],
            'search_fields' => ['description', 'short_description'],
        ],
        'haute_visibilite' => [
            'label' => 'Haute visibilité',
            'type' => 'toggle',
            'search_fields' => ['name', 'description'],
        ],
        'doublure' => [
            'label' => 'Doublure',
            'type' => 'select',
            'options' => [
                'polaire' => 'Polaire',
                'mesh' => 'Mesh',
                'matelassee' => 'Matelassée',
                'sans' => 'Sans doublure',
            ],
            'search_fields' => ['description', 'short_description'],
        ],
        'poche' => [
            'label' => 'Poches',
            'type' => 'select',
            'options' => [
                'kangourou' => 'Kangourou',
                'zippees' => 'Zippées',
                'plaquees' => 'Plaquées',
                'poitrine' => 'Poitrine',
            ],
            'search_fields' => ['description', 'short_description'],
        ],
        'fermeture' => [
            'label' => 'Fermeture',
            'type' => 'select',
            'options' => [
                'zip' => 'Zip intégral',
                'demi-zip' => 'Demi-zip',
                'boutons' => 'Boutons',
                'pression' => 'Pressions',
                'sans' => 'Sans fermeture',
            ],
            'search_fields' => ['description', 'short_description'],
        ],
        'duo_trio' => [
            'label' => 'Duo / Trio',
            'type' => 'toggle',
            'search_fields' => ['name', 'description', 'short_description'],
            'search_terms' => ['duo', 'trio', 'assorti', 'parent-enfant', 'parent enfant'],
        ],
        'elasthanne' => [
            'label' => 'Élasthanne',
            'type' => 'toggle',
        ],
        'poche_toggle' => [
            'label' => 'Avec poche',
            'type' => 'toggle',
        ],
        'capuche' => [
            'label' => 'Capuche',
            'type' => 'toggle',
        ],
        'sans_manches' => [
            'label' => 'Sans manches',
            'type' => 'toggle',
        ],
        'multi_poche' => [
            'label' => 'Multi-poches',
            'type' => 'toggle',
        ],
        'jean' => [
            'label' => 'Jean / Denim',
            'type' => 'toggle',
        ],
        'composition' => [
            'label' => 'Composition',
            'type' => 'select',
            'options' => [
                '80+' => '80%+ coton',
                '60-79' => '60-79% coton',
                '50-59' => '50/50',
            ],
        ],
    ];

    public function mount(?int $categoryId = null, array $categoryIds = []): void
    {
        $this->categoryId = $categoryId;
        $this->categoryIds = $categoryIds;
    }

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingSupplier(): void { $this->resetPage(); }
    public function updatingCut(): void { $this->resetPage(); }
    public function updatingGrammage(): void { $this->resetPage(); }
    public function updatingMaterial(): void { $this->resetPage(); }
    public function updatingColor(): void { $this->resetPage(); }
    public function updatingCol(): void { $this->resetPage(); }
    public function updatingManches(): void { $this->resetPage(); }
    public function updatingCertification(): void { $this->resetPage(); }
    public function updatingImpermeabilite(): void { $this->resetPage(); }
    public function updatingHauteVisibilite(): void { $this->resetPage(); }
    public function updatingDoublure(): void { $this->resetPage(); }
    public function updatingPoche(): void { $this->resetPage(); }
    public function updatingFermeture(): void { $this->resetPage(); }
    public function updatingDuoTrio(): void { $this->resetPage(); }
    public function updatingElasthanne(): void { $this->resetPage(); }
    public function updatingComposition(): void { $this->resetPage(); }
    public function updatingPocheToggle(): void { $this->resetPage(); }
    public function updatingCapuche(): void { $this->resetPage(); }
    public function updatingSansManches(): void { $this->resetPage(); }
    public function updatingMultiPoche(): void { $this->resetPage(); }
    public function updatingJean(): void { $this->resetPage(); }
    public function updatingHauteurChaussette(): void { $this->resetPage(); }
    public function updatingTypeChaussette(): void { $this->resetPage(); }
    public function updatingPanneaux(): void { $this->resetPage(); }
    public function updatingFilet(): void { $this->resetPage(); }
    public function updatingVisiere(): void { $this->resetPage(); }
    public function updatingPatch(): void { $this->resetPage(); }
    public function updatingPompon(): void { $this->resetPage(); }
    public function updatingOurlet(): void { $this->resetPage(); }
    public function updatingBicolor(): void { $this->resetPage(); }
    public function updatingPolaireBonnet(): void { $this->resetPage(); }
    public function updatingToteBag(): void { $this->resetPage(); }
    public function updatingShopping(): void { $this->resetPage(); }
    public function updatingSacADos(): void { $this->resetPage(); }
    public function updatingPochon(): void { $this->resetPage(); }
    public function updatingJute(): void { $this->resetPage(); }
    public function updatingBio(): void { $this->resetPage(); }
    public function updatingMadeInFrance(): void { $this->resetPage(); }
    public function updatingVolume(): void { $this->resetPage(); }
    public function updatingMatelasse(): void { $this->resetPage(); }
    public function updatingFin(): void { $this->resetPage(); }
    public function updatingManchesLongues(): void { $this->resetPage(); }
    public function updatingGenouillere(): void { $this->resetPage(); }
    public function updatingAgroindustrie(): void { $this->resetPage(); }
    public function updatingMedical(): void { $this->resetPage(); }
    public function updatingTypeHv(): void { $this->resetPage(); }
    public function updatingTunique(): void { $this->resetPage(); }
    public function updatingCoiffe(): void { $this->resetPage(); }
    public function updatingGiletServeur(): void { $this->resetPage(); }
    public function updatingSecurite(): void { $this->resetPage(); }
    public function updatingSabot(): void { $this->resetPage(); }
    public function updatingSandale(): void { $this->resetPage(); }
    public function updatingSurvetement(): void { $this->resetPage(); }
    public function updatingMaillotShort(): void { $this->resetPage(); }
    public function updatingChaussetteSport(): void { $this->resetPage(); }
    public function updatingCoiffeSport(): void { $this->resetPage(); }
    public function updatingBallon(): void { $this->resetPage(); }
    public function updatingSweat(): void { $this->resetPage(); }
    public function updatingFermetureZip(): void { $this->resetPage(); }
    public function updatingSacBio(): void { $this->resetPage(); }
    public function updatingLinge(): void { $this->resetPage(); }
    public function updatingBavette(): void { $this->resetPage(); }
    public function updatingCourt(): void { $this->resetPage(); }
    public function updatingVesteTravail(): void { $this->resetPage(); }
    public function updatingBlouse(): void { $this->resetPage(); }
    public function updatingMancheElastique(): void { $this->resetPage(); }
    public function updatingSort(): void { $this->resetPage(); }

    public function clearFilters(): void
    {
        $this->reset([
            'search', 'supplier', 'cut', 'sort', 'grammage', 'material', 'color',
            'col', 'manches', 'certification', 'impermeabilite', 'haute_visibilite',
            'doublure', 'poche', 'fermeture', 'duo_trio', 'elasthanne', 'composition', 'poche_toggle', 'capuche', 'sans_manches', 'multi_poche', 'jean', 'hauteur_chaussette', 'type_chaussette', 'panneaux', 'filet', 'visiere', 'patch', 'pompon', 'ourlet', 'bicolor', 'polaire_bonnet', 'tote_bag', 'shopping', 'sac_a_dos', 'pochon', 'jute', 'bio', 'made_in_france', 'volume', 'matelasse', 'fin', 'manches_longues', 'genouillere', 'agroindustrie', 'medical', 'type_hv', 'tunique', 'coiffe', 'gilet_serveur', 'securite', 'sabot', 'sandale', 'haute_visibilite', 'survetement', 'maillot_short', 'chaussette_sport', 'coiffe_sport', 'ballon', 'sweat', 'fermeture_zip',
        ]);
        $this->resetPage();
    }

    public function hasActiveFilters(): bool
    {
        return $this->search || $this->supplier || $this->cut || $this->sort !== 'newest'
            || $this->grammage || $this->material || $this->color
            || $this->col || $this->manches || $this->certification
            || $this->impermeabilite || $this->haute_visibilite
            || $this->doublure || $this->poche || $this->fermeture || $this->duo_trio || $this->elasthanne || $this->composition || $this->poche_toggle || $this->capuche || $this->sans_manches || $this->multi_poche || $this->jean || $this->hauteur_chaussette || $this->type_chaussette || $this->panneaux || $this->filet || $this->visiere || $this->patch || $this->pompon || $this->ourlet || $this->bicolor || $this->polaire_bonnet || $this->tote_bag || $this->shopping || $this->sac_a_dos || $this->pochon || $this->jute || $this->bio || $this->made_in_france || $this->volume || $this->matelasse || $this->fin || $this->manches_longues || $this->genouillere || $this->agroindustrie || $this->medical || $this->type_hv || $this->tunique || $this->coiffe || $this->gilet_serveur || $this->securite || $this->sabot || $this->sandale || $this->haute_visibilite || $this->survetement || $this->maillot_short || $this->chaussette_sport || $this->coiffe_sport || $this->ballon || $this->sweat || $this->fermeture_zip || $this->sac_bio || $this->linge;
    }

    /** Get the specific filters enabled for the current category */
    public function getActiveSpecificFilters(): array
    {
        $defaultSupplier = ['supplier' => self::SPECIFIC_FILTERS['supplier']];

        if (! $this->categoryId) {
            return $defaultSupplier;
        }

        $category = Category::find($this->categoryId);
        if (! $category) {
            return $defaultSupplier;
        }

        $catFilters = $category->getEffectiveFilters();
        if (empty($catFilters)) {
            return $defaultSupplier;
        }

        $filters = [];

        // New format: associative array with config per filter
        // Old format: flat array of filter keys (backward compatible)
        foreach ($catFilters as $key => $config) {
            if (is_int($key)) {
                // Old format: $config is the filter key string
                if (isset(self::SPECIFIC_FILTERS[$config])) {
                    $filters[$config] = self::SPECIFIC_FILTERS[$config];
                }
                continue;
            }

            // New format: $key is filter name, $config has type/options/etc.
            $base = self::SPECIFIC_FILTERS[$key] ?? [];

            if (($config['type'] ?? '') === 'dynamic') {
                $filters[$key] = $base ?: ['label' => ucfirst($key), 'type' => 'dynamic_select', 'field' => $key];
                continue;
            }

            $filter = array_merge($base, [
                'label' => $config['label'] ?? $base['label'] ?? ucfirst($key),
                'type' => $config['type'] ?? $base['type'] ?? 'select',
            ]);

            if (isset($config['options'])) {
                $filter['options'] = $config['options'];
            }
            if (isset($config['ranges'])) {
                $filter['ranges'] = $config['ranges'];
            }

            $filters[$key] = $filter;
        }

        // Exclude internal config keys (not real filters)
        return array_filter($filters, fn ($k) => ! str_starts_with($k, '_'), ARRAY_FILTER_USE_KEY);
    }

    public function render()
    {
        $query = Product::active()->with(['colors' => fn ($q) => $q->select('id', 'product_id', 'name', 'hex_code', 'image', 'sort_order')]);

        // Categorie
        if (! empty($this->categoryIds)) {
            $catIds = $this->categoryIds;
            $query->where(function ($q) use ($catIds) {
                $q->whereIn('category_id', $catIds)
                  ->orWhereHas('secondaryCategories', fn ($sq) => $sq->whereIn('categories.id', $catIds));
            });
        } elseif ($this->categoryId) {
            $catId = $this->categoryId;
            $query->where(function ($q) use ($catId) {
                $q->where('category_id', $catId)
                  ->orWhereHas('secondaryCategories', fn ($sq) => $sq->where('categories.id', $catId));
            });
        }

        // Recherche multi-champs
        if ($this->search) {
            $words = array_filter(explode(' ', trim($this->search)), fn ($w) => strlen($w) >= 2);
            $query->where(function ($q) use ($words) {
                foreach ($words as $word) {
                    $q->where(function ($sub) use ($word) {
                        $sub->where('name', 'like', "%{$word}%")
                            ->orWhere('reference', 'like', "%{$word}%")
                            ->orWhere('description', 'like', "%{$word}%")
                            ->orWhere('short_description', 'like', "%{$word}%")
                            ->orWhere('material', 'like', "%{$word}%");
                    });
                }
            });
        }

        // Common filters
        if ($this->supplier) {
            $query->where('supplier', $this->supplier);
        }

        if ($this->cut) {
            $query->where('cut', $this->cut);
        }

        if ($this->grammage) {
            $specificFilters = $this->getActiveSpecificFilters();
            $customRanges = $specificFilters['grammage']['ranges'] ?? null;
            if ($customRanges && isset($customRanges[$this->grammage])) {
                [$min, $max] = $customRanges[$this->grammage];
            } else {
                [$min, $max] = $this->parseGrammageRange($this->grammage);
            }
            if ($min !== null) $query->where('grammage', '>=', $min);
            if ($max !== null) $query->where('grammage', '<=', $max);
        }

        if ($this->material) {
            $query->where('material', 'like', "%{$this->material}%");
        }

        if ($this->color) {
            $query->whereHas('colors', fn ($q) => $q->where('name', $this->color));
        }

        // Category-specific filters: use stored filter_tags when available, fallback to text search
        if ($this->manches) {
            $manches = $this->manches;
            $query->where(function ($q) use ($manches) {
                $q->whereJsonContains('filter_tags->manches', $manches);
                $terms = $this->getSearchTermsForFilter('manches', $manches);
                foreach ($terms as $term) {
                    $q->orWhere(function ($sub) use ($term) {
                        $sub->whereNull('filter_tags')
                            ->where(fn ($s) => $s->where('name', 'like', "%{$term}%")->orWhere('description', 'like', "%{$term}%"));
                    });
                }
            });
        }
        $this->applySpecificFilter($query, 'col', $this->col);
        $this->applySpecificFilter($query, 'certification', $this->certification);
        $this->applySpecificFilter($query, 'impermeabilite', $this->impermeabilite);
        $this->applySpecificFilter($query, 'doublure', $this->doublure);
        $this->applySpecificFilter($query, 'poche', $this->poche);
        $this->applySpecificFilter($query, 'fermeture', $this->fermeture);

        if ($this->haute_visibilite) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%haute visibilit%')
                  ->orWhere('name', 'like', '%high vis%')
                  ->orWhere('name', 'like', '%HV%')
                  ->orWhere('description', 'like', '%haute visibilit%')
                  ->orWhere('description', 'like', '%high visibility%');
            });
        }

        if ($this->duo_trio) {
            $query->whereJsonContains('filter_tags->duo_trio', true);
        }

        if ($this->elasthanne) {
            $query->whereJsonContains('filter_tags->elasthanne', true);
        }

        if ($this->composition) {
            $query->whereJsonContains('filter_tags->composition', $this->composition);
        }

        if ($this->type_hv) {
            $query->whereJsonContains('filter_tags->type_hv', $this->type_hv);
        }

        if ($this->poche_toggle) {
            $query->whereJsonContains('filter_tags->poche', true);
        }

        if ($this->capuche) {
            $query->whereJsonContains('filter_tags->capuche', true);
        }

        if ($this->sans_manches) {
            $query->whereJsonContains('filter_tags->sans_manches', true);
        }

        if ($this->multi_poche) {
            $query->whereJsonContains('filter_tags->multi_poche', true);
        }

        if ($this->jean) {
            $query->whereJsonContains('filter_tags->jean', true);
        }

        if ($this->hauteur_chaussette) {
            $query->whereJsonContains('filter_tags->hauteur_chaussette', $this->hauteur_chaussette);
        }

        if ($this->type_chaussette) {
            $query->whereJsonContains('filter_tags->type_chaussette', $this->type_chaussette);
        }

        if ($this->panneaux) {
            if ($this->panneaux === 'other') {
                $query->where(function ($q) {
                    $q->whereJsonContains('filter_tags->panneaux', 3)
                      ->orWhereJsonContains('filter_tags->panneaux', 4)
                      ->orWhereJsonContains('filter_tags->panneaux', 7);
                });
            } else {
                $query->whereJsonContains('filter_tags->panneaux', (int) $this->panneaux);
            }
        }

        if ($this->filet) {
            $query->whereJsonContains('filter_tags->filet', true);
        }

        if ($this->visiere) {
            $query->whereJsonContains('filter_tags->visiere', true);
        }

        if ($this->patch) {
            $query->whereJsonContains('filter_tags->patch', true);
        }

        if ($this->pompon) {
            $query->whereJsonContains('filter_tags->pompon', true);
        }

        if ($this->ourlet) {
            $query->whereJsonContains('filter_tags->ourlet', true);
        }

        if ($this->bicolor) {
            $query->whereJsonContains('filter_tags->bicolor', true);
        }

        if ($this->polaire_bonnet) {
            $query->whereJsonContains('filter_tags->polaire_bonnet', true);
        }

        if ($this->volume) {
            $query->where(function ($q) {
                $vol = $this->volume;
                if ($vol === 'small') {
                    $q->whereRaw("JSON_EXTRACT(filter_tags, '$.volume') <= 15 AND JSON_EXTRACT(filter_tags, '$.volume') > 0");
                } elseif ($vol === 'medium') {
                    $q->whereRaw("JSON_EXTRACT(filter_tags, '$.volume') > 15 AND JSON_EXTRACT(filter_tags, '$.volume') <= 30");
                } elseif ($vol === 'large') {
                    $q->whereRaw("JSON_EXTRACT(filter_tags, '$.volume') > 30");
                }
            });
        }

        foreach (['matelasse', 'fin', 'manches_longues', 'genouillere', 'agroindustrie', 'medical', 'tunique', 'coiffe', 'gilet_serveur', 'securite', 'sabot', 'sandale', 'haute_visibilite', 'survetement', 'maillot_short', 'chaussette_sport', 'coiffe_sport', 'ballon', 'sweat', 'fermeture_zip', 'sac_bio', 'linge', 'bavette', 'court', 'veste_travail', 'blouse', 'manche_elastique'] as $tagFilter) {
            if ($this->{$tagFilter}) {
                $query->whereJsonContains("filter_tags->{$tagFilter}", true);
            }
        }

        foreach (['tote_bag', 'shopping', 'sac_a_dos', 'pochon', 'jute', 'bio', 'made_in_france'] as $tagFilter) {
            if ($this->{$tagFilter}) {
                $query->whereJsonContains("filter_tags->{$tagFilter}", true);
            }
        }

        // Toujours : en stock d'abord, puis bestsellers, puis tri choisi
        $query = $query
            ->orderByRaw('CASE WHEN stock > 0 THEN 0 ELSE 1 END')
            ->orderByDesc('is_featured');

        $query = match ($this->sort) {
            'price_asc' => $query->orderByRaw('CASE WHEN stock > 0 THEN COALESCE(supplier_price, base_price, 99999) ELSE 99999 END ASC'),
            'price_desc' => $query->orderByRaw('CASE WHEN stock > 0 THEN COALESCE(supplier_price, base_price, 0) ELSE -1 END DESC'),
            'name' => $query->orderBy('name', 'asc'),
            'grammage' => $query->orderBy('grammage', 'asc'),
            default => $query->orderBy('created_at', 'desc'),
        };

        // Options de filtres dynamiques
        $baseQuery = Product::active();
        if (! empty($this->categoryIds)) {
            $catIds = $this->categoryIds;
            $baseQuery->where(function ($q) use ($catIds) {
                $q->whereIn('category_id', $catIds)
                  ->orWhereHas('secondaryCategories', fn ($sq) => $sq->whereIn('categories.id', $catIds));
            });
        } elseif ($this->categoryId) {
            $catId = $this->categoryId;
            $baseQuery->where(function ($q) use ($catId) {
                $q->where('category_id', $catId)
                  ->orWhereHas('secondaryCategories', fn ($sq) => $sq->where('categories.id', $catId));
            });
        }

        $suppliers = (clone $baseQuery)->whereNotNull('supplier')->where('supplier', '!=', '')
            ->distinct()->pluck('supplier')->sort()->values();

        return view('livewire.product-catalog', [
            'products' => $query->paginate(24),
            'suppliers' => $suppliers,
            'specificFilters' => $this->getActiveSpecificFilters(),
        ]);
    }

    private function applySpecificFilter($query, string $filterKey, string $value): void
    {
        if (! $value) return;

        $filter = self::SPECIFIC_FILTERS[$filterKey] ?? null;
        if (! $filter || empty($filter['search_fields'])) return;

        // Build search terms from the option key
        $searchTerms = $this->getSearchTermsForFilter($filterKey, $value);

        $fields = $filter['search_fields'];
        $query->where(function ($q) use ($searchTerms, $fields) {
            foreach ($searchTerms as $term) {
                $q->orWhere(function ($sub) use ($term, $fields) {
                    foreach ($fields as $field) {
                        $sub->orWhere($field, 'like', "%{$term}%");
                    }
                });
            }
        });
    }

    /** Map filter value to actual search terms in product data */
    private function getSearchTermsForFilter(string $filterKey, string $value): array
    {
        return match ($filterKey) {
            'col' => match ($value) {
                'rond' => ['col rond', 'crew neck', 'cuello redondo'],
                'v' => ['col v', 'col en v', 'v-neck', 'cuello pico'],
                'polo' => ['col polo', 'polo collar'],
                'montant' => ['col montant', 'col haut', 'stand-up collar'],
                'capuche' => ['capuche', 'hood', 'capucha'],
                'zip' => ['col zippé', 'zip collar', 'cremallera'],
                default => [$value],
            },
            'manches' => match ($value) {
                'courtes' => ['manches courtes', 'manga corta', 'short sleeve', 'M/C'],
                'longues' => ['manches longues', 'manga larga', 'long sleeve', 'M/L'],
                'sans' => ['sans manches', 'sin mangas', 'sleeveless', 'débardeur'],
                '3/4' => ['manches 3/4', '3/4 sleeve'],
                default => [$value],
            },
            'certification' => match ($value) {
                'oeko-tex' => ['oeko-tex', 'oekotex'],
                'gots' => ['gots'],
                'grs' => ['grs', 'global recycled'],
                'fairwear' => ['fair wear', 'fairwear'],
                default => [$value],
            },
            'impermeabilite' => match ($value) {
                'oui' => ['imperméable', 'waterproof', 'impermeable'],
                'deperlant' => ['déperlant', 'water repellent', 'water-repellent'],
                default => [$value],
            },
            'doublure' => match ($value) {
                'polaire' => ['doublure polaire', 'polaire', 'fleece lined'],
                'mesh' => ['doublure mesh', 'mesh lining', 'filet'],
                'matelassee' => ['matelassé', 'quilted', 'acolchado'],
                'sans' => ['sans doublure', 'non doublé'],
                default => [$value],
            },
            'poche' => match ($value) {
                'kangourou' => ['kangourou', 'kangaroo', 'canguro'],
                'zippees' => ['poche zippée', 'poches zippées', 'zip pocket', 'cremallera'],
                'plaquees' => ['poche plaquée', 'poches plaquées', 'patch pocket'],
                'poitrine' => ['poche poitrine', 'chest pocket', 'bolsillo pecho'],
                default => [$value],
            },
            'fermeture' => match ($value) {
                'zip' => ['zip intégral', 'full zip', 'cremallera completa'],
                'demi-zip' => ['demi-zip', 'half zip', '1/4 zip', 'media cremallera'],
                'boutons' => ['boutons', 'buttons', 'botones'],
                'pression' => ['pression', 'snap', 'automático'],
                'sans' => ['sans fermeture', 'enfilable'],
                default => [$value],
            },
            default => [$value],
        };
    }

    private function parseGrammageRange(string $range): array
    {
        return match ($range) {
            'light' => [null, 150],
            'medium' => [150, 250],
            'heavy' => [250, 350],
            'extra' => [350, null],
            default => [null, null],
        };
    }
}
