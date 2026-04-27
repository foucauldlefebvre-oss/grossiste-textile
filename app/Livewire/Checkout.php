<?php

namespace App\Livewire;

use App\Models\Address;
use App\Models\Order;
use App\Models\Quote;
use App\Services\OrderService;
use Livewire\Component;

class Checkout extends Component
{
    // Etape actuelle : 1 = adresse, 2 = paiement
    public int $step = 1;

    public ?Quote $quote = null;
    public bool $hasMarking = false;

    // Adresse livraison
    public string $shipping_first_name = '';
    public string $shipping_last_name = '';
    public string $shipping_company = '';
    public string $shipping_address_line_1 = '';
    public string $shipping_address_line_2 = '';
    public string $shipping_postal_code = '';
    public string $shipping_city = '';
    public string $shipping_country = 'FR';
    public string $shipping_phone = '';

    // Facturation differente
    public bool $different_billing = false;
    public string $billing_first_name = '';
    public string $billing_last_name = '';
    public string $billing_company = '';
    public string $billing_address_line_1 = '';
    public string $billing_address_line_2 = '';
    public string $billing_postal_code = '';
    public string $billing_city = '';
    public string $billing_country = 'FR';

    // Societe / TVA
    public string $shipping_siret = '';
    public string $shipping_vat_number = '';
    public bool $is_company = false;
    public bool $is_intra_eu = false;

    // Paiement
    public string $payment_option = 'full'; // full ou deposit
    public string $payment_method = 'cb'; // cb ou virement
    public bool $accept_conditions = false;

    public function mount(): void
    {
        if (! auth()->check()) {
            $this->redirect(route('login'));
            return;
        }

        $this->quote = Quote::with(['items.product', 'items.technique'])
            ->where('user_id', auth()->id())
            ->whereIn('status', ['draft', 'sent'])
            ->latest()
            ->first();

        // If no draft/sent quote, check for accepted quote with unpaid order (user came back)
        if (! $this->quote) {
            $this->quote = Quote::with(['items.product', 'items.technique'])
                ->where('user_id', auth()->id())
                ->where('status', 'accepted')
                ->whereHas('order', fn ($q) => $q->where('payment_status', 'pending'))
                ->latest()
                ->first();

            // Reset it back to draft so the user can re-order
            if ($this->quote) {
                $this->quote->order?->delete();
                $this->quote->update(['status' => 'draft', 'accepted_at' => null]);
            }
        }

        if (! $this->quote || $this->quote->items->isEmpty()) {
            $this->redirect(route('mon-devis'));
            return;
        }

        // Verifier si le devis contient du marquage
        $this->hasMarking = $this->quote->items->contains(fn ($item) => (float) $item->marking_price_ht > 0);

        // Pre-remplir avec l'adresse par defaut du client
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
            // Convert old full names to codes
            if (strlen($country) > 2) {
                $country = 'FR';
            }
            $this->shipping_country = $country;
            $this->shipping_phone = $default->phone ?? '';
        } else {
            // Fallback depuis le profil utilisateur
            $user = auth()->user();
            $names = explode(' ', $user->name ?? '', 2);
            $this->shipping_first_name = $names[0] ?? '';
            $this->shipping_last_name = $names[1] ?? '';
            $this->shipping_company = $user->company ?? '';
            $this->shipping_phone = $user->phone ?? '';
        }

