<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <h1 class="text-2xl font-bold mb-6">Mon panier</h1>

    @if(! $cart || $cart->items->isEmpty())
        <div class="bg-white rounded-lg border p-12 text-center">
            <svg class="w-12 h-12 mx-auto mb-4 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5"/></svg>
            <p class="text-gray-500 mb-4">Votre panier est vide</p>
            <a href="{{ route('catalogue.index') }}" class="inline-block px-5 py-2.5 bg-bordeaux text-white text-sm rounded-lg hover:bg-bordeaux-dark transition">Voir le catalogue</a>
        </div>
    @else
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2 bg-white rounded-lg border divide-y">
                @foreach($cart->items as $item)
                    <div class="flex gap-4 p-5">
                        <div class="w-20 h-20 bg-gray-100 rounded flex-shrink-0 overflow-hidden">
                            @if($item->product?->main_image)
                                <img src="{{ $item->product->imageUrl() }}" alt="" class="w-full h-full object-cover">
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-medium">{{ $item->product->name ?? '—' }}</p>
                            <p class="text-sm text-gray-500 mb-3">
                                {{ $item->color?->name }}{{ $item->size ? ' / '.$item->size->name : '' }}
                            </p>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <button wire:click="decrement({{ $item->id }})" class="w-8 h-8 border rounded hover:bg-gray-50">−</button>
                                    <span class="w-10 text-center">{{ $item->quantity }}</span>
                                    <button wire:click="increment({{ $item->id }})" class="w-8 h-8 border rounded hover:bg-gray-50">+</button>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm text-gray-500">{{ number_format($item->unit_price_ht, 2, ',', ' ') }} € / unité</p>
                                    <p class="font-semibold text-bordeaux">{{ number_format($item->line_total_ht, 2, ',', ' ') }} €</p>
                                </div>
                            </div>
                        </div>
                        <button wire:click="remove({{ $item->id }})" class="text-gray-300 hover:text-red-500 self-start" aria-label="Retirer">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M1 7h22M9 7V4a2 2 0 012-2h2a2 2 0 012 2v3"/></svg>
                        </button>
                    </div>
                @endforeach
            </div>

            <aside class="bg-gray-50 rounded-lg p-6 space-y-3 h-fit lg:sticky lg:top-24">
                <h3 class="font-semibold mb-2">Récapitulatif</h3>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600">Sous-total HT</span>
                    <span class="font-medium">{{ number_format($cart->subtotal_ht, 2, ',', ' ') }} €</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600">Livraison</span>
                    <span class="font-medium">{{ number_format($cart->shipping_ht, 2, ',', ' ') }} €</span>
                </div>
                <div class="flex justify-between text-base font-bold pt-3 border-t">
                    <span>Total TTC</span>
                    <span class="text-bordeaux">{{ number_format($cart->total_ttc, 2, ',', ' ') }} €</span>
                </div>
                <a href="{{ route('payment.checkout') }}" class="block w-full text-center px-4 py-3 bg-bordeaux text-white font-semibold rounded-lg hover:bg-bordeaux-dark transition">Commander</a>
                <a href="{{ route('catalogue.index') }}" class="block w-full text-center text-sm text-gray-600 hover:text-bordeaux">← Continuer mes achats</a>
            </aside>
        </div>
    @endif
</div>
