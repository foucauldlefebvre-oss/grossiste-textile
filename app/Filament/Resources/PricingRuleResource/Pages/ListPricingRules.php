<?php

namespace App\Filament\Resources\PricingRuleResource\Pages;

use App\Filament\Resources\PricingRuleResource;
use Filament\Resources\Pages\ListRecords;

class ListPricingRules extends ListRecords
{
    protected static string $resource = PricingRuleResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
