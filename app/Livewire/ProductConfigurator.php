<?php

namespace App\Livewire;

use App\Models\PricingRule;
use App\Models\Product;
use App\Models\ProductSize;
use App\Services\QuoteService;
use Livewire\Component;

class ProductConfigurator extends Component
{
    public Product $product;
    public ?int $selectedColorId = null;
    public array $sizeQuantities = [];

    public array $estimate = [];

    public function mount(Product $product): void
    {
        $this->product = $product;

        if ($product->colors->count()) {
            $this->selectedColorId = $product->colors->first()->id;
        }

        $this->initSizeQuantities();
        $this->updateEstimate();
    }

    public function selectColor(int $colorId): void
    {
        $this->selectedColorId = $colorId;
        $this->initSizeQuantities();
        $this->updateEstimate();
    }

    public function syncQuantities(array $quantities): void
    {
        foreach ($quantities as $key => $val) {
            if (array_key_exists($key, $this->sizeQuantities)) {
                $this->sizeQuantities[$key] = max(0, (int) $val);
            }
        }
        $this->updateEstimate();
    }

    public function getSelectedColorProperty()
    {
        if (! $this->selectedColorId) {
            return null;
        }

        return $this->product->colors->firstWhere('id', $this->selectedColorId);
    }

    public function getAvailableSizesProperty()
    {
        if (! $this->selectedColor) {
            return collect();
        }

        return $this->selectedColor->sizes
            ->where('is_available', true)
            ->sortBy(fn ($s) => ProductSize::sortPosition($s->size))
            ->values();
    }

    public function getTotalQuantityProperty(): int
    {
        return array_sum($this->sizeQuantities);
    }

    public function getHasAnySizeQuantityProperty(): bool
    {
        return $this->totalQuantity > 0;
    }

    public function getPriceGridProperty(): array
    {
        $supplierPrice = (float) ($this->product->supplier_price ?: $this->product->base_price);

        if ($supplierPrice <= 0) {
            return [];
        }

        $rules = PricingRule::where('min_price', '<=', $supplierPrice)
            ->where(function ($q) use ($supplierPrice) {
                $q->whereNull('max_price')
                  ->orWhere('max_price', '>=', $supplierPrice);
            })
            ->orderBy('min_quantity')
            ->get();

        $grid = [];
        foreach ($rules as $rule) {
            $grid[] = [
                'min_qty' => $rule->min_quantity,
                'max_qty' => $rule->max_quantity,
                'unit_price' => round($supplierPrice * (float) $rule->coefficient, 2),
                'label' => $rule->max_quantity
                    ? $rule->min_quantity . '-' . $rule->max_quantity
                    : $rule->min_quantity . '+',
            ];
        }

        return $grid;
    }

    protected function initSizeQuantities(): void
    {
        $this->sizeQuantities = [];

        foreach ($this->availableSizes as $size) {
            $this->sizeQuantities[$size->id] = 0;
        }
    }

    public function updateEstimate(): void
    {
        $quantity = $this->totalQuantity;

        if ($quantity < 1) {
            $this->estimate = [];
            return;
        }

        $supplierPrice = (float) ($this->product->supplier_price ?: $this->product->base_price);
        $unitPrice = PricingRule::calculate($supplierPrice, $quantity);

        if ($unitPrice <= 0) {
            $unitPrice = (float) $this->product->base_price;
        }

        $lineTotalHt = $unitPrice * $quantity;

        $this->estimate = [
            'unit_price_ht' => $unitPrice,
            'line_total_ht' => round($lineTotalHt, 2),
            'tva' => round($lineTotalHt * 0.20, 2),
            'line_total_ttc' => round($lineTotalHt * 1.20, 2),
        ];
    }

    public function addToQuote(): void
    {
        $service = app(QuoteService::class);
        $userId = auth()->id();
        $sessionId = session()->getId();

        try {
            $quote = $service->getOrCreateDraft($userId, $sessionId);
            $totalQty = $this->totalQuantity;

            foreach ($this->sizeQuantities as $sizeId => $qty) {
                if ($qty < 1) {
                    continue;
                }

                $service->addItem(
                    quote: $quote,
                    productId: $this->product->id,
                    colorId: $this->selectedColorId,
                    sizeId: (int) $sizeId,
                    techniqueId: null,
                    quantity: $qty,
                    totalQuantityForPricing: $totalQty,
                );
            }

            $this->dispatch('item-added-to-quote');
            $this->dispatch('quantities-reset');
            session()->flash('quote-success', 'Produit ajouté au devis !');

            $this->initSizeQuantities();
            $this->updateEstimate();
        } catch (\InvalidArgumentException $e) {
            session()->flash('quote-error', $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.product-configurator');
    }
}
