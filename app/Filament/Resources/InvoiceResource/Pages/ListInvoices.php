<?php

namespace App\Filament\Resources\InvoiceResource\Pages;

use App\Exports\InvoicesExport;
use App\Filament\Resources\InvoiceResource;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Pages\ListRecords;
use Maatwebsite\Excel\Facades\Excel;

class ListInvoices extends ListRecords
{
    protected static string $resource = InvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('export')
                ->label('Exporter Excel')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->form([
                    Forms\Components\DatePicker::make('from')
                        ->label('Du')
                        ->required()
                        ->default(now()->startOfMonth()),
                    Forms\Components\DatePicker::make('to')
                        ->label('Au')
                        ->required()
                        ->default(now()),
                ])
                ->action(function (array $data) {
                    $name = 'factures-' . $data['from'] . '-' . $data['to'] . '.xlsx';

                    return Excel::download(
                        new InvoicesExport($data['from'], $data['to']),
                        $name,
                    );
                }),
        ];
    }
}
