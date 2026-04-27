<?php

namespace App\Filament\Resources\SerigraphiePricingResource\Pages;

use App\Filament\Resources\SerigraphiePricingResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSerigraphiePricing extends EditRecord
{
    protected static string $resource = SerigraphiePricingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
