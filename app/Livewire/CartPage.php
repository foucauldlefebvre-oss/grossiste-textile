<?php

namespace App\Livewire;

use App\Models\Cart;
use App\Models\CartItem;
use App\Services\CartService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class CartPage extends Component
{
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
        $cart = Cart::with(['items.product', 'items.color', 'items.size'])
            ->active()
            ->where('user_id', Auth::id())
            ->first();

        return view('livewire.cart-page', [
            'cart' => $cart,
        ]);
    }
}
