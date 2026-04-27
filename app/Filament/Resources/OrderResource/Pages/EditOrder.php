<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use App\Services\InvoiceService;
use App\Services\OrderService;
use App\Services\ShippingService;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('mark_paid')
                ->label('Marquer paye')
                ->icon('heroicon-o-banknotes')
                ->color('success')
                ->visible(fn () => $this->record->payment_status !== 'paid')
                ->requiresConfirmation()
                ->modalHeading('Confirmer le paiement')
                ->modalDescription('Ceci marquera la commande comme payee et generera une facture automatiquement.')
                ->action(function () {
                    app(OrderService::class)->markAsPaid($this->record);

                    // Generate invoice PDF
                    $this->record->refresh();
                    if ($this->record->invoice) {
                        app(InvoiceService::class)->generate($this->record->invoice);
                    }

                    Notification::make()
                        ->success()
                        ->title('Paiement confirme')
                        ->body('Facture ' . ($this->record->invoice?->number ?? '') . ' generee.')
                        ->send();

                    $this->refreshFormData(['payment_status', 'status']);
                }),

            Actions\Action::make('download_invoice')
                ->label('Telecharger facture')
                ->icon('heroicon-o-document-arrow-down')
                ->color('gray')
                ->visible(fn () => $this->record->invoice !== null)
                ->action(function () {
                    $invoice = $this->record->invoice;

                    return app(InvoiceService::class)->download($invoice);
                }),

            Actions\Action::make('mark_shipped')
                ->label('Marquer expedie')
                ->icon('heroicon-o-truck')
                ->color('info')
                ->visible(fn () => in_array($this->record->status, ['confirmed', 'in_production']))
                ->form([
                    Forms\Components\Select::make('carrier')
                        ->label('Transporteur')
                        ->options([
                            'Colissimo' => 'Colissimo',
                            'Chronopost' => 'Chronopost',
                            'Mondial Relay' => 'Mondial Relay',
                        ])
                        ->required(),
                    Forms\Components\TextInput::make('tracking_number')
                        ->label('Numero de suivi')
                        ->required()
                        ->maxLength(255),
                ])
                ->action(function (array $data) {
                    app(ShippingService::class)->markAsShipped(
                        $this->record,
                        $data['carrier'],
                        $data['tracking_number'],
                    );

                    Notification::make()
                        ->success()
                        ->title('Commande expediee')
                        ->body('Transporteur : ' . $data['carrier'] . ' — Suivi : ' . $data['tracking_number'])
                        ->send();

                    $this->refreshFormData(['status', 'carrier', 'tracking_number', 'tracking_url', 'shipped_at']);
                }),

            Actions\Action::make('mark_delivered')
                ->label('Marquer livre')
                ->icon('heroicon-o-check-badge')
                ->color('success')
                ->visible(fn () => $this->record->status === 'shipped')
                ->requiresConfirmation()
                ->modalHeading('Confirmer la livraison')
                ->action(function () {
                    app(ShippingService::class)->markAsDelivered($this->record);

                    Notification::make()
                        ->success()
                        ->title('Commande livree')
                        ->send();

                    $this->refreshFormData(['status', 'delivered_at']);
                }),

            Actions\Action::make('track')
                ->label('Suivre le colis')
                ->icon('heroicon-o-map-pin')
                ->color('warning')
                ->visible(fn () => $this->record->tracking_url !== null)
                ->url(fn () => $this->record->tracking_url)
                ->openUrlInNewTab(),

            Actions\DeleteAction::make(),
        ];
    }
}
