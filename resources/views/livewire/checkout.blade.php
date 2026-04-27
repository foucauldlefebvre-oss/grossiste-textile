<div class="max-w-3xl mx-auto">

    {{-- Stepper --}}
    <div class="flex items-center justify-center gap-4 mb-8">
        <div class="flex items-center gap-2">
            <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold {{ $step >= 1 ? 'bg-bordeaux text-white' : 'bg-gray-200 text-gray-500' }}">1</div>
            <span class="text-sm font-medium {{ $step >= 1 ? 'text-gray-900' : 'text-gray-400' }}">Adresse</span>
        </div>
        <div class="w-12 h-0.5 {{ $step >= 2 ? 'bg-bordeaux' : 'bg-gray-200' }}"></div>
        <div class="flex items-center gap-2">
            <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold {{ $step >= 2 ? 'bg-bordeaux text-white' : 'bg-gray-200 text-gray-500' }}">2</div>
            <span class="text-sm font-medium {{ $step >= 2 ? 'text-gray-900' : 'text-gray-400' }}">Reglement</span>
        </div>
    </div>

    @if(session('checkout-error'))
        <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm">{{ session('checkout-error') }}</div>
    @endif

    {{-- ═══ ETAPE 1 : ADRESSE ═══ --}}
    @if($step === 1)
        <div class="bg-white rounded-xl shadow-sm p-6 sm:p-8">
            <h2 class="text-xl font-bold mb-6">Adresse de livraison</h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Prenom <span class="text-red-500">*</span></label>
                    <input type="text" wire:model="shipping_first_name"
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-bordeaux focus:border-bordeaux">
                    @error('shipping_first_name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nom <span class="text-red-500">*</span></label>
                    <input type="text" wire:model="shipping_last_name"
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-bordeaux focus:border-bordeaux">
                    @error('shipping_last_name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div class="sm:col-span-2">
                    <label class="flex items-center gap-2 mb-2">
                        <input type="checkbox" wire:model.live="is_company"
                               class="rounded border-gray-300 text-bordeaux focus:ring-bordeaux">
                        <span class="text-sm font-medium text-gray-700">Je commande pour une societe</span>
                    </label>
                    <input type="text" wire:model="shipping_company" placeholder="Nom de la societe"
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-bordeaux focus:border-bordeaux">
                </div>
                @if($is_company)
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">SIRET <span class="text-red-500">*</span></label>
                        <input type="text" wire:model="shipping_siret" placeholder="XXX XXX XXX XXXXX"
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-bordeaux focus:border-bordeaux">
                        @error('shipping_siret') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    @php
                        $euCountries = ['DE','AT','BE','BG','HR','CY','CZ','DK','EE','ES','FI','GR','HU','IE','IT','LV','LT','LU','MT','NL','PL','PT','RO','SK','SI','SE'];
                        $showIntraEu = in_array($shipping_country, $euCountries);
                    @endphp
                    @if($showIntraEu)
                        <div>
                            <label class="flex items-center gap-2 mb-1">
                                <input type="checkbox" wire:model.live="is_intra_eu"
                                       class="rounded border-gray-300 text-bordeaux focus:ring-bordeaux">
                                <span class="text-sm font-medium text-gray-700">TVA intracommunautaire</span>
                            </label>
                            @if($is_intra_eu)
                                <input type="text" wire:model.blur="shipping_vat_number" placeholder="Ex: DE123456789"
                                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-bordeaux focus:border-bordeaux mt-1">
                                @error('shipping_vat_number') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                            @endif
                        </div>
                    @endif
                @endif
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Adresse <span class="text-red-500">*</span></label>
                    <input type="text" wire:model="shipping_address_line_1"
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-bordeaux focus:border-bordeaux">
                    @error('shipping_address_line_1') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Complement</label>
                    <input type="text" wire:model="shipping_address_line_2"
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-bordeaux focus:border-bordeaux">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Code postal <span class="text-red-500">*</span></label>
                    <input type="text" wire:model="shipping_postal_code"
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-bordeaux focus:border-bordeaux">
                    @error('shipping_postal_code') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Ville <span class="text-red-500">*</span></label>
                    <input type="text" wire:model="shipping_city"
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-bordeaux focus:border-bordeaux">
                    @error('shipping_city') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Pays <span class="text-red-500">*</span></label>
                    <select wire:model.live="shipping_country"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-bordeaux focus:border-bordeaux">
                        <optgroup label="France">
                            <option value="FR">France</option>
                        </optgroup>
                        <optgroup label="Union europeenne">
                            <option value="DE">Allemagne</option>
                            <option value="AT">Autriche</option>
                            <option value="BE">Belgique</option>
                            <option value="BG">Bulgarie</option>
                            <option value="HR">Croatie</option>
                            <option value="CY">Chypre</option>
                            <option value="CZ">Republique tcheque</option>
                            <option value="DK">Danemark</option>
                            <option value="ES">Espagne</option>
                            <option value="EE">Estonie</option>
                            <option value="FI">Finlande</option>
                            <option value="GR">Grece</option>
                            <option value="HU">Hongrie</option>
                            <option value="IE">Irlande</option>
                            <option value="IT">Italie</option>
                            <option value="LV">Lettonie</option>
                            <option value="LT">Lituanie</option>
                            <option value="LU">Luxembourg</option>
                            <option value="MT">Malte</option>
                            <option value="NL">Pays-Bas</option>
                            <option value="PL">Pologne</option>
                            <option value="PT">Portugal</option>
                            <option value="RO">Roumanie</option>
                            <option value="SK">Slovaquie</option>
                            <option value="SI">Slovenie</option>
                            <option value="SE">Suede</option>
                        </optgroup>
                        <optgroup label="Hors UE">
                            <option value="CH">Suisse</option>
                            <option value="LI">Liechtenstein</option>
                        </optgroup>
                        <optgroup label="DOM-TOM">
                            <option value="GP">Guadeloupe</option>
                            <option value="MQ">Martinique</option>
                            <option value="GF">Guyane</option>
                            <option value="RE">Reunion</option>
                            <option value="YT">Mayotte</option>
                            <option value="NC">Nouvelle-Caledonie</option>
                            <option value="PF">Polynesie francaise</option>
                        </optgroup>
                    </select>
                    @php
                        $zone = \App\Services\QuoteService::getShippingZone($shipping_country);
                        $zoneInfo = \App\Services\QuoteService::SHIPPING_ZONES[$zone] ?? null;
                    @endphp
                    @if($zone !== 'france' && $zoneInfo)
                        <p class="text-xs text-amber-600 mt-1">
                            Port : {{ number_format($zoneInfo['rate'], 2, ',', ' ') }} &euro; HT/colis ({{ $zoneInfo['label'] }})
                            @if(! $zoneInfo['tva']) — TVA non applicable @endif
                        </p>
                    @endif
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Telephone <span class="text-red-500">*</span></label>
                    <input type="tel" wire:model="shipping_phone"
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-bordeaux focus:border-bordeaux">
                    @error('shipping_phone') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- Facturation differente --}}
            <div class="mt-6 border-t pt-5">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" wire:model.live="different_billing"
                           class="rounded border-gray-300 text-bordeaux focus:ring-bordeaux">
                    <span class="text-sm font-medium text-gray-700">Adresse de facturation differente</span>
                </label>
            </div>

            @if($different_billing)
                <div class="mt-4 p-4 bg-gray-50 rounded-lg">
                    <h3 class="text-sm font-semibold text-gray-700 mb-3">Adresse de facturation</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Prenom <span class="text-red-500">*</span></label>
                            <input type="text" wire:model="billing_first_name"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-bordeaux focus:border-bordeaux">
                        </div>
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Nom <span class="text-red-500">*</span></label>
                            <input type="text" wire:model="billing_last_name"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-bordeaux focus:border-bordeaux">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-sm text-gray-600 mb-1">Societe</label>
                            <input type="text" wire:model="billing_company"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-bordeaux focus:border-bordeaux">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-sm text-gray-600 mb-1">Adresse <span class="text-red-500">*</span></label>
                            <input type="text" wire:model="billing_address_line_1"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-bordeaux focus:border-bordeaux">
                        </div>
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Code postal <span class="text-red-500">*</span></label>
                            <input type="text" wire:model="billing_postal_code"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-bordeaux focus:border-bordeaux">
                        </div>
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Ville <span class="text-red-500">*</span></label>
                            <input type="text" wire:model="billing_city"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-bordeaux focus:border-bordeaux">
                        </div>
                    </div>
                </div>
            @endif

            <div class="mt-6 flex justify-between">
                <a href="{{ route('mon-devis') }}" class="px-6 py-2.5 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50 transition text-sm">
                    Retour au devis
                </a>
                <button wire:click="goToPayment"
                        class="px-6 py-2.5 bg-bordeaux text-white font-semibold rounded-lg hover:bg-bordeaux-dark transition text-sm">
                    Continuer vers le reglement
                </button>
            </div>
        </div>

    {{-- ═══ ETAPE 2 : REGLEMENT ═══ --}}
    @elseif($step === 2)
        <div class="bg-white rounded-xl shadow-sm p-6 sm:p-8">
            <h2 class="text-xl font-bold mb-6">Reglement et conditions</h2>

            {{-- Resume commande --}}
            <div class="bg-gray-50 rounded-lg p-4 mb-6">
                <div class="flex justify-between text-sm mb-1">
                    <span class="text-gray-500">Devis</span>
                    <span class="font-medium">{{ $quote->reference }}</span>
                </div>
                <div class="flex justify-between text-sm mb-1">
                    <span class="text-gray-500">Livraison</span>
                    <span>{{ $shipping_first_name }} {{ $shipping_last_name }}, {{ $shipping_postal_code }} {{ $shipping_city }}</span>
                </div>
                <div class="flex justify-between font-bold text-lg border-t pt-2 mt-2">
                    <span>Total TTC</span>
                    <span class="text-bordeaux">{{ number_format($quote->total_ttc, 2, ',', ' ') }} &euro;</span>
                </div>
            </div>

            {{-- Option acompte (si marquage) --}}
            @if($hasMarking)
                <div class="mb-6">
                    <h3 class="text-sm font-semibold text-gray-700 mb-3">Modalite de paiement</h3>
                    <div class="space-y-2">
                        <label class="flex items-start gap-3 p-3 border rounded-lg cursor-pointer transition {{ $payment_option === 'full' ? 'border-bordeaux bg-bordeaux-50' : 'border-gray-200 hover:border-gray-300' }}">
                            <input type="radio" wire:model.live="payment_option" value="full"
                                   class="mt-0.5 text-bordeaux focus:ring-bordeaux">
                            <div>
                                <p class="text-sm font-medium text-gray-900">Reglement integral</p>
                                <p class="text-xs text-gray-500">{{ number_format($quote->total_ttc, 2, ',', ' ') }} &euro; TTC</p>
                            </div>
                        </label>
                        <label class="flex items-start gap-3 p-3 border rounded-lg cursor-pointer transition {{ $payment_option === 'deposit' ? 'border-bordeaux bg-bordeaux-50' : 'border-gray-200 hover:border-gray-300' }}">
                            <input type="radio" wire:model.live="payment_option" value="deposit"
                                   class="mt-0.5 text-bordeaux focus:ring-bordeaux">
                            <div>
                                <p class="text-sm font-medium text-gray-900">Acompte 50%</p>
                                <p class="text-xs text-gray-500">
                                    {{ number_format($quote->total_ttc / 2, 2, ',', ' ') }} &euro; maintenant,
                                    solde de {{ number_format($quote->total_ttc / 2, 2, ',', ' ') }} &euro; avant depart atelier
                                </p>
                            </div>
                        </label>
                    </div>
                </div>
            @endif

            {{-- Mode de reglement --}}
            <div class="mb-6">
                <h3 class="text-sm font-semibold text-gray-700 mb-3">Mode de reglement</h3>
                <div class="space-y-2">
                    <label class="flex items-start gap-3 p-3 border rounded-lg cursor-pointer transition {{ $payment_method === 'cb' ? 'border-bordeaux bg-bordeaux-50' : 'border-gray-200 hover:border-gray-300' }}">
                        <input type="radio" wire:model.live="payment_method" value="cb"
                               class="mt-0.5 text-bordeaux focus:ring-bordeaux">
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-900">Carte bancaire</p>
                            <p class="text-xs text-gray-500">Paiement securise par carte bancaire</p>
                        </div>
                        <div class="flex items-center gap-1">
                            <div class="w-8 h-5 bg-blue-700 rounded text-white text-[7px] font-bold flex items-center justify-center">VISA</div>
                            <div class="w-8 h-5 bg-red-500 rounded text-white text-[7px] font-bold flex items-center justify-center">MC</div>
                        </div>
                    </label>
                    <label class="flex items-start gap-3 p-3 border rounded-lg cursor-pointer transition {{ $payment_method === 'virement' ? 'border-bordeaux bg-bordeaux-50' : 'border-gray-200 hover:border-gray-300' }}">
                        <input type="radio" wire:model.live="payment_method" value="virement"
                               class="mt-0.5 text-bordeaux focus:ring-bordeaux">
                        <div>
                            <p class="text-sm font-medium text-gray-900">Virement bancaire</p>
                            <p class="text-xs text-gray-500">Les coordonnees bancaires vous seront communiquees apres validation</p>
                        </div>
                    </label>
                </div>
            </div>

            {{-- Montant a payer --}}
            <div class="bg-bordeaux-50 border border-bordeaux-200 rounded-lg p-4 mb-6">
                <div class="flex justify-between items-center">
                    <span class="text-sm text-bordeaux font-medium">Montant a regler</span>
                    <span class="text-xl font-bold text-bordeaux">
                        {{ number_format($payment_option === 'deposit' ? $quote->total_ttc / 2 : $quote->total_ttc, 2, ',', ' ') }} &euro; TTC
                    </span>
                </div>
                @if($payment_option === 'deposit')
                    <p class="text-xs text-bordeaux/70 mt-1">Acompte de 50% — le solde sera demande avant depart atelier</p>
                @endif
            </div>

            {{-- Conditions --}}
            <div class="mb-6">
                <label class="flex items-start gap-2 cursor-pointer">
                    <input type="checkbox" wire:model="accept_conditions"
                           class="mt-0.5 rounded border-gray-300 text-bordeaux focus:ring-bordeaux">
                    <span class="text-sm text-gray-600">
                        J'accepte les <a href="{{ route('legal.cgv') }}" target="_blank" class="text-bordeaux hover:underline">conditions generales de vente</a>
                        et la <a href="{{ route('legal.privacy') }}" target="_blank" class="text-bordeaux hover:underline">politique de confidentialite</a>.
                    </span>
                </label>
                @error('accept_conditions') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Boutons --}}
            <div class="flex justify-between">
                <button wire:click="backToAddress"
                        class="px-6 py-2.5 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50 transition text-sm">
                    Retour
                </button>
                <button wire:click="placeOrder"
                        wire:loading.attr="disabled"
                        class="px-8 py-2.5 bg-green-600 text-white font-semibold rounded-lg hover:bg-green-700 transition text-sm disabled:opacity-50">
                    <span wire:loading.remove wire:target="placeOrder">
                        @if($payment_method === 'cb')
                            Payer {{ number_format($payment_option === 'deposit' ? $quote->total_ttc / 2 : $quote->total_ttc, 2, ',', ' ') }} &euro;
                        @else
                            Confirmer la commande
                        @endif
                    </span>
                    <span wire:loading wire:target="placeOrder">Traitement...</span>
                </button>
            </div>
        </div>
    @endif
</div>
