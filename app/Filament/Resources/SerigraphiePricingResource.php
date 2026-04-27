<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SerigraphiePricingResource\Pages;
use App\Models\SerigraphiePricing;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SerigraphiePricingResource extends Resource
{
    protected static ?string $model = SerigraphiePricing::class;

    protected static ?string $navigationIcon = 'heroicon-o-table-cells';

    protected static ?string $navigationGroup = 'Config';

    protected static ?string $modelLabel = 'Tarif serigraphie';

    protected static ?string $pluralModelLabel = 'Tarifs serigraphie';

    protected static ?int $navigationSort = 6;

    public static function form(Form $form): Form
    {
        return $form
            ->columns(3)
            ->schema([
                Forms\Components\Select::make('textile_type')
                    ->label('Support')
                    ->options([
                        'light' => 'Clair',
                        'dark' => 'Fonce',
                    ])
                    ->required(),
                Forms\Components\Select::make('num_colors')
                    ->label('Couleurs')
                    ->options([1 => '1 couleur', 2 => '2 couleurs', 3 => '3 couleurs'])
                    ->required(),
                Forms\Components\TextInput::make('quantity_min')
                    ->label('Qte min')
                    ->numeric()
                    ->required(),
                Forms\Components\TextInput::make('quantity_max')
                    ->label('Qte max')
                    ->numeric()
                    ->placeholder('Illimite'),
                Forms\Components\TextInput::make('unit_price')
                    ->label('Prix unitaire HT')
                    ->numeric()
                    ->prefix('EUR')
                    ->step(0.01)
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->columns([
                Tables\Columns\TextColumn::make('textile_type')
                    ->label('Support')
                    ->formatStateUsing(fn ($state) => $state === 'light' ? 'Clair' : 'Fonce')
                    ->badge()
                    ->color(fn ($state) => $state === 'light' ? 'warning' : 'gray'),
                Tables\Columns\TextColumn::make('num_colors')
                    ->label('Couleurs')
                    ->suffix('c')
                    ->sortable(),
                Tables\Columns\TextColumn::make('quantity_min')
                    ->label('Qte min')
                    ->suffix(' pcs')
                    ->sortable(),
                Tables\Columns\TextColumn::make('quantity_max')
                    ->label('Qte max')
                    ->suffix(' pcs')
                    ->placeholder('Illimite')
                    ->sortable(),
                Tables\Columns\TextInputColumn::make('unit_price')
                    ->label('Prix unit. HT')
                    ->type('number')
                    ->step(0.01)
                    ->rules(['required', 'numeric', 'min:0']),
            ])
            ->groups([
                Tables\Grouping\Group::make('textile_type')
                    ->label('Support')
                    ->getTitleFromRecordUsing(fn ($record) => $record->textile_type === 'light'
                        ? 'Sur clair (blanc, beige, naturel, gris chine, ash, blanc chine)'
                        : 'Sur fonce (toutes les autres couleurs)')
                    ->collapsible(),
            ])
            ->defaultGroup('textile_type')
            ->filters([
                Tables\Filters\SelectFilter::make('num_colors')
                    ->label('Couleurs')
                    ->options([1 => '1 couleur', 2 => '2 couleurs', 3 => '3 couleurs']),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->paginated(false);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSerigraphiePricings::route('/'),
            'edit' => Pages\EditSerigraphiePricing::route('/{record}/edit'),
        ];
    }
}
