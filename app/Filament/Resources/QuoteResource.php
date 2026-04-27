<?php

namespace App\Filament\Resources;

use App\Filament\Resources\QuoteResource\Pages;
use App\Filament\Resources\QuoteResource\RelationManagers;
use App\Jobs\SendBatEmailJob;
use App\Models\Quote;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class QuoteResource extends Resource
{
    protected static ?string $model = Quote::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Commandes';

    protected static ?string $modelLabel = 'Devis';

    protected static ?string $pluralModelLabel = 'Devis';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Devis')
                    ->columns(3)
                    ->schema([
                        Forms\Components\TextInput::make('reference')
                            ->label('Référence')
                            ->required()
                            ->disabled(),
                        Forms\Components\Select::make('user_id')
                            ->label('Client')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->placeholder('Anonyme (session)'),
                        Forms\Components\TextInput::make('session_id')
                            ->label('Session ID')
                            ->disabled()
                            ->visible(fn ($record) => $record && ! $record->user_id),
                        Forms\Components\Select::make('status')
                            ->label('Statut')
                            ->options([
                                'draft' => 'Brouillon',
                                'sent' => 'Envoyé',
                                'accepted' => 'Accepté',
                                'refused' => 'Refusé',
                                'expired' => 'Expiré',
                            ])
                            ->required(),
                    ]),

                Forms\Components\Section::make('Montants')
                    ->columns(3)
                    ->schema([
                        Forms\Components\TextInput::make('total_ht')
                            ->label('Total HT')
                            ->numeric()
                            ->prefix('€')
                            ->disabled(),
                        Forms\Components\TextInput::make('total_tva')
                            ->label('TVA')
                            ->numeric()
                            ->prefix('€')
                            ->disabled(),
                        Forms\Components\TextInput::make('total_ttc')
                            ->label('Total TTC')
                            ->numeric()
                            ->prefix('€')
                            ->disabled(),
                    ]),

                Forms\Components\Section::make('Détails')
                    ->columns(2)
                    ->collapsible()
                    ->schema([
                        Forms\Components\DateTimePicker::make('expires_at')
                            ->label('Expire le'),
                        Forms\Components\DateTimePicker::make('accepted_at')
                            ->label('Accepté le'),
                        Forms\Components\Textarea::make('notes')
                            ->label('Notes')
                            ->columnSpanFull()
                            ->rows(3),
                    ]),

                Forms\Components\Section::make('Bon a Tirer (BAT)')
                    ->columns(3)
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        Forms\Components\Select::make('bat_status')
                            ->label('Statut BAT')
                            ->options([
                                'pending' => 'En attente',
                                'sent' => 'Envoyé',
                                'approved' => 'Approuvé',
                                'rejected' => 'Refusé',
                                'revision_requested' => 'Modification demandée',
                            ])
                            ->disabled(),
                        Forms\Components\DateTimePicker::make('bat_sent_at')
                            ->label('Envoye le')
                            ->disabled(),
                        Forms\Components\TextInput::make('bat_token')
                            ->label('Token BAT')
                            ->disabled(),
                        Forms\Components\Textarea::make('bat_rejection_reason')
                            ->label('Motif de refus')
                            ->disabled()
                            ->columnSpanFull()
                            ->rows(2)
                            ->visible(fn ($record) => $record?->bat_status === 'rejected'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('reference')
                    ->label('Réf.')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Client')
                    ->placeholder('Anonyme')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'sent' => 'info',
                        'accepted' => 'success',
                        'refused' => 'danger',
                        'expired' => 'warning',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'draft' => 'Brouillon',
                        'sent' => 'Envoyé',
                        'accepted' => 'Accepté',
                        'refused' => 'Refusé',
                        'expired' => 'Expiré',
                    }),
                Tables\Columns\TextColumn::make('bat_status')
                    ->label('BAT')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'sent' => 'info',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'sent' => 'Envoye',
                        'approved' => 'Approuve',
                        'rejected' => 'Refuse',
                        default => 'En attente',
                    }),
                Tables\Columns\TextColumn::make('items_count')
                    ->label('Lignes')
                    ->counts('items')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_ttc')
                    ->label('Total TTC')
                    ->money('EUR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('expires_at')
                    ->label('Expire le')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Statut')
                    ->options([
                        'draft' => 'Brouillon',
                        'sent' => 'Envoyé',
                        'accepted' => 'Accepté',
                        'refused' => 'Refusé',
                        'expired' => 'Expiré',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('send_bat')
                    ->label('Envoyer BAT')
                    ->icon('heroicon-o-document-check')
                    ->color('warning')
                    ->visible(fn (Quote $record): bool => $record->bat_status === 'pending' && ! empty($record->markings))
                    ->form(fn (Quote $record): array => [
                        Forms\Components\Select::make('bat_svg_template')
                            ->label('Gabarit SVG')
                            ->options([
                                'tshirt-homme' => 'T-shirt homme',
                                'tshirt-femme' => 'T-shirt femme',
                                'polo-homme' => 'Polo homme',
                                'polo-femme' => 'Polo femme',
                                'sweat' => 'Sweat col rond',
                                'sweat-zip' => 'Sweat zippé',
                                'veste' => 'Veste / softshell',
                                'casquette' => 'Casquette',
                                'tote-bag' => 'Tote bag',
                            ])
                            ->required(),
                        Forms\Components\TextInput::make('bat_format')
                            ->label('Format')
                            ->default(fn (Quote $record): string => data_get($record->markings, '0.size', 'A7')),
                        Forms\Components\TextInput::make('bat_position_label')
                            ->label('Position du marquage')
                            ->default(fn (Quote $record): string => (function () use ($record) {
                                $zone = data_get($record->markings, '0.zone', '');
                                $map = [
                                    'poitrine_gauche' => 'Poitrine gauche',
                                    'poitrine_droite' => 'Poitrine droite',
                                    'poitrine_centre' => 'Poitrine centré',
                                    'dos_centre' => 'Dos centré',
                                    'dos_haut' => 'Dos haut',
                                    'manche_gauche' => 'Manche gauche',
                                    'manche_droite' => 'Manche droite',
                                    'col' => 'Col',
                                ];

                                return $map[$zone] ?? 'Poitrine gauche';
                            })()),
                    ])
                    ->action(function (Quote $record, array $data): void {
                        if (! $record->bat_token) {
                            $record->bat_token = Str::random(48);
                        }
                        $record->bat_status = 'sent';
                        $record->bat_sent_at = now();
                        $record->bat_svg_template = $data['bat_svg_template'];
                        $record->bat_format = $data['bat_format'];
                        $record->bat_position_label = $data['bat_position_label'];
                        $record->save();

                        SendBatEmailJob::dispatch($record);

                        Notification::make()
                            ->title('BAT envoyé au client')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\Action::make('view_bat')
                    ->label('Voir BAT')
                    ->icon('heroicon-o-eye')
                    ->color('gray')
                    ->visible(fn (Quote $record): bool => $record->bat_token !== null)
                    ->url(fn (Quote $record): string => $record->bat_client_url)
                    ->openUrlInNewTab(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListQuotes::route('/'),
            'create' => Pages\CreateQuote::route('/create'),
            'view' => Pages\ViewQuote::route('/{record}'),
            'edit' => Pages\EditQuote::route('/{record}/edit'),
        ];
    }
}
