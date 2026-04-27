<div>
    {{-- Overlay (only when open) --}}
    @if($open)
        <div wire:click="close" class="fixed inset-0 bg-black/30 z-40 transition-opacity" x-data x-transition.opacity></div>
    @endif

    {{-- Green toggle tab (always visible, sticks to drawer edge) --}}
    @if(!$open)
        <button
            id="floating-quote-toggle"
            wire:click="toggle"
            class="fixed top-1/2 -translate-y-1/2 right-0 z-50 flex items-center justify-center w-8 h-16 rounded-l-lg shadow-lg bg-green-600 hover:bg-green-700 text-white transition"
            title="Mon devis"
        >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
            </svg>
            @if($quote && $quote->items->count() > 0)
                <span class="absolute -top-2 -left-2 w-5 h-5 bg-red-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center shadow">
                    {{ $quote->items->count() }}
                </span>
            @endif
        </button>
    @endif

    {{-- Drawer --}}
    <div class="fixed top-0 right-0 h-full w-full sm:max-w-md bg-white shadow-2xl z-50 flex flex-col transition-transform duration-300 ease-in-out {{ $open ? 'translate-x-0' : 'translate-x-full' }}">
        {{-- Watermark logo --}}
        <div class="absolute inset-0 flex items-center justify-center pointer-events-none overflow-hidden" style="z-index: 0;">
            <img src="{{ asset('images/logo-icon.png') }}" alt="" class="w-64 h-auto select-none" style="opacity: 0.08; filter: grayscale(100%) contrast(120%); mix-blend-mode: multiply;">
        </div>
        <div class="relative flex flex-col h-full" style="z-index: 1;">

        {{-- Close arrow tab --}}
        @if($open)
            <button
                wire:click="close"
                class="absolute top-1/2 -translate-y-1/2 -left-8 z-10 flex items-center justify-center w-8 h-16 rounded-l-lg shadow-lg bg-green-600 hover:bg-green-700 text-white transition"
                title="Fermer le devis"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                </svg>
            </button>
        @endif

        {{-- Header --}}
        <div class="flex items-center justify-between px-6 py-4 border-b bg-gray-50">
            <div class="flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-bordeaux" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <h2 class="font-semibold text-gray-900">Mon devis</h2>
                @if($quote && $quote->items->count() > 0)
                    <span class="ml-1 px-2 py-0.5 text-xs font-medium bg-bordeaux-100 text-bordeaux rounded-full">
                        {{ $quote->items->count() }} article{{ $quote->items->count() > 1 ? 's' : '' }}
                    </span>
                @endif
            </div>
            <div class="flex items-center gap-1">
                @if($quote && $quote->items->count() > 0)
                    <button
                        wire:click="clearAll"
                        wire:confirm="Vider tout le panier ?"
                        class="p-1.5 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-lg transition"
                        title="Vider le panier"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </button>
                @endif
                <button wire:click="close" class="p-1.5 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>

        {{-- Error --}}
        @if(session('floating-quote-error'))
            <div class="mx-4 mt-3 p-2.5 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm">
                {{ session('floating-quote-error') }}
            </div>
        @endif

        {{-- Content --}}
        <div class="flex-1 overflow-y-auto">
            @if(!$quote || $quote->items->isEmpty())
                {{-- Empty state --}}
                <div class="flex flex-col items-center justify-center h-full text-gray-400 px-6">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <p class="text-lg font-medium text-gray-500 mb-1">Votre devis est vide</p>
                    <p class="text-sm text-center">Parcourez le catalogue pour ajouter des produits a votre devis.</p>
                </div>
            @else
                @php
                    $allMarkingGroups = $this->markingGroups;
                    $activeGroupNum = $this->activeMarkingGroup;
                    $zoneLabels = [
                        'poitrine_gauche' => 'Poitrine G',
                        'poitrine_droite' => 'Poitrine D',
                        'dos' => 'Dos',
                        'dos_haut' => 'Dos (haut)',
                        'manche_gauche' => 'Manche G',
                        'manche_droite' => 'Manche D',
                        'col' => 'Col',
                        'face' => 'Face',
                        'cote' => 'Cote',
                    ];
                    $allGroups = $this->groupedItems;
                @endphp

                {{-- ===== FROZEN MARKING GROUPS (collapsed summary) ===== --}}
                @foreach($allMarkingGroups as $mgNum => $mgData)
                    @if($mgData['frozen'])
                        <div class="border-b-2 border-yellow-200" wire:key="frozen-mg-{{ $mgNum }}">
                            <div
                                class="flex items-center gap-3 px-4 py-3 bg-yellow-50 cursor-pointer select-none hover:bg-yellow-100 transition"
                                wire:click="toggleFrozenGroupExpanded({{ $mgNum }})"
                            >
                                <div class="w-6 h-6 bg-yellow-100 rounded flex items-center justify-center flex-shrink-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-yellow-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </svg>
                                </div>

                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-yellow-800">
                                        Bloc {{ $mgNum + 1 }}
                                        <span class="text-orange-600 font-normal">
                                            &mdash; {{ $mgData['totalQuantity'] }} pc{{ $mgData['totalQuantity'] > 1 ? 's' : '' }},
                                            {{ count($mgData['textileGroups']) }} textile{{ count($mgData['textileGroups']) > 1 ? 's' : '' }}
                                        </span>
                                    </p>
                                    @php
                                        $frozenLogos = $mgData['logos'] ?? [];
                                        $logoCount = count($frozenLogos);
                                        // Collect technique names from logos
                                        $logoTechNames = collect($frozenLogos)
                                            ->pluck('technique_id')
                                            ->filter()
                                            ->map(fn($id) => \App\Models\MarkingTechnique::find($id)?->name)
                                            ->filter()
                                            ->unique()
                                            ->implode(', ');
                                        // Fallback to item technique if logos don't have technique_id
                                        if (!$logoTechNames) {
                                            $logoTechNames = collect($mgData['textileGroups'])->pluck('technique')->filter()->pluck('name')->unique()->implode(', ');
                                        }
                                    @endphp
                                    <p class="text-xs text-orange-500 mt-0.5">
                                        @if($logoTechNames){{ $logoTechNames }}@endif
                                        @if($logoCount) &middot; {{ $logoCount }} logo{{ $logoCount > 1 ? 's' : '' }}@endif
                                    </p>
                                </div>

                                <div class="flex items-center gap-2 flex-shrink-0">
                                    <span class="text-sm font-semibold text-yellow-800 tabular-nums">{{ number_format($mgData['groupTotal'], 2, ',', ' ') }}&euro;</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-yellow-600 transition-transform {{ ($expandedFrozenGroups[$mgNum] ?? false) ? 'rotate-90' : '' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                                    </svg>
                                </div>
                            </div>

                            {{-- Expanded frozen group details --}}
                            @if($expandedFrozenGroups[$mgNum] ?? false)
                                <div class="bg-yellow-50/50">
                                    @foreach($mgData['textileGroups'] as $groupKey => $group)
                                        <div class="px-4 py-2.5 ml-2 border-b border-yellow-100 last:border-b-0" wire:key="frozen-group-{{ $groupKey }}">
                                            {{-- En-tete produit --}}
                                            <div class="flex items-center gap-2 mb-2">
                                                <div class="w-7 h-7 bg-gray-100 rounded overflow-hidden flex-shrink-0">
                                                    @if($group['product']->main_image)
                                                        <img src="{{ $group['product']->imageUrl() }}" alt="" class="w-full h-full object-cover">
                                                    @endif
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <p class="text-xs font-semibold text-gray-800 line-clamp-1">{{ $group['product']->name }}</p>
                                                    @if($group['color'])
                                                        <span class="flex items-center gap-1 mt-0.5">
                                                            <span class="w-2 h-2 rounded-full inline-block border border-gray-200" style="background-color: {{ $group['color']->hex_code }}"></span>
                                                            <span class="text-[10px] text-gray-500">{{ $group['color']->name }}</span>
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>

                                            {{-- Lignes detaillees par taille --}}
                                            <div class="ml-9 space-y-1">
                                                @foreach($group['items'] as $item)
                                                    {{-- Ligne textile --}}
                                                    <div class="flex items-center justify-between text-[10px]">
                                                        <span class="text-gray-600">
                                                            Textile {{ $item->size?->size ?? '' }}
                                                            <span class="text-gray-400">&times; {{ $item->quantity }}</span>
                                                        </span>
                                                        <span class="text-gray-600 tabular-nums">
                                                            {{ number_format($item->unit_price_ht * $item->quantity, 2, ',', ' ') }}&euro;
                                                        </span>
                                                    </div>
                                                    {{-- Ligne marquage (si prix > 0) --}}
                                                    @if((float) $item->marking_price_ht > 0)
                                                        <div class="flex items-center justify-between text-[10px]">
                                                            <span class="text-yellow-700">
                                                                Marquage {{ $item->size?->size ?? '' }}
                                                                <span class="text-yellow-500">&times; {{ $item->quantity }}</span>
                                                                @if($item->technique)
                                                                    <span class="text-yellow-500">({{ $item->technique->name }})</span>
                                                                @endif
                                                            </span>
                                                            <span class="text-yellow-700 tabular-nums">
                                                                {{ number_format($item->marking_price_ht * $item->quantity, 2, ',', ' ') }}&euro;
                                                            </span>
                                                        </div>
                                                    @endif
                                                @endforeach

                                                {{-- Sous-total du groupe --}}
                                                <div class="flex items-center justify-between text-[10px] pt-1 mt-1 border-t border-yellow-200/50">
                                                    <span class="font-semibold text-gray-700">Sous-total</span>
                                                    <span class="font-semibold text-gray-800 tabular-nums">{{ number_format($group['groupTotal'], 2, ',', ' ') }}&euro; HT</span>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach

                                    {{-- Logos du bloc --}}
                                    @if(!empty($mgData['logos']))
                                        <div class="px-4 py-2 ml-2 border-t border-yellow-200/50">
                                            <p class="text-[9px] font-semibold text-gray-400 uppercase tracking-wider mb-1">Logos</p>
                                            @foreach($mgData['logos'] as $logoIdx => $logo)
                                                @php
                                                    $techName = !empty($logo['technique_id']) ? \App\Models\MarkingTechnique::find($logo['technique_id'])?->name : null;
                                                @endphp
                                                <div class="flex items-center gap-2 text-[10px] text-gray-500 py-0.5">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-yellow-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                    </svg>
                                                    <span>
                                                        Logo {{ $logoIdx + 1 }} — {{ $logo['size'] ?? 'A4' }},
                                                        {{ ($logo['colors'] ?? '1') === 'quadri' ? 'Quadri' : ($logo['colors'] ?? '1') . ' coul.' }},
                                                        {{ $zoneLabels[$logo['zone'] ?? ''] ?? 'Non defini' }}
                                                        @if($techName) &middot; <span class="text-yellow-600 font-medium">{{ $techName }}</span> @endif
                                                    </span>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif

                                    {{-- Actions --}}
                                    <div class="flex items-center justify-end gap-3 px-4 py-2 bg-yellow-50">
                                        <button
                                            wire:click="editFrozenGroup({{ $mgNum }})"
                                            class="text-[11px] text-bordeaux hover:text-blue-800 font-medium transition flex items-center gap-1"
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                            </svg>
                                            Modifier
                                        </button>
                                        <button
                                            wire:click="removeFrozenGroup({{ $mgNum }})"
                                            wire:confirm="Supprimer tout ce bloc ?"
                                            class="text-[11px] text-red-400 hover:text-red-600 font-medium transition flex items-center gap-1"
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                            Supprimer
                                        </button>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif
                @endforeach

                {{-- ===== ACTIVE MARKING GROUP (editable) ===== --}}
                @php
                    $hasFrozenGroups = collect($allMarkingGroups)->where('frozen', true)->isNotEmpty();
                    $activeTextileGroups = [];
                    foreach ($allMarkingGroups as $mgNum => $mgData) {
                        if (! $mgData['frozen']) {
                            foreach ($mgData['textileGroups'] as $key => $tg) {
                                $activeTextileGroups[$key] = $tg;
                            }
                        }
                    }
                    $hasActiveItems = ! empty($activeTextileGroups);
                @endphp

                @if($hasActiveItems)
                    {{-- Active group label --}}
                    @if($hasFrozenGroups)
                        <div class="px-4 py-2 bg-bordeaux-50 border-b border-bordeaux-100">
                            <span class="text-xs font-semibold text-blue-800">
                                Bloc {{ $activeGroupNum + 1 }} (en cours)
                            </span>
                        </div>
                    @endif

                    {{-- Active textile lines --}}
                    <div class="divide-y">
                        @foreach($activeTextileGroups as $groupKey => $group)
                            <div class="px-4 py-3" wire:key="group-{{ $groupKey }}">
                                <div class="flex items-start gap-3 cursor-pointer select-none" wire:click="toggleGroupExpanded('{{ $groupKey }}')">
                                    <div class="w-10 h-10 bg-gray-100 rounded-lg flex-shrink-0 overflow-hidden">
                                        @if($group['product']->main_image)
                                            <img src="{{ $group['product']->imageUrl() }}" alt="" class="w-full h-full object-cover">
                                        @else
                                            <div class="w-full h-full flex items-center justify-center text-gray-300">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                </svg>
                                            </div>
                                        @endif
                                    </div>

                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900 line-clamp-1">{{ $group['product']->name }}</p>
                                        <div class="flex items-center gap-2 mt-0.5">
                                            @if($group['color'])
                                                <span class="flex items-center gap-1">
                                                    <span class="w-2.5 h-2.5 rounded-full inline-block border border-gray-200" style="background-color: {{ $group['color']->hex_code }}"></span>
                                                    <span class="text-xs text-gray-500">{{ $group['color']->name }}</span>
                                                </span>
                                            @endif
                                            <span class="text-xs font-medium text-gray-700">{{ $group['totalQuantity'] }} pc{{ $group['totalQuantity'] > 1 ? 's' : '' }}</span>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-2 flex-shrink-0">
                                        <span class="text-sm font-semibold text-gray-900 tabular-nums">{{ number_format($group['groupTotal'], 2, ',', ' ') }}&euro;</span>
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400 transition-transform {{ ($expandedGroups[$groupKey] ?? false) ? 'rotate-90' : '' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                                        </svg>
                                    </div>
                                </div>

                                <div class="flex justify-end mt-1">
                                    <button
                                        wire:click="removeGroup('{{ $groupKey }}')"
                                        wire:confirm="Supprimer ce produit et toutes ses tailles ?"
                                        class="text-[10px] text-gray-400 hover:text-red-500 transition"
                                    >
                                        Supprimer
                                    </button>
                                </div>

                                @if($expandedGroups[$groupKey] ?? false)
                                    <div class="mt-2 ml-[52px] space-y-1.5 border-l-2 border-gray-100 pl-3">
                                        @foreach($group['items'] as $item)
                                            <div class="flex items-center gap-2" wire:key="floating-item-{{ $item->id }}">
                                                <span class="w-8 text-xs font-medium text-gray-500 text-right flex-shrink-0">
                                                    {{ $item->size?->size ?? '-' }}
                                                </span>
                                                <div class="flex items-center gap-1">
                                                    <button wire:click="updateQuantity({{ $item->id }}, {{ $item->quantity - 1 }})"
                                                            wire:loading.attr="disabled"
                                                            class="w-5 h-5 border rounded text-[10px] flex items-center justify-center hover:bg-gray-50 transition disabled:opacity-40 {{ $item->quantity <= 1 ? 'opacity-40 cursor-not-allowed' : '' }}"
                                                            @if($item->quantity <= 1) disabled @endif>
                                                        -
                                                    </button>
                                                    <span class="w-7 text-center text-sm font-medium tabular-nums">{{ $item->quantity }}</span>
                                                    <button wire:click="updateQuantity({{ $item->id }}, {{ $item->quantity + 1 }})"
                                                            wire:loading.attr="disabled"
                                                            class="w-5 h-5 border rounded text-[10px] flex items-center justify-center hover:bg-gray-50 transition disabled:opacity-40">
                                                        +
                                                    </button>
                                                </div>
                                                <span class="flex-1 text-right text-xs tabular-nums text-gray-500">
                                                    {{ number_format($item->line_total_ht, 2, ',', ' ') }}&euro;
                                                </span>
                                                <button wire:click="removeItem({{ $item->id }})"
                                                        wire:confirm="Supprimer cette taille ?"
                                                        class="flex-shrink-0 p-0.5 text-gray-300 hover:text-red-500 transition">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                                    </svg>
                                                </button>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    {{-- ===== SECTION MARQUAGE / LOGOS ===== --}}
                    <div class="border-t-2 border-gray-200 px-4 py-3">
                        <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Marquage</h3>

                        @if(empty($logos))
                            <div class="flex gap-2">
                                <button wire:click="addLogo" class="flex-1 py-2.5 border-2 border-dashed border-gray-300 rounded-lg text-sm font-medium text-gray-500 hover:border-bordeaux-200 hover:text-bordeaux transition">
                                    + Logo
                                </button>
                                <button wire:click="addNameMarking" class="flex-1 py-2.5 border-2 border-dashed border-gray-300 rounded-lg text-sm font-medium text-gray-500 hover:border-blue-200 hover:text-blue-600 transition">
                                    + Prenom/Pseudo
                                </button>
                            </div>
                        @else
                            <div class="space-y-2">
                                @foreach($logos as $logoIdx => $logo)
                                    @php
                                        $isCollapsed = $logo['collapsed'] ?? false;
                                        $hasTechnique = !empty($logo['technique_id']);
                                        $techniqueName = $hasTechnique ? \App\Models\MarkingTechnique::find($logo['technique_id'])?->name : null;
                                        $logoZoneLabel = $zoneLabels[$logo['zone'] ?? ''] ?? ($logo['zone'] ?: 'Non defini');
                                    @endphp

                                    <div class="rounded-lg {{ $isCollapsed ? 'bg-green-50 border border-green-200' : 'bg-gray-50' }} p-3" wire:key="logo-{{ $logoIdx }}">

                                        @php $isNameType = ($logo['type'] ?? 'logo') === 'name'; @endphp

                                        @if($isCollapsed)
                                            {{-- ══ COLLAPSED: summary line ══ --}}
                                            <div class="flex items-center gap-2 cursor-pointer" wire:click="expandLogo({{ $logoIdx }})">
                                                <div class="w-5 h-5 bg-green-100 rounded flex items-center justify-center flex-shrink-0">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                                    </svg>
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    @if($isNameType)
                                                        <span class="text-xs font-semibold text-green-800">Prenom/Pseudo</span>
                                                        <span class="text-[10px] text-green-600 ml-1">
                                                            {{ ucfirst($logo['name_size'] ?? 'petit') }}, {{ $logo['name_lines'] ?? '1' }} ligne(s), {{ $logoZoneLabel }}
                                                            @if($techniqueName) &middot; {{ $techniqueName }} @endif
                                                        </span>
                                                    @else
                                                        <span class="text-xs font-semibold text-green-800">Logo {{ $logoIdx + 1 }}</span>
                                                        <span class="text-[10px] text-green-600 ml-1">
                                                            {{ $logo['size'] ?? 'A4' }}, {{ $logo['colors'] ?? '1' }} coul., {{ $logoZoneLabel }}
                                                            @if($techniqueName) &middot; {{ $techniqueName }} @endif
                                                        </span>
                                                    @endif
                                                </div>
                                                <div class="flex items-center gap-1.5 flex-shrink-0">
                                                    <span class="text-[10px] text-green-500 hover:text-green-700">Modifier</span>
                                                    <button wire:click.stop="removeLogo({{ $logoIdx }})" wire:confirm="Supprimer ce marquage ?" class="text-[10px] text-gray-400 hover:text-red-500">
                                                        &times;
                                                    </button>
                                                </div>
                                            </div>
                                        @else
                                            {{-- ══ EXPANDED: full edit form ══ --}}
                                            <div class="flex items-center justify-between mb-2.5">
                                                <span class="text-sm font-semibold text-gray-700">{{ $isNameType ? 'Prenom / Pseudo' : 'Logo ' . ($logoIdx + 1) }}</span>
                                                <button wire:click="removeLogo({{ $logoIdx }})" wire:confirm="Supprimer ce marquage ?" class="text-[10px] text-gray-400 hover:text-red-500 transition">
                                                    Supprimer
                                                </button>
                                            </div>

                                            @if($isNameType)
                                                {{-- ══ NAME/PSEUDO OPTIONS ══ --}}
                                                <div class="flex gap-2 mb-3">
                                                    <div class="flex-1">
                                                        <label class="block text-xs text-gray-500 mb-1">Taille</label>
                                                        <select x-on:change="$wire.updateNameSize({{ $logoIdx }}, $event.target.value)"
                                                                class="w-full text-sm border-gray-300 rounded-md shadow-sm focus:ring-bordeaux focus:border-bordeaux">
                                                            <option value="petit" @selected(($logo['name_size'] ?? 'petit') === 'petit')>Petit</option>
                                                            <option value="grand" @selected(($logo['name_size'] ?? 'petit') === 'grand')>Grand</option>
                                                        </select>
                                                    </div>
                                                    <div class="flex-1">
                                                        <label class="block text-xs text-gray-500 mb-1">Lignes</label>
                                                        <select x-on:change="$wire.updateNameLines({{ $logoIdx }}, $event.target.value)"
                                                                class="w-full text-sm border-gray-300 rounded-md shadow-sm focus:ring-bordeaux focus:border-bordeaux">
                                                            <option value="1" @selected(($logo['name_lines'] ?? '1') === '1')>1 ligne</option>
                                                            <option value="2" @selected(($logo['name_lines'] ?? '1') === '2')>2 lignes</option>
                                                            <option value="3" @selected(($logo['name_lines'] ?? '1') === '3')>3 lignes</option>
                                                        </select>
                                                    </div>
                                                </div>

                                                {{-- Technique selection for name marking --}}
                                                @php
                                                    $nameTechniques = \App\Models\MarkingTechnique::whereIn('slug', ['broderie', 'dtf', 'flocage-flex'])
                                                        ->where('is_active', true)->get();
                                                @endphp
                                                <label class="block text-xs text-gray-500 mb-1">Technique</label>
                                                <div class="grid grid-cols-3 gap-1.5 mb-3">
                                                    @foreach($nameTechniques as $nt)
                                                        <button wire:click="applyNameTechnique({{ $logoIdx }}, {{ $nt->id }})"
                                                                class="rounded-lg border-2 p-2 text-center cursor-pointer hover:shadow-md transition-all text-xs font-medium
                                                                    {{ ($logo['technique_id'] ?? null) === $nt->id ? 'border-bordeaux bg-bordeaux-50 text-bordeaux' : 'border-gray-200 bg-gray-50 text-gray-600' }}">
                                                            {{ $nt->name }}
                                                        </button>
                                                    @endforeach
                                                </div>
                                            @else
                                                {{-- ══ LOGO OPTIONS ══ --}}
                                                <div class="flex gap-2 mb-3">
                                                    <div class="flex-1">
                                                        <label class="block text-xs text-gray-500 mb-1">Taille</label>
                                                        @php
                                                            $allowedSizes = ['A7', 'A6', 'A5', 'A4', 'A3'];
                                                            $sizeGroup = collect($allGroups)->first();
                                                            $sizeProduct = $sizeGroup['product'] ?? null;
                                                            if ($sizeProduct?->category) {
                                                                $sizeFilters = $sizeProduct->category->getEffectiveFilters();
                                                                $maxSizes = $sizeFilters['_max_logo_sizes'] ?? null;
                                                                if ($maxSizes) $allowedSizes = $maxSizes;
                                                            }
                                                            $defaultSize = count($allowedSizes) === 1 ? $allowedSizes[0] : 'A4';
                                                        @endphp
                                                        <select x-on:change="$wire.updateLogoSize({{ $logoIdx }}, $event.target.value)"
                                                                class="w-full text-sm border-gray-300 rounded-md shadow-sm focus:ring-bordeaux focus:border-bordeaux">
                                                            @foreach($allowedSizes as $size)
                                                                <option value="{{ $size }}" @selected(($logo['size'] ?? $defaultSize) === $size)>{{ $size }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="flex-1">
                                                        <label class="block text-xs text-gray-500 mb-1">Couleurs</label>
                                                        <select x-on:change="$wire.updateLogoColors({{ $logoIdx }}, $event.target.value)"
                                                                class="w-full text-sm border-gray-300 rounded-md shadow-sm focus:ring-bordeaux focus:border-bordeaux">
                                                            @foreach(['1','2','3','4','5','6'] as $c)
                                                                <option value="{{ $c }}" @selected(($logo['colors'] ?? '1') === $c)>{{ $c }} couleur{{ $c > 1 ? 's' : '' }}</option>
                                                            @endforeach
                                                            <option value="quadri" @selected(($logo['colors'] ?? '1') === 'quadri')>Quadri</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            @endif

                                            @if(!$isNameType)
                                            <div class="flex items-center justify-between mb-1">
                                                <label class="text-xs text-gray-500">Emplacement</label>
                                                <label class="flex items-center gap-1.5 cursor-pointer">
                                                    <input type="checkbox" wire:click="toggleLogoDifferent({{ $logoIdx }})" @checked($logo['different'] ?? false)
                                                           class="rounded border-gray-300 text-bordeaux focus:ring-bordeaux w-3.5 h-3.5">
                                                    <span class="text-[11px] text-gray-500">Different par textile</span>
                                                </label>
                                            </div>

                                            @if(!($logo['different'] ?? false))
                                                <select x-on:change="$wire.updateLogoZone({{ $logoIdx }}, $event.target.value)"
                                                        class="w-full text-sm border-gray-300 rounded-md shadow-sm focus:ring-bordeaux focus:border-bordeaux mb-3">
                                                    <option value="">-- Emplacement --</option>
                                                    @foreach($zoneLabels as $zoneKey => $zoneLabel)
                                                        <option value="{{ $zoneKey }}" @selected(($logo['zone'] ?? '') === $zoneKey)>{{ $zoneLabel }}</option>
                                                    @endforeach
                                                </select>
                                            @else
                                                <div class="space-y-1.5 mb-3">
                                                    @foreach($allGroups as $groupKey => $group)
                                                        @php
                                                            $textileData = $logo['textiles'][$groupKey] ?? ['selected' => false, 'zone' => ''];
                                                            $isSelected = $textileData['selected'] ?? false;
                                                        @endphp
                                                        <div class="flex items-center gap-2" wire:key="logo-{{ $logoIdx }}-textile-{{ $groupKey }}">
                                                            <input type="checkbox" wire:click="toggleTextileForLogo({{ $logoIdx }}, '{{ $groupKey }}')" @checked($isSelected)
                                                                   class="rounded border-gray-300 text-bordeaux focus:ring-bordeaux flex-shrink-0 w-3.5 h-3.5">
                                                            <div class="flex items-center gap-1 min-w-0 flex-1">
                                                                @if($group['color'])
                                                                    <span class="w-2 h-2 rounded-full flex-shrink-0 border border-gray-200" style="background-color: {{ $group['color']->hex_code }}"></span>
                                                                @endif
                                                                <span class="text-xs text-gray-700 truncate">{{ Str::limit($group['product']->name, 18) }}</span>
                                                                <span class="text-[10px] text-gray-400">({{ $group['totalQuantity'] }})</span>
                                                            </div>
                                                            @if($isSelected)
                                                                <select x-on:change="$wire.updateLogoTextileZone({{ $logoIdx }}, '{{ $groupKey }}', $event.target.value)"
                                                                        class="text-[11px] border-gray-300 rounded py-0.5 px-1 focus:ring-bordeaux focus:border-bordeaux w-24 flex-shrink-0">
                                                                    <option value="">Zone</option>
                                                                    @foreach($zoneLabels as $zoneKey => $zoneLabel)
                                                                        <option value="{{ $zoneKey }}" @selected(($textileData['zone'] ?? '') === $zoneKey)>{{ $zoneLabel }}</option>
                                                                    @endforeach
                                                                </select>
                                                            @endif
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif

                                            {{-- ══ PER-LOGO TECHNIQUE RECOMMENDATIONS ══ --}}
                                            @php
                                                $logoRecs = $this->logoRecommendations($logoIdx);
                                                $lastLabels = ['Badge PVC', 'Impression DTG'];
                                                $logoOptsAll = collect($logoRecs['allOptions'] ?? []);
                                                $logoOptsPriority = $logoOptsAll->filter(fn ($o) => !in_array($o['label'] ?? '', $lastLabels));
                                                $logoOptsLast = $logoOptsAll->filter(fn ($o) => in_array($o['label'] ?? '', $lastLabels));
                                                $logoOpts = $logoOptsPriority->concat($logoOptsLast)->unique('label')->take(6)->values()->all();
                                                $logoCheapestLabel = $logoRecs['cheapest']['label'] ?? null;
                                                $logoQualityLabel = $logoRecs['quality']['label'] ?? null;
                                                $logoCheapestId = null;
                                                $logoQualityId = null;
                                                if ($logoRecs['cheapest'] && !empty($logoRecs['cheapest']['groups'])) {
                                                    $logoCheapestId = collect($logoRecs['cheapest']['groups'])->first()['technique']->id ?? null;
                                                }
                                                if ($logoRecs['quality'] && !empty($logoRecs['quality']['groups'])) {
                                                    $logoQualityId = collect($logoRecs['quality']['groups'])->first()['technique']->id ?? null;
                                                }
                                                $logoCheapSameQuality = $logoCheapestLabel && $logoQualityLabel && $logoCheapestLabel === $logoQualityLabel;
                                            @endphp

                                            @if(!empty($logoOpts))
                                                @php
                                                    $heatWarning = null;
                                                    $logoWarning = null;
                                                    $broderieWarning = null;
                                                    // Vérifier TOUS les produits du groupe pour les avertissements
                                                    foreach ($allGroups as $hwGroup) {
                                                        $hwProduct = $hwGroup['product'] ?? null;
                                                        if ($hwProduct?->category) {
                                                            $hwFilters = $hwProduct->category->getEffectiveFilters();
                                                            $hw = $hwFilters['_heat_warning'] ?? null;
                                                            if ($hw && !$heatWarning) {
                                                                $heatWarning = [
                                                                    'techniques' => $hw['heat_warning_techniques'] ?? [],
                                                                    'message' => $hw['heat_warning_message'] ?? '',
                                                                ];
                                                            }
                                                        }
                                                    }
                                                    $hwProduct = collect($allGroups)->first()['product'] ?? null;
                                                    if ($hwProduct?->category) {
                                                        $hwFilters = $hwProduct->category->getEffectiveFilters();
                                                        $lw = $hwFilters['_logo_warning'] ?? null;
                                                        if ($lw) {
                                                            $logoColors = (int)($logo['colors'] ?? 1);
                                                            $maxBefore = $lw['max_colors_before_warning'] ?? 6;
                                                            if ($logoColors > $maxBefore) {
                                                                $logoWarning = $lw['message'] ?? '';
                                                            }
                                                        }
                                                        $bw = $hwFilters['_broderie_warning'] ?? null;
                                                        if ($bw) {
                                                            $broderieWarning = [
                                                                'technique' => $bw['recommended_technique'] ?? 1,
                                                                'message' => $bw['message'] ?? '',
                                                            ];
                                                        }
                                                    }
                                                @endphp
                                                <div class="mt-1">
                                                    <h4 class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider mb-2">Technique pour ce logo</h4>
                                                    <div class="grid grid-cols-2 gap-1.5">
                                                        @foreach($logoOpts as $optIdx => $opt)
                                                            @php
                                                                $isCheapest = ($opt['label'] ?? '') === $logoCheapestLabel;
                                                                $isQuality = ($opt['label'] ?? '') === $logoQualityLabel;
                                                                $isBoth = $isCheapest && $logoCheapSameQuality;
                                                                $isSelected = ($logo['selected_label'] ?? null) === ($opt['label'] ?? null) && ($logo['selected_label'] ?? null) !== null;
                                                                // Heat warning : seulement si la technique N'est PAS broderie
                                                                // Et si c'est un mix, pas de warning si le mix utilise broderie pour les produits sensibles
                                                                $isMix = isset($opt['isMix']) && $opt['isMix'];
                                                                $isHeatWarning = $heatWarning && $opt['techniqueId'] !== 1 && !$isMix;
                                                                $isLogoWarning = $logoWarning && $opt['techniqueId'] !== 1;
                                                                $isBroderieRecommended = ($logoWarning && $opt['techniqueId'] === 1)
                                                                    || ($broderieWarning && $opt['techniqueId'] === ($broderieWarning['technique'] ?? 1));
                                                                $isBroderieTriangle = $broderieWarning && $opt['techniqueId'] !== ($broderieWarning['technique'] ?? 1);

                                                                if ($isSelected) {
                                                                    $borderClass = 'border-bordeaux bg-bordeaux-50 ring-2 ring-bordeaux-200';
                                                                } elseif ($isBoth) {
                                                                    $borderClass = 'border-bordeaux-200 bg-bordeaux-50';
                                                                } elseif ($isCheapest) {
                                                                    $borderClass = 'border-green-400 bg-green-50';
                                                                } elseif ($isQuality) {
                                                                    $borderClass = 'border-purple-400 bg-purple-50';
                                                                } else {
                                                                    $borderClass = 'border-gray-200 bg-gray-50';
                                                                }
                                                            @endphp

                                                            <div wire:click="applyLogoRecommendation({{ $logoIdx }}, 'allOption', {{ $optIdx }})"
                                                                 class="rounded-lg border-2 {{ $borderClass }} p-2 flex flex-col cursor-pointer hover:shadow-md transition-all relative"
                                                                 @if($isHeatWarning) title="{{ $heatWarning['message'] }}" @elseif($isLogoWarning) title="{{ $logoWarning }}" @elseif($isBroderieTriangle) title="{{ $broderieWarning['message'] }}" @endif>
                                                                @if($isSelected)
                                                                    <div class="absolute -top-1.5 -right-1.5 w-4 h-4 bg-bordeaux rounded-full flex items-center justify-center shadow">
                                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-2.5 w-2.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                                                        </svg>
                                                                    </div>
                                                                @endif

                                                                @if($isHeatWarning)
                                                                    <div class="absolute top-0 left-0" title="{{ $heatWarning['message'] }}">
                                                                        <div class="w-0 h-0 border-t-[20px] border-t-amber-400 border-r-[20px] border-r-transparent"></div>
                                                                        <svg class="w-2.5 h-2.5 text-white absolute top-0.5 left-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.168 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 6a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 6zm0 9a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/></svg>
                                                                    </div>
                                                                @elseif($isLogoWarning)
                                                                    <div class="absolute top-0 left-0" title="{{ $logoWarning }}">
                                                                        <div class="w-0 h-0 border-t-[20px] border-t-orange-500 border-r-[20px] border-r-transparent"></div>
                                                                        <svg class="w-2.5 h-2.5 text-white absolute top-0.5 left-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.168 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 6a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 6zm0 9a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/></svg>
                                                                    </div>
                                                                @elseif($isBroderieTriangle)
                                                                    <div class="absolute top-0 left-0" title="{{ $broderieWarning['message'] }}">
                                                                        <div class="w-0 h-0 border-t-[20px] border-t-blue-400 border-r-[20px] border-r-transparent"></div>
                                                                        <svg class="w-2.5 h-2.5 text-white absolute top-0.5 left-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a.75.75 0 000 1.5h.253a.25.25 0 01.244.304l-.459 2.066A1.75 1.75 0 0010.747 15H11a.75.75 0 000-1.5h-.253a.25.25 0 01-.244-.304l.459-2.066A1.75 1.75 0 009.253 9H9z" clip-rule="evenodd"/></svg>
                                                                    </div>
                                                                @endif

                                                                <div class="flex flex-wrap gap-0.5 mb-1">
                                                                    @if($isBroderieRecommended)
                                                                        <span class="px-1 py-0.5 rounded-full text-[8px] font-semibold bg-blue-100 text-blue-700">Conseille</span>
                                                                    @endif
                                                                    @if($isCheapest)
                                                                        <span class="px-1 py-0.5 rounded-full text-[8px] font-semibold bg-green-100 text-green-700">Moins cher</span>
                                                                    @endif
                                                                    @if($isQuality)
                                                                        <span class="px-1 py-0.5 rounded-full text-[8px] font-semibold bg-purple-100 text-purple-700">Qualite</span>
                                                                    @endif
                                                                </div>

                                                                <p class="text-[11px] font-semibold text-gray-800 mb-0.5">{{ $opt['label'] }}</p>
                                                                <span class="text-[10px] font-bold text-gray-900">{{ number_format($opt['totalCost'], 2, ',', ' ') }}&euro;</span>
                                                            </div>
                                                        @endforeach
                                                    </div>

                                                </div>
                                            @endif

                                            @endif {{-- end !$isNameType --}}

                                            {{-- Collapse button (only if technique chosen) --}}
                                            @if($hasTechnique)
                                                <button wire:click="collapseLogo({{ $logoIdx }})"
                                                        class="mt-3 w-full py-1.5 text-xs font-medium text-green-700 bg-green-100 rounded-lg hover:bg-green-200 transition">
                                                    {{ $isNameType ? 'Valider le prenom' : 'Valider ce logo' }}
                                                </button>
                                            @endif
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif

                    </div>


                @elseif($hasFrozenGroups)
                    {{-- No active items but frozen groups exist: show empty state for new block --}}
                    <div class="flex flex-col items-center justify-center py-12 text-gray-400 px-6">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        <p class="text-sm font-medium text-gray-500 mb-1">Nouveau bloc vide</p>
                        <p class="text-xs text-center text-gray-400">Ajoutez des textiles pour creer un nouveau bloc de marquage.</p>
                        <a href="{{ route('catalogue.index') }}" wire:navigate class="mt-4 px-4 py-2 text-sm font-medium text-green-600 hover:text-green-700 hover:bg-green-50 rounded-lg border border-green-200 transition">
                            + Ajouter un textile
                        </a>
                    </div>
                @endif
            @endif
        </div>

        {{-- Footer --}}
        @if($quote && $quote->items->isNotEmpty())
            <div class="border-t bg-white px-4 py-3 space-y-2 flex-shrink-0">
                {{-- Action buttons --}}
                @if($hasActiveItems ?? false)
                    <div class="flex gap-2">
                        @if(!empty($logos))
                            <button wire:click="addLogo" class="flex-1 py-2 border-2 border-dashed border-gray-300 rounded-lg text-xs font-medium text-gray-500 hover:border-bordeaux-200 hover:text-bordeaux transition">
                                + Logo
                            </button>
                            <button wire:click="addNameMarking" class="flex-1 py-2 border-2 border-dashed border-gray-300 rounded-lg text-xs font-medium text-gray-500 hover:border-blue-200 hover:text-blue-600 transition">
                                + Prenom
                            </button>
                        @endif
                        <a href="{{ route('catalogue.index') }}" wire:navigate class="flex-1 py-2 text-center text-xs font-medium text-green-600 hover:text-green-700 hover:bg-green-50 rounded-lg border border-green-200 transition">
                            + Textile
                        </a>
                        <div class="flex-1 relative" x-data="{ showInfo: false }">
                            <button
                                wire:click="freezeCurrentGroup"
                                class="w-full flex items-center justify-center gap-1 py-2 bg-yellow-500 hover:bg-yellow-600 text-white text-xs font-semibold rounded-lg transition shadow-sm"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                                Figer
                                <span @mouseenter="showInfo = true" @mouseleave="showInfo = false"
                                      @click.stop
                                      class="w-4 h-4 rounded-full bg-white/30 text-[9px] font-bold flex items-center justify-center ml-0.5 cursor-help">i</span>
                            </button>
                            <div x-show="showInfo" x-transition
                                 class="absolute w-56 text-white text-[11px] leading-relaxed rounded-lg p-3 shadow-lg"
                                 style="z-index: 99999; bottom: 100%; right: 0; margin-bottom: 8px; background-color: #6b1d2a;">
                                <p class="font-semibold mb-1">Pourquoi figer ?</p>
                                <p>Figez ce groupe de textiles avec le(s) meme(s) marquage(s) pour optimiser les couts. Cela permet d'ajouter d'autres textiles avec un/des marquage(s) different(s) ou sans marquage, pour un devis complet.</p>
                                <div class="absolute top-full right-2 w-0 h-0 border-l-[6px] border-l-transparent border-r-[6px] border-r-transparent border-t-[6px]" style="border-top-color: #6b1d2a;"></div>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Totals --}}
                <div class="space-y-1 pt-2 border-t">
                    <div class="flex justify-between text-sm text-gray-500">
                        <span>Produits HT</span>
                        <span>{{ number_format($quote->total_ht - ($quote->shipping_ht ?? 0), 2, ',', ' ') }} &euro;</span>
                    </div>
                    @if(($quote->shipping_ht ?? 0) > 0)
                        <div class="flex justify-between text-sm text-gray-500">
                            <span>Port ({{ $quote->shipping_parcels }} colis)</span>
                            <span>{{ number_format($quote->shipping_ht, 2, ',', ' ') }} &euro;</span>
                        </div>
                    @endif
                    <div class="flex justify-between text-sm text-gray-500">
                        <span>Total HT</span>
                        <span>{{ number_format($quote->total_ht, 2, ',', ' ') }} &euro;</span>
                    </div>
                    <div class="flex justify-between text-sm text-gray-500">
                        <span>TVA (20%)</span>
                        <span>{{ number_format($quote->total_tva, 2, ',', ' ') }} &euro;</span>
                    </div>
                    <div class="flex justify-between font-bold text-base pt-1 border-t">
                        <span>Total TTC</span>
                        <span class="text-bordeaux">{{ number_format($quote->total_ttc, 2, ',', ' ') }} &euro;</span>
                    </div>
                </div>

                {{-- CTA --}}
                <a href="{{ route('mon-devis') }}" class="block w-full px-4 py-2.5 bg-bordeaux text-white text-sm font-semibold rounded-lg hover:bg-bordeaux-dark transition text-center">
                    Voir le devis
                </a>
            </div>
        @endif
    </div>
    </div>
</div>
