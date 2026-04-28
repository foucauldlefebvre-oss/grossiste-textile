<?php

namespace App\Livewire;

use App\Models\Cart;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

class CartCounter extends Component
{
    public int $count = 0;

    public function mount(): void
    {
        $this->refreshCount();
    }

    #[On('cart-updated')]
    public function refreshCount(): void
    {
        if (! Auth::check()) {
            $this->count = 0;
            return;
        }

        $cart = Cart::active()->where('user_id', Auth::id())->first();
        $this->count = $cart ? (int) $cart->items()->sum('quantity') : 0;
    }

    public function openSidebar(): void
    {
        $this->dispatch('cart-sidebar-open');
    }

    public function render()
    {
        return view('livewire.cart-counter');
    }
}
