<div>
    @if($submitted)
        {{-- Success state --}}
        <div class="text-center py-16">
            <div class="w-20 h-20 mx-auto mb-6 bg-green-100 rounded-full flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
            </div>
            <h2 class="text-2xl font-bold mb-2">Devis envoye !</h2>
            <p class="text-gray-500 mb-2">Reference : <strong>{{ $quote->reference }}</strong></p>
            <p class="text-gray-500 mb-6">Votre devis expire le {{ $quote->expires_at?->format('d/m/Y') ?? '—' }}.</p>
            <a href="{{ route('catalogue.index') }}" class="px-6 py-3 bg-bordeaux text-white font-semibold rounded-lg hover:bg-bordeaux-dark transition">
                Continuer mes achats
            </a>
        </div>

    @elseif(!$quote || $quote->items->isEmpty())
        {{-- Empty state --}}
        <div class="text-center py-16 text-gray-400">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <h2 class="text-xl font-semibold text-gray-600 mb-2">Votre devis est vide</h2>
            <p class="text-sm mb-6">Parcourez notre catalogue pour ajouter des produits</p>
            <a href="{{ route('catalogue.index') }}" class="px-6 py-3 bg-bordeaux text-white font-semibold rounded-lg hover:bg-bordeaux-dark transition">
                Voir le catalogue
            </a>
        </div>

    @else
        {{-- Error --}}
        @if(session('quote-error'))
            <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm">
                {{ session('quote-error') }}
            </div>
        @endif

        @php
            $zoneLabels = [
                'poitrine_gauche' => 'Poitrine gauche',
                'poitrine_droite' => 'Poitrine droite',
                'poitrine_centre' => 'Poitrine centre',
                'dos_centre' => 'Dos centre',
                'dos_haut' => 'Dos haut',
                'manche_gauche' => 'Manche gauche',
                'manche_droite' => 'Manche droite',
                'col' => 'Col',
                'face' => 'Face',
                'dos' => 'Dos',
            ];

            // Regrouper par marking_group
            $markingGroups = $quote->items->groupBy('marking_group')->sortKeys();

            // Markings JSON
            $markings = $quote->markings ?? [];
            $firstVal = !empty($markings) ? reset($markings) : null;
            $isGrouped = is_array($firstVal) && (empty($firstVal) || !isset($firstVal['size']));
        @endphp

        <div class="bg-white rounded-xl shadow-sm overflow-hidden mb-6">
            <div class="px-4 sm:px-6 py-4 border-b bg-gray-50 flex items-center justify-between">
                <h2 class="font-semibold">Ref. {{ $quote->reference }}</h2>
                <span class="text-xs text-gray-400">{{ $markingGroups->count() }} bloc{{ $markingGroups->count() > 1 ? 's' : '' }}</span>
            </div>

            @foreach($markingGroups as $mgNum => $mgItems)
                @php
                    // Sous-grouper par produit+couleur
                    $textileGroups = $mgItems->groupBy(fn($item) => $item->product_id . '-' . $item->product_color_id);

                    // Logos de ce marking_group
                    if ($isGrouped) {
                        $logos = $markings[$mgNum] ?? [];
                        if (!empty($logos) && isset($logos['size'])) $logos = [$logos];
                    } else {
                        $logos = $markings;
                    }
                    if (empty($logos)) $logos = [];

                    // Totaux du bloc
                    $blocTextileTotal = $mgItems->sum(fn($i) => (float) $i->unit_price_ht * $i->quantity);
                    $blocMarkingTotal = $mgItems->sum(fn($i) => (float) $i->marking_price_ht * $i->quantity);
                    $blocTotal = $mgItems->sum('line_total_ht');
                @endphp

                <div class="border-b last:border-b-0">
                    {{-- En-tete du bloc --}}
                    @if($markingGroups->count() > 1)
                        <div class="px-4 sm:px-6 py-2.5 bg-gray-50/50 border-b">
                            <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Bloc {{ $loop->iteration }}</span>
                        </div>
                    @endif

                    {{-- === SECTION 1 : TEXTILES (sans marquage) === --}}
                    <div class="px-4 sm:px-6 py-3">
                        <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider mb-2">Textiles</p>
                    </div>
                    @foreach($textileGroups as $tgKey => $tgItems)
                        @php
                            $firstItem = $tgItems->first();
                            $product = $firstItem->product;
                            $color = $firstItem->color;
                            $sortedItems = $tgItems->sortBy(fn($i) => array_search($i->size?->size, ['XXS','XS','S','M','L','XL','XXL','3XL','4XL','5XL','1/2','3/4','5/6','7/8','9/10','11/12','13/14']) !== false ? array_search($i->size?->size, ['XXS','XS','S','M','L','XL','XXL','3XL','4XL','5XL','1/2','3/4','5/6','7/8','9/10','11/12','13/14']) : 99);
                        @endphp

                        <div class="px-4 sm:px-6 pb-3">
                            {{-- Produit + couleur --}}
                            <div class="flex items-center gap-3 mb-2">
                                <div class="w-10 h-10 bg-gray-100 rounded-lg flex-shrink-0 overflow-hidden">
                                    @if($product->main_image)
                                        <img src="{{ $product->imageUrl() }}" alt="" class="w-full h-full object-cover">
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <a href="{{ $product->url }}" class="font-semibold text-sm text-gray-900 hover:text-bordeaux transition line-clamp-1">
                                        {{ $product->name }}
                                    </a>
                                    @if($color)
                                        <span class="flex items-center gap-1 mt-0.5">
                                            <span class="w-3 h-3 rounded-full inline-block border border-gray-200" style="background-color: {{ $color->hex_code }}; {{ $color->hex_code === '#FFFFFF' ? 'box-shadow: inset 0 0 0 1px #d1d5db;' : '' }}"></span>
                                            <span class="text-xs text-gray-500">{{ $color->name }}</span>
                                        </span>
                                    @endif
                                </div>
                                <div class="text-right">
                                    <span class="text-xs font-medium text-gray-500">{{ $tgItems->sum('quantity') }} pcs</span>
                                </div>
                            </div>

                            {{-- Répartition tailles --}}
                            <div class="flex flex-wrap gap-1 ml-13">
                                @foreach($sortedItems as $item)
                                    <div class="flex items-center gap-1 bg-gray-50 rounded px-2 py-1 text-xs">
                                        <span class="text-gray-500">{{ $item->size?->size ?? '—' }}</span>
                                        <span class="font-medium">x{{ $item->quantity }}</span>
                                        <div class="flex items-center gap-0.5 ml-1">
                                            <button wire:click="updateQuantity({{ $item->id }}, {{ $item->quantity - 1 }})"
                                                    class="w-4 h-4 border rounded text-[9px] flex items-center justify-center hover:bg-gray-100 {{ $item->quantity <= 1 ? 'opacity-30' : '' }}"
                                                    @if($item->quantity <= 1) disabled @endif>-</button>
                                            <button wire:click="updateQuantity({{ $item->id }}, {{ $item->quantity + 1 }})"
                                                    class="w-4 h-4 border rounded text-[9px] flex items-center justify-center hover:bg-gray-100">+</button>
                                        </div>
                                        <button wire:click="removeItem({{ $item->id }})" wire:confirm="Supprimer ?"
                                                class="text-gray-300 hover:text-red-500 transition ml-0.5">
                                            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                        </button>
                                    </div>
                                @endforeach
                            </div>

                            {{-- Prix textile --}}
                            <div class="flex justify-between text-xs text-gray-500 mt-2 ml-13">
                                <span>P.U. HT : {{ number_format($firstItem->unit_price_ht, 2, ',', ' ') }} &euro;</span>
                                <span class="font-medium text-gray-700">{{ number_format($tgItems->sum(fn($i) => $i->unit_price_ht * $i->quantity), 2, ',', ' ') }} &euro; HT</span>
                            </div>
                        </div>

                        @if(!$loop->last)
                            <hr class="mx-6 border-gray-100">
                        @endif
                    @endforeach

                    {{-- === SECTION 2 : MARQUAGE (séparé) === --}}
                    @if(!empty($logos))
                        @php
                            $mgTotalQty = $mgItems->sum('quantity');
                            $blocMarkingTotalCalc = $mgItems->sum(fn($i) => (float) $i->marking_price_ht * $i->quantity);
                        @endphp
                        <div class="px-4 sm:px-6 py-3 bg-amber-50/50 border-t">
                            <p class="text-[10px] font-semibold text-amber-700 uppercase tracking-wider mb-2">Marquage — {{ $mgTotalQty }} pieces</p>

                            @php
                                // Calculer le prix marquage unitaire par logo
                                $firstMarkedItem = $mgItems->first(fn($i) => (float) $i->marking_price_ht > 0);
                                $markingUnitPrice = $firstMarkedItem ? (float) $firstMarkedItem->marking_price_ht : 0;
                                $logoCount = count($logos);
                                $markingPerLogo = $logoCount > 0 ? $markingUnitPrice / $logoCount : $markingUnitPrice;
                            @endphp

                            @foreach($logos as $li => $logo)
                                @php
                                    $techName = !empty($logo['technique_id']) ? \App\Models\MarkingTechnique::find($logo['technique_id'])?->name : null;
                                    $isName = ($logo['type'] ?? 'logo') === 'name';
                                    $logoColors = ($logo['colors'] ?? '1') === 'quadri' ? 'Quadri' : ($logo['colors'] ?? '1') . 'C';
                                    $logoSize = $logo['size'] ?? 'A4';
                                    $logoZone = $zoneLabels[$logo['zone'] ?? ''] ?? ($logo['zone'] ?: '');
                                    $logoTotal = $markingPerLogo * $mgTotalQty;
                                @endphp
                                <div class="py-2 {{ !$loop->last ? 'border-b border-amber-200/30' : '' }}">
                                    <div class="flex items-start justify-between text-xs">
                                        <div class="text-amber-800">
                                            @if($isName)
                                                <span class="font-semibold">Prenom/Pseudo</span>
                                                — {{ ucfirst($logo['name_size'] ?? 'petit') }}, {{ $logo['name_lines'] ?? '1' }} ligne(s)
                                            @else
                                                <span class="font-semibold">Logo {{ $li + 1 }}</span>
                                                — {{ $logoSize }}, {{ $logoColors }}
                                                @if($logoZone), {{ $logoZone }} @endif
                                            @endif
                                            @if($techName)
                                                <br><span class="text-amber-600 font-medium">{{ $techName }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    @if($markingPerLogo > 0)
                                        <div class="flex justify-between text-[11px] text-amber-600 mt-1">
                                            <span>{{ $mgTotalQty }} pcs x {{ number_format($markingPerLogo, 2, ',', ' ') }} &euro; HT/pce</span>
                                            <span class="font-semibold text-amber-800 tabular-nums">{{ number_format($logoTotal, 2, ',', ' ') }} &euro; HT</span>
                                        </div>
                                    @endif
                                </div>
                            @endforeach

                            @if($blocMarkingTotalCalc > 0)
                                <div class="flex items-center justify-between text-xs mt-2 pt-2 border-t border-amber-200/50">
                                    <span class="text-amber-700 font-medium">Total marquage ({{ $logoCount }} logo{{ $logoCount > 1 ? 's' : '' }})</span>
                                    <span class="text-amber-800 font-bold tabular-nums">{{ number_format($blocMarkingTotalCalc, 2, ',', ' ') }} &euro; HT</span>
                                </div>
                            @endif
                        </div>
                    @endif

                    {{-- Sous-total du bloc --}}
                    <div class="px-4 sm:px-6 py-2.5 bg-gray-50 border-t flex items-center justify-between">
                        <span class="text-xs text-gray-500">
                            Textile : {{ number_format($blocTextileTotal, 2, ',', ' ') }}&euro;
                            @if($blocMarkingTotal > 0)
                                &nbsp;+&nbsp; Marquage : {{ number_format($blocMarkingTotal, 2, ',', ' ') }}&euro;
                            @endif
                        </span>
                        <span class="text-sm font-semibold text-gray-800 tabular-nums">{{ number_format($blocTotal, 2, ',', ' ') }}&euro; HT</span>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Totals --}}
        <div class="bg-white rounded-xl shadow-sm p-4 sm:p-6">
            <div class="max-w-sm ml-auto space-y-2">
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">Produits HT</span>
                    <span>{{ number_format($quote->total_ht - ($quote->shipping_ht ?? 0), 2, ',', ' ') }} &euro;</span>
                </div>
                @if(($quote->shipping_ht ?? 0) > 0)
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Port ({{ $quote->shipping_parcels ?? '?' }} colis)</span>
                        <span>{{ number_format($quote->shipping_ht, 2, ',', ' ') }} &euro;</span>
                    </div>
                @endif
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">Total HT</span>
                    <span>{{ number_format($quote->total_ht, 2, ',', ' ') }} &euro;</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">TVA (20%)</span>
                    <span>{{ number_format($quote->total_tva, 2, ',', ' ') }} &euro;</span>
                </div>
                <div class="border-t pt-2 flex justify-between font-bold text-lg">
                    <span>Total TTC</span>
                    <span class="text-bordeaux">{{ number_format($quote->total_ttc, 2, ',', ' ') }} &euro;</span>
                </div>
            </div>

            @if($quoteEdited)
                {{-- ═══ DEVIS EDITE : banniere + actions post-edition ═══ --}}
                <div class="mt-6 p-4 bg-green-50 border border-green-200 rounded-xl">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                        <div>
                            <p class="font-semibold text-green-800">Devis edite — {{ $quote->reference }}</p>
                            <p class="text-xs text-green-600">
                                Valable jusqu'au {{ $quote->expires_at?->format('d/m/Y') ?? '—' }}
                                ({{ $quote->daysRemaining() }} jours restants)
                            </p>
                        </div>
                    </div>
                    <div class="flex flex-col sm:flex-row gap-3">
                        {{-- Telecharger PDF --}}
                        <a href="{{ asset('storage/' . $quote->bat_pdf) }}" download
                           class="flex-1 px-4 py-2.5 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-white transition text-center text-sm flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                            </svg>
                            Telecharger le PDF
                        </a>

                        {{-- Passer commande --}}
                        <a href="{{ route('payment.checkout') }}"
                           class="flex-1 px-4 py-2.5 bg-green-600 text-white font-semibold rounded-lg hover:bg-green-700 transition text-sm flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                            </svg>
                            Passer commande
                        </a>
                    </div>
                </div>
            @else
                {{-- ═══ DEVIS NON EDITE : boutons normaux ═══ --}}
                <div class="mt-6 flex flex-col sm:flex-row justify-end gap-3">
                    <a href="{{ route('catalogue.index') }}" class="px-6 py-3 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50 transition text-center">
                        Continuer mes achats
                    </a>
                    <button wire:click="confirmEditQuote"
                            class="px-6 py-3 bg-bordeaux text-white font-semibold rounded-lg hover:bg-bordeaux-dark transition flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Editer le devis PDF
                    </button>
                    <a href="{{ route('payment.checkout') }}"
                       class="px-6 py-3 bg-green-600 text-white font-semibold rounded-lg hover:bg-green-700 transition flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                        </svg>
                        Passer commande
                    </a>
                </div>
            @endif

            {{-- Modale de confirmation edition devis --}}
            @if($showConfirmModal)
                <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40" wire:click.self="cancelEditQuote">
                    <div class="bg-white rounded-2xl shadow-xl max-w-md w-full mx-4 p-6">
                        <div class="text-center mb-5">
                            <div class="w-14 h-14 mx-auto mb-3 bg-bordeaux-50 rounded-full flex items-center justify-center">
                                <svg class="w-7 h-7 text-bordeaux" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </div>
                            <h3 class="text-lg font-bold text-gray-900">Editer votre devis ?</h3>
                            <p class="text-sm text-gray-500 mt-2">
                                Votre devis sera edite au format PDF et sera
                                <strong>valable 2 semaines</strong>.
                                Vous pourrez le retrouver dans votre espace client
                                et passer commande ulterieurement.
                            </p>
                        </div>
                        <div class="flex gap-3">
                            <button wire:click="cancelEditQuote"
                                    class="flex-1 px-4 py-2.5 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50 transition text-sm">
                                Annuler
                            </button>
                            <button wire:click="editQuotePdf"
                                    wire:loading.attr="disabled"
                                    class="flex-1 px-4 py-2.5 bg-bordeaux text-white font-semibold rounded-lg hover:bg-bordeaux-dark transition text-sm">
                                <span wire:loading.remove wire:target="editQuotePdf">OK, editer le devis</span>
                                <span wire:loading wire:target="editQuotePdf">Generation...</span>
                            </button>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    @endif

    {{-- Auto-download PDF --}}
    @script
    <script>
        $wire.on('download-quote-pdf', ({ url }) => {
            const a = document.createElement('a');
            a.href = url;
            a.download = '';
            document.body.appendChild(a);
            a.click();
            a.remove();
        });
    </script>
    @endscript
</div>
