@php
    $order = $getRecord();
    $order->load('items.product', 'items.color', 'items.size', 'items.technique');
    $quote = $order->quote;
    $markings = $quote?->markings ?? [];

    // Group items by marking_group
    $groups = $order->items->groupBy('marking_group')->sortKeys();

    $zoneLabels = [
        'poitrine_gauche' => 'Poitrine gauche', 'poitrine_droite' => 'Poitrine droite',
        'poitrine_centre' => 'Poitrine centre', 'dos_centre' => 'Dos centre',
        'dos_haut' => 'Dos haut', 'manche_gauche' => 'Manche gauche',
        'manche_droite' => 'Manche droite', 'col' => 'Col',
    ];

@endphp

<div class="space-y-4">
    @foreach($groups as $mgNum => $mgItems)
        @php
            $textileGroups = $mgItems->groupBy(fn($i) => $i->product_id . '-' . $i->product_color_id);
            $mgTotalQty = $mgItems->sum('quantity');
            $mgTotalHt = $mgItems->sum('line_total_ht');

            // Get logos for this marking group — try string and int keys
            $logos = $markings[(string) $mgNum] ?? $markings[$mgNum] ?? [];
            // If it's a single logo (has 'size' key), wrap in array
            if (!empty($logos) && isset($logos['size'])) $logos = [$logos];
            // Filter out empty arrays
            $logos = array_filter($logos, fn($l) => !empty($l));
        @endphp

        <div class="border rounded-lg overflow-hidden">
            <div class="bg-gray-100 px-4 py-2 flex items-center justify-between">
                <span class="font-semibold text-sm">Bloc {{ intval($mgNum) + 1 }} — {{ $mgTotalQty }} pce(s)</span>
                <span class="text-sm font-bold text-primary-600">{{ number_format($mgTotalHt, 2, ',', ' ') }} &euro; HT</span>
            </div>

            {{-- Items --}}
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 text-xs text-gray-500">
                        <th class="text-left px-4 py-1.5">Produit</th>
                        <th class="text-left px-2 py-1.5">Couleur</th>
                        <th class="text-left px-2 py-1.5">Taille</th>
                        <th class="text-center px-2 py-1.5">Qte</th>
                        <th class="text-right px-2 py-1.5">P.U. textile</th>
                        <th class="text-right px-2 py-1.5">P.U. marquage</th>
                        <th class="text-right px-4 py-1.5">Total HT</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @foreach($mgItems->sortBy(fn($i) => $i->product_id . '-' . ($i->size?->sort_order ?? 0)) as $item)
                        <tr>
                            <td class="px-4 py-1.5 font-medium">{{ $item->product?->name ?? '?' }}</td>
                            <td class="px-2 py-1.5 text-gray-500">{{ $item->color?->name ?? '-' }}</td>
                            <td class="px-2 py-1.5 text-gray-500">{{ $item->size?->size ?? '-' }}</td>
                            <td class="px-2 py-1.5 text-center">{{ $item->quantity }}</td>
                            <td class="px-2 py-1.5 text-right tabular-nums">{{ number_format($item->unit_price_ht, 2, ',', ' ') }}&euro;</td>
                            <td class="px-2 py-1.5 text-right tabular-nums {{ (float)$item->marking_price_ht > 0 ? 'text-amber-600' : 'text-gray-300' }}">
                                {{ number_format($item->marking_price_ht, 2, ',', ' ') }}&euro;
                            </td>
                            <td class="px-4 py-1.5 text-right font-semibold tabular-nums">{{ number_format($item->line_total_ht, 2, ',', ' ') }}&euro;</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            {{-- Marquage details + fichiers --}}
            @if(!empty($logos))
                @php
                    $dir = 'orders/' . $order->reference;
                    $disk = \Illuminate\Support\Facades\Storage::disk('public');
                    $allFiles = $disk->exists($dir) ? $disk->files($dir) : [];
                @endphp
                <div class="bg-amber-50 px-4 py-3 border-t">
                    <p class="text-xs font-semibold text-amber-800 mb-2">Marquage</p>
                    @foreach($logos as $li => $logo)
                        @php
                            $techName = !empty($logo['technique_id']) ? \App\Models\MarkingTechnique::find($logo['technique_id'])?->name : null;
                            $isName = ($logo['type'] ?? 'logo') === 'name';
                        @endphp
                        <div class="bg-white rounded-lg border p-3 mb-2">
                            <div class="flex items-start justify-between">
                                <div>
                                    @if($isName)
                                        <div class="flex items-center gap-2 mb-1">
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-blue-100 text-blue-700 rounded-full text-[10px] font-semibold">Prenom/Pseudo</span>
                                            @if($techName) <span class="text-xs font-semibold text-blue-700">{{ $techName }}</span> @endif
                                        </div>
                                        <div class="text-xs text-gray-600 space-y-0.5">
                                            <p><span class="text-gray-400">Taille :</span> {{ ucfirst($logo['name_size'] ?? 'petit') }}</p>
                                            <p><span class="text-gray-400">Lignes :</span> {{ $logo['name_lines'] ?? '1' }}</p>
                                        </div>
                                        <div class="mt-2 px-2 py-1 bg-blue-50 border border-blue-200 rounded text-[10px] text-blue-700">
                                            Tableau Excel des prenoms a fournir par le client
                                        </div>
                                    @else
                                        <div class="flex items-center gap-2 mb-1">
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-amber-100 text-amber-700 rounded-full text-[10px] font-semibold">Logo {{ $li + 1 }}</span>
                                            @if($techName) <span class="text-xs font-semibold text-amber-700">{{ $techName }}</span> @endif
                                        </div>
                                        <div class="text-xs text-gray-600 space-y-0.5">
                                            <p><span class="text-gray-400">Format :</span> {{ $logo['size'] ?? 'A4' }}</p>
                                            <p><span class="text-gray-400">Couleurs :</span> {{ ($logo['colors'] ?? '1') === 'quadri' ? 'Quadri' : ($logo['colors'] ?? '1') . ' couleur(s)' }}</p>
                                            <p><span class="text-gray-400">Emplacement :</span> {{ $zoneLabels[$logo['zone'] ?? ''] ?? ($logo['zone'] ?: 'Non defini') }}</p>
                                        </div>

                                        {{-- Fichiers client lies a ce logo --}}
                                        @if(!empty($allFiles))
                                            <div class="mt-2 pt-2 border-t border-amber-100">
                                                <p class="text-[10px] text-gray-400 font-medium mb-1">Fichiers client :</p>
                                                <div class="flex flex-wrap gap-1">
                                                    @foreach($allFiles as $file)
                                                        @php
                                                            $fname = basename($file);
                                                            $furl = $disk->url($file);
                                                            $fext = strtolower(pathinfo($fname, PATHINFO_EXTENSION));
                                                            $isImg = in_array($fext, ['jpg','jpeg','png','svg','webp']);
                                                        @endphp
                                                        <a href="{{ $furl }}" target="_blank" class="inline-flex items-center gap-1 px-2 py-1 bg-gray-50 border rounded text-[10px] hover:bg-blue-50 hover:border-blue-300 transition">
                                                            @if($isImg)
                                                                <img src="{{ $furl }}" class="w-6 h-6 object-contain rounded">
                                                            @else
                                                                <svg class="w-3 h-3 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                                            @endif
                                                            <span class="text-gray-600">{{ $fname }}</span>
                                                        </a>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @else
                                            <div class="mt-2 px-2 py-1 bg-red-50 border border-red-200 rounded text-[10px] text-red-600">
                                                Aucun fichier charge par le client
                                            </div>
                                        @endif
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    @endforeach
</div>
