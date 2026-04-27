<?php

namespace App\Filament\Resources\QuoteResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    protected static ?string $title = 'Lignes du devis';

    protected static ?string $modelLabel = 'Ligne';

    public function form(Form $form): Form
    {
        return $form
            ->columns(3)
            ->schema([
                Forms\Components\Select::make('product_id')
                    ->label('Produit')
                    ->relationship('product', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\Select::make('product_color_id')
                    ->label('Coloris')
                    ->relationship('color', 'name')
                    ->searchable(),
                Forms\Components\Select::make('product_size_id')
                    ->label('Taille')
                    ->relationship('size', 'size')
                    ->searchable(),
                Forms\Components\Select::make('marking_technique_id')
                    ->label('Technique')
                    ->relationship('technique', 'name')
                    ->searchable(),
                Forms\Components\TextInput::make('quantity')
                    ->label('Quantité')
                    ->numeric()
                    ->required()
                    ->minValue(1),
                Forms\Components\Select::make('marking_zone')
                    ->label('Zone marquage')
                    ->options([
                        'poitrine_gauche' => 'Poitrine gauche',
                        'poitrine_droite' => 'Poitrine droite',
                        'dos' => 'Dos',
                        'dos_haut' => 'Dos (haut)',
                        'manche_gauche' => 'Manche gauche',
                        'manche_droite' => 'Manche droite',
                        'col' => 'Col',
                        'face' => 'Face',
                        'cote' => 'Côté',
                    ]),
                Forms\Components\TextInput::make('unit_price_ht')
                    ->label('Prix unit. HT')
                    ->numeric()
                    ->prefix('€')
                    ->step(0.01),
                Forms\Components\TextInput::make('marking_price_ht')
                    ->label('Marquage HT')
                    ->numeric()
                    ->prefix('€')
                    ->step(0.01),
                Forms\Components\TextInput::make('line_total_ht')
                    ->label('Total ligne HT')
                    ->numeric()
                    ->prefix('€')
                    ->disabled(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('product.name')
                    ->label('Produit')
                    ->limit(30),
                Tables\Columns\TextColumn::make('color.name')
                    ->label('Coloris')
                    ->placeholder('-'),
                Tables\Columns\TextColumn::make('size.size')
                    ->label('Taille')
                    ->placeholder('-'),
                Tables\Columns\TextColumn::make('technique.name')
                    ->label('Technique')
                    ->placeholder('-'),
                Tables\Columns\TextColumn::make('quantity')
                    ->label('Qté')
                    ->sortable(),
                Tables\Columns\TextColumn::make('unit_price_ht')
                    ->label('PU HT')
                    ->money('EUR'),
                Tables\Columns\TextColumn::make('line_total_ht')
                    ->label('Total HT')
                    ->money('EUR'),
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
