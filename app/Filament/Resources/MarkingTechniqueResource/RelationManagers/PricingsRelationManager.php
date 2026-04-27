<?php

namespace App\Filament\Resources\MarkingTechniqueResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class PricingsRelationManager extends RelationManager
{
    protected static string $relationship = 'pricings';

    protected static ?string $title = 'Grille tarifaire';

    protected static ?string $modelLabel = 'Tranche tarifaire';

    public function form(Form $form): Form
    {
        return $form
            ->columns(3)
            ->schema([
                Forms\Components\TextInput::make('quantity_min')
                    ->label('Qté min')
                    ->numeric()
                    ->required(),
                Forms\Components\TextInput::make('quantity_max')
                    ->label('Qté max')
                    ->numeric()
                    ->placeholder('Illimité'),
                Forms\Components\TextInput::make('num_colors')
                    ->label('Nb couleurs')
                    ->numeric()
                    ->default(1),
                Forms\Components\TextInput::make('unit_price')
                    ->label('Prix unitaire HT')
                    ->numeric()
                    ->prefix('€')
                    ->step(0.01)
                    ->required(),
                Forms\Components\TextInput::make('setup_cost')
                    ->label('Frais de mise en place')
                    ->numeric()
                    ->prefix('€')
                    ->step(0.01)
                    ->default(0),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('quantity_min')
            ->columns([
                Tables\Columns\TextColumn::make('quantity_min')
                    ->label('Qté min')
                    ->sortable(),
                Tables\Columns\TextColumn::make('quantity_max')
                    ->label('Qté max')
                    ->placeholder('∞')
                    ->sortable(),
                Tables\Columns\TextColumn::make('num_colors')
                    ->label('Couleurs')
                    ->sortable(),
                Tables\Columns\TextColumn::make('unit_price')
                    ->label('Prix unit. HT')
                    ->money('EUR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('setup_cost')
                    ->label('Mise en place')
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
