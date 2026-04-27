<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TechniqueMarquageResource\Pages;
use App\Models\TechniqueMarquage;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TechniqueMarquageResource extends Resource
{
    protected static ?string $model = TechniqueMarquage::class;

    protected static ?string $navigationIcon = 'heroicon-o-photo';

    protected static ?string $navigationGroup = 'Site web';

    protected static ?string $navigationLabel = 'Techniques carousel';

    protected static ?string $modelLabel = 'Technique';

    protected static ?string $pluralModelLabel = 'Techniques carousel';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nom')
                    ->required()
                    ->label('Nom de la technique'),
                Forms\Components\TextInput::make('description_courte')
                    ->required()
                    ->label('Ex: Dès 10 pièces'),
                Forms\Components\FileUpload::make('image')
                    ->image()
                    ->disk('public')
                    ->directory('techniques')
                    ->imageResizeMode('cover')
                    ->imageCropAspectRatio('4:3')
                    ->imageResizeTargetWidth('400')
                    ->imageResizeTargetHeight('300')
                    ->label('Photo (400x300px recommandé)'),
                Forms\Components\TextInput::make('ordre')
                    ->numeric()
                    ->default(0)
                    ->label("Ordre d'affichage"),
                Forms\Components\Toggle::make('actif')
                    ->default(true)
                    ->label('Visible sur le site'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->reorderable('ordre')
            ->defaultSort('ordre')
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->label('Photo')
                    ->disk('public')
                    ->square()
                    ->size(50),
                Tables\Columns\TextColumn::make('nom')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('description_courte')
                    ->label('Description'),
                Tables\Columns\TextColumn::make('ordre')
                    ->sortable(),
                Tables\Columns\ToggleColumn::make('actif')
                    ->label('Actif'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTechniqueMarquages::route('/'),
            'create' => Pages\CreateTechniqueMarquage::route('/create'),
            'edit' => Pages\EditTechniqueMarquage::route('/{record}/edit'),
        ];
    }
}
