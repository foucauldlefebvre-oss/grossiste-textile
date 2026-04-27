<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use App\Models\QuoteItem;
use App\Models\ShippingRate;
use Illuminate\Support\Collection;

class ShippingService
{
    const TRACKING_URLS = [
        'Colissimo' => 'https://www.laposte.fr/outils/suivre-vos-envois?code={tracking}',
        'Chronopost' => 'https://www.chronopost.fr/tracking-no-powerful/trackSk498/{tracking}',
        'Mondial Relay' => 'https://www.mondialrelay.fr/suivi-de-colis?numeroExpedition={tracking}',
    ];

    /**
     * Detect shipping zone from country code and postal code.
     */
    public function detectZone(string $country, ?string $postalCode = null): string
    {
        if ($country === 'FR') {
            // DOM-TOM
            if ($postalCode && preg_match('/^(97[1-6]|98[6-8])/', $postalCode)) {
                return 'DOM-TOM';
            }

            return 'France';
        }

        $eu = ['DE', 'BE', 'LU', 'NL', 'IT', 'ES', 'PT', 'AT', 'IE', 'FI', 'SE', 'DK', 'PL', 'CZ', 'SK', 'HU', 'RO', 'BG', 'HR', 'SI', 'EE', 'LV', 'LT', 'GR', 'CY', 'MT'];
        if (in_array($country, $eu)) {
            return 'Europe';
        }

        return 'International';
    }

    /**
     * Get available shipping rates for a given weight and zone.
     */
    public function calculateRates(float $weight, string $zone): Collection
    {
        return ShippingRate::active()
            ->forWeight($weight)
            ->where('zone', $zone)
            ->orderBy('price_ht')
            ->get()
            ->map(fn (ShippingRate $rate) => [
                'id' => $rate->id,
                'carrier' => $rate->carrier,
                'zone' => $rate->zone,
                'price_ht' => (float) $rate->price_ht,
                'price_ttc' => round((float) $rate->price_ht * 1.20, 2),
                'delivery_min' => $rate->delivery_days_min,
                'delivery_max' => $rate->delivery_days_max,
                'delivery_label' => $this->formatDeliveryDays($rate->delivery_days_min, $rate->delivery_days_max),
            ]);
    }

    /**
     * Estimate total weight from quote items.
     */
    public function estimateWeight(Collection $items): float
    {
        $weight = 0;
        foreach ($items as $item) {
            $product = $item->product ?? Product::find($item->product_id);
            $productWeight = $product?->weight ?? 0.3; // default 300g
            $weight += $productWeight * $item->quantity;
        }

        return round($weight, 3);
    }

    /**
     * Get tracking URL for a carrier and tracking number.
     */
    public function getTrackingUrl(string $carrier, string $trackingNumber): string
    {
        $template = self::TRACKING_URLS[$carrier] ?? null;

        if ($template) {
            return str_replace('{tracking}', urlencode($trackingNumber), $template);
        }

        return '';
    }

    /**
     * Mark an order as shipped.
     */
    public function markAsShipped(Order $order, string $carrier, string $trackingNumber): void
    {
        $trackingUrl = $this->getTrackingUrl($carrier, $trackingNumber);

        $order->update([
            'status' => 'shipped',
            'carrier' => $carrier,
            'tracking_number' => $trackingNumber,
            'tracking_url' => $trackingUrl,
            'shipped_at' => now(),
        ]);

        app(NotificationService::class)->sendOrderShipped($order);
    }

    /**
     * Mark an order as delivered.
     */
    public function markAsDelivered(Order $order): void
    {
        $order->update([
            'status' => 'delivered',
            'delivered_at' => now(),
        ]);
    }

    /**
     * Get list of supported carriers.
     */
    public function getCarriers(): array
    {
        return array_keys(self::TRACKING_URLS);
    }

    /**
     * Format delivery days label.
     */
    private function formatDeliveryDays(?int $min, ?int $max): string
    {
        if ($min && $max) {
            return $min === $max
                ? "{$min} jour" . ($min > 1 ? 's' : '')
                : "{$min}-{$max} jours";
        }

        if ($min) {
            return "A partir de {$min} jour" . ($min > 1 ? 's' : '');
        }

        return 'Delai non communique';
    }
}
