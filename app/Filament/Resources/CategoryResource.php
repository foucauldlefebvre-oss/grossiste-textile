<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CategoryResource\Pages;
use App\Livewire\ProductCatalog;
use App\Models\Category;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    protected static ?string $navigationGroup = 'Catalogue';

    protected static ?string $modelLabel = 'Catégorie';

    protected static ?string $pluralModelLabel = 'Catégories';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informations')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Select::make('parent_id')
                            ->label('Catégorie parente')
                            ->relationship('parent', 'name')
                            ->searchable()
                            ->preload()
                            ->placeholder('Aucune (racine)'),
                        Forms\Components\TextInput::make('name')
                            ->label('Nom')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Forms\Set $set, ?string $state) => $set('slug', Str::slug($state ?? ''))),
                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        Forms\Components\TextInput::make('sort_order')
                            ->label('Ordre')
                            ->numeric()
                            ->default(0),
                        Forms\Components\Textarea::make('description')
                            ->columnSpanFull()
                            ->rows(3),
                        Forms\Components\FileUpload::make('image')
                            ->image()
                            ->directory('categories')
                            ->imageResizeMode('cover')
                            ->imageCropAspectRatio('16:9'),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),
                    ]),

                Forms\Components\Section::make('Filtres du catalogue')
                    ->description('Sélectionnez les filtres disponibles pour cette catégorie. Si vide, hérite du parent.')
                    ->collapsed()
                    ->schema([
                        Forms\Components\CheckboxList::make('available_filters')
                            ->label('Filtres actifs')
                            ->options(
                                collect(ProductCatalog::SPECIFIC_FILTERS)
                                    ->mapWithKeys(fn ($filter, $key) => [$key => $filter['label']])
                                    ->toArray()
                            )
                            ->columns(3)
                            ->descriptions(
                                collect(ProductCatalog::SPECIFIC_FILTERS)
                                    ->mapWithKeys(fn ($filter, $key) => [
                                        $key => $filter['type'] === 'dynamic_select'
                                            ? 'Dynamique'
                                            : implode(', ', array_values($filter['options'] ?? [])),
                                    ])
                                    ->toArray()
                            ),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->square()
                    ->size(40),
                Tables\Columns\TextColumn::make('name')
                    ->label('Nom')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('parent.name')
                    ->label('Parente')
                    ->placeholder('Racine')
                    ->sortable(),
                Tables\Columns\TextColumn::make('products_count')
                    ->label('Produits')
                    ->counts('products')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Ordre')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('parent_id')
                    ->label('Parente')
                    ->relationship('parent', 'name')
                    ->placeholder('Toutes'),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active'),
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
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'edit' => Pages\EditCategory::route('/{record}/edit'),
        ];
    }
}
