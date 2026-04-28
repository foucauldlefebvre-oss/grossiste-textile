<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CartResource\Pages;
use App\Models\Cart;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CartResource extends Resource
{
    protected static ?string $model = Cart::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    protected static ?string $navigationGroup = 'Commandes';

    protected static ?string $modelLabel = 'Panier';

    protected static ?string $pluralModelLabel = 'Paniers';

    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Placeholder::make('readonly')
                ->label('Lecture seule')
                ->content('Les paniers ne sont pas éditables côté admin. Suivi commercial uniquement.'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('#')->sortable(),
                Tables\Columns\TextColumn::make('user.name')->label('Client')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('user.email')->label('Email')->searchable()->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Statut')
                    ->colors([
                        'warning' => 'active',
                        'success' => 'converted',
                        'gray' => 'abandoned',
                    ])
                    ->sortable(),
                Tables\Columns\TextColumn::make('items_count')
                    ->label('Articles')
                    ->counts('items'),
                Tables\Columns\TextColumn::make('total_ht')
                    ->label('Total HT')
                    ->money('eur')
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Dernière modif')
                    ->since()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active' => 'Actif',
                        'converted' => 'Converti',
                        'abandoned' => 'Abandonné',
                    ])
                    ->default('active'),
                Tables\Filters\Filter::make('above_min_order')
                    ->label('Total > 100€ HT (au-dessus du min commande)')
                    ->query(fn ($q) => $q->where('total_ht', '>', 100)),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->defaultSort('updated_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCarts::route('/'),
            'view' => Pages\ViewCart::route('/{record}'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }
}
