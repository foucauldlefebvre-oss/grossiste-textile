<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    {{-- TODO étape future: refonte UX du checkout. Stub fonctionnel pour 2b. --}}

    @if($cart)
        <header class="mb-8">
            <h1 class="text-2xl font-bold mb-2">Commande</h1>
            <div class="flex items-center gap-3 text-sm">
                <span class="px-3 py-1 rounded-full {{ $step === 1 ? 'bg-bordeaux text-white' : 'bg-gray-200 text-gray-600' }}">1. Adresse</span>
                <span class="text-gray-400">→</span>
                <span class="px-3 py-1 rounded-full {{ $step === 2 ? 'bg-bordeaux text-white' : 'bg-gray-200 text-gray-600' }}">2. Paiement</span>
            </div>
        </header>

        @if(session('checkout-error'))
            <div class="mb-6 px-4 py-3 bg-red-50 border border-red-200 text-red-700 text-sm rounded-lg">{{ session('checkout-error') }}</div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2 space-y-6">
                @if($step === 1)
                    <section class="bg-white rounded-lg border p-6 space-y-4">
                        <h2 class="font-semibold">Adresse de livraison</h2>
                        <div class="grid grid-cols-2 gap-3">
                            <input type="text" wire:model="shipping_first_name" placeholder="Prénom" class="border-gray-300 rounded">
                            <input type="text" wire:model="shipping_last_name" placeholder="Nom" class="border-gray-300 rounded">
                        </div>
                        <input type="text" wire:model="shipping_company" placeholder="Société (optionnel)" class="w-full border-gray-300 rounded">
                        <input type="text" wire:model="shipping_address_line_1" placeholder="Adresse" class="w-full border-gray-300 rounded">
                        <input type="text" wire:model="shipping_address_line_2" placeholder="Complément (optionnel)" class="w-full border-gray-300 rounded">
                        <div class="grid grid-cols-3 gap-3">
                            <input type="text" wire:model="shipping_postal_code" placeholder="CP" class="border-gray-300 rounded">
                            <input type="text" wire:model="shipping_city" placeholder="Ville" class="col-span-2 border-gray-300 rounded">
                        </div>
                        <select wire:model.live="shipping_country" class="w-full border-gray-300 rounded">
                            <option value="FR">France</option>
                            <option value="DE">Allemagne</option>
                            <option value="BE">Belgique</option>
                            <option value="ES">Espagne</option>
                            <option value="IT">Italie</option>
                            <option value="LU">Luxembourg</option>
                            <option value="NL">Pays-Bas</option>
                            <option value="CH">Suisse</option>
                        </select>
                        <input type="text" wire:model="shipping_phone" placeholder="Téléphone" class="w-full border-gray-300 rounded">

                        <label class="flex items-center gap-2 text-sm pt-3 border-t">
                            <input type="checkbox" wire:model.live="is_company">
                            <span>Je commande pour une société (SIRET requis)</span>
                        </label>
                        @if($is_company)
                            <input type="text" wire:model="shipping_siret" placeholder="SIRET (14 chiffres)" class="w-full border-gray-300 rounded">
                        @endif

                        @error('shipping_first_name') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                        @error('shipping_last_name') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                        @error('shipping_address_line_1') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                        @error('shipping_postal_code') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                        @error('shipping_city') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                        @error('shipping_phone') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                        @error('shipping_siret') <p class="text-sm text-red-600">{{ $message }}</p> @enderror

                        <button type="button" wire:click="goToPayment" class="w-full px-4 py-3 bg-bordeaux text-white font-semibold rounded-lg hover:bg-bordeaux-dark transition">Continuer vers le paiement</button>
                    </section>
                @else
                    <section class="bg-white rounded-lg border p-6 space-y-4">
                        <h2 class="font-semibold">Mode de paiement</h2>
                        <label class="flex items-center gap-2">
                            <input type="radio" wire:model="payment_method" value="cb">
                            <span>Carte bancaire</span>
                        </label>
                        <label class="flex items-center gap-2">
                            <input type="radio" wire:model="payment_method" value="virement">
                            <span>Virement bancaire</span>
                        </label>

                        <h2 class="font-semibold pt-4 border-t">Échéance</h2>
                        <label class="flex items-center gap-2">
                            <input type="radio" wire:model="payment_option" value="full">
                            <span>Paiement intégral</span>
                        </label>
                        <label class="flex items-center gap-2">
                            <input type="radio" wire:model="payment_option" value="deposit">
                            <span>Acompte 50%</span>
                        </label>

                        <label class="flex items-start gap-2 pt-4 border-t text-sm">
                            <input type="checkbox" wire:model="accept_conditions" class="mt-0.5">
                            <span>J'accepte les <a href="{{ route('legal.cgv') }}" target="_blank" class="underline">conditions générales de vente</a>.</span>
                        </label>
                        @error('accept_conditions') <p class="text-sm text-red-600">{{ $message }}</p> @enderror

                        <div class="flex gap-3 pt-3">
                            <button type="button" wire:click="backToAddress" class="px-5 py-3 border rounded-lg hover:bg-gray-50">Retour</button>
                            <button type="button" wire:click="placeOrder" class="flex-1 px-5 py-3 bg-bordeaux text-white font-semibold rounded-lg hover:bg-bordeaux-dark transition">Valider la commande</button>
                        </div>
                    </section>
                @endif
            </div>

            <aside class="bg-gray-50 rounded-lg p-6 space-y-4 h-fit lg:sticky lg:top-24">
                <h3 class="font-semibold">Récapitulatif</h3>
                <ul class="space-y-2 text-sm">
                    @foreach($cart->items as $item)
                        <li class="flex justify-between">
                            <span class="text-gray-600">{{ $item->product->name ?? '—' }} × {{ $item->quantity }}</span>
                            <span>{{ number_format($item->line_total_ht, 2, ',', ' ') }} €</span>
                        </li>
                    @endforeach
                </ul>
                <div class="space-y-1 text-sm pt-3 border-t">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Sous-total HT</span>
                        <span>{{ number_format($cart->subtotal_ht, 2, ',', ' ') }} €</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Livraison ({{ $cart->shipping_parcels }} colis)</span>
                        <span>{{ number_format($cart->shipping_ht, 2, ',', ' ') }} €</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">TVA</span>
                        <span>{{ number_format($cart->total_tva, 2, ',', ' ') }} €</span>
                    </div>
                    <div class="flex justify-between text-base font-bold pt-2 border-t">
                        <span>Total TTC</span>
                        <span class="text-bordeaux">{{ number_format($cart->total_ttc, 2, ',', ' ') }} €</span>
                    </div>
                </div>
            </aside>
        </div>
    @else
        <p class="text-gray-500">Chargement…</p>
    @endif
</div>
