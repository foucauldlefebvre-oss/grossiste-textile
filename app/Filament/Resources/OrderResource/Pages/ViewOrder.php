<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use App\Filament\Resources\OrderResource\Widgets\OrderDocumentsWidget;
use App\Models\OrderDocument;
use App\Services\OrderNotificationService;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Str;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        $actions = [
            Action::make('back')
                ->label('Retour')
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url(OrderResource::getUrl('index')),

            Action::make('edit')
                ->label('Modifier')
                ->icon('heroicon-o-pencil')
                ->url(fn () => OrderResource::getUrl('edit', ['record' => $this->record])),
        ];

        // Actions BAT (seulement si commande avec marquage)
        if ($this->record->has_marking) {
            $actions[] = Action::make('upload_bat')
                ->label('Envoyer BAT')
                ->icon('heroicon-o-paint-brush')
                ->color('warning')
                ->form([
                    TextInput::make('label')
                        ->label('Libelle')
                        ->default('BAT — ' . $this->record->reference),
                    FileUpload::make('files')
                        ->label('Fichiers BAT (PDF, JPEG)')
                        ->disk('public')
                        ->directory('order-documents/' . $this->record->id)
                        ->multiple()
                        ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])
                        ->required(),
                    Toggle::make('notify_client')
                        ->label('Notifier le client par email')
                        ->default(true),
                ])
                ->action(function (array $data): void {
                    $files = is_array($data['files']) ? $data['files'] : [$data['files']];

                    foreach ($files as $path) {
                        $ext = pathinfo($path, PATHINFO_EXTENSION);
                        OrderDocument::create([
                            'order_id' => $this->record->id,
                            'type' => 'bat',
                            'label' => $data['label'],
                            'path' => $path,
                            'original_name' => basename($path),
                            'mime_type' => in_array($ext, ['jpg', 'jpeg', 'png']) ? "image/{$ext}" : 'application/pdf',
                            'size' => 0,
                            'visible_to_client' => true,
                            'uploaded_by' => auth()->user()?->name,
                            'user_id' => auth()->id(),
                        ]);
                    }

                    // Generer un token BAT si pas encore fait
                    if (! $this->record->bat_token) {
                        $this->record->update([
                            'bat_token' => Str::random(48),
                            'bat_status' => 'sent',
                        ]);
                    } else {
                        $this->record->update(['bat_status' => 'sent']);
                    }

                    if ($data['notify_client'] ?? false) {
                        app(OrderNotificationService::class)->notifyClientBatReady($this->record);
                    }

                    Notification::make()->title('BAT envoye au client')->success()->send();
                    $this->redirect(OrderResource::getUrl('view', ['record' => $this->record]));
                });

            // Statut BAT actuel
            if ($this->record->bat_status === 'approved') {
                $actions[] = Action::make('bat_approved')
                    ->label('BAT approuve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->disabled();
            } elseif ($this->record->bat_status === 'revision_requested') {
                $actions[] = Action::make('bat_revision')
                    ->label('Modification demandee')
                    ->icon('heroicon-o-exclamation-triangle')
                    ->color('danger')
                    ->disabled();
            }
        }

        // Step toggles
        $actions[] = $this->makeStepAction('prep', 'Prepa', 'heroicon-o-clipboard-document-check');
        $actions[] = $this->makeStepAction('production', 'Production', 'heroicon-o-cog-6-tooth');

        // Bouton "Prete a expedier"
        $actions[] = Action::make('ready_to_ship')
            ->label('Prete a expedier')
            ->icon('heroicon-o-truck')
            ->color($this->record->status_shipping === 'done' ? 'success' : 'warning')
            ->action(function (): void {
                if ($this->record->status_shipping === 'done') {
                    $this->record->markStepPending('shipping');
                } else {
                    $this->record->markStepDone('shipping');
                    app(OrderNotificationService::class)->notifyReadyToShip($this->record);
                }

                Notification::make()
                    ->title($this->record->status_shipping === 'done' ? 'Expedition validee' : 'Expedition remise en attente')
                    ->success()
                    ->send();

                $this->redirect(OrderResource::getUrl('view', ['record' => $this->record]));
            });

        // Upload étiquettes transport
        $actions[] = Action::make('upload_shipping_labels')
            ->label('Etiquettes colis')
            ->icon('heroicon-o-truck')
            ->color('info')
            ->form([
                Select::make('carrier')
                    ->label('Transporteur')
                    ->options([
                        'Chronopost' => 'Chronopost',
                        'Colissimo' => 'Colissimo',
                        'DPD' => 'DPD',
                        'UPS' => 'UPS',
                        'Mondial Relay' => 'Mondial Relay',
                        'Autre' => 'Autre',
                    ])
                    ->default($this->record->carrier ?? 'Chronopost')
                    ->required(),
                TextInput::make('tracking_number')
                    ->label('N° de suivi')
                    ->placeholder('Ex: XY123456789FR')
                    ->required(),
                FileUpload::make('label_file')
                    ->label('Etiquettes PDF (optionnel — si plusieurs colis)')
                    ->disk('public')
                    ->directory('shipping-labels/' . $this->record->id)
                    ->acceptedFileTypes(['application/pdf']),
                TextInput::make('tracking_url')
                    ->label('URL de suivi')
                    ->placeholder('https://www.chronopost.fr/tracking-no-de-suivi/...')
                    ->url(),
                Toggle::make('notify_client')
                    ->label('Notifier le client de l\'expedition')
                    ->default(true),
            ])
            ->action(function (array $data): void {
                $nbColis = 1;

                // Si étiquette PDF uploadée
                $path = $data['label_file'] ?? null;
                if ($path) {
                    $path = is_array($path) ? reset($path) : $path;
                    $fullPath = storage_path('app/public/' . $path);

                    if (file_exists($fullPath)) {
                        $content = file_get_contents($fullPath);
                        $nbColis = max(1, preg_match_all('/\/Type\s*\/Page[^s]/i', $content));
                    }

                    OrderDocument::create([
                        'order_id' => $this->record->id,
                        'type' => 'shipping_label',
                        'label' => 'Etiquettes — ' . $nbColis . ' colis',
                        'path' => $path,
                        'original_name' => basename($path),
                        'mime_type' => 'application/pdf',
                        'visible_to_client' => false,
                        'uploaded_by' => auth()->user()?->name,
                        'user_id' => auth()->id(),
                    ]);
                }

                // Générer l'URL de suivi automatiquement si pas renseignée
                $trackingUrl = $data['tracking_url'] ?? null;
                $trackingNumber = $data['tracking_number'] ?? null;
                if ($trackingNumber && ! $trackingUrl) {
                    $trackingUrl = match ($data['carrier'] ?? '') {
                        'Chronopost' => 'https://www.chronopost.fr/tracking-no-de-suivi/suivi-page?listeNumerosLT=' . $trackingNumber,
                        'Colissimo' => 'https://www.laposte.fr/outils/suivre-vos-envois?code=' . $trackingNumber,
                        'DPD' => 'https://trace.dpd.fr/fr/trace/' . $trackingNumber,
                        'UPS' => 'https://www.ups.com/track?tracknum=' . $trackingNumber,
                        'Mondial Relay' => 'https://www.mondialrelay.fr/suivi-de-colis/?numeroExpedition=' . $trackingNumber,
                        default => null,
                    };
                }

                // Update order shipping info
                $this->record->update([
                    'carrier' => $data['carrier'],
                    'tracking_number' => $trackingNumber,
                    'tracking_url' => $trackingUrl,
                    'shipped_at' => now(),
                    'status' => 'shipped',
                ]);

                // Notify client
                if ($data['notify_client'] ?? false) {
                    app(\App\Services\NotificationService::class)->sendOrderShipped($this->record);
                }

                Notification::make()
                    ->title($nbColis . ' colis enregistre(s) — Commande expediee')
                    ->success()
                    ->send();

                $this->redirect(OrderResource::getUrl('view', ['record' => $this->record]));
            });

        // Document generique
        $actions[] = Action::make('add_document')
            ->label('+ Document')
            ->icon('heroicon-o-paper-clip')
            ->color('gray')
            ->form([
                Select::make('type')
                    ->label('Type')
                    ->options([
                        'client_file' => 'Fichier client',
                        'invoice' => 'Facture',
                        'quote_pdf' => 'Devis PDF',
                        'other' => 'Autre',
                    ])
                    ->required(),
                TextInput::make('label')
                    ->label('Libelle'),
                FileUpload::make('file')
                    ->label('Fichier')
                    ->disk('public')
                    ->directory('order-documents/' . ($this->record?->id ?? 'tmp'))
                    ->required(),
                Toggle::make('visible_to_client')
                    ->label('Visible par le client')
                    ->default(false),
            ])
            ->action(function (array $data): void {
                $file = $data['file'];
                $path = is_array($file) ? reset($file) : $file;

                OrderDocument::create([
                    'order_id' => $this->record->id,
                    'type' => $data['type'],
                    'label' => $data['label'],
                    'path' => $path,
                    'original_name' => basename($path),
                    'visible_to_client' => $data['visible_to_client'] ?? false,
                    'uploaded_by' => auth()->user()?->name,
                    'user_id' => auth()->id(),
                ]);

                Notification::make()->title('Document ajoute')->success()->send();
            });

        return $actions;
    }

    private function makeStepAction(string $step, string $label, string $icon): Action
    {
        $statusCol = "status_{$step}";
        $isDone = ($this->record?->$statusCol ?? 'pending') === 'done';

        return Action::make("toggle_{$step}")
            ->label($isDone ? "{$label} ✓" : $label)
            ->icon($icon)
            ->color($isDone ? 'success' : 'gray')
            ->action(function () use ($step, $isDone): void {
                if ($isDone) {
                    $this->record->markStepPending($step);
                } else {
                    $this->record->markStepDone($step);
                }

                Notification::make()
                    ->title(ucfirst($step) . ($isDone ? ' remis en attente' : ' valide'))
                    ->success()
                    ->send();

                $this->redirect(OrderResource::getUrl('view', ['record' => $this->record]));
            });
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Client')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('user.name')->label('Nom'),
                        TextEntry::make('user.email')->label('Email'),
                        TextEntry::make('user.phone')->label('Telephone')->placeholder('—'),
                        TextEntry::make('user.company')->label('Societe')->placeholder('—'),
                        TextEntry::make('customer_siret')->label('SIRET')->placeholder('—'),
                        TextEntry::make('customer_vat_number')->label('TVA intra')->placeholder('—'),
                    ]),

                Section::make('Paiement')
                    ->columns(4)
                    ->schema([
                        TextEntry::make('payment_status')
                            ->label('Statut')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'paid' => 'success',
                                'partial' => 'warning',
                                default => 'danger',
                            })
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                'pending' => 'A valider',
                                'partial' => 'Acompte 50%',
                                'paid' => 'Paye',
                                'refunded' => 'Rembourse',
                                default => $state,
                            }),
                        TextEntry::make('total_ttc')
                            ->label('Total TTC')
                            ->money('EUR'),
                        TextEntry::make('amount_paid')
                            ->label('Paye')
                            ->money('EUR'),
                        TextEntry::make('customer_notes')
                            ->label('Mode')
                            ->formatStateUsing(fn (?string $state): string =>
                                str_contains($state ?? '', 'Acompte') ? 'Acompte 50/50' : 'Integral'
                            ),
                    ]),

                // Detail des blocs
                Section::make('Detail de la commande')
                    ->schema([
                        \Filament\Infolists\Components\ViewEntry::make('order_blocks')
                            ->label('')
                            ->view('filament.infolists.order-blocks'),
                    ]),

                Section::make('BAT')
                    ->columns(3)
                    ->visible(fn () => $this->record->has_marking)
                    ->schema([
                        TextEntry::make('bat_status')
                            ->label('Statut BAT')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'approved' => 'success',
                                'sent' => 'warning',
                                'revision_requested' => 'danger',
                                default => 'gray',
                            }),
                        TextEntry::make('bat_client_comment')
                            ->label('Commentaire client')
                            ->placeholder('—'),
                        TextEntry::make('bat_client_decided_at')
                            ->label('Decision le')
                            ->dateTime('d/m/Y H:i')
                            ->placeholder('—'),
                    ]),

                Section::make('Livraison')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('carrier')->label('Transporteur')->placeholder('—'),
                        TextEntry::make('tracking_number')->label('N suivi')->placeholder('—'),
                        TextEntry::make('tracking_url')
                            ->label('URL suivi')
                            ->placeholder('—')
                            ->url(fn ($record) => $record->tracking_url)
                            ->openUrlInNewTab(),
                        TextEntry::make('shipped_at')
                            ->label('Expedie le')
                            ->dateTime('d/m/Y H:i')
                            ->placeholder('—'),
                        \Filament\Infolists\Components\ViewEntry::make('shipping_labels')
                            ->label('Etiquettes')
                            ->view('filament.infolists.shipping-labels')
                            ->columnSpanFull(),
                    ]),

                Section::make('Notes')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('admin_notes')->label('Notes admin')->placeholder('—'),
                        TextEntry::make('customer_notes')->label('Notes client')->placeholder('—'),
                    ]),
            ]);
    }

    protected function getFooterWidgets(): array
    {
        return [
            OrderDocumentsWidget::class,
        ];
    }
}
