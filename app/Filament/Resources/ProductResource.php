<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Models\BrandColor;
use App\Models\BrandSize;
use App\Models\Category;
use App\Models\PricingRule;
use App\Models\Product;
use App\Models\ProductColor;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?string $navigationGroup = 'Catalogue';

    protected static ?string $modelLabel = 'Produit';

    protected static ?string $pluralModelLabel = 'Produits';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // ─── LIGNE 1 : Identification ───
                Forms\Components\Grid::make(4)
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nom du produit')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Forms\Set $set, Forms\Get $get, ?string $state) {
                                $set('slug', Str::slug($state ?? ''));
                                $categoryName = null;
                                if ($catId = $get('category_id')) {
                                    $categoryName = Category::find($catId)?->name;
                                }
                                $set('reference', Product::generateReference($get('supplier'), $categoryName, $state));
                            }),

                        Forms\Components\TextInput::make('reference')
                            ->label('Référence')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),

                        Forms\Components\Select::make('supplier')
                            ->label('Marque')
                            ->options(fn () => Product::whereNotNull('supplier')
                                ->distinct()->pluck('supplier', 'supplier')
                                ->union(collect([
                                    'Kariban' => 'Kariban',
                                    'Stanley/Stella' => 'Stanley/Stella',
                                    'B&C' => 'B&C',
                                    'Gildan' => 'Gildan',
                                    'Fruit of the Loom' => 'Fruit of the Loom',
                                    "SOL'S" => "SOL'S",
                                    'Result' => 'Result',
                                    'Roly' => 'Roly',
                                ]))
                                ->sort())
                            ->searchable()
                            ->required()
                            ->live()
                            ->afterStateUpdated(function (Forms\Set $set, Forms\Get $get, ?string $state) {
                                $categoryName = null;
                                if ($catId = $get('category_id')) {
                                    $categoryName = Category::find($catId)?->name;
                                }
                                $set('reference', Product::generateReference($state, $categoryName, $get('name')));
                            }),

                        Forms\Components\Select::make('category_id')
                            ->label('Catégorie principale')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->afterStateUpdated(function (Forms\Set $set, Forms\Get $get, ?string $state) {
                                $categoryName = Category::find($state)?->name;
                                $set('reference', Product::generateReference($get('supplier'), $categoryName, $get('name')));
                            }),

                        Forms\Components\Select::make('secondaryCategories')
                            ->label('Catégories secondaires')
                            ->relationship('secondaryCategories', 'name')
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->helperText('Navigation uniquement, URL canonique = catégorie principale'),
                    ]),

                // ─── LIGNE 2 : Technique ───
                Forms\Components\Grid::make(4)
                    ->schema([
                        Forms\Components\TextInput::make('slug')
                            ->label('URL slug')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->prefix('/{categorie}/'),

                        Forms\Components\TextInput::make('material')
                            ->label('Composition')
                            ->placeholder('100% Coton, 80/20 Poly/Coton'),

                        Forms\Components\TextInput::make('grammage')
                            ->label('Grammage')
                            ->numeric()
                            ->suffix('g/m²'),

                        Forms\Components\TextInput::make('weight')
                            ->label('Poids')
                            ->numeric()
                            ->suffix('g')
                            ->step(0.001),
                    ]),

                // ─── LIGNE 3 : Coupe + Prix + Options ───
                Forms\Components\Grid::make(5)
                    ->schema([
                        Forms\Components\Select::make('cut')
                            ->label('Coupe')
                            ->options([
                                'homme' => 'Homme',
                                'femme' => 'Femme',
                                'mixte' => 'Mixte',
                                'enfant' => 'Enfant',
                                'bebe' => 'Bébé',
                            ])
                            ->default('mixte')
                            ->required(),

                        Forms\Components\TextInput::make('supplier_price')
                            ->label('Prix fournisseur HT')
                            ->numeric()
                            ->prefix('€')
                            ->step(0.01)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Forms\Set $set, ?string $state) {
                                if ($state && (float) $state > 0) {
                                    $set('base_price', PricingRule::calculate((float) $state, 1));
                                }
                            }),

                        Forms\Components\TextInput::make('base_price')
                            ->label('Prix vente HT (auto)')
                            ->numeric()
                            ->prefix('€')
                            ->step(0.01)
                            ->helperText('Auto-calculé (fournisseur × coeff). Modifiable.'),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Actif')
                            ->default(true)
                            ->inline(false),

                        Forms\Components\Toggle::make('is_featured')
                            ->label('Mis en avant')
                            ->default(false)
                            ->inline(false),
                    ]),

                // ─── Description courte ───
                Forms\Components\Textarea::make('short_description')
                    ->label('Description courte')
                    ->rows(2)
                    ->columnSpanFull(),

                // ─── Mots-clés SEO ───
                Forms\Components\TagsInput::make('seo_keywords')
                    ->label('Mots-clés SEO')
                    ->placeholder('t-shirt personnalisé, broderie...')
                    ->columnSpanFull(),

                // ─── SECTION IMAGES ───
                Forms\Components\Section::make('Images')
                    ->icon('heroicon-o-photo')
                    ->collapsed()
                    ->columns(2)
                    ->schema([
                        Forms\Components\FileUpload::make('main_image')
                            ->label('Image principale')
                            ->image()
                            ->directory('products'),
                        Forms\Components\FileUpload::make('gallery')
                            ->label('Galerie')
                            ->image()
                            ->multiple()
                            ->directory('products/gallery')
                            ->reorderable(),
                    ]),

                // ─── SECTION DÉCLINAISONS COULEURS/TAILLES ───
                Forms\Components\Section::make('Déclinaisons couleurs / tailles')
                    ->icon('heroicon-o-swatch')
                    ->description('Chaque groupe associe des couleurs à leurs tailles disponibles')
                    ->schema([
                        Forms\Components\Repeater::make('color_groups')
                            ->label('')
                            ->schema([
                                Forms\Components\Grid::make(3)
                                    ->schema([
                                        // Couleurs sélectionnables (depuis catalogue marque ou existant)
                                        Forms\Components\Select::make('colors')
                                            ->label('Couleurs')
                                            ->multiple()
                                            ->searchable()
                                            ->options(function (Forms\Get $get) {
                                                $supplier = $get('../../supplier');

                                                // Try brand catalog first
                                                if ($supplier) {
                                                    $brandColors = BrandColor::where('brand', $supplier)
                                                        ->orderBy('name')
                                                        ->get();

                                                    if ($brandColors->isNotEmpty()) {
                                                        return $brandColors->mapWithKeys(fn ($c) => [
                                                            $c->name => $c->name . ($c->hex_code ? " ({$c->hex_code})" : ''),
                                                        ]);
                                                    }
                                                }

                                                // Fallback: existing product colors for this supplier
                                                $colors = ProductColor::query()
                                                    ->when($supplier, fn ($q) => $q->whereHas('product', fn ($pq) => $pq->where('supplier', $supplier)))
                                                    ->select('name', 'hex_code')
                                                    ->distinct()
                                                    ->orderBy('name')
                                                    ->get();

                                                if ($colors->isEmpty()) {
                                                    $colors = ProductColor::select('name', 'hex_code')
                                                        ->distinct()
                                                        ->orderBy('name')
                                                        ->get();
                                                }

                                                return $colors->mapWithKeys(fn ($c) => [
                                                    $c->name => $c->name . ($c->hex_code ? " ({$c->hex_code})" : ''),
                                                ]);
                                            })
                                            ->required()
                                            ->columnSpan(2),

                                        // Prix fournisseur spécifique pour ce groupe (blanc souvent plus cher)
                                        Forms\Components\TextInput::make('group_supplier_price')
                                            ->label('Prix fournisseur groupe')
                                            ->numeric()
                                            ->prefix('€')
                                            ->step(0.01)
                                            ->placeholder('Laisser vide = prix produit')
                                            ->helperText('Ex: blanc souvent plus cher'),
                                    ]),

                                // Tailles avec sélection rapide
                                Forms\Components\Actions::make([
                                    Forms\Components\Actions\Action::make('load_brand_sizes')
                                        ->label('Tailles marque')
                                        ->icon('heroicon-m-arrow-down-tray')
                                        ->color('success')
                                        ->size('xs')
                                        ->action(function (Forms\Set $set, Forms\Get $get) {
                                            $supplier = $get('../../supplier');
                                            if ($supplier) {
                                                $brandSizes = BrandSize::forBrand($supplier);
                                                if (! empty($brandSizes)) {
                                                    $set('sizes', $brandSizes);
                                                }
                                            }
                                        }),
                                    Forms\Components\Actions\Action::make('select_s_2xl')
                                        ->label('S → 2XL')
                                        ->color('primary')
                                        ->size('xs')
                                        ->action(function (Forms\Set $set, Forms\Get $get) {
                                            $current = $get('sizes') ?? [];
                                            $set('sizes', array_values(array_unique(array_merge($current, ['S', 'M', 'L', 'XL', '2XL']))));
                                        }),
                                    Forms\Components\Actions\Action::make('select_all_adult')
                                        ->label('Toutes adultes')
                                        ->color('gray')
                                        ->size('xs')
                                        ->action(function (Forms\Set $set, Forms\Get $get) {
                                            $current = $get('sizes') ?? [];
                                            $set('sizes', array_values(array_unique(array_merge($current, ['XXS', 'XS', 'S', 'M', 'L', 'XL', '2XL', '3XL', '4XL', '5XL', '6XL', '7XL', '8XL']))));
                                        }),
                                    Forms\Components\Actions\Action::make('select_children')
                                        ->label('Enfants')
                                        ->color('warning')
                                        ->size('xs')
                                        ->action(function (Forms\Set $set, Forms\Get $get) {
                                            $current = $get('sizes') ?? [];
                                            $set('sizes', array_values(array_unique(array_merge($current, ['2ans', '4ans', '6ans', '8ans', '10ans', '12ans', '14ans']))));
                                        }),
                                    Forms\Components\Actions\Action::make('select_baby')
                                        ->label('Bébé')
                                        ->color('info')
                                        ->size('xs')
                                        ->action(function (Forms\Set $set, Forms\Get $get) {
                                            $current = $get('sizes') ?? [];
                                            $set('sizes', array_values(array_unique(array_merge($current, ['3M', '6M', '12M', '18M', '24M']))));
                                        }),
                                    Forms\Components\Actions\Action::make('clear_sizes')
                                        ->label('Décocher')
                                        ->color('danger')
                                        ->size('xs')
                                        ->action(fn (Forms\Set $set) => $set('sizes', [])),
                                ]),

                                Forms\Components\CheckboxList::make('sizes')
                                    ->label('')
                                    ->options([
                                        'XXS' => 'XXS', 'XS' => 'XS', 'S' => 'S', 'M' => 'M', 'L' => 'L',
                                        'XL' => 'XL', '2XL' => '2XL', '3XL' => '3XL', '4XL' => '4XL',
                                        '5XL' => '5XL', '6XL' => '6XL', '7XL' => '7XL', '8XL' => '8XL',
                                        '3M' => '3 mois', '6M' => '6 mois', '12M' => '12 mois',
                                        '18M' => '18 mois', '24M' => '24 mois',
                                        '2ans' => '2 ans', '4ans' => '4 ans', '6ans' => '6 ans',
                                        '8ans' => '8 ans', '10ans' => '10 ans', '12ans' => '12 ans',
                                        '14ans' => '14 ans', 'TU' => 'Taille unique',
                                    ])
                                    ->columns(7)
                                    ->columnSpanFull(),

                                // Supplément grandes tailles
                                Forms\Components\TextInput::make('large_size_supplement')
                                    ->label('Supplément grandes tailles (3XL+)')
                                    ->numeric()
                                    ->prefix('+€')
                                    ->step(0.01)
                                    ->default(0)
                                    ->helperText('Appliqué au prix fournisseur des tailles 3XL et au-dessus'),
                            ])
                            ->addActionLabel('+ Ajouter un groupe de couleurs')
                            ->defaultItems(1)
                            ->collapsible()
                            ->itemLabel(function (array $state): ?string {
                                $colors = $state['colors'] ?? [];
                                $sizes = $state['sizes'] ?? [];
                                if (empty($colors)) {
                                    return null;
                                }
                                $colorList = implode(', ', array_slice($colors, 0, 3));
                                if (count($colors) > 3) {
                                    $colorList .= ' +' . (count($colors) - 3);
                                }
                                return $colorList . ' → ' . count($sizes) . ' taille(s)';
                            })
                            ->columnSpanFull(),
                    ]),

                // TODO 2b: section Marquage (compatible_techniques) supprimée (Q4)

                // ─── SECTION SEO (collapsed) ───
                Forms\Components\Section::make('SEO avancé')
                    ->icon('heroicon-o-globe-alt')
                    ->collapsed()
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('meta_title')
                            ->label('Titre SEO')
                            ->maxLength(70)
                            ->live(onBlur: true)
                            ->hint(fn (?string $state) => strlen($state ?? '') . '/70'),

                        Forms\Components\TextInput::make('seo_url')
                            ->label('URL personnalisée')
                            ->prefix('/{categorie}/')
                            ->unique(ignoreRecord: true),

                        Forms\Components\Textarea::make('meta_description')
                            ->label('Meta description')
                            ->maxLength(160)
                            ->rows(2)
                            ->live(onBlur: true)
                            ->hint(fn (?string $state) => strlen($state ?? '') . '/160')
                            ->columnSpanFull(),

                        Forms\Components\RichEditor::make('description')
                            ->label('Description complète (HTML)')
                            ->columnSpanFull(),

                        Forms\Components\TagsInput::make('certifications')
                            ->placeholder('OEKO-TEX, GOTS...'),

                        Forms\Components\TextInput::make('sort_order')
                            ->label('Ordre d\'affichage')
                            ->numeric()
                            ->default(0),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->defaultPaginationPageOption(20)
            ->columns([
                Tables\Columns\ImageColumn::make('main_image')
                    ->label('')
                    ->square()
                    ->size(40),
                Tables\Columns\TextColumn::make('reference')
                    ->label('Réf.')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->size('xs'),
                Tables\Columns\TextColumn::make('name')
                    ->label('Nom')
                    ->searchable()
                    ->sortable()
                    ->limit(35),
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Catégorie')
                    ->sortable()
                    ->badge()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('supplier')
                    ->label('Marque')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('cut')
                    ->label('Coupe')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'homme' => 'info',
                        'femme' => 'danger',
                        'mixte' => 'success',
                        'enfant' => 'warning',
                        'bebe' => 'info',
                        default => 'gray',
                    })
                    ->toggleable(),
                Tables\Columns\TextColumn::make('base_price')
                    ->label('Prix HT')
                    ->money('EUR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('colors_count')
                    ->label('Coloris')
                    ->counts('colors')
                    ->sortable(),
                Tables\Columns\TextColumn::make('stock_total')
                    ->label('Stock')
                    ->getStateUsing(fn ($record) => $record->colors->flatMap->sizes->sum('stock'))
                    ->badge()
                    ->color(fn ($state) => match (true) {
                        $state > 20 => 'success',
                        $state > 0 => 'warning',
                        default => 'danger',
                    })
                    ->toggleable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Actif')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category_id')
                    ->label('Catégorie')
                    ->relationship('category', 'name'),
                Tables\Filters\SelectFilter::make('supplier')
                    ->label('Marque')
                    ->options(fn () => Product::whereNotNull('supplier')->distinct()->pluck('supplier', 'supplier')),
                Tables\Filters\SelectFilter::make('cut')
                    ->label('Coupe')
                    ->options([
                        'homme' => 'Homme',
                        'femme' => 'Femme',
                        'mixte' => 'Mixte',
                        'enfant' => 'Enfant',
                        'bebe' => 'Bébé',
                    ]),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Actif'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ColorsRelationManager::class,
            // TODO 2b: TechniqueRulesRelationManager supprimé (Q4)
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
