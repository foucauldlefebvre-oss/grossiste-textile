<?php

namespace App\Filament\Resources\GroupShopResource\Pages;

use App\Filament\Resources\GroupShopResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListGroupShops extends ListRecords
{
    protected static string $resource = GroupShopResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
