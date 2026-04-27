<?php

namespace App\Filament\Resources\ProductResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ColorsRelationManager extends RelationManager
{
    protected static string $relationship = 'colors';

    protected static ?string $title = 'Coloris';

    protected static ?string $modelLabel = 'Coloris';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nom du coloris')
                    ->required()
                    ->maxLength(255),
                Forms\Components\ColorPicker::make('hex_code')
                    ->label('Couleur'),
                Forms\Components\FileUpload::make('image')
                    ->label('Photo')
                    ->image()
                    ->directory('products/colors'),
                Forms\Components\TextInput::make('stock')
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('sort_order')
                    ->label('Ordre')
                    ->numeric()
                    ->default(0),
                Forms\Components\Toggle::make('is_active')
                    ->label('Actif')
                    ->default(true),

                Forms\Components\Section::make('Tailles disponibles')
                    ->description('Cochez les tailles disponibles pour ce coloris')
                    ->columnSpanFull()
                    ->schema([
                        Forms\Components\Actions::make([
                            Forms\Components\Actions\Action::make('select_s_2xl')
                                ->label('S → 2XL')
                                ->icon('heroicon-o-check-circle')
                                ->color('primary')
                                ->size('sm')
                                ->action(function (Forms\Set $set, Forms\Get $get) {
                                    $current = $get('selected_sizes') ?? [];
                                    $quickSizes = ['S', 'M', 'L', 'XL', '2XL'];
                                    $set('selected_sizes', array_values(array_unique(array_merge($current, $quickSizes))));
                                }),
                            Forms\Components\Actions\Action::make('select_all_adult')
                                ->label('Toutes adultes')
                                ->icon('heroicon-o-check-circle')
                                ->color('gray')
                                ->size('sm')
                                ->action(function (Forms\Set $set, Forms\Get $get) {
                                    $current = $get('selected_sizes') ?? [];
                                    $adultSizes = ['XXS', 'XS', 'S', 'M', 'L', 'XL', '2XL', '3XL', '4XL', '5XL', '6XL', '7XL', '8XL'];
                                    $set('selected_sizes', array_values(array_unique(array_merge($current, $adultSizes))));
                                }),
                            Forms\Components\Actions\Action::make('select_children')
                                ->label('Tailles enfants')
                                ->icon('heroicon-o-check-circle')
                                ->color('warning')
                                ->size('sm')
                                ->action(function (Forms\Set $set, Forms\Get $get) {
                                    $current = $get('selected_sizes') ?? [];
                                    $childSizes = ['2ans', '4ans', '6ans', '8ans', '10ans', '12ans', '14ans'];
                                    $set('selected_sizes', array_values(array_unique(array_merge($current, $childSizes))));
                                }),
                            Forms\Components\Actions\Action::make('clear_sizes')
                                ->label('Tout décocher')
                                ->icon('heroicon-o-x-circle')
                                ->color('danger')
                                ->size('sm')
                                ->action(fn (Forms\Set $set) => $set('selected_sizes', [])),
                        ]),

                        Forms\Components\CheckboxList::make('selected_sizes')
                            ->label('')
                            ->options([
                                'XXS' => 'XXS',
                                'XS' => 'XS',
                                'S' => 'S',
                                'M' => 'M',
                                'L' => 'L',
                                'XL' => 'XL',
                                '2XL' => '2XL',
                                '3XL' => '3XL',
                                '4XL' => '4XL',
                                '5XL' => '5XL',
                                '6XL' => '6XL',
                                '7XL' => '7XL',
                                '8XL' => '8XL',
                                '2ans' => '2 ans',
                                '4ans' => '4 ans',
                                '6ans' => '6 ans',
                                '8ans' => '8 ans',
                                '10ans' => '10 ans',
                                '12ans' => '12 ans',
                                '14ans' => '14 ans',
                                'TU' => 'Taille unique',
                            ])
                            ->columns(4)
                            ->bulkToggleable(),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->columns([
                Tables\Columns\ColorColumn::make('hex_code')
                    ->label('Couleur'),
                Tables\Columns\TextColumn::make('name')
                    ->label('Nom')
                    ->searchable(),
                Tables\Columns\ImageColumn::make('image')
                    ->label('Photo')
                    ->square()
                    ->size(40),
                Tables\Columns\TextColumn::make('sizes_count')
                    ->label('Tailles')
                    ->counts('sizes'),
                Tables\Columns\TextColumn::make('sizes_stock_summary')
                    ->label('Stock par taille')
                    ->getStateUsing(function ($record) {
                        return $record->sizes
                            ->sortBy(fn ($s) => \App\Models\ProductSize::sortPosition($s->size))
                            ->map(fn ($s) => "{$s->size}: {$s->stock}")
                            ->join(' | ');
                    })
                    ->wrap()
                    ->size('xs')
                    ->color(fn ($record) => $record->sizes->sum('stock') > 0 ? null : 'danger'),
                Tables\Columns\TextColumn::make('total_stock')
                    ->label('Stock total')
                    ->getStateUsing(fn ($record) => $record->sizes->sum('stock'))
                    ->badge()
                    ->color(fn ($state) => match (true) {
                        $state > 20 => 'success',
                        $state > 0 => 'warning',
                        default => 'danger',
                    })
                    ->sortable(query: fn ($query, string $direction) => $query->withSum('sizes', 'stock')->orderBy('sizes_sum_stock', $direction)),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Actif')
                    ->boolean(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->after(function ($record, array $data) {
                        $selectedSizes = $data['selected_sizes'] ?? [];
                        foreach ($selectedSizes as $size) {
                            $record->sizes()->create([
                                'size' => $size,
                                'stock' => 0,
                                'is_available' => true,
                            ]);
                        }
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->mutateRecordDataUsing(function (array $data, $record): array {
                        $data['selected_sizes'] = $record->sizes->pluck('size')->toArray();
                        return $data;
                    })
                    ->after(function ($record, array $data) {
                        $selectedSizes = $data['selected_sizes'] ?? [];
                        $existingSizes = $record->sizes->pluck('size')->toArray();

                        // Remove deselected sizes
                        $record->sizes()
                            ->whereNotIn('size', $selectedSizes)
                            ->delete();

                        // Add new sizes
                        foreach ($selectedSizes as $size) {
                            if (!in_array($size, $existingSizes)) {
                                $record->sizes()->create([
                                    'size' => $size,
                                    'stock' => 0,
                                    'is_available' => true,
                                ]);
                            }
                        }
                    }),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
