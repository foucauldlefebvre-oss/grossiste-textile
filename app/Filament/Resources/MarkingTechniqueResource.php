<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MarkingTechniqueResource\Pages;
use App\Filament\Resources\MarkingTechniqueResource\RelationManagers;
use App\Models\MarkingTechnique;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class MarkingTechniqueResource extends Resource
{
    protected static ?string $model = MarkingTechnique::class;

    protected static ?string $navigationIcon = 'heroicon-o-paint-brush';

    protected static ?string $navigationGroup = 'Catalogue';

    protected static ?string $modelLabel = 'Technique de marquage';

    protected static ?string $pluralModelLabel = 'Techniques de marquage';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Technique')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nom')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Forms\Set $set, ?string $state) => $set('slug', Str::slug($state ?? ''))),
                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        Forms\Components\Textarea::make('description')
                            ->columnSpanFull()
                            ->rows(3),
                        Forms\Components\TextInput::make('icon')
                            ->label('Icône')
                            ->placeholder('heroicon-o-...'),
                        Forms\Components\TextInput::make('sort_order')
                            ->label('Ordre')
                            ->numeric()
                            ->default(0),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),
                    ]),

                Forms\Components\Section::make('Contraintes')
                    ->relationship('constraint')
                    ->columns(3)
                    ->schema([
                        Forms\Components\TextInput::make('min_quantity')
                            ->label('Quantité minimum')
                            ->numeric()
                            ->default(1),
                        Forms\Components\TextInput::make('max_colors')
                            ->label('Couleurs max')
                            ->numeric()
                            ->placeholder('Illimité (quadri)'),
                        Forms\Components\TextInput::make('max_visual_width')
                            ->label('Largeur max (mm)')
                            ->numeric(),
                        Forms\Components\TextInput::make('max_visual_height')
                            ->label('Hauteur max (mm)')
                            ->numeric(),
                        Forms\Components\TextInput::make('production_days_min')
                            ->label('Délai min (jours)')
                            ->numeric(),
                        Forms\Components\TextInput::make('production_days_max')
                            ->label('Délai max (jours)')
                            ->numeric(),
                        Forms\Components\TagsInput::make('compatible_materials')
                            ->label('Matières compatibles')
                            ->placeholder('coton, polyester...')
                            ->columnSpanFull(),
                        Forms\Components\TagsInput::make('incompatible_materials')
                            ->label('Matières incompatibles')
                            ->placeholder('softshell, polaire...')
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('notes')
                            ->columnSpanFull()
                            ->rows(2),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Technique')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('constraint.min_quantity')
                    ->label('Qté min')
                    ->sortable(),
                Tables\Columns\TextColumn::make('constraint.max_colors')
                    ->label('Couleurs max')
                    ->placeholder('Quadri')
                    ->sortable(),
                Tables\Columns\TextColumn::make('constraint.production_days_min')
                    ->label('Délai')
                    ->formatStateUsing(fn ($record) => $record->constraint
                        ? $record->constraint->production_days_min . '-' . $record->constraint->production_days_max . 'j'
                        : '-'),
                Tables\Columns\TextColumn::make('product_rules_count')
                    ->label('Produits')
                    ->counts('productRules')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            RelationManagers\PricingsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMarkingTechniques::route('/'),
            'create' => Pages\CreateMarkingTechnique::route('/create'),
            'edit' => Pages\EditMarkingTechnique::route('/{record}/edit'),
        ];
    }
}
