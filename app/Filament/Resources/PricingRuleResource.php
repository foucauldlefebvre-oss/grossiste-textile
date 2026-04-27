<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PricingRuleResource\Pages;
use App\Models\PricingRule;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PricingRuleResource extends Resource
{
    protected static ?string $model = PricingRule::class;

    protected static ?string $navigationIcon = 'heroicon-o-calculator';

    protected static ?string $navigationGroup = 'Config';

    protected static ?string $modelLabel = 'Coefficient prix';

    protected static ?string $pluralModelLabel = 'Coefficients prix';

    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->columns(3)
            ->schema([
                Forms\Components\TextInput::make('label')
                    ->label('Tranche prix')
                    ->disabled(),
                Forms\Components\TextInput::make('min_price')
                    ->label('Prix min')
                    ->numeric()
                    ->prefix('€')
                    ->disabled(),
                Forms\Components\TextInput::make('max_price')
                    ->label('Prix max')
                    ->numeric()
                    ->prefix('€')
                    ->disabled()
                    ->placeholder('Illimite'),
                Forms\Components\TextInput::make('min_quantity')
                    ->label('Qte min')
                    ->numeric()
                    ->disabled(),
                Forms\Components\TextInput::make('max_quantity')
                    ->label('Qte max')
                    ->numeric()
                    ->disabled()
                    ->placeholder('Illimite'),
                Forms\Components\TextInput::make('coefficient')
                    ->label('Coefficient')
                    ->numeric()
                    ->step(0.001)
                    ->required()
                    ->prefix('x')
                    ->minValue(0.001),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->columns([
                Tables\Columns\TextColumn::make('label')
                    ->label('Tranche prix')
                    ->sortable(),
                Tables\Columns\TextColumn::make('min_quantity')
                    ->label('Qte min')
                    ->suffix(' pcs')
                    ->sortable(),
                Tables\Columns\TextColumn::make('max_quantity')
                    ->label('Qte max')
                    ->suffix(' pcs')
                    ->placeholder('Illimite')
                    ->sortable(),
                Tables\Columns\TextInputColumn::make('coefficient')
                    ->label('Coefficient')
                    ->type('number')
                    ->step(0.001)
                    ->rules(['required', 'numeric', 'min:0.001']),
            ])
            ->groups([
                Tables\Grouping\Group::make('label')
                    ->label('Tranche prix')
                    ->collapsible(),
            ])
            ->defaultGroup('label')
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->paginated(false);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPricingRules::route('/'),
            'edit' => Pages\EditPricingRule::route('/{record}/edit'),
        ];
    }
}
