<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use App\Models\Order;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;

class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'active' => Tab::make('Actives')
                ->modifyQueryUsing(fn ($query) => $query->where('status', '!=', 'archived'))
                ->badge(Order::where('status', '!=', 'archived')->count()),
            'archived' => Tab::make('Archivees')
                ->modifyQueryUsing(fn ($query) => $query->where('status', 'archived'))
                ->badge(Order::where('status', 'archived')->count())
                ->icon('heroicon-o-archive-box'),
        ];
    }
}
