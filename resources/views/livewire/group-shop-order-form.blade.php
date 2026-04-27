<div>
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b bg-gray-50">
            <h2 class="text-lg font-bold">Passer commande</h2>
        </div>

        {{-- Error --}}
        @if(session('order-error'))
            <div class="mx-6 mt-4 p-3 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm">
                {{ session('order-error') }}
            </div>
        @endif

        <form wire:submit="submit" class="p-6 space-y-8">
            {{-- Buyer info --}}
            <div>
                <h3 class="font-semibold text-gray-900 mb-4">Vos informations</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Prenom *</label>
                        <input type="text" wire:model="firstName" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('firstName') border-red-300 @enderror">
                        @error('firstName') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nom *</label>
                        <input type="text" wire:model="lastName" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('lastName') border-red-300 @enderror">
                        @error('lastName') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
                        <input type="email" wire:model="email" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('email') border-red-300 @enderror">
                        @error('email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Service / Departement</label>
                        <input type="text" wire:model="department"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>
            </div>

            {{-- Product lines --}}
            <div>
                <h3 class="font-semibold text-gray-900 mb-4">Selection des produits</h3>
                <div class="space-y-4">
                    @foreach($this->groupShop->products as $gsProduct)
                        @php
                            $sizes = $gsProduct->allowed_sizes
                                ? $gsProduct->product->sizes->whereIn('id', $gsProduct->allowed_sizes)
                                : $gsProduct->product->sizes;
                            $gspId = $gsProduct->id;
                        @endphp
                        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4 p-4 border rounded-lg {{ ($lines[$gspId]['quantity'] ?? 0) > 0 ? 'border-blue-200 bg-blue-50/30' : 'border-gray-200' }}">
                            {{-- Product image --}}
                            <div class="w-14 h-14 bg-gray-100 rounded-lg flex-shrink-0 overflow-hidden">
                                @if($gsProduct->product->main_image)
                                    <img src="{{ $gsProduct->product->imageUrl() }}" alt="" class="w-full h-full object-cover">
                                @endif
                            </div>

                            {{-- Product name + price --}}
                            <div class="flex-1 min-w-0">
                                <p class="font-medium text-gray-900">{{ $gsProduct->product->name }}</p>
                                @if($gsProduct->fixed_price)
                                    <p class="text-sm text-blue-600 font-semibold">{{ number_format($gsProduct->fixed_price, 2, ',', ' ') }} &euro; HT</p>
                                @else
                                    <p class="text-sm text-gray-500">{{ $gsProduct->product->display_price > 0 ? number_format($gsProduct->product->display_price, 2, ',', ' ') . ' € HT' : 'Sur devis' }}</p>
                                @endif
                            </div>

                            {{-- Size select --}}
                            <div class="w-full sm:w-32">
                                <select wire:model.live="lines.{{ $gspId }}.size"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">Taille</option>
                                    @foreach($sizes->unique('size') as $size)
                                        <option value="{{ $size->size }}">{{ $size->size }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Quantity --}}
                            <div class="w-full sm:w-24">
                                <input type="number" wire:model.live="lines.{{ $gspId }}.quantity"
                                       min="0" max="999" placeholder="Qte"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm text-center focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>

                            {{-- Line total --}}
                            <div class="w-20 text-right flex-shrink-0">
                                @if(($lines[$gspId]['quantity'] ?? 0) > 0 && ($lines[$gspId]['fixed_price'] ?? null))
                                    <p class="font-semibold text-sm">{{ number_format($lines[$gspId]['fixed_price'] * $lines[$gspId]['quantity'], 2, ',', ' ') }} &euro;</p>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Total + Submit --}}
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4 pt-4 border-t">
                <div>
                    @if($this->orderTotal > 0)
                        <p class="text-lg font-bold">Total : <span class="text-blue-600">{{ number_format($this->orderTotal, 2, ',', ' ') }} &euro; HT</span></p>
                    @endif
                </div>
                <button type="submit"
                        wire:loading.attr="disabled"
                        class="px-8 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition disabled:opacity-50">
                    <span wire:loading.remove wire:target="submit">Valider ma commande</span>
                    <span wire:loading wire:target="submit">Envoi en cours...</span>
                </button>
            </div>
        </form>
    </div>
</div>
