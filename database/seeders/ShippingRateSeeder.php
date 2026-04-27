<?php

namespace Database\Seeders;

use App\Models\ShippingRate;
use Illuminate\Database\Seeder;

class ShippingRateSeeder extends Seeder
{
    public function run(): void
    {
        $rates = [
            // Colissimo — France
            ['Colissimo', 'France', 0, 0.5, 5.50, 2, 3],
            ['Colissimo', 'France', 0.5, 1, 6.80, 2, 3],
            ['Colissimo', 'France', 1, 2, 7.90, 2, 3],
            ['Colissimo', 'France', 2, 5, 9.50, 2, 4],
            ['Colissimo', 'France', 5, 10, 13.50, 3, 4],
            ['Colissimo', 'France', 10, 30, 18.50, 3, 5],

            // Colissimo — Europe
            ['Colissimo', 'Europe', 0, 0.5, 12.50, 4, 7],
            ['Colissimo', 'Europe', 0.5, 1, 14.90, 4, 7],
            ['Colissimo', 'Europe', 1, 2, 17.50, 4, 7],
            ['Colissimo', 'Europe', 2, 5, 22.00, 5, 8],
            ['Colissimo', 'Europe', 5, 10, 32.00, 5, 10],

            // Chronopost — France (express)
            ['Chronopost', 'France', 0, 0.5, 12.90, 1, 1],
            ['Chronopost', 'France', 0.5, 1, 14.50, 1, 1],
            ['Chronopost', 'France', 1, 2, 16.90, 1, 1],
            ['Chronopost', 'France', 2, 5, 21.50, 1, 2],
            ['Chronopost', 'France', 5, 10, 28.90, 1, 2],
            ['Chronopost', 'France', 10, 30, 38.50, 1, 2],

            // Chronopost — Europe
            ['Chronopost', 'Europe', 0, 0.5, 24.90, 2, 3],
            ['Chronopost', 'Europe', 0.5, 1, 28.50, 2, 3],
            ['Chronopost', 'Europe', 1, 2, 34.90, 2, 4],
            ['Chronopost', 'Europe', 2, 5, 45.00, 3, 5],

            // Mondial Relay — France (economique)
            ['Mondial Relay', 'France', 0, 0.5, 3.90, 3, 5],
            ['Mondial Relay', 'France', 0.5, 1, 4.50, 3, 5],
            ['Mondial Relay', 'France', 1, 3, 5.90, 3, 6],
            ['Mondial Relay', 'France', 3, 5, 6.90, 4, 6],
            ['Mondial Relay', 'France', 5, 10, 8.50, 4, 7],
            ['Mondial Relay', 'France', 10, 30, 12.50, 4, 7],

            // Mondial Relay — Europe
            ['Mondial Relay', 'Europe', 0, 0.5, 6.90, 5, 8],
            ['Mondial Relay', 'Europe', 0.5, 1, 8.50, 5, 8],
            ['Mondial Relay', 'Europe', 1, 3, 10.90, 5, 10],
            ['Mondial Relay', 'Europe', 3, 5, 14.50, 6, 10],
        ];

        foreach ($rates as [$carrier, $zone, $wMin, $wMax, $price, $dMin, $dMax]) {
            ShippingRate::create([
                'carrier' => $carrier,
                'zone' => $zone,
                'weight_min' => $wMin,
                'weight_max' => $wMax,
                'price_ht' => $price,
                'delivery_days_min' => $dMin,
                'delivery_days_max' => $dMax,
                'is_active' => true,
            ]);
        }

        $this->command->info('Tarifs livraison : ' . count($rates) . ' tarifs crees (Colissimo, Chronopost, Mondial Relay)');
    }
}
