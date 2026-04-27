<?php

namespace App\Filament\Resources\MarkingTechniqueResource\Pages;

use App\Filament\Resources\MarkingTechniqueResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMarkingTechnique extends EditRecord
{
    protected static string $resource = MarkingTechniqueResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
