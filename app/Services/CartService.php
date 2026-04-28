<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\PricingRule;
use App\Models\Product;
use App\Models\ProductColor;
use App\Models\ProductSize;
use Illuminate\Support\Facades\Auth;

class CartService
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

    /**
     * Get or create the active cart for the authenticated user.
     * Throws if user is not authenticated (no guest cart on grossiste).
     */
    public function getOrCreateActive(?int $userId = null): Cart
    {
        $userId = $userId ?? Auth::id();
        if (! $userId) {
            throw new \RuntimeException('CartService requires an authenticated user (no guest cart on grossiste-textile).');
        }

        $cart = Cart::active()->where('user_id', $userId)->first();
        if ($cart) {
            return $cart;
        }

        return Cart::create([
            'user_id' => $userId,
            'status' => 'active',
        ]);
    }

    /**
     * Add an item to the cart, or increment quantity if same product+color+size exists.
     */
    public function addItem(
        Cart $cart,
        int $productId,
        ?int $colorId,
        ?int $sizeId,
        int $quantity
    ): CartItem {
        $product = Product::findOrFail($productId);

        $existing = $cart->items()
            ->where('product_id', $productId)
            ->where('product_color_id', $colorId)
            ->where('product_size_id', $sizeId)
            ->first();

        $newQty = ($existing?->quantity ?? 0) + max(1, $quantity);
        $unitPrice = $this->calculateUnitPrice($product, $colorId, $sizeId, $newQty);

        if ($existing) {
            $existing->update([
                'quantity' => $newQty,
                'unit_price_ht' => $unitPrice,
                'line_total_ht' => round($unitPrice * $newQty, 2),
            ]);
            $item = $existing;
        } else {
            $item = $cart->items()->create([
                'product_id' => $productId,
                'product_color_id' => $colorId,
                'product_size_id' => $sizeId,
                'quantity' => $newQty,
                'unit_price_ht' => $unitPrice,
                'line_total_ht' => round($unitPrice * $newQty, 2),
            ]);
        }

        $this->recalculateProductSiblings($cart, $productId);
        $this->recalculate($cart);

        return $item;
    }

    /**
     * Update the quantity of a cart item. Recalculates degressive pricing for siblings.
     */
    public function updateQuantity(CartItem $item, int $quantity): void
    {
        if ($quantity < 1) {
            $this->removeItem($item);
            return;
        }

        $item->update(['quantity' => $quantity]);
        $this->recalculateProductSiblings($item->cart, $item->product_id);
        $this->recalculate($item->cart);
    }

    public function removeItem(CartItem $item): void
    {
        $cart = $item->cart;
        $productId = $item->product_id;
        $item->delete();
        if ($cart) {
            $this->recalculateProductSiblings($cart, $productId);
            $this->recalculate($cart);
        }
    }

    /**
     * Recalculate unit prices on all items of the same product across colors/sizes,
     * using cumulative quantity for degressive pricing.
     */
    private function recalculateProductSiblings(Cart $cart, int $productId): void
    {
        $items = $cart->items()->where('product_id', $productId)->get();
        if ($items->isEmpty()) {
            return;
        }

        $product = Product::find($productId);
        if (! $product) {
            return;
        }

        $totalQty = (int) $items->sum('quantity');
        foreach ($items as $sib) {
            $unitPrice = $this->calculateUnitPrice($product, $sib->product_color_id, $sib->product_size_id, $totalQty);
            $sib->update([
                'unit_price_ht' => $unitPrice,
                'line_total_ht' => round($unitPrice * $sib->quantity, 2),
            ]);
        }
    }

    /**
     * Calculate the unit price for a product at a given quantity, applying:
     * - Color-specific supplier_price if defined
     * - PricingRule degressive coefficient
     * - Size price_supplement
     */
    public function calculateUnitPrice(Product $product, ?int $colorId, ?int $sizeId, int $quantity): float
    {
        $colorSupplierPrice = null;
        if ($colorId) {
            $colorSupplierPrice = ProductColor::where('id', $colorId)->value('supplier_price');
        }

        $supplierPrice = (float) ($colorSupplierPrice ?: $product->supplier_price ?: $product->base_price);
        if ($supplierPrice <= 0) {
            return (float) $product->base_price;
        }

        $price = PricingRule::calculate($supplierPrice, $quantity);

        // null = below min quantity, 0 = "sur devis" — fallback to highest-quantity rule
        if ($price === null || (float) $price === 0.0) {
            $fallback = PricingRule::where('min_price', '<=', $supplierPrice)
                ->where(function ($q) use ($supplierPrice) {
                    $q->whereNull('max_price')->orWhere('max_price', '>=', $supplierPrice);
                })
                ->where('coefficient', '>', 0)
                ->orderBy('min_quantity', 'desc')
                ->first();

            $price = $fallback
                ? round($supplierPrice * (float) $fallback->coefficient, 2)
                : (float) ($product->base_price ?: 0);
        }

        if ($sizeId) {
            $supplement = ProductSize::where('id', $sizeId)->value('price_supplement') ?? 0;
            $price += (float) $supplement;
        }

        return (float) $price;
    }

    /**
     * Recalculate cart totals (subtotal, shipping, TVA, TTC).
     */
    public function recalculate(Cart $cart, ?string $vatExemption = null): void
    {
        $cart->refresh();

        $subtotalHt = (float) $cart->items()->sum('line_total_ht');
        $shipping = $this->calculateShipping($cart);
        $shippingHt = $shipping['shipping_ht'];

        $totalBeforeTva = $subtotalHt + $shippingHt;
        $effectiveExemption = $vatExemption ?? $cart->vat_exemption;
        $tvaRate = $effectiveExemption ? 0 : self::TVA_RATE;
        $totalTva = round($totalBeforeTva * $tvaRate, 2);
        $totalTtc = round($totalBeforeTva + $totalTva, 2);

        $cart->update([
            'subtotal_ht' => $subtotalHt,
            'shipping_ht' => $shippingHt,
            'shipping_parcels' => $shipping['parcels'],
            'shipping_per_parcel' => $shipping['rate_per_parcel'],
            'total_ht' => $totalBeforeTva,
            'total_tva' => $totalTva,
            'total_ttc' => $totalTtc,
        ]);
    }

    /**
     * Compute shipping based on cart items' weight buckets.
     * Reuses the same parcel-counting logic as the previous QuoteService.
     */
    public function calculateShipping(Cart $cart): array
    {
        $items = $cart->items()->with('product.category.parent')->get();
        $totalFraction = 0.0;

        foreach ($items as $item) {
            if (! $item->product) continue;

            $unitsPerBox = $item->product->package_weight;
            if (! $unitsPerBox || $unitsPerBox <= 0) {
                $unitsPerBox = 50; // fallback générique
            }
            $totalFraction += $item->quantity / $unitsPerBox;
        }

        $totalParcels = (int) ceil($totalFraction);
        $zone = $cart->shipping_zone ?? 'france';
        $ratePerParcel = self::getShippingRate($zone);
        $shippingHt = round($totalParcels * $ratePerParcel, 2);

        return [
            'parcels' => $totalParcels,
            'shipping_ht' => $shippingHt,
            'rate_per_parcel' => $ratePerParcel,
        ];
    }

    /**
     * Convert an active cart to an order. Marks the cart as 'converted'.
     */
    public function convertToOrder(Cart $cart): Cart
    {
        if ($cart->isEmpty()) {
            throw new \InvalidArgumentException('Le panier est vide.');
        }

        $cart->update([
            'status' => 'converted',
            'converted_at' => now(),
        ]);

        return $cart->refresh();
    }

    /**
     * Mark old inactive carts as abandoned. Called by maintenance cleanup.
     */
    public function abandonStaleCarts(int $afterDays = 30): int
    {
        return Cart::active()
            ->where('updated_at', '<', now()->subDays($afterDays))
            ->update([
                'status' => 'abandoned',
                'abandoned_at' => now(),
            ]);
    }
}
