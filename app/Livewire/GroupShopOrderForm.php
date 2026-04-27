<?php

namespace App\Livewire;

use App\Models\GroupShop;
use App\Models\GroupShopOrder;
use App\Models\GroupShopProduct;
use App\Services\QuoteService;
use Livewire\Component;

class GroupShopOrderForm extends Component
{
    public int $groupShopId;

    // Buyer info
    public string $firstName = '';
    public string $lastName = '';
    public string $email = '';
    public string $department = '';

    // Order lines
    public array $lines = [];

    public function mount(int $groupShopId): void
    {
        $this->groupShopId = $groupShopId;
        $this->initLines();
    }

    private function initLines(): void
    {
        $shop = GroupShop::with('products.product')->find($this->groupShopId);
        if (! $shop) {
            return;
        }

        foreach ($shop->products as $gsp) {
            $this->lines[$gsp->id] = [
                'product_name' => $gsp->product->name,
                'size' => '',
                'quantity' => 0,
                'fixed_price' => $gsp->fixed_price ? (float) $gsp->fixed_price : null,
            ];
        }
    }

    public function getGroupShopProperty(): GroupShop
    {
        return GroupShop::with([
            'products.product.colors',
            'products.product.sizes',
            'products.technique',
        ])->findOrFail($this->groupShopId);
    }

    public function getOrderTotalProperty(): float
    {
        $total = 0;
        foreach ($this->lines as $gspId => $line) {
            if ($line['quantity'] > 0 && $line['fixed_price']) {
                $total += $line['fixed_price'] * $line['quantity'];
            }
        }

        return round($total, 2);
    }

    public function submit(): void
    {
        $this->validate([
            'firstName' => 'required|string|max:255',
            'lastName' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'department' => 'nullable|string|max:255',
        ]);

        $shop = GroupShop::findOrFail($this->groupShopId);
        if (! $shop->isOpen()) {
            session()->flash('order-error', 'Cette boutique est fermee.');
            return;
        }

        // Check at least one line has quantity > 0
        $hasItems = collect($this->lines)->contains(fn ($l) => $l['quantity'] > 0 && $l['size'] !== '');
        if (! $hasItems) {
            session()->flash('order-error', 'Selectionnez au moins un produit avec une taille et une quantite.');
            return;
        }

        foreach ($this->lines as $gspId => $line) {
            if ($line['quantity'] < 1 || $line['size'] === '') {
                continue;
            }

            $gsp = GroupShopProduct::find($gspId);
            if (! $gsp) {
                continue;
            }

            $unitPrice = $gsp->fixed_price
                ? (float) $gsp->fixed_price
                : (float) $gsp->product->base_price;

            GroupShopOrder::create([
                'group_shop_id' => $this->groupShopId,
                'group_shop_product_id' => $gspId,
                'first_name' => $this->firstName,
                'last_name' => $this->lastName,
                'email' => $this->email,
                'department' => $this->department ?: null,
                'size' => $line['size'],
                'quantity' => $line['quantity'],
                'unit_price' => $unitPrice,
                'total' => round($unitPrice * $line['quantity'], 2),
            ]);
        }

        return $this->redirect(route('group-shop.confirmation', $shop), navigate: true);
    }

    public function render()
    {
        return view('livewire.group-shop-order-form');
    }
}
