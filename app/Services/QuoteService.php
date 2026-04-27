<?php

namespace App\Services;

use App\Models\Quote;
use App\Models\QuoteItem;

/**
 * TODO 2b: ce service sera remplacé par CartService (Q1 — commande B2B simple).
 *
 * Stub minimal pour permettre à l'app de booter pendant la transition 2a → 2b :
 *   - Constantes TVA_RATE, SHIPPING_ZONES, ZONE_COUNTRIES préservées (utilisées
 *     par OrderService, OrderResource, Checkout).
 *   - Méthodes statiques shipping (getShippingZone, getShippingRate, getVatExemption)
 *     préservées (utilisées par Checkout).
 *   - recalculate() simplifié sans logique marquage.
 *   - Toutes les autres méthodes (addItem, updateItemQuantity, submit, estimate,
 *     calculateMarkingPrice, getSetupCost, calculateNameMarkingPrice, etc.)
 *     ont été supprimées avec le système de marquage (broderie, sérigraphie, DTF…).
 *
 * Si une page tente d'utiliser le panier (FloatingQuote, Checkout, QuotePage)
 * elle plantera proprement — c'est attendu jusqu'à 2b.
 */
class QuoteService
{
    public const TVA_RATE = 0.20;

    public const SHIPPING_ZONES = [
        'france'   => ['rate' => 9.50,  'tva' => true,  'label' => 'France metropolitaine'],
        'eu'       => ['rate' => 35.00, 'tva' => false, 'label' => 'Union europeenne'],
        'suisse'   => ['rate' => 85.00, 'tva' => false, 'label' => 'Suisse (DAP)'],
        'dom_tom'  => ['rate' => 137.00,'tva' => false, 'label' => 'DOM-TOM'],
    ];

    public const ZONE_COUNTRIES = [
        'france' => ['FR'],
        'eu' => ['DE','AT','BE','BG','HR','CY','CZ','DK','EE','ES','FI','GR','HU','IE','IT','LV','LT','LU','MT','NL','PL','PT','RO','SK','SI','SE'],
        'suisse' => ['CH','LI'],
        'dom_tom' => ['GP','MQ','GF','RE','YT','PM','NC','PF','WF','BL','MF'],
    ];

    public static function getShippingZone(string $countryCode): string
    {
        $cc = strtoupper(trim($countryCode));
        foreach (self::ZONE_COUNTRIES as $zone => $countries) {
            if (in_array($cc, $countries)) {
                return $zone;
            }
        }
        return 'france';
    }

    public static function getShippingRate(string $zone): float
    {
        return self::SHIPPING_ZONES[$zone]['rate'] ?? 9.50;
    }

    public static function getVatExemption(string $zone, ?string $vatNumber = null): ?string
    {
        if ($zone === 'france') {
            return null;
        }
        if ($zone === 'eu' && $vatNumber) {
            return 'intra_eu';
        }
        if ($zone === 'eu' && ! $vatNumber) {
            return null;
        }
        if ($zone === 'suisse') {
            return 'export';
        }
        if ($zone === 'dom_tom') {
            return 'dom_tom';
        }
        return null;
    }

    public function recalculate(Quote $quote, ?string $vatExemption = null): void
    {
        $totalHt = (float) $quote->items()->sum('line_total_ht');
        $tvaRate = $vatExemption ? 0 : self::TVA_RATE;
        $totalTva = round($totalHt * $tvaRate, 2);
        $totalTtc = round($totalHt + $totalTva, 2);

        $quote->update([
            'total_ht' => $totalHt,
            'total_tva' => $totalTva,
            'total_ttc' => $totalTtc,
        ]);
    }

    public function removeItem(QuoteItem $item): void
    {
        $quote = $item->quote;
        $item->delete();
        if ($quote) {
            $this->recalculate($quote);
        }
    }

    private function notImplemented(string $method): never
    {
        throw new \RuntimeException(
            "QuoteService::{$method}() a été désactivé en 2a. Refactor en CartService prévu en 2b."
        );
    }

    public function getOrCreateDraft(?int $userId = null, ?string $sessionId = null): Quote
    {
        $this->notImplemented(__FUNCTION__);
    }

    public function addItem(): QuoteItem
    {
        $this->notImplemented(__FUNCTION__);
    }

    public function updateItemQuantity(QuoteItem $item, int $quantity): void
    {
        $this->notImplemented(__FUNCTION__);
    }

    public function submit(Quote $quote): Quote
    {
        $this->notImplemented(__FUNCTION__);
    }

    public function estimate(): array
    {
        $this->notImplemented(__FUNCTION__);
    }

    public function calculateShipping(Quote $quote): array
    {
        return ['parcels' => 0, 'shipping_ht' => 0, 'rate_per_parcel' => 0];
    }
}
