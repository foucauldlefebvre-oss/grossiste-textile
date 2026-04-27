<?php

namespace App\Filament\Resources\MarkingTechniqueResource\Pages;

use App\Filament\Resources\MarkingTechniqueResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMarkingTechniques extends ListRecords
{
    protected static string $resource = MarkingTechniqueResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
