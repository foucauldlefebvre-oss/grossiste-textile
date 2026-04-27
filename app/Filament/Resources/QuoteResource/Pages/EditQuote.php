<?php

namespace App\Filament\Resources\QuoteResource\Pages;

use App\Filament\Resources\QuoteResource;
use App\Services\BatService;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditQuote extends EditRecord
{
    protected static string $resource = QuoteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('generate_bat')
                ->label('Generer BAT')
                ->icon('heroicon-o-document-arrow-down')
                ->color('info')
                ->requiresConfirmation()
                ->modalHeading('Generer le Bon a Tirer')
                ->modalDescription('Un PDF sera genere avec le detail du devis. Vous pourrez ensuite l\'envoyer au client.')
                ->action(function () {
                    $service = app(BatService::class);
                    $service->generate($this->record);

                    Notification::make()
                        ->success()
                        ->title('BAT genere')
                        ->body('Le bon a tirer a ete genere avec succes.')
                        ->send();
                }),

            Actions\Action::make('send_bat')
                ->label('Envoyer BAT')
                ->icon('heroicon-o-paper-airplane')
                ->color('success')
                ->visible(fn () => $this->record->bat_pdf !== null)
                ->requiresConfirmation()
                ->modalHeading('Envoyer le BAT au client')
                ->modalDescription('Un lien unique sera genere pour que le client puisse consulter et approuver le BAT en ligne.')
                ->action(function () {
                    $service = app(BatService::class);
                    $token = $service->send($this->record);
                    $url = $service->getApprovalUrl($this->record);

                    Notification::make()
                        ->success()
                        ->title('BAT envoye')
                        ->body('Lien d\'approbation : ' . $url)
                        ->persistent()
                        ->send();
                }),

            Actions\Action::make('download_bat')
                ->label('Telecharger BAT')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->visible(fn () => $this->record->bat_pdf !== null)
                ->action(function () {
                    $service = app(BatService::class);

                    return $service->download($this->record);
                }),

            Actions\Action::make('view_bat_link')
                ->label('Lien BAT')
                ->icon('heroicon-o-link')
                ->color('warning')
                ->visible(fn () => $this->record->bat_token !== null)
                ->url(fn () => route('bat.show', $this->record->bat_token))
                ->openUrlInNewTab(),

            Actions\DeleteAction::make(),
        ];
    }
}
