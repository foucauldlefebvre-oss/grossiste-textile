<?php

namespace App\Filament\Resources\CartResource\Pages;

use App\Filament\Resources\CartResource;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewCart extends ViewRecord
{
    protected static string $resource = CartResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Section::make('Panier')->schema([
                TextEntry::make('id')->label('#'),
                TextEntry::make('user.name')->label('Client'),
                TextEntry::make('user.email')->label('Email'),
                TextEntry::make('status')->label('Statut')->badge(),
                TextEntry::make('subtotal_ht')->money('eur')->label('Sous-total HT'),
                TextEntry::make('shipping_ht')->money('eur')->label('Livraison HT'),
                TextEntry::make('total_ht')->money('eur')->label('Total HT'),
                TextEntry::make('total_tva')->money('eur')->label('TVA'),
                TextEntry::make('total_ttc')->money('eur')->label('Total TTC'),
                TextEntry::make('shipping_zone')->label('Zone livraison'),
                TextEntry::make('vat_exemption')->label('Exonération TVA'),
                TextEntry::make('created_at')->dateTime()->label('Créé le'),
                TextEntry::make('updated_at')->dateTime()->label('Dernière modif'),
                TextEntry::make('converted_at')->dateTime()->label('Converti le'),
                TextEntry::make('abandoned_at')->dateTime()->label('Abandonné le'),
            ])->columns(3),

            Section::make('Articles')->schema([
                RepeatableEntry::make('items')->schema([
                    TextEntry::make('product.name')->label('Produit'),
                    TextEntry::make('color.name')->label('Couleur'),
                    TextEntry::make('size.name')->label('Taille'),
                    TextEntry::make('quantity')->label('Quantité'),
                    TextEntry::make('unit_price_ht')->money('eur')->label('Prix unitaire HT'),
                    TextEntry::make('line_total_ht')->money('eur')->label('Total ligne HT'),
                ])->columns(6),
            ]),
        ]);
    }
}
