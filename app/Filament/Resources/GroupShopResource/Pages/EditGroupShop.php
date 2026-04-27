<?php

namespace App\Filament\Resources\GroupShopResource\Pages;

use App\Exports\GroupShopOrdersExport;
use App\Filament\Resources\GroupShopResource;
use App\Imports\GroupShopOrdersImport;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Maatwebsite\Excel\Facades\Excel;

class EditGroupShop extends EditRecord
{
    protected static string $resource = GroupShopResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('export')
                ->label('Exporter Excel')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->action(function () {
                    $name = 'commandes-' . $this->record->slug . '-' . date('Y-m-d') . '.xlsx';

                    return Excel::download(
                        new GroupShopOrdersExport($this->record->id),
                        $name,
                    );
                }),

            Actions\Action::make('import')
                ->label('Importer Excel')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('info')
                ->form([
                    Forms\Components\FileUpload::make('file')
                        ->label('Fichier Excel')
                        ->acceptedFileTypes(['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-excel', 'text/csv'])
                        ->required()
                        ->directory('imports'),
                ])
                ->action(function (array $data) {
                    $import = new GroupShopOrdersImport($this->record->id);
                    Excel::import($import, storage_path('app/public/' . $data['file']));

                    Notification::make()
                        ->success()
                        ->title('Import termine')
                        ->body($import->getImportedCount() . ' commande(s) importee(s).')
                        ->send();
                }),

            Actions\Action::make('view_shop')
                ->label('Voir la boutique')
                ->icon('heroicon-o-eye')
                ->url(fn () => route('group-shop.show', $this->record))
                ->openUrlInNewTab(),

            Actions\DeleteAction::make(),
        ];
    }
}
