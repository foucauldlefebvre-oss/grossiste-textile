<?php

namespace App\Livewire;

use App\Models\Address;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\CartService;
use Illuminate\Support\Str;
use Livewire\Component;

class CheckoutForm extends Component
{
    public int $step = 1; // 1 = adresse, 2 = paiement

    public ?Cart $cart = null;

    // Shipping address
    public string $shipping_first_name = '';
    public string $shipping_last_name = '';
    public string $shipping_company = '';
    public string $shipping_address_line_1 = '';
    public string $shipping_address_line_2 = '';
    public string $shipping_postal_code = '';
    public string $shipping_city = '';
    public string $shipping_country = 'FR';
    public string $shipping_phone = '';

    // Billing
    public bool $different_billing = false;
    public string $billing_first_name = '';
    public string $billing_last_name = '';
    public string $billing_company = '';
    public string $billing_address_line_1 = '';
    public string $billing_address_line_2 = '';
    public string $billing_postal_code = '';
    public string $billing_city = '';
    public string $billing_country = 'FR';

    // Société / TVA
    public string $shipping_siret = '';
    public string $shipping_vat_number = '';
    public bool $is_company = false;
    public bool $is_intra_eu = false;

    // Paiement
    public string $payment_option = 'full'; // full | deposit
    public string $payment_method = 'cb';   // cb | virement
    public bool $accept_conditions = false;

    public function mount(): void
    {
        if (! auth()->check()) {
            $this->redirect(route('login'));
            return;
        }

        $this->cart = Cart::with(['items.product', 'items.color', 'items.size'])
            ->active()
            ->where('user_id', auth()->id())
            ->latest()
            ->first();

        if (! $this->cart || $this->cart->items->isEmpty()) {
            $this->redirect(route('cart'));
            return;
        }

        // Pré-remplir avec l'adresse par défaut
        $default = auth()->user()->addresses()->where('is_default', true)->first()
            ?? auth()->user()->addresses()->first();

        if ($default) {
            $this->shipping_first_name = $default->first_name ?? '';
            $this->shipping_last_name = $default->last_name ?? '';
            $this->shipping_company = $default->company ?? '';
            $this->shipping_address_line_1 = $default->address_line_1 ?? '';
            $this->shipping_address_line_2 = $default->address_line_2 ?? '';
            $this->shipping_postal_code = $default->postal_code ?? '';
            $this->shipping_city = $default->city ?? '';
            $country = $default->country ?? 'FR';
            $this->shipping_country = strlen($country) > 2 ? 'FR' : $country;
            $this->shipping_phone = $default->phone ?? '';
        } else {
            $user = auth()->user();
            $names = explode(' ', $user->name ?? '', 2);
            $this->shipping_first_name = $names[0] ?? '';
            $this->shipping_last_name = $names[1] ?? '';
            $this->shipping_company = $user->company ?? '';
            $this->shipping_phone = $user->phone ?? '';
        }

        $user = auth()->user();
        $this->shipping_siret = $user->siret ?? '';
        $this->shipping_vat_number = $user->vat_number ?? '';
        $this->is_company = ! empty($this->shipping_company);
    }

    public function updatedShippingCountry(): void
    {
        $euCountries = ['DE','AT','BE','BG','HR','CY','CZ','DK','EE','ES','FI','GR','HU','IE','IT','LV','LT','LU','MT','NL','PL','PT','RO','SK','SI','SE'];
        if (! in_array($this->shipping_country, $euCountries)) {
            $this->is_intra_eu = false;
            $this->shipping_vat_number = '';
        }
        $this->updateShippingZone();
    }

    public function updatedIsIntraEu(): void
    {
        $this->updateShippingZone();
    }

    public function updatedShippingVatNumber(): void
    {
        $this->updateShippingZone();
    }

    private function updateShippingZone(): void
    {
        if (! $this->cart) return;

        $zone = CartService::getShippingZone($this->shipping_country);
        $vatExemption = CartService::getVatExemption($zone, $this->is_intra_eu ? $this->shipping_vat_number : null);

        $this->cart->update([
            'shipping_zone' => $zone,
            'vat_exemption' => $vatExemption,
        ]);
        app(CartService::class)->recalculate($this->cart, $vatExemption);
        $this->cart->refresh();
    }

    public function goToPayment(): void
    {
        $this->validate([
            'shipping_first_name' => 'required|string|max:255',
            'shipping_last_name' => 'required|string|max:255',
            'shipping_address_line_1' => 'required|string|max:255',
            'shipping_postal_code' => 'required|string|max:10',
            'shipping_city' => 'required|string|max:255',
            'shipping_phone' => 'required|string|max:50',
        ], [
            'shipping_first_name.required' => 'Le prénom est obligatoire.',
            'shipping_last_name.required' => 'Le nom est obligatoire.',
            'shipping_address_line_1.required' => 'L\'adresse est obligatoire.',
            'shipping_postal_code.required' => 'Le code postal est obligatoire.',
            'shipping_city.required' => 'La ville est obligatoire.',
            'shipping_phone.required' => 'Le téléphone est obligatoire.',
        ]);

        if ($this->different_billing) {
            $this->validate([
                'billing_first_name' => 'required|string|max:255',
                'billing_last_name' => 'required|string|max:255',
                'billing_address_line_1' => 'required|string|max:255',
                'billing_postal_code' => 'required|string|max:10',
                'billing_city' => 'required|string|max:255',
            ]);
        }

        if ($this->is_company) {
            $this->validate([
                'shipping_siret' => 'required|string|min:14|max:20',
            ], [
                'shipping_siret.required' => 'Le SIRET est obligatoire pour une société.',
                'shipping_siret.min' => 'Le SIRET doit contenir 14 chiffres.',
            ]);
        }

        if ($this->is_intra_eu) {
            $this->validate([
                'shipping_vat_number' => 'required|string|min:8|max:20',
            ], [
                'shipping_vat_number.required' => 'Le numéro de TVA intracommunautaire est obligatoire.',
            ]);
        }

        $this->updateShippingZone();
        $this->step = 2;
    }

