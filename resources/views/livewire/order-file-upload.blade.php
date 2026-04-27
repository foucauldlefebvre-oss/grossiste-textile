<div>
    @if(session('upload-success'))
        <div class="mb-4 p-3 bg-green-50 border border-green-200 text-green-700 rounded-lg text-sm">
            {{ session('upload-success') }}
        </div>
    @endif

    @php
        $markings = $order->quote?->markings ?? [];
        $disk = \Illuminate\Support\Facades\Storage::disk('public');

        // Collecter tous les logos de tous les blocs
        $allLogos = [];
        $blockIndex = 0;
        foreach ($markings as $blockLogos) {
            if (!is_array($blockLogos) || empty($blockLogos)) continue;
            // Si c'est un format plat (ancien)
            if (isset($blockLogos['size'])) $blockLogos = [$blockLogos];
            foreach ($blockLogos as $logo) {
                if (empty($logo) || !is_array($logo)) continue;
                $logo['_block'] = $blockIndex;
                $allLogos[] = $logo;
            }
            $blockIndex++;
        }
    @endphp

    @if(!empty($allLogos))
        <div class="space-y-5">
            @php $currentBlock = -1; $logoCounter = 0; @endphp
            @foreach($allLogos as $li => $logo)
                @if(($logo['_block'] ?? 0) !== $currentBlock)
                    @php $currentBlock = $logo['_block'] ?? 0; $logoCounter = 0; @endphp
                    <div class="border-t pt-3 mt-3 first:border-0 first:pt-0 first:mt-0">
                        <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Bloc {{ $currentBlock + 1 }}</p>
                    </div>
                @endif
                @php
                    $logoCounter++;
                    $isName = ($logo['type'] ?? 'logo') === 'name';
                    $techName = !empty($logo['technique_id']) ? \App\Models\MarkingTechnique::find($logo['technique_id'])?->name : null;
                    $logoLabel = $isName ? 'Prenom/Pseudo' : 'Logo ' . $logoCounter;
                    $logoDesc = $isName
                        ? ucfirst($logo['name_size'] ?? 'petit') . ', ' . ($logo['name_lines'] ?? '1') . ' ligne(s)'
                        : ($logo['size'] ?? 'A4') . ', ' . (($logo['colors'] ?? '1') === 'quadri' ? 'Quadri' : ($logo['colors'] ?? '1') . ' couleur(s)');
                    $blockPrefix = count($allLogos) > 1 ? 'bloc-' . ($currentBlock + 1) . '/' : '';
                    $logoDir = 'orders/' . $order->reference . '/' . $blockPrefix . ($isName ? 'prenoms' : 'logo-' . $logoCounter);
                    $logoFiles = $disk->exists($logoDir) ? $disk->files($logoDir) : [];
                @endphp

                <div class="border rounded-xl p-4 {{ !empty($logoFiles) ? 'border-green-300 bg-green-50' : 'border-amber-200 bg-amber-50/20' }}">
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center gap-2">
                            @if(!empty($logoFiles))
                                <svg class="w-5 h-5 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            @endif
                            <div>
                                <h3 class="font-semibold text-sm {{ !empty($logoFiles) ? 'text-green-800' : '' }}">{{ $logoLabel }}</h3>
                                <p class="text-xs text-gray-500">{{ $logoDesc }}@if($techName) — {{ $techName }}@endif</p>
                            </div>
                        </div>
                        @if(!empty($logoFiles))
                            <span class="text-xs text-green-700 font-bold bg-green-200 px-2.5 py-1 rounded-full">OK</span>
                        @else
                            <span class="text-xs text-amber-600 font-medium bg-amber-100 px-2 py-0.5 rounded-full">En attente</span>
                        @endif
                    </div>

                    @if(!empty($logoFiles))
                        @foreach($logoFiles as $file)
                            <div class="flex items-center justify-between bg-white rounded-lg px-3 py-2 mb-1 border border-green-200 text-sm">
                                <div class="flex items-center gap-2 min-w-0">
                                    <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                    <span class="truncate text-xs text-green-800 font-medium">{{ basename($file) }}</span>
                                    <span class="text-[10px] text-gray-400">{{ number_format($disk->size($file) / 1024, 0) }} Ko</span>
                                </div>
                                <button wire:click="deleteLogoFile('{{ $file }}')" class="text-xs text-red-400 hover:text-red-600 ml-2">Modifier</button>
                            </div>
                        @endforeach
                    @else
                        @if($isName)
                            <p class="text-[11px] text-gray-400 mb-2">Tableur Excel ou CSV avec la liste des prenoms et tailles</p>
                        @else
                            <p class="text-[11px] text-gray-400 mb-2">
                                @if(in_array($logo['technique_id'] ?? 0, [2, 5, 11]))
                                    Vectoriel recommande : .ai, .eps, .pdf ou image HD (.png, .jpg)
                                @elseif(in_array($logo['technique_id'] ?? 0, [3, 6, 10]))
                                    Fichier sans fond : .png, .psd, .pdf, .ai, .eps
                                @else
                                    .ai, .eps, .pdf, .png, .jpg, .psd, .svg
                                @endif
                            </p>
                        @endif

                        <label class="flex flex-col items-center justify-center w-full h-16 border-2 border-dashed border-gray-300 rounded-lg cursor-pointer hover:border-bordeaux hover:bg-bordeaux-50/30 transition">
                            <span class="text-xs text-gray-500">Cliquez pour charger</span>
                            <input type="file" wire:model="currentFile" class="hidden"
                                   x-on:change="$wire.set('currentLogoIndex', {{ $li }})">
                        </label>

                        <div wire:loading wire:target="currentFile" class="mt-2 text-center text-xs text-bordeaux">
                            Chargement en cours...
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @else
        {{-- Fallback upload global --}}
        <form wire:submit="uploadFiles">
            <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-bordeaux-200 transition">
                <svg class="w-10 h-10 mx-auto text-gray-300 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/>
                </svg>
                <label class="cursor-pointer">
                    <span class="text-sm text-bordeaux font-semibold hover:underline">Choisir des fichiers</span>
                    <input type="file" wire:model="newFiles" multiple accept="image/*,.pdf,.ai,.eps,.psd,.svg,application/postscript,application/illustrator" class="hidden">
                </label>
            </div>
            @if($newFiles)
                <button type="submit" class="mt-3 w-full py-2.5 bg-bordeaux text-white font-semibold rounded-lg hover:bg-bordeaux-dark transition text-sm">
                    Envoyer {{ count($newFiles) }} fichier(s)
                </button>
            @endif
        </form>
    @endif

    @php
        // Compter les logos chargés vs total
        $totalLogos = count($allLogos ?? []);
        $loadedLogos = 0;
        foreach (($allLogos ?? []) as $idx => $l) {
            $bn = ($blockMap[$idx] ?? 0);
            $lnb = 0;
            for ($j = 0; $j <= $idx; $j++) { if (($blockMap[$j] ?? 0) === $bn) $lnb++; }
            $bp = $totalLogos > 1 ? 'bloc-' . ($bn + 1) . '/' : '';
            $sd = (($l['type'] ?? 'logo') === 'name') ? 'prenoms' : 'logo-' . $lnb;
            $ld = 'orders/' . $order->reference . '/' . $bp . $sd;
            if ($disk->exists($ld) && count($disk->files($ld)) > 0) $loadedLogos++;
        }
        $allLoaded = $totalLogos > 0 && $loadedLogos >= $totalLogos;
    @endphp

    @if($allLoaded)
        <div class="mt-5 bg-green-50 border border-green-300 rounded-xl p-4 text-center">
            <svg class="w-8 h-8 text-green-500 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            <p class="font-bold text-green-800">Tous vos fichiers ont ete envoyes !</p>
            <p class="text-xs text-green-600 mt-1">Notre equipe va preparer votre BAT. Vous recevrez un email quand il sera pret.</p>
            <a href="{{ route('account.orders.show', $order->reference) }}" class="inline-block mt-3 px-6 py-2.5 bg-bordeaux text-white font-semibold rounded-lg hover:bg-bordeaux-700 transition text-sm">
                Retour a ma commande
            </a>
        </div>
    @else
        <div class="mt-4 text-center">
            <p class="text-xs text-gray-400 mb-2">{{ $loadedLogos }}/{{ $totalLogos }} fichier(s) charge(s)</p>
            <a href="{{ route('account.orders.show', $order->reference) }}" class="text-xs text-gray-400 hover:text-gray-600">
                Passer — je chargerai mes fichiers plus tard
            </a>
        </div>
    @endif
</div>
