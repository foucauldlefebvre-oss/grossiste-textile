<?php

namespace App\Filament\Pages;

use App\Models\Invoice;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Quote;
use App\Models\User;
use App\Models\Visit;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;

class Statistics extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $navigationGroup = 'Commandes';

    protected static ?string $navigationLabel = 'Statistiques';

    protected static ?string $title = 'Statistiques';

    protected static ?int $navigationSort = 10;

    protected static string $view = 'filament.pages.statistics';

    public ?string $date_from = null;

    public ?string $date_to = null;

    public function mount(): void
    {
        $this->date_from = now()->startOfMonth()->toDateString();
        $this->date_to = now()->toDateString();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Periode')
                    ->schema([
                        DatePicker::make('date_from')
                            ->label('Du')
                            ->required()
                            ->live(),
                        DatePicker::make('date_to')
                            ->label('Au')
                            ->required()
                            ->live(),
                    ])
                    ->columns(2)
                    ->compact(),
            ]);
    }

    public function getStats(): array
    {
        $from = $this->date_from ?? now()->startOfMonth()->toDateString();
        $to = ($this->date_to ?? now()->toDateString()) . ' 23:59:59';

        return [
            'visits' => Visit::where('is_bot', false)->whereBetween('visited_at', [$from, $to])->count(),
            'unique_visitors' => Visit::where('is_bot', false)->whereBetween('visited_at', [$from, $to])->distinct('ip_hash')->count('ip_hash'),
            'bot_visits' => Visit::where('is_bot', true)->whereBetween('visited_at', [$from, $to])->count(),
            'new_clients' => User::whereBetween('created_at', [$from, $to])->count(),
            'total_clients' => User::count(),
            'quotes_created' => Quote::whereBetween('created_at', [$from, $to])->count(),
            'quotes_accepted' => Quote::where('status', 'accepted')->whereBetween('accepted_at', [$from, $to])->count(),
            'orders_created' => Order::whereBetween('created_at', [$from, $to])->count(),
            'orders_paid' => Order::where('payment_status', 'paid')->whereBetween('created_at', [$from, $to])->count(),
            'revenue_ht' => (float) Order::where('payment_status', 'paid')->whereBetween('created_at', [$from, $to])->sum('total_ht'),
            'revenue_ttc' => (float) Order::where('payment_status', 'paid')->whereBetween('created_at', [$from, $to])->sum('total_ttc'),
            'invoices' => Invoice::whereBetween('issued_at', [$from, $to])->count(),
        ];
    }

    public function getTopProducts(): array
    {
        $from = $this->date_from ?? now()->startOfMonth()->toDateString();
        $to = ($this->date_to ?? now()->toDateString()) . ' 23:59:59';

        return DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->whereBetween('orders.created_at', [$from, $to])
            ->where('orders.status', '!=', 'cancelled')
            ->select(
                'products.name',
                'products.reference',
                DB::raw('SUM(order_items.quantity) as total_qty'),
                DB::raw('COUNT(DISTINCT orders.id) as nb_orders'),
                DB::raw('SUM(order_items.line_total_ht) as total_ht'),
            )
            ->groupBy('products.id', 'products.name', 'products.reference')
            ->orderByDesc('total_qty')
            ->limit(15)
            ->get()
            ->toArray();
    }

    public function getTopTechniques(): array
    {
        $from = $this->date_from ?? now()->startOfMonth()->toDateString();
        $to = ($this->date_to ?? now()->toDateString()) . ' 23:59:59';

        return DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->leftJoin('techniques_marquage', 'techniques_marquage.id', '=', 'order_items.marking_technique_id')
            ->whereBetween('orders.created_at', [$from, $to])
            ->where('orders.status', '!=', 'cancelled')
            ->whereNotNull('order_items.marking_technique_id')
            ->select(
                'techniques_marquage.nom as name',
                DB::raw('COUNT(*) as nb_lines'),
                DB::raw('SUM(order_items.quantity) as total_qty'),
                DB::raw('SUM(order_items.marking_price_ht * order_items.quantity) as total_marking_ht'),
            )
            ->groupBy('techniques_marquage.id', 'techniques_marquage.nom')
            ->orderByDesc('total_qty')
            ->get()
            ->toArray();
    }

    public function getMarginData(): array
    {
        $from = $this->date_from ?? now()->startOfMonth()->toDateString();
        $to = ($this->date_to ?? now()->toDateString()) . ' 23:59:59';

        $data = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->whereBetween('orders.created_at', [$from, $to])
            ->where('orders.status', '!=', 'cancelled')
            ->select(
                DB::raw('SUM(order_items.line_total_ht) as total_vente_ht'),
                DB::raw('SUM(products.supplier_price * order_items.quantity) as total_achat_ht'),
            )
            ->first();

        $venteHt = (float) ($data->total_vente_ht ?? 0);
        $achatHt = (float) ($data->total_achat_ht ?? 0);
        $margeHt = $venteHt - $achatHt;
        $margePct = $venteHt > 0 ? round($margeHt / $venteHt * 100, 1) : 0;

        return [
            'vente_ht' => $venteHt,
            'achat_textile_ht' => $achatHt,
            'marge_textile_ht' => $margeHt,
            'marge_pct' => $margePct,
        ];
    }

    public function getTopPages(): array
    {
        $from = $this->date_from ?? now()->startOfMonth()->toDateString();
        $to = ($this->date_to ?? now()->toDateString()) . ' 23:59:59';

        return DB::table('visits')
            ->where('is_bot', false)
            ->whereBetween('visited_at', [$from, $to])
            ->select('path', DB::raw('COUNT(*) as views'))
            ->groupBy('path')
            ->orderByDesc('views')
            ->limit(15)
            ->get()
            ->toArray();
    }
}