    public function backToAddress(): void
    {
        $this->step = 1;
    }

    public function placeOrder(): void
    {
        $this->validate([
            'accept_conditions' => 'accepted',
            'payment_method' => 'required|in:cb,virement',
        ], [
            'accept_conditions.accepted' => 'Vous devez accepter les conditions.',
        ]);

        $shippingAddress = Address::create([
            'user_id' => auth()->id(),
            'label' => 'Livraison',
            'first_name' => $this->shipping_first_name,
            'last_name' => $this->shipping_last_name,
            'company' => $this->shipping_company,
            'address_line_1' => $this->shipping_address_line_1,
            'address_line_2' => $this->shipping_address_line_2,
            'postal_code' => $this->shipping_postal_code,
            'city' => $this->shipping_city,
            'country' => $this->shipping_country,
            'phone' => $this->shipping_phone,
        ]);

        $billingAddress = $shippingAddress;
        if ($this->different_billing) {
            $billingAddress = Address::create([
                'user_id' => auth()->id(),
                'label' => 'Facturation',
                'first_name' => $this->billing_first_name,
                'last_name' => $this->billing_last_name,
                'company' => $this->billing_company,
                'address_line_1' => $this->billing_address_line_1,
                'address_line_2' => $this->billing_address_line_2,
                'postal_code' => $this->billing_postal_code,
                'city' => $this->billing_city,
                'country' => $this->billing_country,
            ]);
        }

        // Save SIRET/VAT
        $user = auth()->user();
        if ($this->shipping_siret && $this->shipping_siret !== $user->siret) {
            $user->update(['siret' => $this->shipping_siret]);
        }
        if ($this->shipping_vat_number && $this->shipping_vat_number !== $user->vat_number) {
            $user->update(['vat_number' => $this->shipping_vat_number]);
        }

        $zone = CartService::getShippingZone($this->shipping_country);
        $vatExemption = CartService::getVatExemption(
            $zone,
            $this->is_intra_eu ? $this->shipping_vat_number : null
        );

        // Convertir cart → order
        $cart = $this->cart->refresh();
        $order = $this->createOrderFromCart($cart, $shippingAddress, $billingAddress, $zone, $vatExemption);

        $order->update([
            'customer_notes' => $this->payment_option === 'deposit' ? 'Acompte 50% choisi' : null,
            'customer_vat_number' => $this->is_intra_eu ? $this->shipping_vat_number : null,
            'customer_siret' => $this->shipping_siret ?: null,
        ]);

        app(CartService::class)->convertToOrder($cart);

        $amount = $this->payment_option === 'deposit'
            ? round((float) $order->total_ttc / 2, 2)
            : (float) $order->total_ttc;

        if ($this->payment_method === 'virement') {
            $this->redirect(route('checkout.virement', ['order' => $order->reference]));
        } else {
            try {
                $formToken = app(\App\Services\SystempayService::class)->createPayment($order, $amount);
                session(['systempay_form_token' => $formToken, 'systempay_order_ref' => $order->reference]);
                $this->redirect(route('checkout.systempay', ['order' => $order->reference]));
            } catch (\Exception $e) {
                session()->flash('checkout-error', 'Erreur paiement : ' . $e->getMessage());
            }
        }
    }

    private function createOrderFromCart(Cart $cart, Address $shipping, Address $billing, string $zone, ?string $vatExemption): Order
    {
        $reference = $this->generateOrderReference();

        $order = Order::create([
            'reference' => $reference,
            'user_id' => $cart->user_id,
            'shipping_address_id' => $shipping->id,
            'billing_address_id' => $billing->id,
            'status' => 'pending',
            'payment_status' => 'pending',
            'subtotal_ht' => $cart->subtotal_ht,
            'shipping_ht' => $cart->shipping_ht,
            'shipping_zone' => $zone,
            'shipping_per_parcel' => CartService::getShippingRate($zone),
            'total_ht' => $cart->total_ht,
            'total_tva' => $cart->total_tva,
            'total_ttc' => $cart->total_ttc,
            'vat_exemption' => $vatExemption,
        ]);

        foreach ($cart->items as $item) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $item->product_id,
                'product_color_id' => $item->product_color_id,
                'product_size_id' => $item->product_size_id,
                'quantity' => $item->quantity,
                'unit_price_ht' => $item->unit_price_ht,
                'line_total_ht' => $item->line_total_ht,
            ]);
        }

        return $order->refresh();
    }

    private function generateOrderReference(): string
    {
        $prefix = 'CMD' . date('Ym');
        $last = Order::where('reference', 'like', $prefix . '%')
            ->orderByDesc('reference')
            ->value('reference');
        $number = $last ? ((int) substr($last, strlen($prefix))) + 1 : 1;
        return $prefix . str_pad($number, 4, '0', STR_PAD_LEFT);
    }

    public function render()
    {
        return view('livewire.checkout-form');
    }
}
