<?php

namespace App\Filament\Resources\ShippingRateResource\Pages;

use App\Filament\Resources\ShippingRateResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageShippingRates extends ManageRecords
{
    protected static string $resource = ShippingRateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
