<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NotificationLogResource\Pages;
use App\Models\NotificationLog;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class NotificationLogResource extends Resource
{
    protected static ?string $model = NotificationLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-bell';

    protected static ?string $navigationGroup = 'Clients';

    protected static ?string $navigationLabel = 'Historique notifications';

    protected static ?string $modelLabel = 'Notification';

    protected static ?string $pluralModelLabel = 'Notifications';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->columns(2)
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->label('Utilisateur')
                            ->relationship('user', 'name')
                            ->searchable(),
                        Forms\Components\TextInput::make('type')
                            ->label('Type')
                            ->disabled(),
                        Forms\Components\TextInput::make('channel')
                            ->label('Canal')
                            ->disabled(),
                        Forms\Components\TextInput::make('recipient')
                            ->label('Destinataire')
                            ->disabled(),
                        Forms\Components\TextInput::make('subject')
                            ->label('Sujet')
                            ->disabled()
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('status')
                            ->label('Statut')
                            ->disabled(),
                        Forms\Components\Textarea::make('error')
                            ->label('Erreur')
                            ->disabled()
                            ->visible(fn ($record) => $record?->error !== null),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'welcome' => 'info',
                        'order_confirmation' => 'success',
                        'order_shipped' => 'primary',
                        'bat_ready' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'welcome' => 'Bienvenue',
                        'order_confirmation' => 'Confirmation',
                        'order_shipped' => 'Expedition',
                        'bat_ready' => 'BAT',
                        default => $state,
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('channel')
                    ->label('Canal')
                    ->badge()
                    ->color('gray'),
                Tables\Columns\TextColumn::make('recipient')
                    ->label('Destinataire')
                    ->searchable()
                    ->limit(30),
                Tables\Columns\TextColumn::make('subject')
                    ->label('Sujet')
                    ->limit(40)
                    ->toggleable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'sent' => 'success',
                        'failed' => 'danger',
                        'pending' => 'warning',
                    }),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Utilisateur')
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Type')
                    ->options([
                        'welcome' => 'Bienvenue',
                        'order_confirmation' => 'Confirmation commande',
                        'order_shipped' => 'Expedition',
                        'bat_ready' => 'BAT pret',
                    ]),
                Tables\Filters\SelectFilter::make('status')
                    ->label('Statut')
                    ->options([
                        'sent' => 'Envoye',
                        'failed' => 'Echoue',
                        'pending' => 'En attente',
                    ]),
                Tables\Filters\SelectFilter::make('channel')
                    ->label('Canal')
                    ->options([
                        'email' => 'Email',
                        'sms' => 'SMS',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListNotificationLogs::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
