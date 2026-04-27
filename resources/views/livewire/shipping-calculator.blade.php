<div>
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b bg-gray-50">
            <h3 class="font-semibold flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0" />
                </svg>
                Estimation livraison
            </h3>
        </div>

        <div class="p-6">
            {{-- Input row --}}
            <div class="flex flex-col sm:flex-row gap-3">
                <div class="flex-1">
                    <label class="block text-xs font-medium text-gray-500 mb-1">Pays</label>
                    <select wire:model="country" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="FR">France</option>
                        <option value="BE">Belgique</option>
                        <option value="LU">Luxembourg</option>
                        <option value="DE">Allemagne</option>
                        <option value="NL">Pays-Bas</option>
                        <option value="IT">Italie</option>
                        <option value="ES">Espagne</option>
                        <option value="PT">Portugal</option>
                        <option value="AT">Autriche</option>
                        <option value="CH">Suisse</option>
                        <option value="GB">Royaume-Uni</option>
                    </select>
                </div>
                <div class="flex-1">
                    <label class="block text-xs font-medium text-gray-500 mb-1">Code postal</label>
                    <input type="text" wire:model="postalCode" placeholder="Ex: 75001" maxlength="10"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div class="flex items-end">
                    <button wire:click="calculate"
                            wire:loading.attr="disabled"
                            class="px-5 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition disabled:opacity-50">
                        <span wire:loading.remove wire:target="calculate">Calculer</span>
                        <span wire:loading wire:target="calculate">...</span>
                    </button>
                </div>
            </div>

            {{-- Results --}}
            @if($calculated)
                <div class="mt-4 pt-4 border-t">
                    {{-- Zone + weight info --}}
                    <div class="flex items-center gap-4 text-sm text-gray-500 mb-3">
                        <span>Zone : <strong class="text-gray-700">{{ $zone }}</strong></span>
                        <span>Poids estime : <strong class="text-gray-700">{{ number_format($estimatedWeight, 1, ',', '') }} kg</strong></span>
                    </div>

                    @if($rates->isEmpty())
                        <div class="text-center py-4 text-gray-400 text-sm">
                            Aucun transporteur disponible pour cette destination et ce poids.
                        </div>
                    @else
                        <div class="space-y-2">
                            @foreach($rates as $rate)
                                <button wire:click="selectRate({{ $rate['id'] }})"
                                        class="w-full flex items-center justify-between p-3 border rounded-lg transition text-left {{ $selectedRateId === $rate['id'] ? 'border-blue-500 bg-blue-50 ring-1 ring-blue-500' : 'border-gray-200 hover:border-gray-300' }}">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full flex items-center justify-center {{ $selectedRateId === $rate['id'] ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-400' }}">
                                            @if($selectedRateId === $rate['id'])
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                                </svg>
                                            @else
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0" />
                                                </svg>
                                            @endif
                                        </div>
                                        <div>
                                            <p class="font-medium text-sm text-gray-900">{{ $rate['carrier'] }}</p>
                                            <p class="text-xs text-gray-500">{{ $rate['delivery_label'] }}</p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <p class="font-bold text-sm {{ $selectedRateId === $rate['id'] ? 'text-blue-600' : 'text-gray-900' }}">
                                            {{ number_format($rate['price_ttc'], 2, ',', ' ') }} &euro; TTC
                                        </p>
                                        <p class="text-xs text-gray-400">{{ number_format($rate['price_ht'], 2, ',', ' ') }} &euro; HT</p>
                                    </div>
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>
