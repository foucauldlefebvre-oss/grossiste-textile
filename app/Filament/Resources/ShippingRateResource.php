<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ShippingRateResource\Pages;
use App\Models\ShippingRate;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ShippingRateResource extends Resource
{
    protected static ?string $model = ShippingRate::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';

    protected static ?string $navigationGroup = 'Config';

    protected static ?string $modelLabel = 'Tarif livraison';

    protected static ?string $pluralModelLabel = 'Tarifs livraison';

    protected static ?int $navigationSort = 10;

    public static function form(Form $form): Form
    {
        return $form
            ->columns(3)
            ->schema([
                Forms\Components\Select::make('carrier')
                    ->label('Transporteur')
                    ->options([
                        'Colissimo' => 'Colissimo',
                        'Chronopost' => 'Chronopost',
                        'Mondial Relay' => 'Mondial Relay',
                    ])
                    ->required()
                    ->searchable(),
                Forms\Components\Select::make('zone')
                    ->label('Zone')
                    ->options([
                        'France' => 'France metropolitaine',
                        'Europe' => 'Europe',
                        'DOM-TOM' => 'DOM-TOM',
                        'International' => 'International',
                    ])
                    ->required(),
                Forms\Components\Toggle::make('is_active')
                    ->label('Actif')
                    ->default(true),
                Forms\Components\TextInput::make('weight_min')
                    ->label('Poids min (kg)')
                    ->numeric()
                    ->step(0.001)
                    ->default(0)
                    ->required()
                    ->suffix('kg'),
                Forms\Components\TextInput::make('weight_max')
                    ->label('Poids max (kg)')
                    ->numeric()
                    ->step(0.001)
                    ->required()
                    ->suffix('kg'),
                Forms\Components\TextInput::make('price_ht')
                    ->label('Prix HT')
                    ->numeric()
                    ->step(0.01)
                    ->required()
                    ->prefix('€'),
                Forms\Components\TextInput::make('delivery_days_min')
                    ->label('Delai min (jours)')
                    ->numeric()
                    ->minValue(1),
                Forms\Components\TextInput::make('delivery_days_max')
                    ->label('Delai max (jours)')
                    ->numeric()
                    ->minValue(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('carrier')
            ->groups(['carrier', 'zone'])
            ->columns([
                Tables\Columns\TextColumn::make('carrier')
                    ->label('Transporteur')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('zone')
                    ->label('Zone')
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'France' => 'success',
                        'Europe' => 'info',
                        'DOM-TOM' => 'warning',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('weight_min')
                    ->label('Poids min')
                    ->suffix(' kg')
                    ->sortable(),
                Tables\Columns\TextColumn::make('weight_max')
                    ->label('Poids max')
                    ->suffix(' kg')
                    ->sortable(),
                Tables\Columns\TextColumn::make('price_ht')
                    ->label('Prix HT')
                    ->money('EUR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('delivery_days_min')
                    ->label('Delai')
                    ->formatStateUsing(function ($record) {
                        if ($record->delivery_days_min && $record->delivery_days_max) {
                            return $record->delivery_days_min . '-' . $record->delivery_days_max . 'j';
                        }

                        return $record->delivery_days_min ? $record->delivery_days_min . 'j' : '-';
                    }),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Actif')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('carrier')
                    ->label('Transporteur')
                    ->options([
                        'Colissimo' => 'Colissimo',
                        'Chronopost' => 'Chronopost',
                        'Mondial Relay' => 'Mondial Relay',
                    ]),
                Tables\Filters\SelectFilter::make('zone')
                    ->label('Zone')
                    ->options([
                        'France' => 'France',
                        'Europe' => 'Europe',
                        'DOM-TOM' => 'DOM-TOM',
                        'International' => 'International',
                    ]),
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageShippingRates::route('/'),
        ];
    }
}
