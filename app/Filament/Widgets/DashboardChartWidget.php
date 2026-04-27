<?php

namespace App\Filament\Widgets;

use App\Models\Invoice;
use App\Models\Order;
use App\Models\Quote;
use App\Models\User;
use App\Models\Visit;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardChartWidget extends ChartWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $maxHeight = '300px';

    public ?string $filter = 'visits';

    protected function getFilters(): ?array
    {
        return [
            'visits' => 'Visites',
            'orders' => 'Commandes',
            'quotes' => 'Devis',
            'revenue' => 'CA TTC (€)',
            'clients' => 'Nouveaux clients',
            'invoices' => 'Factures',
        ];
    }

    public function getHeading(): string
    {
        $labels = $this->getFilters();

        return $labels[$this->filter] ?? 'Statistiques';
    }

    protected function getData(): array
    {
        $days = 30;
        $labels = [];
        $values = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $labels[] = $date->format('d/m');
            $dayStart = $date->startOfDay()->toDateTimeString();
            $dayEnd = $date->endOfDay()->toDateTimeString();

            $values[] = match ($this->filter) {
                'visits' => Visit::where('is_bot', false)->whereBetween('visited_at', [$dayStart, $dayEnd])->count(),
                'orders' => Order::whereBetween('created_at', [$dayStart, $dayEnd])->where('status', '!=', 'cancelled')->count(),
                'quotes' => Quote::whereBetween('created_at', [$dayStart, $dayEnd])->count(),
                'revenue' => (float) Order::where('payment_status', 'paid')->whereBetween('created_at', [$dayStart, $dayEnd])->sum('total_ttc'),
                'clients' => User::whereBetween('created_at', [$dayStart, $dayEnd])->count(),
                'invoices' => Invoice::whereBetween('issued_at', [$dayStart, $dayEnd])->count(),
                default => 0,
            };
        }

        $color = match ($this->filter) {
            'visits' => '#3b82f6',
            'orders' => '#ef4444',
            'quotes' => '#f59e0b',
            'revenue' => '#10b981',
            'clients' => '#6366f1',
            'invoices' => '#14b8a6',
            default => '#6b7280',
        };

        return [
            'datasets' => [
                [
                    'label' => $this->getFilters()[$this->filter] ?? '',
                    'data' => $values,
                    'borderColor' => $color,
                    'backgroundColor' => $color . '20',
                    'fill' => true,
                    'tension' => 0.3,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
