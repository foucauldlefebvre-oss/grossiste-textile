<?php

namespace App\Services;

use App\Models\MarkingTechnique;
use App\Models\PricingRule;
use App\Models\Product;
use App\Models\ProductColor;
use App\Models\Quote;
use App\Models\QuoteItem;
use App\Models\BroderiePricing;
use App\Models\SerigraphiePricing;
use App\Models\TechniquePricing;
use App\Models\TransferPricing;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class QuoteService
{
    const TVA_RATE = 0.20;

    /**
     * Get or create a draft quote for the current user/session.
     */
    public function getOrCreateDraft(?int $userId = null, ?string $sessionId = null): Quote
    {
        $query = Quote::draft();

        if ($userId) {
            $query->where('user_id', $userId);
        } else {
            $query->whereNull('user_id')->where('session_id', $sessionId);
        }

        $quote = $query->first();

        if (! $quote) {
            $quote = Quote::create([
                'reference' => $this->generateReference(),
                'user_id' => $userId,
                'session_id' => $sessionId,
                'status' => 'draft',
                'total_ht' => 0,
                'total_tva' => 0,
                'total_ttc' => 0,
            ]);
        }

        return $quote;
    }

    /**
     * Add an item to a quote.
     */
    public function addItem(
        Quote $quote,
        int $productId,
        ?int $colorId,
        ?int $sizeId,
        ?int $techniqueId,
        int $quantity,
        ?string $markingZone = null,
        int $visualColors = 1,
        ?int $totalQuantityForPricing = null,
        bool $includeSetupCost = true,
    ): QuoteItem {
        $product = Product::findOrFail($productId);
        $technique = $techniqueId ? MarkingTechnique::with('constraint')->findOrFail($techniqueId) : null;

        $pricingQty = $totalQuantityForPricing ?? $quantity;

        // Validate constraints using total quantity for pricing
        if ($technique) {
            $this->validateConstraints($product, $technique, $pricingQty);
        }

        // Calculate textile unit price using degressive coefficient grid
        // Includes color-specific supplier_price and size supplement if applicable
        $unitPriceHt = $this->calculateTextilePrice($product, $pricingQty, $colorId, $sizeId);
        $textileColorName = $colorId ? ProductColor::find($colorId)?->name : null;
        $markingPriceHt = $technique
            ? $this->calculateMarkingPrice($technique->id, $pricingQty, $visualColors, $textileColorName)
            : 0;
        if ($technique) {
            $markingPriceHt *= $this->getProductTechniqueMultiplier($productId, $technique->id);
        }
        $lineTotalHt = ($unitPriceHt + $markingPriceHt) * $quantity;

        // Add setup cost only if requested (avoid duplication for multi-size orders)
        if ($technique && $includeSetupCost) {
            $setupCost = $this->getSetupCost($technique->id, $pricingQty, $visualColors);
            $lineTotalHt += $setupCost;
        }

        $item = $quote->items()->create([
            'marking_group' => $quote->active_marking_group ?? 0,
            'product_id' => $productId,
            'product_color_id' => $colorId,
            'product_size_id' => $sizeId,
            'marking_technique_id' => $techniqueId,
            'quantity' => $quantity,
            'unit_price_ht' => $unitPriceHt,
            'marking_price_ht' => $markingPriceHt,
            'line_total_ht' => round($lineTotalHt, 2),
            'marking_zone' => $markingZone,
            'visual_colors' => $visualColors,
        ]);

        $this->recalculate($quote);

        return $item;
    }

    /**
     * Update the quantity of a quote item, recalculating group siblings for degressive pricing.
     */
    public function updateItemQuantity(QuoteItem $item, int $quantity): void
    {
        $quote = $item->quote;

        // Get sibling items in the same group (same marking_group + product + color)
        $siblings = $quote->items()
            ->where('marking_group', $item->marking_group ?? 0)
            ->where('product_id', $item->product_id)
            ->where('product_color_id', $item->product_color_id)
            ->where('id', '!=', $item->id)
            ->get();

        // Total quantity for the group (with the new quantity for this item)
        $groupTotalQty = $siblings->sum('quantity') + $quantity;

        $technique = $item->marking_technique_id
            ? MarkingTechnique::with('constraint')->find($item->marking_technique_id)
            : null;

        if ($technique) {
            $this->validateConstraints($item->product, $technique, $groupTotalQty);
        }

        $visualColors = $item->visual_colors ?? 1;
        $textileColorName = $item->product_color_id ? ProductColor::find($item->product_color_id)?->name : null;

        // Calculate marking price using GROUP total for degressive pricing
        $markingPriceHt = $technique
            ? $this->calculateMarkingPrice($technique->id, $groupTotalQty, $visualColors, $textileColorName)
            : 0;
        if ($technique) {
            $markingPriceHt *= $this->getProductTechniqueMultiplier($item->product_id, $technique->id);
        }

        // Update the current item
        $lineTotalHt = ((float) $item->unit_price_ht + $markingPriceHt) * $quantity;

        // Setup cost on the first item of the group only
        $isFirstInGroup = $siblings->isEmpty() || $item->id === $quote->items()
            ->where('marking_group', $item->marking_group ?? 0)
            ->where('product_id', $item->product_id)
            ->where('product_color_id', $item->product_color_id)
            ->orderBy('id')
            ->value('id');

        if ($technique && $isFirstInGroup) {
            $setupCost = $this->getSetupCost($technique->id, $groupTotalQty, $visualColors);
            $lineTotalHt += $setupCost;
        }

        $item->update([
            'quantity' => $quantity,
            'marking_price_ht' => $markingPriceHt,
            'line_total_ht' => round($lineTotalHt, 2),
        ]);

        // Recalculate siblings (degressive tier may have changed)
        $this->recalculateGroupSiblings($siblings, $technique, $groupTotalQty, $visualColors, $item);

        $this->recalculate($quote);
    }

    /**
     * Update marking configuration for an entire group of items (same product + color).
     */
    public function updateGroupMarking(Collection $items, ?int $techniqueId, ?string $zone, int $visualColors = 1, ?string $visualFormat = null): void
    {
        if ($items->isEmpty()) {
            return;
        }

        $firstItem = $items->first();
        $quote = $firstItem->quote;
        $product = $firstItem->product;
        $groupTotalQty = $items->sum('quantity');

        $technique = $techniqueId
            ? MarkingTechnique::with('constraint')->findOrFail($techniqueId)
            : null;

        // Validate constraints with group total quantity
        if ($technique) {
            $this->validateConstraints($product, $technique, $groupTotalQty);

            // Validate max_colors from constraint
            $constraint = $technique->constraint;
            if ($constraint && $constraint->max_colors && $visualColors > $constraint->max_colors) {
                $visualColors = $constraint->max_colors;
            }
        }

        // Calculate marking price using group total for degressive pricing
        $textileColorName = $firstItem->product_color_id ? ProductColor::find($firstItem->product_color_id)?->name : null;
        $markingPriceHt = $technique
            ? $this->calculateMarkingPrice($technique->id, $groupTotalQty, $visualColors, $textileColorName, $visualFormat)
            : 0;
        if ($technique) {
            $markingPriceHt *= $this->getProductTechniqueMultiplier($product->id, $technique->id);
        }

        $setupCost = $technique
            ? $this->getSetupCost($technique->id, $groupTotalQty, $visualColors)
            : 0;

        $isFirst = true;
        foreach ($items->sortBy('id') as $item) {
            $lineTotalHt = ((float) $item->unit_price_ht + $markingPriceHt) * $item->quantity;

            // Setup cost only on the first item
            if ($isFirst && $technique) {
                $lineTotalHt += $setupCost;
                $isFirst = false;
            }

            $item->update([
                'marking_technique_id' => $techniqueId,
                'marking_zone' => $zone,
                'visual_colors' => $technique ? $visualColors : null,
                'marking_price_ht' => $markingPriceHt,
                'line_total_ht' => round($lineTotalHt, 2),
            ]);
        }

        $this->recalculate($quote);
    }

    /**
     * Update marking for a group with multiple logos, each having its own technique.
     * Sums marking prices across all logos.
     *
     * @param  array  $logos  [{technique_id, colors, size, zone}, ...]
     */
    public function updateGroupMarkingMultiLogo(Collection $items, array $logos): void
    {
        if ($items->isEmpty()) {
            return;
        }

        $firstItem = $items->first();
        $quote = $firstItem->quote;
        $product = $firstItem->product;
        $groupTotalQty = $items->sum('quantity');
        $textileColorName = $firstItem->product_color_id ? ProductColor::find($firstItem->product_color_id)?->name : null;

        $totalMarkingPriceHt = 0;
        $totalSetupCost = 0;
        $firstTechniqueId = null;
        $seenTechniques = []; // avoid duplicate setup costs per technique

        foreach ($logos as $logo) {
            $techniqueId = $logo['technique_id'] ?? null;
            if (! $techniqueId) {
                continue;
            }

            if ($firstTechniqueId === null) {
                $firstTechniqueId = $techniqueId;
            }

            $technique = MarkingTechnique::with('constraint')->find($techniqueId);
            if (! $technique) {
                continue;
            }

            // Name/Pseudo marking has its own pricing logic
            if (($logo['type'] ?? 'logo') === 'name') {
                $markingPrice = $this->calculateNameMarkingPrice(
                    $techniqueId,
                    $groupTotalQty,
                    $logo['name_size'] ?? 'petit',
                    (int) ($logo['name_lines'] ?? 1),
                    $textileColorName
                );
            } else {
                $numColors = ($logo['colors'] ?? '1') === 'quadri' ? 99 : max(1, (int) ($logo['colors'] ?? 1));
                $visualFormat = $logo['size'] ?? 'A4';
                $markingPrice = $this->calculateMarkingPrice($techniqueId, $groupTotalQty, $numColors, $textileColorName, $visualFormat);
            }
            $markingPrice *= $this->getProductTechniqueMultiplier($product->id, $techniqueId);
            $totalMarkingPriceHt += $markingPrice;

            // Setup cost only once per technique
            if (! isset($seenTechniques[$techniqueId])) {
                $totalSetupCost += $this->getSetupCost($techniqueId, $groupTotalQty, $numColors);
                $seenTechniques[$techniqueId] = true;
            }
        }

        $isFirst = true;
        foreach ($items->sortBy('id') as $item) {
            $lineTotalHt = ((float) $item->unit_price_ht + $totalMarkingPriceHt) * $item->quantity;

            if ($isFirst && $totalSetupCost > 0) {
                $lineTotalHt += $totalSetupCost;
                $isFirst = false;
            }

            $item->update([
                'marking_technique_id' => $firstTechniqueId,
                'marking_price_ht' => $totalMarkingPriceHt,
                'line_total_ht' => round($lineTotalHt, 2),
            ]);
        }

        $this->recalculate($quote);
    }

    /**
     * Recalculate sibling items in a group after a quantity change.
     */
    private function recalculateGroupSiblings(Collection $siblings, ?MarkingTechnique $technique, int $groupTotalQty, int $visualColors, QuoteItem $changedItem): void
    {
        if ($siblings->isEmpty() || ! $technique) {
            return;
        }

        $textileColorName = $changedItem->product_color_id ? ProductColor::find($changedItem->product_color_id)?->name : null;
        $markingPriceHt = $this->calculateMarkingPrice($technique->id, $groupTotalQty, $visualColors, $textileColorName);
        $markingPriceHt *= $this->getProductTechniqueMultiplier($changedItem->product_id, $technique->id);
        $setupCost = $this->getSetupCost($technique->id, $groupTotalQty, $visualColors);

        // Determine the first item ID in the full group (including changedItem)
        $allIds = $siblings->pluck('id')->push($changedItem->id)->sort()->first();

        foreach ($siblings as $sibling) {
            $lineTotalHt = ((float) $sibling->unit_price_ht + $markingPriceHt) * $sibling->quantity;

            if ($sibling->id === $allIds) {
                $lineTotalHt += $setupCost;
            }

            $sibling->update([
                'marking_price_ht' => $markingPriceHt,
                'line_total_ht' => round($lineTotalHt, 2),
            ]);
        }
    }

    /**
     * Remove an item from a quote.
     */
    public function removeItem(QuoteItem $item): void
    {
        $quote = $item->quote;
        $item->delete();
        $this->recalculate($quote);
    }

    /**
     * Recalculate quote totals.
     * Applies mixed-color rule for serigraphie before summing.
     */
    /** Shipping cost per parcel (HT) by zone */
    private const SHIPPING_PER_PARCEL = 9.50;

    public const SHIPPING_ZONES = [
        'france'   => ['rate' => 9.50,  'tva' => true,  'label' => 'France metropolitaine'],
        'eu'       => ['rate' => 35.00, 'tva' => false, 'label' => 'Union europeenne'],
        'suisse'   => ['rate' => 85.00, 'tva' => false, 'label' => 'Suisse (DAP)'],
        'dom_tom'  => ['rate' => 137.00,'tva' => false, 'label' => 'DOM-TOM'],
    ];

    /** Countries by zone */
    public const ZONE_COUNTRIES = [
        'france' => ['FR'],
        'eu' => ['DE','AT','BE','BG','HR','CY','CZ','DK','EE','ES','FI','GR','HU','IE','IT','LV','LT','LU','MT','NL','PL','PT','RO','SK','SI','SE'],
        'suisse' => ['CH','LI'],
        'dom_tom' => ['GP','MQ','GF','RE','YT','PM','NC','PF','WF','BL','MF'],
    ];

    /**
     * Determine shipping zone from country code.
     */
    public static function getShippingZone(string $countryCode): string
    {
        $cc = strtoupper(trim($countryCode));
        foreach (self::ZONE_COUNTRIES as $zone => $countries) {
            if (in_array($cc, $countries)) {
                return $zone;
            }
        }
        return 'france'; // Default
    }

    /**
     * Get shipping rate per parcel for a zone.
     */
    public static function getShippingRate(string $zone): float
    {
        return self::SHIPPING_ZONES[$zone]['rate'] ?? self::SHIPPING_PER_PARCEL;
    }

    /**
     * Determine if TVA applies for a zone + client type.
     * Returns: null (TVA applies), or exemption reason string.
     */
    public static function getVatExemption(string $zone, ?string $vatNumber = null): ?string
    {
        if ($zone === 'france') {
            return null; // TVA applies
        }
        if ($zone === 'eu' && $vatNumber) {
            return 'intra_eu'; // Autoliquidation
        }
        if ($zone === 'eu' && ! $vatNumber) {
            return null; // Particulier EU = TVA française
        }
        if ($zone === 'suisse') {
            return 'export'; // Export hors UE
        }
        if ($zone === 'dom_tom') {
            return 'dom_tom'; // Assimilé export
        }
        return null;
    }

    /**
     * Units per parcel by product category type.
     * Based on parent category or specific category patterns.
     */
    private const UNITS_PER_PARCEL = [
        // T-shirts, débardeurs, maillots sport, bodies, bavoirs
        'light_top' => 100,
        // Polos, chemises, pulls légers
        'medium_top' => 50,
        // Sweats, polaires, vestes, manteaux, parkas, softshells, doudounes
        'heavy_top' => 20,
        // Pantalons, shorts, jupes, joggings
        'bottom' => 50,
        // Casquettes, bonnets, bobs, chapeaux
        'headwear' => 100,
        // Sacs, trousses, portefeuilles
        'bags' => 50,
        // Serviettes, tabliers, couvertures, chiffons
        'home' => 50,
        // Chaussures
        'shoes' => 10,
        // Accessoires divers (écharpes, gants, ceintures, cravates)
        'accessories' => 100,
        // Default
        'default' => 50,
    ];

    /**
     * Determine the parcel type for a product based on its category.
     */
    private function getParcelType(Product $product): string
    {
        $catId = $product->category_id;
        $cat = $product->category;
        $parentId = $cat?->parent_id;
        $catSlug = $cat?->slug ?? '';
        $parentSlug = $cat?->parent?->slug ?? '';

        // T-shirts, débardeurs, maillots sport, bodies, bavoirs, chasubles, bébé
        if (in_array($parentId, [1, 88, 103]) || in_array($catId, [10, 11, 12, 13, 14, 15, 89, 90, 104, 105, 106, 107, 108, 109, 110, 219, 98, 102])) {
            return 'light_top';
        }

        // Polos, chemises
        if (in_array($parentId, [2, 27]) || in_array($catId, [16, 17, 18, 19, 20, 28, 29, 77, 220, 99])) {
            return 'medium_top';
        }

        // Sweats, polaires, pulls, vestes, doudounes, manteaux, parkas, softshells, bodywarmer, blouses
        if (in_array($parentId, [21, 30, 36, 40, 43, 45]) || in_array($catId, [22, 23, 24, 25, 225, 31, 32, 33, 34, 37, 38, 39, 41, 42, 226, 44, 46, 47, 76, 79, 80, 82, 83, 85, 86, 87, 94, 215, 217, 230, 221, 100])) {
            return 'heavy_top';
        }

        // Pantalons, shorts, joggings, jupes
        if (in_array($parentId, [48]) || in_array($catId, [49, 50, 51, 78, 91, 92])) {
            return 'bottom';
        }

        // Couvre-chef
        if (in_array($parentId, [57]) || in_array($catId, [58, 59, 60, 61, 62, 63, 64])) {
            return 'headwear';
        }

        // Sacs
        if (in_array($parentId, [65]) || in_array($catId, [66, 67, 68, 69, 70, 71, 101])) {
            return 'bags';
        }

        // Maison (serviettes, tabliers, couvertures)
        if (in_array($parentId, [111, 116]) || in_array($catId, [112, 113, 115, 117, 118, 119])) {
            return 'home';
        }

        // Chaussures
        if (in_array($catId, [216, 223])) {
            return 'shoes';
        }

        // Sous-vêtements, accessoires
        if (in_array($parentId, [7, 52]) || in_array($catId, [53, 54, 55, 72, 73, 74, 84, 96])) {
            return 'accessories';
        }

        return 'default';
    }

    /**
     * Calculate shipping: count parcels based on product colissage or category fallback.
     */
    public function calculateShipping(Quote $quote): array
    {
        $items = $quote->items()->with('product.category.parent')->get();
        $totalFraction = 0;

        foreach ($items as $item) {
            if (! $item->product) continue;

            $unitsPerBox = $item->product->package_weight;

            if (! $unitsPerBox || $unitsPerBox <= 0) {
                $type = $this->getParcelType($item->product);
                $unitsPerBox = self::UNITS_PER_PARCEL[$type] ?? self::UNITS_PER_PARCEL['default'];
            }

            $totalFraction += $item->quantity / $unitsPerBox;
        }

        $totalParcels = (int) ceil($totalFraction);
        $zone = $quote->shipping_zone ?? 'france';
        $ratePerParcel = self::getShippingRate($zone);
        $shippingHt = round($totalParcels * $ratePerParcel, 2);

        return [
            'parcels' => $totalParcels,
            'shipping_ht' => $shippingHt,
            'rate_per_parcel' => $ratePerParcel,
        ];
    }

    /**
     * Recalculate quote totals with zone-aware shipping and TVA.
     */
    public function recalculate(Quote $quote, ?string $vatExemption = null): void
    {
        $quote->refresh();

        $this->enforceSerigraphieMixedRule($quote);

        $totalHt = $quote->items()->sum('line_total_ht');

        // Calculate shipping
        $shipping = $this->calculateShipping($quote);
        $shippingHt = $shipping['shipping_ht'];

        $totalWithShipping = $totalHt + $shippingHt;

        // TVA depends on exemption
        $tvaRate = $vatExemption ? 0 : self::TVA_RATE;
        $totalTva = round($totalWithShipping * $tvaRate, 2);
        $totalTtc = round($totalWithShipping + $totalTva, 2);

        $quote->update([
            'total_ht' => $totalWithShipping,
            'shipping_ht' => $shippingHt,
            'shipping_parcels' => $shipping['parcels'],
            'shipping_per_parcel' => $shipping['rate_per_parcel'],
            'total_tva' => $totalTva,
            'total_ttc' => $totalTtc,
        ]);
    }

    /**
     * If a quote has serigraphie items on both light AND dark textiles,
     * force dark pricing on all serigraphie items.
     */
    private function enforceSerigraphieMixedRule(Quote $quote): void
    {
        $serigraphie = MarkingTechnique::where('slug', 'serigraphie')->first();
        if (! $serigraphie) {
            return;
        }

        $seriItems = $quote->items()
            ->where('marking_technique_id', $serigraphie->id)
            ->get();

        if ($seriItems->count() < 2) {
            return;
        }

        $hasLight = false;
        $hasDark = false;

        foreach ($seriItems as $item) {
            $colorName = $item->product_color_id ? ProductColor::find($item->product_color_id)?->name : null;
            if ($colorName && SerigraphiePricing::isLightColor($colorName)) {
                $hasLight = true;
            } else {
                $hasDark = true;
            }
        }

        // Only apply when truly mixed (both light and dark present)
        if (! $hasLight || ! $hasDark) {
            return;
        }

        // Group by product+color, recalculate all with dark pricing
        $groups = $seriItems->groupBy(fn ($i) => $i->product_id . '-' . $i->product_color_id);

        foreach ($groups as $groupItems) {
            $groupTotal = $groupItems->sum('quantity');
            $visualColors = $groupItems->first()->visual_colors ?? 1;
            $darkPrice = SerigraphiePricing::calculate($groupTotal, $visualColors, 'standard', ['fonce']);

            foreach ($groupItems->sortBy('id') as $item) {
                if ((float) $item->marking_price_ht === $darkPrice) {
                    continue;
                }

                $lineTotalHt = ((float) $item->unit_price_ht + $darkPrice) * $item->quantity;

                $item->update([
                    'marking_price_ht' => $darkPrice,
                    'line_total_ht' => round($lineTotalHt, 2),
                ]);
            }
        }
    }

    /**
     * Calculate textile unit price HT using the degressive coefficient grid.
     * Uses supplier_price × coefficient. Falls back to base_price if no supplier_price.
     * Supports color-specific supplier_price and size-specific supplements.
     */
    public function calculateTextilePrice(Product $product, int $quantity, ?int $colorId = null, ?int $sizeId = null): float
    {
        // Color may have its own supplier_price (e.g. white is more expensive)
        $colorSupplierPrice = null;
        if ($colorId) {
            $colorSupplierPrice = ProductColor::where('id', $colorId)->value('supplier_price');
        }

        $supplierPrice = (float) ($colorSupplierPrice ?: $product->supplier_price ?: $product->base_price);

        if ($supplierPrice <= 0) {
            return (float) $product->base_price;
        }

        $basePrice = PricingRule::calculate($supplierPrice, $quantity);

        // null = quantity below minimum ("x"), 0 = "sur devis"
        if ($basePrice === null || $basePrice == 0) {
            return (float) ($product->base_price ?: 0);
        }

        // Add size supplement (e.g. large sizes 3XL+ cost more)
        if ($sizeId) {
            $supplement = \App\Models\ProductSize::where('id', $sizeId)->value('price_supplement') ?? 0;
            $basePrice += (float) $supplement;
        }

        return $basePrice;
    }

    /**
     * Calculate name/pseudo marking price.
     *
     * Petit broderie: broderie A7 price + 1€/extra line
     * Petit flex/DTF: DTF A6 price + 0.50€/extra line
     * Grand broderie: broderie A4 price / 2 + 1.50€/extra line
     * Grand flex/DTF: DTF A4 price + 1€/extra line
     */
    public function calculateNameMarkingPrice(int $techniqueId, int $quantity, string $nameSize, int $lines, ?string $textileColorName = null): float
    {
        $technique = MarkingTechnique::find($techniqueId);
        if (! $technique) {
            return 0;
        }

        $slug = $technique->slug;
        $extraLines = max(0, $lines - 1);

        if ($slug === 'broderie') {
            if ($nameSize === 'petit') {
                // Petit broderie = broderie A7 (petit logo rempli) + 1€/extra line
                $basePrice = TransferPricing::where('technique', 'broderie')
                    ->where('format', 'A7')
                    ->where('quantity', '<=', $quantity)
                    ->orderByDesc('quantity')
                    ->value('unit_price') ?? 0;
                return round((float) $basePrice + ($extraLines * 1.00), 2);
            } else {
                // Grand broderie = broderie A4 / 2 + 1.50€/extra line
                $basePrice = TransferPricing::where('technique', 'broderie')
                    ->where('format', 'A4')
                    ->where('quantity', '<=', $quantity)
                    ->orderByDesc('quantity')
                    ->value('unit_price') ?? 0;
                return round((float) $basePrice / 2 + ($extraLines * 1.50), 2);
            }
        } else {
            // Flex or DTF
            $dtfTechnique = 'dtf'; // Use DTF grid for both flex and DTF

            if ($nameSize === 'petit') {
                // Petit flex/DTF = DTF A6 + 0.50€/extra line
                $basePrice = TransferPricing::where('technique', $dtfTechnique)
                    ->where('format', 'A6')
                    ->where('quantity', '<=', $quantity)
                    ->orderByDesc('quantity')
                    ->value('unit_price') ?? 0;
                return round((float) $basePrice + ($extraLines * 0.50), 2);
            } else {
                // Grand flex/DTF = DTF A4 + 1€/extra line
                $basePrice = TransferPricing::where('technique', $dtfTechnique)
                    ->where('format', 'A4')
                    ->where('quantity', '<=', $quantity)
                    ->orderByDesc('quantity')
                    ->value('unit_price') ?? 0;
                return round((float) $basePrice + ($extraLines * 1.00), 2);
            }
        }
    }

    /**
     * Calculate marking unit price from the degressive pricing grid.
     * Delegates to specialized models for serigraphie and transfer techniques.
     */
    public function calculateMarkingPrice(int $techniqueId, int $quantity, int $numColors = 1, ?string $textileColorName = null, ?string $visualFormat = null): float
    {
        $technique = MarkingTechnique::find($techniqueId);

        if ($technique && $technique->slug === 'serigraphie') {
            $textileColors = $textileColorName ? [$textileColorName] : [];

            return SerigraphiePricing::calculate($quantity, $numColors, 'standard', $textileColors);
        }

        if ($technique && $technique->slug === 'broderie') {
            return BroderiePricing::calculate($visualFormat ?? 'A4', $quantity);
        }

        if ($technique && in_array($technique->slug, ['dtf', 'transfert-offset', 'impression-dtg', 'transfert-serigraphique'])) {
            $slug = match ($technique->slug) {
                'impression-dtg' => 'dtg',
                default => $technique->slug,
            };
            return TransferPricing::calculate($slug, $visualFormat ?? 'A4', $quantity);
        }

        $pricing = TechniquePricing::where('marking_technique_id', $techniqueId)
            ->where('quantity_min', '<=', $quantity)
            ->where(function ($q) use ($quantity) {
                $q->whereNull('quantity_max')
                  ->orWhere('quantity_max', '>=', $quantity);
            })
            ->where('num_colors', '<=', $numColors)
            ->orderBy('num_colors', 'desc')
            ->orderBy('quantity_min', 'desc')
            ->first();

        return $pricing ? (float) $pricing->unit_price : 0;
    }

    /**
     * Get setup cost for the matching pricing tier.
     * Serigraphie has no setup cost (included in unit price grid).
     */
    public function getSetupCost(int $techniqueId, int $quantity, int $numColors = 1): float
    {
        $technique = MarkingTechnique::find($techniqueId);
        if ($technique && in_array($technique->slug, ['serigraphie', 'dtf', 'transfert-offset', 'broderie', 'impression-dtg', 'transfert-serigraphique'])) {
            return 0;
        }

        $pricing = TechniquePricing::where('marking_technique_id', $techniqueId)
            ->where('quantity_min', '<=', $quantity)
            ->where(function ($q) use ($quantity) {
                $q->whereNull('quantity_max')
                  ->orWhere('quantity_max', '>=', $quantity);
            })
            ->where('num_colors', '<=', $numColors)
            ->orderBy('num_colors', 'desc')
            ->orderBy('quantity_min', 'desc')
            ->first();

        return $pricing ? (float) $pricing->setup_cost : 0;
    }

    /**
     * Get textile-specific supplement multiplier for a product+technique combination.
     * Returns a multiplier (e.g. 1.10 for +10%, 1.20 for +20%, 1.30 for both).
     */
    public function getProductTechniqueMultiplier(int $productId, int $techniqueId): float
    {
        $rule = \App\Models\ProductTechniqueRule::where('product_id', $productId)
            ->where('marking_technique_id', $techniqueId)
            ->first();

        if (! $rule) {
            return 1.0;
        }

        $multiplier = 1.0;
        if ($rule->supplement_10) {
            $multiplier += 0.10;
        }
        if ($rule->supplement_20) {
            $multiplier += 0.20;
        }

        return $multiplier;
    }

    /**
     * Validate technique constraints against product and quantity.
     */
    public function validateConstraints(Product $product, MarkingTechnique $technique, int $quantity): void
    {
        $constraint = $technique->constraint;

        if ($constraint && $quantity < $constraint->min_quantity) {
            throw new \InvalidArgumentException(
                "La quantite minimum pour {$technique->name} est de {$constraint->min_quantity} pieces."
            );
        }

        // Check product compatibility
        $rule = $product->techniqueRules()
            ->where('marking_technique_id', $technique->id)
            ->first();

        if ($rule && ! $rule->is_compatible) {
            $reason = $rule->incompatibility_reason ?: 'technique incompatible avec ce produit';
            throw new \InvalidArgumentException(
                "{$technique->name} : {$reason}"
            );
        }
    }

    /**
     * Submit a draft quote (change status to "sent").
     */
    public function submit(Quote $quote): Quote
    {
        if ($quote->items()->count() === 0) {
            throw new \InvalidArgumentException('Le devis est vide.');
        }

        $this->recalculate($quote);

        $quote->update([
            'status' => 'sent',
            'expires_at' => now()->addDays(30),
        ]);

        return $quote->refresh();
    }

    /**
     * Generate a unique quote reference.
     */
    private function generateReference(): string
    {
        $prefix = 'DEV-' . date('Ym') . '-';
        $last = Quote::where('reference', 'like', $prefix . '%')
            ->orderByDesc('reference')
            ->value('reference');

        $number = $last
            ? ((int) substr($last, strlen($prefix))) + 1
            : 1;

        return $prefix . str_pad($number, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Get a live price estimate (without creating an item).
     */
    public function estimate(int $productId, ?int $techniqueId, int $quantity, int $visualColors = 1): array
    {
        $product = Product::findOrFail($productId);
        $unitPriceHt = $this->calculateTextilePrice($product, $quantity);
        $markingPriceHt = 0;
        $setupCost = 0;

        if ($techniqueId) {
            $markingPriceHt = $this->calculateMarkingPrice($techniqueId, $quantity, $visualColors);
            $setupCost = $this->getSetupCost($techniqueId, $quantity, $visualColors);
        }

        $lineTotalHt = ($unitPriceHt + $markingPriceHt) * $quantity + $setupCost;
        $tva = round($lineTotalHt * self::TVA_RATE, 2);

        return [
            'unit_price_ht' => $unitPriceHt,
            'marking_price_ht' => $markingPriceHt,
            'setup_cost' => $setupCost,
            'line_total_ht' => round($lineTotalHt, 2),
            'tva' => $tva,
            'line_total_ttc' => round($lineTotalHt + $tva, 2),
        ];
    }
}
