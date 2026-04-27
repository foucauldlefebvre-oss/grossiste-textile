<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GroupShopResource\Pages;
use App\Filament\Resources\GroupShopResource\RelationManagers;
use App\Models\GroupShop;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class GroupShopResource extends Resource
{
    protected static ?string $model = GroupShop::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationGroup = 'Boutiques';

    protected static ?string $modelLabel = 'Boutique groupée';

    protected static ?string $pluralModelLabel = 'Boutiques groupées';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informations')
                    ->columns(2)
                    ->schema([
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
                        Forms\Components\Textarea::make('description')
                            ->columnSpanFull()
                            ->rows(3),
                        Forms\Components\FileUpload::make('logo')
                            ->image()
                            ->directory('group-shops'),
                        Forms\Components\TextInput::make('password')
                            ->label('Mot de passe')
                            ->password()
                            ->revealable()
                            ->placeholder('Laisser vide si pas de protection'),
                    ]),

                Forms\Components\Section::make('Configuration')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Select::make('pricing_mode')
                            ->label('Mode tarification')
                            ->options([
                                'fixed' => 'Prix fixe',
                                'degressive' => 'Dégressif (grille)',
                            ])
                            ->required(),
                        Forms\Components\Select::make('payment_mode')
                            ->label('Mode paiement')
                            ->options([
                                'individual' => 'Individuel',
                                'collective' => 'Collectif',
                            ])
                            ->required(),
                        Forms\Components\Select::make('status')
                            ->label('Statut')
                            ->options([
                                'draft' => 'Brouillon',
                                'open' => 'Ouverte',
                                'closed' => 'Fermée',
                                'archived' => 'Archivée',
                            ])
                            ->required()
                            ->default('draft'),
                        Forms\Components\Select::make('created_by')
                            ->label('Créé par')
                            ->relationship('creator', 'name')
                            ->searchable()
                            ->required(),
                    ]),

                Forms\Components\Section::make('Période')
                    ->columns(2)
                    ->schema([
                        Forms\Components\DateTimePicker::make('opens_at')
                            ->label('Ouverture'),
                        Forms\Components\DateTimePicker::make('closes_at')
                            ->label('Fermeture'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nom')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('creator.name')
                    ->label('Créateur')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'open' => 'success',
                        'closed' => 'warning',
                        'archived' => 'danger',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'draft' => 'Brouillon',
                        'open' => 'Ouverte',
                        'closed' => 'Fermée',
                        'archived' => 'Archivée',
                    }),
                Tables\Columns\TextColumn::make('payment_mode')
                    ->label('Paiement')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'individual' => 'Individuel',
                        'collective' => 'Collectif',
                    })
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('products_count')
                    ->label('Produits')
                    ->counts('products')
                    ->sortable(),
                Tables\Columns\TextColumn::make('orders_count')
                    ->label('Commandes')
                    ->counts('orders')
                    ->sortable(),
                Tables\Columns\TextColumn::make('orders_sum_total')
                    ->label('CA HT')
                    ->sum('orders', 'total')
                    ->money('EUR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('closes_at')
                    ->label('Fermeture')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Statut')
                    ->options([
                        'draft' => 'Brouillon',
                        'open' => 'Ouverte',
                        'closed' => 'Fermée',
                        'archived' => 'Archivée',
                    ]),
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
            RelationManagers\ProductsRelationManager::class,
            RelationManagers\OrdersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListGroupShops::route('/'),
            'create' => Pages\CreateGroupShop::route('/create'),
            'edit' => Pages\EditGroupShop::route('/{record}/edit'),
        ];
    }
}
