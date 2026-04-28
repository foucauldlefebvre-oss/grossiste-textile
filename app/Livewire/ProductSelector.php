<?php

namespace App\Livewire;

use App\Models\Product;
use App\Services\CartService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;

class ProductSelector extends Component
{
    public Product $product;

    /** @var ?int */
    public $selectedColorId = null;

    /** @var array<int,int>  product_size_id => quantity */
    public array $quantities = [];

    public ?string $flash = null;

    public function mount(Product $product): void
    {
        $this->product = $product;
        $this->selectedColorId = $product->colors()->orderBy('sort_order')->value('id');
    }

    public function selectColor(int $colorId): void
    {
        $this->selectedColorId = $colorId;
        $this->quantities = [];
        $this->flash = null;
    }

    /** Total quantity across all selected sizes for the active color. */
    #[Computed]
    public function totalQuantity(): int
    {
        return (int) array_sum(array_map('intval', $this->quantities));
    }

    /**
     * Live unit price preview based on the cumulative quantity (degressive).
     * Returns 0 if user not authenticated (price hidden).
     */
    #[Computed]
    public function previewUnitPrice(): float
    {
        if (! Auth::check()) {
            return 0;
        }

        $qty = max(1, $this->totalQuantity());
        return app(CartService::class)->calculateUnitPrice(
            $this->product,
            $this->selectedColorId,
            null,
            $qty
        );
    }

    public function addToCart(): void
    {
        if (! Auth::check()) {
            return; // bouton remplacé par CTA register côté UI
        }

        if ($this->totalQuantity() < 1) {
            $this->flash = 'Sélectionnez au moins une quantité.';
            return;
        }

        $cart = app(CartService::class)->getOrCreateActive(Auth::id());

        foreach ($this->quantities as $sizeId => $qty) {
            $qty = (int) $qty;
            if ($qty < 1) {
                continue;
            }
            app(CartService::class)->addItem(
                $cart,
                $this->product->id,
                $this->selectedColorId,
                (int) $sizeId,
                $qty
            );
        }

        $this->quantities = [];
        $this->flash = 'Ajouté au panier.';
        $this->dispatch('cart-updated');
        $this->dispatch('cart-sidebar-open');
    }

    public function render()
    {
        $color = $this->selectedColorId
            ? $this->product->colors()
                ->with(['sizes' => fn ($q) => $q->where('is_available', true)->orderBy('id')])
                ->find($this->selectedColorId)
            : null;

        $colors = $this->product->colors()->orderBy('sort_order')->get();

        return view('livewire.product-selector', [
            'colors' => $colors,
            'color' => $color,
        ]);
    }
}
