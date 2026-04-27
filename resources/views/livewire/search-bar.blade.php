<div class="relative" x-data="{ focused: false, mobileOpen: false }">
    {{-- Desktop search --}}
    <div class="relative hidden sm:block">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>
        <input wire:model.live.debounce.300ms="query"
               @focus="focused = true"
               @click.away="focused = false; $wire.close()"
               type="text"
               placeholder="Rechercher un produit, une matiere..."
               class="w-48 lg:w-64 pl-10 pr-4 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-bordeaux focus:border-bordeaux focus:w-80 transition-all">
    </div>

    {{-- Mobile search toggle --}}
    <button @click="mobileOpen = !mobileOpen" class="sm:hidden p-2 text-gray-500 hover:text-bordeaux transition">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>
    </button>

    {{-- Mobile search overlay --}}
    <div x-show="mobileOpen" x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 -translate-y-2"
         class="sm:hidden fixed inset-x-0 top-16 z-50 bg-white border-b shadow-lg p-3">
        <div class="relative">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
            <input wire:model.live.debounce.300ms="query"
                   @click.away="if(mobileOpen && !$event.target.closest('[x-data]')) { mobileOpen = false; $wire.close(); }"
                   x-ref="mobileInput"
                   x-init="$watch('mobileOpen', v => { if(v) $nextTick(() => $refs.mobileInput.focus()) })"
                   type="text"
                   placeholder="Rechercher un produit, une matiere..."
                   class="w-full pl-10 pr-10 py-2.5 border rounded-lg text-sm focus:ring-2 focus:ring-bordeaux focus:border-bordeaux">
            <button @click="mobileOpen = false; $wire.set('query', ''); $wire.close()" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
    </div>

    {{-- Dropdown results --}}
    @if($open && $this->results->count())
        <div class="absolute top-full mt-2 right-0 w-[calc(100vw-2rem)] sm:w-[28rem] bg-white rounded-xl shadow-xl border z-50 max-h-[28rem] overflow-y-auto">
            <div class="px-4 py-2 border-b bg-gray-50">
                <span class="text-xs text-gray-400">{{ $this->results->count() }} resultat{{ $this->results->count() > 1 ? 's' : '' }} pour "{{ $query }}"</span>
            </div>
            @foreach($this->results as $product)
                @php
                    // Trouver un extrait du match dans la description
                    $excerpt = '';
                    $searchWords = array_filter(explode(' ', strtolower($query)), fn($w) => strlen($w) >= 2);
                    $desc = strip_tags($product->description ?? $product->short_description ?? '');
                    foreach ($searchWords as $word) {
                        $pos = stripos($desc, $word);
                        if ($pos !== false) {
                            $start = max(0, $pos - 30);
                            $excerpt = ($start > 0 ? '...' : '') . Str::limit(substr($desc, $start), 80);
                            break;
                        }
                    }
                @endphp
                <a href="{{ $product->url }}"
                   class="flex items-center gap-3 px-4 py-3 hover:bg-gray-50 transition border-b last:border-b-0"
                   wire:click="close">
                    <div class="w-12 h-12 bg-gray-100 rounded-lg flex-shrink-0 overflow-hidden">
                        @if($product->main_image)
                            <img src="{{ $product->imageUrl() }}" alt="" class="w-full h-full object-cover" loading="lazy">
                        @endif
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 truncate">{{ $product->name }}</p>
                        <p class="text-xs text-gray-400">{{ $product->reference }} &middot; {{ $product->supplier }}</p>
                        @if($excerpt)
                            <p class="text-[11px] text-gray-400 mt-0.5 line-clamp-1">{{ $excerpt }}</p>
                        @endif
                    </div>
                    <span class="text-sm font-bold text-bordeaux flex-shrink-0">{{ $product->display_price > 0 ? number_format($product->display_price, 2, ',', ' ') . ' €' : 'Sur devis' }}</span>
                </a>
            @endforeach

            <a href="{{ route('catalogue.index', ['search' => $query]) }}" class="block text-center py-3 text-sm text-bordeaux hover:bg-gray-50 font-medium">
                Voir tous les resultats &rarr;
            </a>
        </div>
    @elseif($open && $this->results->isEmpty())
        <div class="absolute top-full mt-2 right-0 w-[calc(100vw-2rem)] sm:w-96 bg-white rounded-xl shadow-xl border z-50 p-6 text-center text-gray-400">
            <svg class="w-8 h-8 mx-auto mb-2 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <p class="text-sm">Aucun resultat pour "{{ $query }}"</p>
            <p class="text-xs mt-1">Essayez avec d'autres mots-cles</p>
        </div>
    @endif
</div>
