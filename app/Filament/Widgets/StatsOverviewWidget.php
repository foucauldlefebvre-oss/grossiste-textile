<?php

namespace App\Filament\Widgets;

use App\Filament\Pages\Statistics as StatisticsPage;
use App\Filament\Resources\InvoiceResource;
use App\Filament\Resources\OrderResource;
use App\Models\Cart;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\User;
use App\Models\Visit;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    protected function getColumns(): int
    {
        return 6;
    }

    protected function getStats(): array
    {
        $todayVisits = Visit::where('is_bot', false)->whereDate('visited_at', today())->count();
        $pendingOrders = Order::whereIn('status', ['pending', 'confirmed', 'in_production'])->count();
        $pendingPayment = Order::where('payment_status', 'pending')->where('status', '!=', 'cancelled')->count();

        return [
            Stat::make('Visites', $todayVisits)
                ->icon('heroicon-o-eye')
                ->url(StatisticsPage::getUrl()),

            Stat::make('Paniers actifs', Cart::active()->where('total_ht', '>', 0)->count())
                ->icon('heroicon-o-shopping-cart')
                ->color('warning'),

            Stat::make('Commandes', $pendingOrders)
                ->description($pendingPayment > 0 ? $pendingPayment . ' att. paiement' : null)
                ->icon('heroicon-o-shopping-cart')
                ->color($pendingPayment > 0 ? 'danger' : 'success')
                ->url(OrderResource::getUrl()),

            Stat::make('Factures', Invoice::count())
                ->icon('heroicon-o-document-text')
                ->color('success')
                ->url(InvoiceResource::getUrl()),

            Stat::make('Clients', User::count())
                ->icon('heroicon-o-users')
                ->color('info'),

            Stat::make('CA TTC', number_format((float) Order::where('payment_status', 'paid')->sum('total_ttc'), 0, ',', ' ') . ' €')
                ->icon('heroicon-o-banknotes')
                ->color('success')
                ->url(StatisticsPage::getUrl()),
        ];
    }
}
