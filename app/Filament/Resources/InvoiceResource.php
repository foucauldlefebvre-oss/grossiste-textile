<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InvoiceResource\Pages;
use App\Models\Invoice;
use App\Services\InvoiceService;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Commandes';

    protected static ?string $modelLabel = 'Facture';

    protected static ?string $pluralModelLabel = 'Factures';

    protected static ?int $navigationSort = 3;

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('number', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('number')
                    ->label('Numero')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('order.reference')
                    ->label('Commande')
                    ->searchable()
                    ->url(fn ($record) => $record->order ? OrderResource::getUrl('view', ['record' => $record->order]) : null),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Client')
                    ->searchable()
                    ->description(fn ($record) => $record->user?->company),

                Tables\Columns\TextColumn::make('issued_at')
                    ->label('Date')
                    ->dateTime('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_ttc')
                    ->label('Total TTC')
                    ->money('EUR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'deposit' => 'warning',
                        'paid' => 'success',
                        'settled' => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'deposit' => 'Acompte',
                        'paid' => 'Payee',
                        'settled' => 'Soldee',
                        default => ucfirst($state),
                    }),

                Tables\Columns\TextColumn::make('payment_method')
                    ->label('Paiement')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'cb' => 'info',
                        'wire_transfer' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'cb' => 'CB',
                        'wire_transfer' => 'Virement',
                        default => '-',
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Statut')
                    ->options([
                        'deposit' => 'Acompte',
                        'paid' => 'Payee',
                        'settled' => 'Soldee',
                    ]),

                Tables\Filters\SelectFilter::make('payment_method')
                    ->label('Mode de paiement')
                    ->options([
                        'cb' => 'Carte bancaire',
                        'wire_transfer' => 'Virement',
                    ]),

                Tables\Filters\Filter::make('date_range')
                    ->form([
                        Forms\Components\DatePicker::make('from')->label('Du'),
                        Forms\Components\DatePicker::make('to')->label('Au'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['from'], fn ($q) => $q->where('issued_at', '>=', $data['from']))
                            ->when($data['to'], fn ($q) => $q->where('issued_at', '<=', $data['to'] . ' 23:59:59'));
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('download_pdf')
                    ->label('PDF')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->action(function ($record) {
                        return app(InvoiceService::class)->download($record);
                    }),

                Tables\Actions\Action::make('resend_email')
                    ->label('Renvoyer')
                    ->icon('heroicon-o-envelope')
                    ->color('info')
                    ->requiresConfirmation()
                    ->modalHeading('Renvoyer la facture par email')
                    ->modalDescription(fn ($record) => 'Renvoyer la facture ' . $record->number . ' au client ?')
                    ->action(function ($record) {
                        $order = $record->order;
                        if (! $order) {
                            return;
                        }

                        $notif = app(\App\Services\NotificationService::class);

                        match ($record->status) {
                            'deposit' => $notif->sendInvoiceDeposit($order, $record),
                            'settled' => $notif->sendInvoicePaid($order, $record),
                            default => $notif->sendInvoice($order, $record),
                        };

                        Notification::make()->title('Facture renvoyee par email')->success()->send();
                    }),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInvoices::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
