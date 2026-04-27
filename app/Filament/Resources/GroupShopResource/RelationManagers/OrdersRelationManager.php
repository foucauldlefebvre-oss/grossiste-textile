<?php

namespace App\Filament\Resources\GroupShopResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class OrdersRelationManager extends RelationManager
{
    protected static string $relationship = 'orders';

    protected static ?string $title = 'Commandes';

    protected static ?string $modelLabel = 'Commande';

    public function form(Form $form): Form
    {
        return $form
            ->columns(2)
            ->schema([
                Forms\Components\TextInput::make('first_name')
                    ->label('Prenom')
                    ->required(),
                Forms\Components\TextInput::make('last_name')
                    ->label('Nom')
                    ->required(),
                Forms\Components\TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->required(),
                Forms\Components\TextInput::make('department')
                    ->label('Service'),
                Forms\Components\Select::make('group_shop_product_id')
                    ->label('Produit')
                    ->relationship('groupShopProduct.product', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\TextInput::make('size')
                    ->label('Taille')
                    ->required(),
                Forms\Components\TextInput::make('quantity')
                    ->label('Quantite')
                    ->numeric()
                    ->required()
                    ->default(1)
                    ->minValue(1),
                Forms\Components\Select::make('payment_status')
                    ->label('Paiement')
                    ->options([
                        'pending' => 'En attente',
                        'paid' => 'Paye',
                        'refunded' => 'Rembourse',
                    ])
                    ->required()
                    ->default('pending'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('last_name')
                    ->label('Nom')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('first_name')
                    ->label('Prenom')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('department')
                    ->label('Service')
                    ->placeholder('-')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('groupShopProduct.product.name')
                    ->label('Produit')
                    ->limit(30),
                Tables\Columns\TextColumn::make('size')
                    ->label('Taille')
                    ->sortable(),
                Tables\Columns\TextColumn::make('quantity')
                    ->label('Qte')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total')
                    ->label('Total HT')
                    ->money('EUR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('payment_status')
                    ->label('Paiement')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'paid' => 'success',
                        'refunded' => 'danger',
                        default => 'warning',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'paid' => 'Paye',
                        'refunded' => 'Rembourse',
                        default => 'En attente',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('payment_status')
                    ->label('Paiement')
                    ->options([
                        'pending' => 'En attente',
                        'paid' => 'Paye',
                        'refunded' => 'Rembourse',
                    ]),
                Tables\Filters\SelectFilter::make('size')
                    ->label('Taille')
                    ->options(fn () => $this->ownerRecord->orders()
                        ->distinct()
                        ->pluck('size', 'size')
                        ->toArray()),
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
                    Tables\Actions\BulkAction::make('mark_paid')
                        ->label('Marquer paye')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(fn ($records) => $records->each->update(['payment_status' => 'paid'])),
                ]),
            ]);
    }
}
