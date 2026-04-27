<?php

namespace App\Filament\Resources\TechniqueMarquageResource\Pages;

use App\Filament\Resources\TechniqueMarquageResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTechniqueMarquage extends EditRecord
{
    protected static string $resource = TechniqueMarquageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
