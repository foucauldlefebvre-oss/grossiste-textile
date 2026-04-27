<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Models\Order;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    protected static ?string $navigationGroup = 'Commandes';

    protected static ?string $modelLabel = 'Commande';

    protected static ?string $pluralModelLabel = 'Commandes';

    protected static ?int $navigationSort = 2;

    public static function getNavigationBadge(): ?string
    {
        $pending = Order::where('payment_status', 'pending')->where('status', 'pending')->count();
        return $pending > 0 ? (string) $pending : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Commande')
                    ->columns(3)
                    ->schema([
                        Forms\Components\TextInput::make('reference')
                            ->label('Reference')
                            ->disabled(),
                        Forms\Components\Select::make('user_id')
                            ->label('Client')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->disabled(),
                        Forms\Components\Select::make('status')
                            ->label('Statut')
                            ->options([
                                'pending' => 'En attente de paiement',
                                'confirmed' => 'Confirmee',
                                'in_production' => 'En production',
                                'shipped' => 'Expediee',
                                'delivered' => 'Livree',
                                'cancelled' => 'Annulee',
                                'refunded' => 'Remboursee',
                            ])
                            ->required(),
                        Forms\Components\Select::make('payment_status')
                            ->label('Paiement')
                            ->options([
                                'pending' => 'En attente',
                                'partial' => 'Acompte recu (50%)',
                                'paid' => 'Paye integralement',
                                'refunded' => 'Rembourse',
                            ])
                            ->required(),
                        Forms\Components\Placeholder::make('payment_mode')
                            ->label('Mode de paiement')
                            ->content(function ($record) {
                                if (! $record) return '-';
                                $mode = $record->stripe_payment_intent_id ? 'CB (Systempay)' : 'Virement bancaire';
                                $deposit = str_contains($record->customer_notes ?? '', 'Acompte') ? ' — Acompte 50% choisi' : ' — Paiement integral';
                                return $mode . $deposit;
                            }),
                        Forms\Components\Placeholder::make('amount_info')
                            ->label('Montant')
                            ->content(function ($record) {
                                if (! $record) return '-';
                                $isDeposit = str_contains($record->customer_notes ?? '', 'Acompte');
                                $paid = (float) ($record->amount_paid ?? 0);
                                $total = (float) $record->total_ttc;
                                $remaining = $total - $paid;
                                if ($isDeposit) {
                                    return "Total: " . number_format($total, 2, ',', ' ') . "€ | Paye: " . number_format($paid, 2, ',', ' ') . "€ | Restant: " . number_format($remaining, 2, ',', ' ') . "€";
                                }
                                return number_format($total, 2, ',', ' ') . "€ TTC";
                            }),
                    ]),

                Forms\Components\Section::make('Montants')
                    ->columns(5)
                    ->collapsed()
                    ->schema([
                        Forms\Components\TextInput::make('subtotal_ht')->label('Sous-total HT')->numeric()->prefix('€')->disabled(),
                        Forms\Components\TextInput::make('shipping_ht')->label('Livraison HT')->numeric()->prefix('€')->disabled(),
                        Forms\Components\TextInput::make('total_ht')->label('Total HT')->numeric()->prefix('€')->disabled(),
                        Forms\Components\TextInput::make('total_tva')->label('TVA')->numeric()->prefix('€')->disabled(),
                        Forms\Components\TextInput::make('total_ttc')->label('Total TTC')->numeric()->prefix('€')->disabled(),
                    ]),

                Forms\Components\Section::make('Client')
                    ->columns(3)
                    ->collapsed()
                    ->schema([
                        Forms\Components\Placeholder::make('customer_company')
                            ->label('Societe')
                            ->content(fn ($record) => $record?->user?->company ?? '-'),
                        Forms\Components\Placeholder::make('customer_siret_info')
                            ->label('SIRET')
                            ->content(fn ($record) => $record?->customer_siret ?? $record?->user?->siret ?? '-'),
                        Forms\Components\Placeholder::make('customer_vat')
                            ->label('TVA intra')
                            ->content(fn ($record) => $record?->customer_vat_number ?? '-'),
                        Forms\Components\Placeholder::make('shipping_address')
                            ->label('Adresse livraison')
                            ->content(fn ($record) => $record?->shippingAddress?->full_address ?? '-'),
                        Forms\Components\Placeholder::make('shipping_zone_info')
                            ->label('Zone')
                            ->content(fn ($record) => \App\Services\QuoteService::SHIPPING_ZONES[$record?->shipping_zone ?? 'france']['label'] ?? 'France'),
                        Forms\Components\Placeholder::make('vat_exemption_info')
                            ->label('Exoneration TVA')
                            ->content(fn ($record) => match ($record?->vat_exemption) {
                                'intra_eu' => 'Autoliquidation UE',
                                'export' => 'Export hors UE',
                                'dom_tom' => 'DOM-TOM',
                                default => 'Non (TVA 20%)',
                            }),
                    ]),

                Forms\Components\Section::make('Fichiers client')
                    ->collapsed()
                    ->schema([
                        Forms\Components\Placeholder::make('uploaded_files')
                            ->label('')
                            ->content(function ($record) {
                                if (! $record) return 'Aucun fichier';
                                $dir = 'orders/' . $record->reference;
                                $disk = \Illuminate\Support\Facades\Storage::disk('public');
                                if (! $disk->exists($dir)) return 'Aucun fichier charge par le client.';
                                $files = $disk->files($dir);
                                if (empty($files)) return 'Aucun fichier charge par le client.';
                                $html = '';
                                foreach ($files as $f) {
                                    $name = basename($f);
                                    $url = $disk->url($f);
                                    $size = round($disk->size($f) / 1024);
                                    $html .= "<a href='{$url}' target='_blank' class='inline-flex items-center gap-1 px-2 py-1 bg-blue-50 text-blue-700 rounded text-xs mr-1 mb-1 hover:bg-blue-100'>{$name} ({$size}Ko)</a> ";
                                }
                                return new \Illuminate\Support\HtmlString($html);
                            }),
                    ]),

                Forms\Components\Section::make('Livraison')
                    ->columns(3)
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        Forms\Components\Select::make('carrier')
                            ->label('Transporteur')
                            ->options(['Colissimo' => 'Colissimo', 'Chronopost' => 'Chronopost', 'Mondial Relay' => 'Mondial Relay', 'DPD' => 'DPD', 'UPS' => 'UPS'])
                            ->searchable(),
                        Forms\Components\TextInput::make('tracking_number')->label('N° suivi'),
                        Forms\Components\TextInput::make('tracking_url')->label('URL suivi')->url(),
                        Forms\Components\DateTimePicker::make('shipped_at')->label('Expedie le'),
                        Forms\Components\DateTimePicker::make('delivered_at')->label('Livre le'),
                    ]),

                Forms\Components\Section::make('Notes')
                    ->columns(2)
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        Forms\Components\Textarea::make('admin_notes')->label('Notes admin')->rows(3),
                        Forms\Components\Textarea::make('customer_notes')->label('Notes client')->rows(3)->disabled(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('reference')
                    ->label('Ref.')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Client')
                    ->searchable()
                    ->sortable()
                    ->description(fn ($record) => $record->user?->company),
                Tables\Columns\TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'danger',
                        'confirmed' => 'info',
                        'in_production' => 'warning',
                        'shipped' => 'primary',
                        'delivered' => 'success',
                        'cancelled' => 'gray',
                        'refunded' => 'gray',
                        'archived' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'Attente paiement',
                        'confirmed' => 'Confirmee',
                        'in_production' => 'En production',
                        'shipped' => 'Expediee',
                        'delivered' => 'Livree',
                        'cancelled' => 'Annulee',
                        'refunded' => 'Remboursee',
                        'archived' => 'Archivee',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('payment_status')
                    ->label('Paiement')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'danger',
                        'partial' => 'warning',
                        'paid' => 'success',
                        'refunded' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state, $record): string => match ($state) {
                        'pending' => 'A valider',
                        'partial' => 'Acompte 50%',
                        'paid' => 'Paye',
                        'refunded' => 'Rembourse',
                        default => $state,
                    }),
                Tables\Columns\IconColumn::make('has_marking')
                    ->label('Marquage')
                    ->boolean()
                    ->trueIcon('heroicon-o-paint-brush')
                    ->falseIcon('heroicon-o-minus')
                    ->trueColor('warning'),
                Tables\Columns\TextColumn::make('total_ttc')
                    ->label('Total TTC')
                    ->money('EUR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Statut')
                    ->options([
                        'pending' => 'Attente paiement',
                        'confirmed' => 'Confirmee',
                        'in_production' => 'En production',
                        'shipped' => 'Expediee',
                        'delivered' => 'Livree',
                        'cancelled' => 'Annulee',
                    ]),
                Tables\Filters\SelectFilter::make('payment_status')
                    ->label('Paiement')
                    ->options([
                        'pending' => 'A valider',
                        'partial' => 'Acompte 50%',
                        'paid' => 'Paye',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('validate_payment')
                    ->label('Valider paiement')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Valider le paiement')
                    ->modalDescription(fn ($record) => 'Confirmer la reception du paiement pour la commande ' . $record->reference . ' (' . number_format($record->total_ttc, 2, ',', ' ') . '€ TTC) ?')
                    ->form([
                        Forms\Components\Select::make('payment_type')
                            ->label('Type')
                            ->options([
                                'full' => 'Paiement integral',
                                'deposit' => 'Acompte 50%',
                            ])
                            ->default(fn ($record) => str_contains($record->customer_notes ?? '', 'Acompte') ? 'deposit' : 'full')
                            ->required(),
                    ])
                    ->action(function ($record, array $data) {
                        $total = (float) $record->total_ttc;
                        $amount = $data['payment_type'] === 'deposit' ? round($total / 2, 2) : $total;

                        // Utilise markAsPaid qui genere la facture + envoie tous les mails
                        app(\App\Services\OrderService::class)->markAsPaid($record, null, $amount);

                        // Mail 7 : paiement recu (client)
                        app(\App\Services\NotificationService::class)->sendPaymentReceived($record->fresh());

                        Notification::make()->title('Paiement valide + facture generee')->success()->send();
                    })
                    ->visible(fn ($record) => in_array($record->payment_status, ['pending', 'partial'])),

                Tables\Actions\Action::make('validate_balance')
                    ->label('Solder')
                    ->icon('heroicon-o-banknotes')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Solder la commande')
                    ->modalDescription(fn ($record) => 'Confirmer la reception du solde restant (' . number_format($record->total_ttc - ($record->amount_paid ?? 0), 2, ',', ' ') . '€) ?')
                    ->action(function ($record) {
                        // Mail 7 : paiement recu
                        app(\App\Services\NotificationService::class)->sendPaymentReceived($record);

                        // Solder : maj invoice en "soldee" + regenere PDF + mail 12
                        app(\App\Services\OrderService::class)->settleInvoice($record);

                        Notification::make()->title('Commande soldee + facture mise a jour')->success()->send();
                    })
                    ->visible(fn ($record) => $record->payment_status === 'partial'),

                Tables\Actions\Action::make('generate_invoice')
                    ->label('Facturer')
                    ->icon('heroicon-o-document-text')
                    ->color('info')
                    ->requiresConfirmation()
                    ->modalHeading('Generer la facture')
                    ->modalDescription(fn ($record) => 'Generer et envoyer la facture pour ' . $record->reference . ' ?')
                    ->action(function ($record) {
                        app(\App\Services\OrderService::class)->generateInvoiceManual($record);
                        Notification::make()->title('Facture generee et envoyee')->success()->send();
                    })
                    ->visible(fn ($record) => ! $record->invoice && $record->status !== 'pending' && $record->status !== 'archived'),

                Tables\Actions\Action::make('archive')
                    ->label('Archiver')
                    ->icon('heroicon-o-archive-box')
                    ->color('gray')
                    ->requiresConfirmation()
                    ->modalHeading('Archiver la commande')
                    ->modalDescription('Cette commande sera masquee de la liste principale.')
                    ->action(fn ($record) => $record->update(['status' => 'archived']))
                    ->visible(fn ($record) => in_array($record->status, ['delivered', 'confirmed', 'shipped'])
                        && $record->payment_status === 'paid'
                        && $record->invoice !== null
                        && in_array($record->invoice->status, ['paid', 'settled'])),

                Tables\Actions\Action::make('delete_order')
                    ->label('Supprimer')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Supprimer la commande')
                    ->modalDescription(fn ($record) => 'Supprimer definitivement la commande ' . $record->reference . ' ?')
                    ->action(function ($record) {
                        $record->items()->delete();
                        $record->documents()->each(function ($doc) {
                            if ($doc->path && \Illuminate\Support\Facades\Storage::disk('public')->exists($doc->path)) {
                                \Illuminate\Support\Facades\Storage::disk('public')->delete($doc->path);
                            }
                            $doc->delete();
                        });
                        if ($record->invoice) {
                            $record->invoice->delete();
                        }
                        if ($record->quote_id) {
                            \App\Models\Quote::where('id', $record->quote_id)->update(['status' => 'draft', 'accepted_at' => null]);
                        }
                        $record->delete();
                        Notification::make()->title('Commande supprimee')->success()->send();
                    })
                    ->visible(fn ($record) => $record->status === 'cancelled'),

                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'view' => Pages\ViewOrder::route('/{record}'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
