<div>
    @if($open)
        <div wire:click="close"
             class="fixed inset-0 bg-black/40 z-40 transition-opacity"></div>

        <aside class="fixed right-0 top-0 bottom-0 w-full sm:w-[420px] bg-white shadow-2xl z-50 flex flex-col">
            <header class="flex items-center justify-between px-5 py-4 border-b">
                <h2 class="font-semibold text-lg">Mon panier</h2>
                <button wire:click="close" type="button" class="text-gray-400 hover:text-gray-700" aria-label="Fermer">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </header>

            <div class="flex-1 overflow-y-auto">
                @if(! $cart || $cart->items->isEmpty())
                    <div class="flex flex-col items-center justify-center h-full px-6 text-center text-gray-500">
                        <svg class="w-12 h-12 mb-3 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5"/></svg>
                        <p class="text-sm">Votre panier est vide</p>
                        <a href="{{ route('catalogue.index') }}" class="mt-4 px-4 py-2 bg-bordeaux text-white text-sm rounded-lg hover:bg-bordeaux-dark transition">Voir le catalogue</a>
                    </div>
                @else
                    <ul class="divide-y">
                        @foreach($cart->items as $item)
                            <li class="flex gap-3 p-4">
                                <div class="w-16 h-16 bg-gray-100 rounded flex-shrink-0 overflow-hidden">
                                    @if($item->product?->main_image)
                                        <img src="{{ $item->product->imageUrl() }}" alt="" class="w-full h-full object-cover">
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 truncate">{{ $item->product->name ?? '—' }}</p>
                                    <p class="text-xs text-gray-500">
                                        {{ $item->color?->name }}{{ $item->size ? ' / '.$item->size->name : '' }}
                                    </p>
                                    <div class="flex items-center justify-between mt-2">
                                        <div class="flex items-center gap-1">
                                            <button wire:click="decrement({{ $item->id }})" class="w-7 h-7 border rounded text-sm hover:bg-gray-50">−</button>
                                            <span class="w-8 text-center text-sm">{{ $item->quantity }}</span>
                                            <button wire:click="increment({{ $item->id }})" class="w-7 h-7 border rounded text-sm hover:bg-gray-50">+</button>
                                        </div>
                                        <p class="text-sm font-semibold text-bordeaux">{{ number_format($item->line_total_ht, 2, ',', ' ') }} €</p>
                                    </div>
                                </div>
                                <button wire:click="remove({{ $item->id }})" class="text-gray-300 hover:text-red-500 self-start" aria-label="Retirer">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M1 7h22M9 7V4a2 2 0 012-2h2a2 2 0 012 2v3"/></svg>
                                </button>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            @if($cart && $cart->items->isNotEmpty())
                <footer class="border-t px-5 py-4 space-y-3">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Sous-total HT</span>
                        <span class="font-semibold">{{ number_format($cart->subtotal_ht, 2, ',', ' ') }} €</span>
                    </div>
                    <a href="{{ route('cart') }}" class="block w-full text-center px-4 py-2.5 border border-bordeaux text-bordeaux text-sm font-semibold rounded-lg hover:bg-bordeaux-50 transition">
                        Voir le panier
                    </a>
                    <a href="{{ route('payment.checkout') }}" class="block w-full text-center px-4 py-2.5 bg-bordeaux text-white text-sm font-semibold rounded-lg hover:bg-bordeaux-dark transition">
                        Commander
                    </a>
                </footer>
            @endif
        </aside>
    @endif
</div>
