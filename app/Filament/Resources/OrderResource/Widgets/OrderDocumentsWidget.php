<?php

namespace App\Filament\Resources\OrderResource\Widgets;

use App\Models\OrderDocument;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Model;

class OrderDocumentsWidget extends BaseWidget
{
    public ?Model $record = null;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Documents';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                OrderDocument::query()->where('order_id', $this->record?->getKey())
            )
            ->columns([
                Tables\Columns\TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'bat' => 'warning',
                        'client_file' => 'info',
                        'invoice' => 'success',
                        'quote_pdf' => 'primary',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'bat' => 'BAT',
                        'client_file' => 'Fichier client',
                        'invoice' => 'Facture',
                        'quote_pdf' => 'Devis PDF',
                        'other' => 'Autre',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('label')
                    ->label('Nom')
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('original_name')
                    ->label('Fichier')
                    ->limit(30),
                Tables\Columns\TextColumn::make('formatted_size')
                    ->label('Taille'),
                Tables\Columns\IconColumn::make('visible_to_client')
                    ->label('Visible client')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime('d/m/Y H:i'),
            ])
            ->actions([
                Tables\Actions\Action::make('download')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->url(fn (OrderDocument $record): string => $record->download_url)
                    ->openUrlInNewTab(),
                Tables\Actions\Action::make('toggle_visibility')
                    ->icon(fn (OrderDocument $record): string => $record->visible_to_client
                        ? 'heroicon-o-eye-slash'
                        : 'heroicon-o-eye')
                    ->color('gray')
                    ->action(fn (OrderDocument $record) => $record->update([
                        'visible_to_client' => ! $record->visible_to_client,
                    ])),
                Tables\Actions\DeleteAction::make(),
            ])
            ->emptyStateHeading('Aucun document')
            ->emptyStateDescription('Ajoutez des documents via le bouton dans le header.');
    }
}
