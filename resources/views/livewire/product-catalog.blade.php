<div>
    {{-- Barre de filtres --}}
    <div x-data="{ showFilters: false }" class="mb-6">
        {{-- Tri + compteur + bouton filtres --}}
        <div class="flex items-center justify-between gap-3 mb-3">
            <p class="text-sm text-gray-500 flex-shrink-0">{{ $products->total() }} produit{{ $products->total() > 1 ? 's' : '' }}</p>
            <div class="flex items-center gap-2">
                <button @click="showFilters = !showFilters"
                        class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-lg border transition"
                        :class="showFilters ? 'bg-bordeaux-50 border-bordeaux-200 text-bordeaux' : 'bg-white border-gray-200 text-gray-600 hover:border-gray-300'">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                    Filtres
                    @if($this->hasActiveFilters())
                        <span class="w-1.5 h-1.5 bg-bordeaux rounded-full"></span>
                    @endif
                </button>
                <select wire:model.live="sort" class="px-2.5 py-1.5 border border-gray-200 rounded-lg text-xs focus:ring-1 focus:ring-bordeaux focus:border-bordeaux">
                    <option value="newest">Trier par</option>
                    <option value="price_asc">Prix croissant</option>
                    <option value="price_desc">Prix decroissant</option>
                    <option value="name">Nom A-Z</option>
                    <option value="grammage">Grammage</option>
                </select>
            </div>
        </div>

        {{-- Filtres depliables --}}
        <div x-show="showFilters" x-collapse class="overflow-hidden">
            <div class="py-3 px-3 bg-gray-50 rounded-lg border border-gray-100 space-y-3">

                {{-- Filtres : recherche + menus déroulants + cases à cocher --}}
                <div class="flex flex-wrap items-end gap-2">
                    {{-- Recherche --}}
                    <div class="flex-1 min-w-[10em]">
                        <label class="block text-[0.65em] text-gray-500 uppercase tracking-wider mb-1">Recherche</label>
                        <input wire:model.live.debounce.300ms="search" type="text" placeholder="Mot-cle..."
                               class="w-full px-2.5 py-1.5 border border-gray-200 rounded-md text-xs focus:ring-1 focus:ring-bordeaux focus:border-bordeaux">
                    </div>

                    {{-- Menus déroulants --}}
                    @foreach($specificFilters as $filterKey => $filterDef)
                        @if(str_starts_with($filterKey, '_')) @continue @endif
                        @php $fType = $filterDef['type'] ?? 'select'; @endphp
                        @if($fType === 'dynamic_select')
                            <div>
                                <label class="block text-[0.65em] text-gray-500 uppercase tracking-wider mb-1">{{ $filterDef['label'] ?? ucfirst($filterKey) }}</label>
                                <select wire:model.live="{{ $filterKey }}" class="px-2 py-1.5 border border-gray-200 rounded-md text-xs focus:ring-1 focus:ring-bordeaux">
                                    <option value="">Toutes</option>
                                    @if($filterKey === 'supplier')
                                        @foreach($suppliers as $s)
                                            <option value="{{ $s }}">{{ $s }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        @elseif($fType === 'select')
                            <div>
                                <label class="block text-[0.65em] text-gray-500 uppercase tracking-wider mb-1">{{ $filterDef['label'] ?? ucfirst($filterKey) }}</label>
                                <select wire:model.live="{{ $filterKey }}" class="px-2 py-1.5 border border-gray-200 rounded-md text-xs focus:ring-1 focus:ring-bordeaux">
                                    <option value="">Tous</option>
                                    @foreach(($filterDef['options'] ?? []) as $val => $label)
                                        <option value="{{ $val }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                    @endforeach

                    {{-- Cases à cocher (groupées à droite) --}}
                    @php $toggles = collect($specificFilters)->filter(fn($f, $k) => ($f['type'] ?? '') === 'toggle' && !str_starts_with($k, '_')); @endphp
                    @if($toggles->count())
                        <div class="flex items-center gap-3 ml-auto pl-3 border-l border-gray-200">
                            @foreach($toggles as $filterKey => $filterDef)
                                <label class="flex items-center gap-1.5 cursor-pointer">
                                    <input wire:model.live="{{ $filterKey }}" type="checkbox" value="1"
                                           class="w-3.5 h-3.5 rounded border-gray-300 text-bordeaux focus:ring-bordeaux">
                                    <span class="text-xs text-gray-600 whitespace-nowrap">{{ $filterDef['label'] ?? ucfirst($filterKey) }}</span>
                                </label>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Ligne 3 : Couleur + Reinitialiser --}}
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="text-[0.65em] text-gray-500 uppercase tracking-wider">Couleur</span>
                    @php
                        $commonColors = [
                            'Noir' => '#000000', 'Blanc' => '#FFFFFF', 'Marine' => '#1B1F3B',
                            'Gris' => '#9CA3AF', 'Rouge' => '#DC2626', 'Bleu Royal' => '#4169E1',
                            'Vert' => '#16A34A', 'Jaune' => '#EAB308', 'Orange' => '#F97316',
                            'Rose' => '#EC4899', 'Bordeaux' => '#8B1A1A', 'Bleu Ciel' => '#87CEEB',
                        ];
                    @endphp
                    @foreach($commonColors as $name => $hex)
                        <button wire:click="$set('color', '{{ $color === $name ? '' : $name }}')"
                                title="{{ $name }}"
                                class="w-5 h-5 rounded-full border-2 transition flex-shrink-0 {{ $color === $name ? 'border-bordeaux ring-2 ring-bordeaux-200 scale-125' : 'border-gray-200 hover:border-gray-400' }}"
                                style="background-color: {{ $hex }}; {{ $hex === '#FFFFFF' ? 'box-shadow: inset 0 0 0 1px #e5e7eb;' : '' }}">
                        </button>
                    @endforeach
                    @if($color)
                        <span class="text-[0.65em] text-bordeaux font-medium ml-1">{{ $color }}</span>
                    @endif

                    <div class="flex-1"></div>

                    @if($this->hasActiveFilters())
                        <button wire:click="clearFilters" class="text-xs text-red-500 hover:text-red-700 font-medium flex items-center gap-1">
                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                            Reinitialiser
                        </button>
                    @endif
                </div>

                {{-- Tags filtres actifs --}}
                @if($this->hasActiveFilters())
                    <div class="flex flex-wrap gap-1.5">
                        @if($search)
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-bordeaux-50 text-bordeaux text-[0.65em] rounded-full">
                                "{{ $search }}"
                                <button wire:click="$set('search', '')" class="hover:text-red-600">&times;</button>
                            </span>
                        @endif
                        @if($supplier)
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-bordeaux-50 text-bordeaux text-[0.65em] rounded-full">
                                {{ $supplier }}
                                <button wire:click="$set('supplier', '')" class="hover:text-red-600">&times;</button>
                            </span>
                        @endif
                        @if($cut)
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-bordeaux-50 text-bordeaux text-[0.65em] rounded-full">
                                {{ ucfirst($cut) }}
                                <button wire:click="$set('cut', '')" class="hover:text-red-600">&times;</button>
                            </span>
                        @endif
                        @if($grammage)
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-bordeaux-50 text-bordeaux text-[0.65em] rounded-full">
                                {{ match($grammage) { 'light' => '< 150g', 'medium' => '150-250g', 'heavy' => '250-350g', 'extra' => '> 350g', default => $grammage } }}
                                <button wire:click="$set('grammage', '')" class="hover:text-red-600">&times;</button>
                            </span>
                        @endif
                        @if($material)
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-bordeaux-50 text-bordeaux text-[0.65em] rounded-full">
                                {{ ucfirst($material) }}
                                <button wire:click="$set('material', '')" class="hover:text-red-600">&times;</button>
                            </span>
                        @endif
                        @if($color)
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-bordeaux-50 text-bordeaux text-[0.65em] rounded-full">
                                {{ $color }}
                                <button wire:click="$set('color', '')" class="hover:text-red-600">&times;</button>
                            </span>
                        @endif
                        @foreach($specificFilters as $fKey => $fDef)
                            @if($fKey !== 'supplier' && $this->$fKey)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-bordeaux-50 text-bordeaux text-[0.65em] rounded-full">
                                    {{ $fDef['options'][$this->$fKey] ?? ucfirst($this->$fKey) }}
                                    <button wire:click="$set('{{ $fKey }}', '')" class="hover:text-red-600">&times;</button>
                                </span>
                            @endif
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Grille produits --}}
    @if($products->count())
        <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 sm:gap-6">
            @foreach($products as $product)
                @include('catalogue._product-card', ['product' => $product])
            @endforeach
        </div>

        <div class="mt-8">
            {{ $products->links() }}
        </div>
    @else
        <div class="text-center py-16 text-gray-400">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
            <p class="text-lg font-medium">Aucun produit trouve</p>
            <p class="text-sm mt-1">Essayez de modifier vos filtres</p>
            @if($this->hasActiveFilters())
                <button wire:click="clearFilters" class="mt-4 px-4 py-2 text-sm text-bordeaux border border-bordeaux rounded-lg hover:bg-bordeaux-50 transition">
                    Reinitialiser les filtres
                </button>
            @endif
        </div>
    @endif
</div>