        // Pre-fill company info
        $user = auth()->user();
        $this->shipping_siret = $user->siret ?? '';
        $this->shipping_vat_number = $user->vat_number ?? '';
        $this->is_company = ! empty($this->shipping_company);
    }

    /**
     * Detect shipping zone from country and update quote shipping.
     */
    public function updatedShippingCountry(): void
    {
        // Reset intra-EU if country is not EU
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
        if (! $this->quote) return;

        $zone = \App\Services\QuoteService::getShippingZone($this->shipping_country);
        $this->quote->update(['shipping_zone' => $zone]);
        app(\App\Services\QuoteService::class)->recalculate(
            $this->quote,
            \App\Services\QuoteService::getVatExemption($zone, $this->is_intra_eu ? $this->shipping_vat_number : null)
        );
        $this->quote->refresh();
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
            'shipping_first_name.required' => 'Le prenom est obligatoire.',
            'shipping_last_name.required' => 'Le nom est obligatoire.',
            'shipping_address_line_1.required' => 'L\'adresse est obligatoire.',
            'shipping_postal_code.required' => 'Le code postal est obligatoire.',
            'shipping_city.required' => 'La ville est obligatoire.',
            'shipping_phone.required' => 'Le telephone est obligatoire.',
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

        // Validate company fields
        if ($this->is_company) {
            $this->validate([
                'shipping_siret' => 'required|string|min:14|max:20',
            ], [
                'shipping_siret.required' => 'Le SIRET est obligatoire pour une societe.',
                'shipping_siret.min' => 'Le SIRET doit contenir 14 chiffres.',
            ]);
        }

        if ($this->is_intra_eu) {
            $this->validate([
                'shipping_vat_number' => 'required|string|min:8|max:20',
            ], [
                'shipping_vat_number.required' => 'Le numero de TVA intracommunautaire est obligatoire.',
            ]);
        }

        // Update shipping zone before going to payment
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

        // Sauvegarder / creer l'adresse de livraison
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

        // Soumettre le devis si encore draft
        if ($this->quote->status === 'draft') {
            app(\App\Services\QuoteService::class)->submit($this->quote);
            $this->quote->refresh();
        }

        // Save SIRET/VAT to user profile
        $user = auth()->user();
        if ($this->shipping_siret && $this->shipping_siret !== $user->siret) {
            $user->update(['siret' => $this->shipping_siret]);
        }
        if ($this->shipping_vat_number && $this->shipping_vat_number !== $user->vat_number) {
            $user->update(['vat_number' => $this->shipping_vat_number]);
        }

        // Zone & TVA
        $zone = \App\Services\QuoteService::getShippingZone($this->shipping_country);
        $vatExemption = \App\Services\QuoteService::getVatExemption(
            $zone,
            $this->is_intra_eu ? $this->shipping_vat_number : null
        );

        // Creer la commande
        $order = app(OrderService::class)->createFromQuote($this->quote);
        $order->update([
            'shipping_address_id' => $shippingAddress->id,
            'billing_address_id' => $billingAddress->id,
            'shipping_zone' => $zone,
            'shipping_per_parcel' => \App\Services\QuoteService::getShippingRate($zone),
            'vat_exemption' => $vatExemption,
            'customer_vat_number' => $this->is_intra_eu ? $this->shipping_vat_number : null,
            'customer_siret' => $this->shipping_siret ?: null,
            'customer_notes' => $this->payment_option === 'deposit' ? 'Acompte 50% choisi' : null,
        ]);

        // Montant a payer
        $amount = $this->payment_option === 'deposit'
            ? round((float) $order->total_ttc / 2, 2)
            : (float) $order->total_ttc;

        if ($this->payment_method === 'virement') {
            // Store amount to pay (deposit or full) — mails sent on confirmation only
            $order->update(['customer_notes' => ($this->payment_option === 'deposit' ? 'Acompte 50% choisi' : null)]);
            $this->redirect(route('checkout.virement', ['order' => $order->reference]));
        } else {
            // Paiement CB via Systempay
            try {
                $formToken = app(\App\Services\SystempayService::class)->createPayment($order, $amount);
                // Store formToken in session and redirect to payment page
                session(['systempay_form_token' => $formToken, 'systempay_order_ref' => $order->reference]);
                $this->redirect(route('checkout.systempay', ['order' => $order->reference]));
            } catch (\Exception $e) {
                session()->flash('checkout-error', 'Erreur paiement : ' . $e->getMessage());
            }
        }
    }

    public function render()
    {
        return view('livewire.checkout');
    }
}
