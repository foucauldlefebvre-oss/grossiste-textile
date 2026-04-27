<?php

namespace App\Filament\Resources\QuoteResource\Pages;

use App\Filament\Resources\QuoteResource;
use App\Helpers\BatHelper;
use App\Jobs\SendBatEmailJob;
use App\Models\Quote;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Str;

class ViewQuote extends Page
{
    protected static string $resource = QuoteResource::class;

    protected static string $view = 'filament.resources.quote-resource.pages.view-quote';

    public Quote $record;

    public function mount(int $record): void
    {
        $this->record = Quote::with([
            'items.product',
            'items.color',
            'items.technique',
            'user',
        ])->findOrFail($record);
    }

    public function getTitle(): string
    {
        return 'Devis ' . $this->record->reference;
    }

    public function getBreadcrumbs(): array
    {
        return [
            QuoteResource::getUrl('index') => 'Devis',
            $this->record->reference,
        ];
    }

    protected function getHeaderActions(): array
    {
        $zone = data_get($this->record->markings, '0.zone', '');
        $positionLabel = BatHelper::zoneLabel($zone);
        $format = data_get($this->record->markings, '0.size', 'A7');

        $firstItem = $this->record->items->first();
        $productName = $firstItem?->product?->name ?? '';
        $defaultTemplate = BatHelper::detectTemplate($productName);

        return [
            // ACTION A : Retour
            Action::make('back')
                ->label('Retour')
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->action(fn () => $this->redirect(QuoteResource::getUrl('index'))),

            // ACTION B : Devis en PDF
            Action::make('pdf')
                ->label('Devis en PDF')
                ->icon('heroicon-o-document-text')
                ->color('gray')
                ->action(function () {
                    Notification::make()
                        ->title('Génération PDF — à implémenter')
                        ->info()
                        ->send();
                }),

            // ACTION C : Générer / Voir le BAT
            Action::make('bat')
                ->label(fn () => $this->record->bat_token ? 'Voir le BAT' : 'Générer le BAT')
                ->icon('heroicon-o-paint-brush')
                ->color('warning')
                ->visible(fn () => $this->record->markings !== null)
                ->action(function () {
                    if ($this->record->bat_token) {
                        $this->js("window.open('/bat/{$this->record->bat_token}', '_blank')");
                        return;
                    }

                    $zone = data_get($this->record->markings, '0.zone', '');

                    $this->record->bat_token = Str::random(48);
                    $this->record->bat_status = 'sent';
                    $this->record->bat_sent_at = now();
                    $this->record->bat_svg_template = BatHelper::buildTemplatesJson($this->record);
                    $this->record->bat_format = data_get($this->record->markings, '0.size', 'A7');
                    $this->record->bat_position_label = BatHelper::zoneLabel($zone);
                    $this->record->save();

                    SendBatEmailJob::dispatch($this->record);

                    Notification::make()
                        ->title('BAT envoyé au client')
                        ->success()
                        ->send();

                    $this->redirect(request()->header('Referer'));
                }),

            // ACTION D : Créer la commande
            Action::make('create_order')
                ->label('Créer la commande')
                ->icon('heroicon-o-shopping-cart')
                ->color('success')
                ->visible(fn () => $this->record->bat_status === 'approved')
                ->action(function () {
                    Notification::make()
                        ->title('Création commande — à implémenter')
                        ->info()
                        ->send();
                }),
        ];
    }
}
