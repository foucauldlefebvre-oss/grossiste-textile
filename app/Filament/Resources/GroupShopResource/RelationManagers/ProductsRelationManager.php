<?php

namespace App\Filament\Resources\GroupShopResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ProductsRelationManager extends RelationManager
{
    protected static string $relationship = 'products';

    protected static ?string $title = 'Produits de la boutique';

    protected static ?string $modelLabel = 'Produit';

    public function form(Form $form): Form
    {
        return $form
            ->columns(2)
            ->schema([
                Forms\Components\Select::make('product_id')
                    ->label('Produit')
                    ->relationship('product', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\Select::make('marking_technique_id')
                    ->label('Technique de marquage')
                    ->relationship('technique', 'name')
                    ->searchable()
                    ->preload(),
                Forms\Components\TextInput::make('fixed_price')
                    ->label('Prix fixe HT')
                    ->numeric()
                    ->prefix('€')
                    ->step(0.01)
                    ->placeholder('Utiliser la grille'),
                Forms\Components\TextInput::make('sort_order')
                    ->label('Ordre')
                    ->numeric()
                    ->default(0),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->columns([
                Tables\Columns\TextColumn::make('product.name')
                    ->label('Produit')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('technique.name')
                    ->label('Technique')
                    ->placeholder('-'),
                Tables\Columns\TextColumn::make('fixed_price')
                    ->label('Prix fixe HT')
                    ->money('EUR')
                    ->placeholder('Grille'),
                Tables\Columns\TextColumn::make('orders_count')
                    ->label('Commandes')
                    ->counts('orders'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
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
}
