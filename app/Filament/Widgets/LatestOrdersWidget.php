<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestOrdersWidget extends BaseWidget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Dernieres commandes';

    public function table(Table $table): Table
    {
        return $table
            ->query(Order::query()->where('status', '!=', 'archived')->latest()->limit(8))
            ->columns([
                Tables\Columns\TextColumn::make('reference')
                    ->label('Ref.')
                    ->copyable()
                    ->size('sm'),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Client')
                    ->size('sm'),
                Tables\Columns\TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->size('sm')
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'danger',
                        'confirmed' => 'info',
                        'in_production' => 'warning',
                        'shipped' => 'primary',
                        'delivered' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'Attente',
                        'confirmed' => 'Confirmee',
                        'in_production' => 'Production',
                        'shipped' => 'Expediee',
                        'delivered' => 'Livree',
                        'cancelled' => 'Annulee',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('payment_status')
                    ->label('Paiement')
                    ->badge()
                    ->size('sm')
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'danger',
                        'partial' => 'warning',
                        'paid' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'Att.',
                        'partial' => '50%',
                        'paid' => 'Paye',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('total_ttc')
                    ->label('TTC')
                    ->money('EUR')
                    ->size('sm'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date')
                    ->date('d/m')
                    ->size('sm'),
            ])
            ->paginated(false);
    }
}
