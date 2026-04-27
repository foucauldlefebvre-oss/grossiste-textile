<?php

namespace App\Filament\Resources\ProductResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class TechniqueRulesRelationManager extends RelationManager
{
    protected static string $relationship = 'techniqueRules';

    protected static ?string $title = 'Compatibilité marquage';

    protected static ?string $modelLabel = 'Règle technique';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('marking_technique_id')
                    ->label('Technique')
                    ->relationship('technique', 'name')
                    ->required()
                    ->preload(),
                Forms\Components\Toggle::make('is_compatible')
                    ->label('Compatible')
                    ->default(true),
                Forms\Components\TextInput::make('incompatibility_reason')
                    ->label('Raison d\'incompatibilité')
                    ->maxLength(255)
                    ->visible(fn (Forms\Get $get) => ! $get('is_compatible')),
                Forms\Components\Toggle::make('supplement_10')
                    ->label('Supplement +10%')
                    ->default(false)
                    ->visible(fn (Forms\Get $get) => $get('is_compatible')),
                Forms\Components\Toggle::make('supplement_20')
                    ->label('Supplement +20%')
                    ->default(false)
                    ->visible(fn (Forms\Get $get) => $get('is_compatible')),
                Forms\Components\CheckboxList::make('marking_zones')
                    ->label('Zones de marquage')
                    ->options([
                        'poitrine_gauche' => 'Poitrine gauche',
                        'poitrine_droite' => 'Poitrine droite',
                        'dos' => 'Dos',
                        'dos_haut' => 'Dos (haut)',
                        'manche_gauche' => 'Manche gauche',
                        'manche_droite' => 'Manche droite',
                        'col' => 'Col',
                        'face' => 'Face (casquettes)',
                        'cote' => 'Côté (casquettes)',
                    ])
                    ->columns(3),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('technique.name')
                    ->label('Technique')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_compatible')
                    ->label('Compatible')
                    ->boolean(),
                Tables\Columns\TextColumn::make('incompatibility_reason')
                    ->label('Raison')
                    ->placeholder('-')
                    ->limit(40),
                Tables\Columns\IconColumn::make('supplement_10')
                    ->label('+10%')
                    ->boolean(),
                Tables\Columns\IconColumn::make('supplement_20')
                    ->label('+20%')
                    ->boolean(),
                Tables\Columns\TextColumn::make('marking_zones')
                    ->label('Zones')
                    ->formatStateUsing(fn ($state) => is_array($state) ? implode(', ', $state) : '-')
                    ->wrap(),
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
