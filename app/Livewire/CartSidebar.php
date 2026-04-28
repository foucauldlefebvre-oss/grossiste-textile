<?php

namespace App\Livewire;

use App\Models\Cart;
use App\Models\CartItem;
use App\Services\CartService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

class CartSidebar extends Component
{
    public bool $open = false;
    public ?int $cartId = null;

    public function mount(): void
    {
        $this->loadCart();
    }

    #[On('cart-sidebar-open')]
    public function openSidebar(): void
    {
        $this->loadCart();
        $this->open = true;
    }

    #[On('cart-updated')]
    public function loadCart(): void
    {
        if (! Auth::check()) {
            $this->cartId = null;
            return;
        }

        $cart = Cart::active()->where('user_id', Auth::id())->first();
        $this->cartId = $cart?->id;
    }

    public function close(): void
    {
        $this->open = false;
    }

    public function increment(int $itemId): void
    {
        $item = $this->ownedItem($itemId);
        if (! $item) return;
        app(CartService::class)->updateQuantity($item, $item->quantity + 1);
        $this->dispatch('cart-updated');
    }

    public function decrement(int $itemId): void
    {
        $item = $this->ownedItem($itemId);
        if (! $item) return;
        app(CartService::class)->updateQuantity($item, $item->quantity - 1);
        $this->dispatch('cart-updated');
    }

    public function remove(int $itemId): void
    {
        $item = $this->ownedItem($itemId);
        if (! $item) return;
        app(CartService::class)->removeItem($item);
        $this->dispatch('cart-updated');
    }

    private function ownedItem(int $itemId): ?CartItem
    {
        return CartItem::with('cart')
            ->where('id', $itemId)
            ->whereHas('cart', fn ($q) => $q->where('user_id', Auth::id()))
            ->first();
    }

    public function render()
    {
        $cart = $this->cartId
            ? Cart::with(['items.product', 'items.color', 'items.size'])->find($this->cartId)
            : null;

        return view('livewire.cart-sidebar', [
            'cart' => $cart,
        ]);
    }
}
