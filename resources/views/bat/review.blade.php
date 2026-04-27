<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bon A Tirer — {{ $quote->reference }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body class="bg-gray-50 min-h-screen">

@php
    use App\Helpers\BatHelper;

    // Build group data: one entry per marking_group that has items
    $markings = $quote->markings ?? [];

    // Detect format: grouped [[...], [...]] vs flat [{...}, {......}]
    $firstVal = reset($markings);
    $isGrouped = is_array($firstVal) && (empty($firstVal) || !isset($firstVal['size']));

    $allItems = $quote->items;
    $itemsByGroup = $allItems->groupBy('marking_group');

    $groupData = collect();
    foreach ($itemsByGroup as $groupId => $items) {
        $firstItem = $items->first();
        $hexColor = $firstItem?->color?->hex_code ?? '#333333';
        $template = BatHelper::getTemplate($quote, (int) $groupId);

        // Logos for this group
        if ($isGrouped) {
            $logos = $markings[$groupId] ?? [];
            // If it's an array of logos (each has 'size'), use it; otherwise wrap
            if (!empty($logos) && isset($logos['size'])) {
                $logos = [$logos]; // single logo not wrapped
            }
        } else {
            // Flat format: all logos apply to every group
            $logos = $markings;
        }

        // Skip groups with no logos AND no items (empty active group)
        if (empty($logos) && $items->isEmpty()) continue;

        // Ensure at least one logo entry for display
        if (empty($logos)) {
            $logos = [['size' => 'A7', 'zone' => '', 'colors' => '1']];
        }

        $groupData[$groupId] = [
            'items'     => $items,
            'firstItem' => $firstItem,
            'hexColor'  => $hexColor,
            'isDark'    => BatHelper::isDark($hexColor),
            'template'  => $template,
            'logos'     => array_values($logos),
        ];
    }

    // First group defaults
    $activeGroup = $groupData->first();
    $activeLogo = $activeGroup['logos'][0] ?? [];
    $activeFormat = $activeLogo['size'] ?? 'A7';
    $activeDims = BatHelper::formatDimensions($activeFormat);

    $logoUrl = $quote->bat_logo_path
        ? Storage::url($quote->bat_logo_path)
        : null;
@endphp

{{-- Header --}}
<header class="bg-white border-b">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex items-center justify-between">
        <span class="text-lg font-bold text-gray-900">marquage-textile.fr</span>
        <div class="text-right">
            <h1 class="text-lg font-semibold text-gray-900">Bon A Tirer — {{ $quote->reference }}</h1>
        </div>
    </div>
</header>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    {{-- Group tabs (only if multiple groups) --}}
    @if($groupData->count() > 1)
    <div class="flex gap-2 mb-6 flex-wrap">
        @foreach($groupData as $gid => $gdata)
            <button onclick="showGroup({{ $gid }})"
                    id="tab-{{ $gid }}"
                    class="group-tab px-4 py-2 rounded-lg text-sm font-medium border-2 transition
                           {{ $loop->first ? 'border-blue-500 bg-blue-50 text-blue-700' : 'border-gray-200 bg-white text-gray-600 hover:border-gray-300' }}">
                <span class="inline-block w-3 h-3 rounded-full mr-1.5 align-middle border border-gray-200"
                      style="background-color: {{ $gdata['hexColor'] }}"></span>
                {{ $gdata['firstItem']?->product?->name ?? 'Groupe '.($gid+1) }}
                — {{ $gdata['firstItem']?->color?->name ?? '' }}
            </button>
        @endforeach
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">

        {{-- ═══ LEFT COLUMN — SVG Previews ═══ --}}
        <div class="lg:sticky lg:top-8 lg:self-start">
            @foreach($groupData as $gid => $gdata)
            <div class="bat-group {{ $loop->first ? '' : 'hidden' }}" id="group-{{ $gid }}" data-group="{{ $gid }}">
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h2 class="text-lg font-semibold mb-2">
                        {{ $gdata['firstItem']?->product?->name ?? 'Apercu' }}
                    </h2>
                    <p class="text-sm text-gray-500 mb-4">
                        @if($gdata['firstItem']?->color)
                            <span class="inline-block w-3 h-3 rounded-full mr-1 align-middle border border-gray-200"
                                  style="background-color: {{ $gdata['hexColor'] }}"></span>
                            {{ $gdata['firstItem']->color->name }}
                        @endif
                        @if($gdata['firstItem']?->technique)
                            &middot; {{ $gdata['firstItem']->technique->name }}
                        @endif
                    </p>

                    {{-- Logo tabs within group --}}
                    @if(count($gdata['logos']) > 1)
                    <div class="flex gap-1.5 mb-3">
                        @foreach($gdata['logos'] as $li => $logo)
                            <button onclick="showLogo({{ $gid }}, {{ $li }})"
                                    id="logo-tab-{{ $gid }}-{{ $li }}"
                                    class="logo-tab-{{ $gid }} px-3 py-1 rounded text-xs font-medium border transition
                                           {{ $li === 0 ? 'border-blue-400 bg-blue-50 text-blue-700' : 'border-gray-200 text-gray-500 hover:border-gray-300' }}">
                                Logo {{ $li + 1 }}
                                @if(!empty($logo['zone']))
                                    — {{ BatHelper::zoneLabel($logo['zone']) }}
                                @endif
                            </button>
                        @endforeach
                    </div>
                    @endif

                    {{-- SVG for each logo --}}
                    @foreach($gdata['logos'] as $li => $logo)
                        @php
                            $logoDims = BatHelper::formatDimensions($logo['size'] ?? 'A7');
                            $logoZone = $logo['zone'] ?? '';
                            $logoPos = BatHelper::zoneLabel($logoZone);
                        @endphp
                        <div class="bat-logo-view bat-logo-{{ $gid }} border rounded-lg p-4 bg-gray-50 {{ $li === 0 ? '' : 'hidden' }}"
                             id="logo-{{ $gid }}-{{ $li }}">
                            @include('bat.svg.' . $gdata['template'], [
                                'hexColor'      => $gdata['hexColor'],
                                'isDark'        => $gdata['isDark'],
                                'logoUrl'       => $logoUrl,
                                'logoX'         => $quote->bat_logo_x ?? 0,
                                'logoY'         => $quote->bat_logo_y ?? 0,
                                'logoWidth'     => $quote->bat_logo_width ?? $logoDims['width'],
                                'logoHeight'    => $quote->bat_logo_height ?? $logoDims['height'],
                                'maxWidth'      => $logoDims['width'],
                                'maxHeight'     => $logoDims['height'],
                                'positionLabel' => $logoPos,
                                'isDraggable'   => true,
                                'markingIndex'  => $li,
                            ])
                        </div>
                    @endforeach
                </div>
            </div>
            @endforeach
        </div>

        {{-- ═══ RIGHT COLUMN — Actions ═══ --}}
        <div class="space-y-6">

            {{-- SECTION 1: Upload logo --}}
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-semibold mb-1">Votre logo</h3>
                <p class="text-sm text-gray-500 mb-4">
                    Formats acceptes : PNG, JPEG, PDF, AI, EPS, PSD — Taille max : 10 Mo
                </p>
                <div id="drop-zone"
                     class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center cursor-pointer hover:border-blue-400 hover:bg-blue-50/50 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 mx-auto mb-3 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                    </svg>
                    <p class="text-sm text-gray-600 font-medium">Cliquez ou glissez votre logo</p>
                    <p id="file-name" class="text-xs text-gray-400 mt-1 hidden"></p>
                    <input type="file" id="logo-upload" accept=".png,.jpg,.jpeg,.pdf,.ai,.eps,.psd" class="hidden">
                </div>
                <button id="btn-upload" disabled
                        class="mt-3 w-full px-4 py-2.5 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition disabled:opacity-50 disabled:cursor-not-allowed">
                    Envoyer le logo
                </button>
                <div id="upload-feedback" class="mt-2 text-sm hidden"></div>
            </div>

            {{-- SECTION 2: Dimensions --}}
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-semibold mb-1">Dimensions du logo</h3>
                <p class="text-sm text-gray-500 mb-4">
                    Format : {{ $activeFormat }}
                    (max {{ $activeDims['width'] }} &times; {{ $activeDims['height'] }} cm)
                </p>
                <div class="space-y-4">
                    <div>
                        <label class="flex justify-between text-sm font-medium text-gray-700 mb-1">
                            <span>Largeur</span>
                            <span id="width-value" class="text-blue-600">{{ number_format($quote->bat_logo_width ?? $activeDims['width'], 1, ',', '') }} cm</span>
                        </label>
                        <input type="range" id="logo-width"
                               min="1" max="{{ $activeDims['width'] }}" step="0.1"
                               value="{{ $quote->bat_logo_width ?? $activeDims['width'] }}"
                               class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-blue-600">
                    </div>
                    <div>
                        <label class="flex justify-between text-sm font-medium text-gray-700 mb-1">
                            <span>Hauteur</span>
                            <span id="height-value" class="text-blue-600">{{ number_format($quote->bat_logo_height ?? $activeDims['height'], 1, ',', '') }} cm</span>
                        </label>
                        <input type="range" id="logo-height"
                               min="1" max="{{ $activeDims['height'] }}" step="0.1"
                               value="{{ $quote->bat_logo_height ?? $activeDims['height'] }}"
                               class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-blue-600">
                    </div>
                </div>
                <div id="notif-proportions" class="mt-3 p-3 bg-amber-50 border border-amber-200 rounded-lg text-sm text-amber-800 hidden">
                    Les dimensions atteignent le maximum du format.
                </div>
                <button id="btn-save-dims"
                        class="mt-4 w-full px-4 py-2.5 bg-gray-800 text-white font-semibold rounded-lg hover:bg-gray-900 transition">
                    Enregistrer la position
                </button>
                <div id="dims-feedback" class="mt-2 text-sm hidden"></div>
            </div>

            {{-- SECTION 3: Decision --}}
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-semibold mb-1">Votre decision</h3>
                <p class="text-sm text-gray-500 mb-4">
                    Verifiez le positionnement et les dimensions de votre logo avant de valider.
                    Le BAT approuve servira de reference pour la production.
                </p>
                <textarea id="client-comment" rows="3"
                          class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                          placeholder="Commentaire ou demande de modification..."></textarea>
                <div class="flex gap-3 mt-4">
                    <button id="btn-approve"
                            class="flex-1 px-6 py-3 bg-green-600 text-white font-semibold rounded-lg hover:bg-green-700 transition">
                        Approuver le BAT
                    </button>
                    <button id="btn-revision"
                            class="flex-1 px-6 py-3 bg-amber-500 text-white font-semibold rounded-lg hover:bg-amber-600 transition">
                        Demander une modification
                    </button>
                </div>
            </div>

            {{-- SECTION 4: Order summary --}}
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <button id="toggle-summary" class="w-full px-6 py-4 flex items-center justify-between text-left hover:bg-gray-50 transition">
                    <span class="font-semibold">Voir le detail de la commande</span>
                    <svg id="summary-chevron" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
                <div id="summary-content" class="hidden border-t">
                    <div class="px-6 py-4 space-y-3 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-500">Reference</span>
                            <span class="font-medium">{{ $quote->reference }}</span>
                        </div>
                        @foreach($groupData as $gid => $gdata)
                        <div class="flex justify-between">
                            <span class="text-gray-500">Groupe {{ $gid + 1 }}</span>
                            <span class="font-medium">
                                {{ $gdata['firstItem']?->product?->name ?? '—' }}
                                — {{ $gdata['firstItem']?->color?->name ?? '—' }}
                                @if($gdata['firstItem']?->technique)
                                    ({{ $gdata['firstItem']->technique->name }})
                                @endif
                            </span>
                        </div>
                        @endforeach
                        @if($quote->total_ttc)
                        <div class="flex justify-between border-t pt-3">
                            <span class="font-semibold">Total TTC</span>
                            <span class="font-bold text-blue-600">{{ number_format($quote->total_ttc, 2, ',', ' ') }} &euro;</span>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    const SCALE = 8;
    const token = @json($quote->bat_token);
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    const saveUrl = '/bat/' + token + '/save';
    const decideUrl = '/bat/' + token + '/decide';
    const confirmedUrl = '/bat/' + token + '/confirmed';

    // ─── Group switching ─────────────────────────
    window.showGroup = function(groupId) {
        document.querySelectorAll('.bat-group').forEach(el => el.classList.add('hidden'));
        document.getElementById('group-' + groupId)?.classList.remove('hidden');
        document.querySelectorAll('.group-tab').forEach(el => {
            el.classList.remove('border-blue-500', 'bg-blue-50', 'text-blue-700');
            el.classList.add('border-gray-200', 'bg-white', 'text-gray-600');
        });
        const tab = document.getElementById('tab-' + groupId);
        if (tab) {
            tab.classList.remove('border-gray-200', 'bg-white', 'text-gray-600');
            tab.classList.add('border-blue-500', 'bg-blue-50', 'text-blue-700');
        }
    };

    // ─── Logo switching within a group ────────────
    window.showLogo = function(groupId, logoIndex) {
        document.querySelectorAll('.bat-logo-' + groupId).forEach(el => el.classList.add('hidden'));
        document.getElementById('logo-' + groupId + '-' + logoIndex)?.classList.remove('hidden');
        document.querySelectorAll('.logo-tab-' + groupId).forEach(el => {
            el.classList.remove('border-blue-400', 'bg-blue-50', 'text-blue-700');
            el.classList.add('border-gray-200', 'text-gray-500');
        });
        const tab = document.getElementById('logo-tab-' + groupId + '-' + logoIndex);
        if (tab) {
            tab.classList.remove('border-gray-200', 'text-gray-500');
            tab.classList.add('border-blue-400', 'bg-blue-50', 'text-blue-700');
        }
    };

    // ─── Upload ──────────────────────────────────
    const dropZone = document.getElementById('drop-zone');
    const fileInput = document.getElementById('logo-upload');
    const fileName = document.getElementById('file-name');
    const btnUpload = document.getElementById('btn-upload');
    const uploadFeedback = document.getElementById('upload-feedback');

    dropZone.addEventListener('click', () => fileInput.click());
    dropZone.addEventListener('dragover', (e) => { e.preventDefault(); dropZone.classList.add('border-blue-400', 'bg-blue-50'); });
    dropZone.addEventListener('dragleave', () => { dropZone.classList.remove('border-blue-400', 'bg-blue-50'); });
    dropZone.addEventListener('drop', (e) => {
        e.preventDefault();
        dropZone.classList.remove('border-blue-400', 'bg-blue-50');
        if (e.dataTransfer.files.length) { fileInput.files = e.dataTransfer.files; fileInput.dispatchEvent(new Event('change')); }
    });
    fileInput.addEventListener('change', () => {
        if (fileInput.files.length) { fileName.textContent = fileInput.files[0].name; fileName.classList.remove('hidden'); btnUpload.disabled = false; }
    });

    btnUpload.addEventListener('click', async () => {
        if (!fileInput.files.length) return;
        btnUpload.disabled = true; btnUpload.textContent = 'Envoi en cours...';
        const fd = new FormData(); fd.append('logo', fileInput.files[0]);
        try {
            const res = await fetch(saveUrl, { method: 'POST', headers: { 'X-CSRF-TOKEN': csrfToken }, body: fd });
            const data = await res.json();
            uploadFeedback.classList.remove('hidden', 'text-red-600', 'text-green-600');
            if (data.success && data.logo_url) {
                uploadFeedback.classList.add('text-green-600');
                uploadFeedback.textContent = 'Logo envoye avec succes !';
                // Update all logo images in all SVGs
                document.querySelectorAll('[id^="logo-img-"]').forEach(img => img.setAttribute('href', data.logo_url));
                if (!document.querySelector('[id^="logo-img-"]')) setTimeout(() => location.reload(), 800);
            } else {
                uploadFeedback.classList.add('text-red-600');
                uploadFeedback.textContent = 'Erreur lors de l\'envoi.';
            }
        } catch (err) {
            uploadFeedback.classList.remove('hidden');
            uploadFeedback.classList.add('text-red-600');
            uploadFeedback.textContent = 'Erreur reseau.';
        }
        btnUpload.textContent = 'Envoyer le logo'; btnUpload.disabled = false;
    });

    // ─── Dimension sliders ───────────────────────
    const sliderW = document.getElementById('logo-width');
    const sliderH = document.getElementById('logo-height');
    const valW = document.getElementById('width-value');
    const valH = document.getElementById('height-value');
    const notifProp = document.getElementById('notif-proportions');
    const dimsFeedback = document.getElementById('dims-feedback');
    let saveTimer = null;

    function updateZone() {
        const w = parseFloat(sliderW.value);
        const h = parseFloat(sliderH.value);
        valW.textContent = w.toFixed(1).replace('.', ',') + ' cm';
        valH.textContent = h.toFixed(1).replace('.', ',') + ' cm';
        const maxW = parseFloat(sliderW.max);
        const maxH = parseFloat(sliderH.max);
        notifProp.classList.toggle('hidden', !(w >= maxW - 0.1 || h >= maxH - 0.1));

        // Update all visible zone rects and logo images
        document.querySelectorAll('[id^="logo-zone-"]').forEach(zone => {
            zone.setAttribute('width', w * SCALE);
            zone.setAttribute('height', h * SCALE);
        });
        document.querySelectorAll('[id^="logo-img-"]').forEach(img => {
            img.setAttribute('width', w * SCALE);
            img.setAttribute('height', h * SCALE);
        });
        document.querySelectorAll('[id^="dim-w-"]').forEach(el => { el.textContent = w.toFixed(1) + ' cm'; });
        document.querySelectorAll('[id^="dim-h-"]').forEach(el => { el.textContent = h.toFixed(1) + ' cm'; });
    }

    sliderW.addEventListener('input', updateZone);
    sliderH.addEventListener('input', updateZone);

    function debouncedSave() { clearTimeout(saveTimer); saveTimer = setTimeout(() => savePosition(), 800); }
    sliderW.addEventListener('change', debouncedSave);
    sliderH.addEventListener('change', debouncedSave);

    async function savePosition(logoX, logoY) {
        const body = { logo_width: parseFloat(sliderW.value), logo_height: parseFloat(sliderH.value), logo_x: logoX ?? 0, logo_y: logoY ?? 0 };
        try {
            const res = await fetch(saveUrl, { method: 'POST', headers: { 'X-CSRF-TOKEN': csrfToken, 'Content-Type': 'application/json', 'Accept': 'application/json' }, body: JSON.stringify(body) });
            const data = await res.json();
            dimsFeedback.classList.remove('hidden', 'text-red-600', 'text-green-600');
            if (data.success) { dimsFeedback.classList.add('text-green-600'); dimsFeedback.textContent = 'Position enregistree.'; setTimeout(() => dimsFeedback.classList.add('hidden'), 2000); }
        } catch (err) { dimsFeedback.classList.remove('hidden'); dimsFeedback.classList.add('text-red-600'); dimsFeedback.textContent = 'Erreur reseau.'; }
    }

    document.getElementById('btn-save-dims').addEventListener('click', () => savePosition());

    // ─── Drag & drop logo in SVG ─────────────────
    document.querySelectorAll('[id^="bat-svg"]').forEach(svg => {
        const imgs = svg.querySelectorAll('[id^="logo-img-"]');
        imgs.forEach(logoImg => {
            let dragging = false, startPt = null, startX = 0, startY = 0;
            function svgPoint(evt) { const pt = svg.createSVGPoint(); pt.x = evt.clientX; pt.y = evt.clientY; return pt.matrixTransform(svg.getScreenCTM().inverse()); }
            logoImg.addEventListener('mousedown', (e) => { e.preventDefault(); dragging = true; startPt = svgPoint(e); startX = parseFloat(logoImg.getAttribute('x')); startY = parseFloat(logoImg.getAttribute('y')); logoImg.style.cursor = 'grabbing'; });
            svg.addEventListener('mousemove', (e) => {
                if (!dragging) return;
                const pt = svgPoint(e);
                const idx = logoImg.id.split('-').pop();
                const zone = svg.querySelector('[id^="logo-zone-"]');
                if (!zone) return;
                const zoneX = parseFloat(zone.getAttribute('x')), zoneY = parseFloat(zone.getAttribute('y'));
                const zoneW = parseFloat(zone.getAttribute('width')), zoneH = parseFloat(zone.getAttribute('height'));
                const imgW = parseFloat(logoImg.getAttribute('width')), imgH = parseFloat(logoImg.getAttribute('height'));
                let newX = Math.max(zoneX, Math.min(startX + (pt.x - startPt.x), zoneX + zoneW - imgW));
                let newY = Math.max(zoneY, Math.min(startY + (pt.y - startPt.y), zoneY + zoneH - imgH));
                logoImg.setAttribute('x', newX); logoImg.setAttribute('y', newY);
            });
            function endDrag() {
                if (!dragging) return; dragging = false; logoImg.style.cursor = 'grab';
                const zone = svg.querySelector('[id^="logo-zone-"]');
                if (!zone) return;
                const cmX = (parseFloat(logoImg.getAttribute('x')) - parseFloat(zone.getAttribute('x'))) / SCALE;
                const cmY = (parseFloat(logoImg.getAttribute('y')) - parseFloat(zone.getAttribute('y'))) / SCALE;
                savePosition(cmX, cmY);
            }
            svg.addEventListener('mouseup', endDrag); svg.addEventListener('mouseleave', endDrag);
        });
    });

    // ─── Decision ────────────────────────────────
    const comment = document.getElementById('client-comment');
    document.getElementById('btn-approve').addEventListener('click', async () => { if (!confirm('Etes-vous sur ? Cette action lancera la production.')) return; await submitDecision('approved'); });
    document.getElementById('btn-revision').addEventListener('click', async () => { if (!comment.value.trim()) { alert('Veuillez indiquer ce que vous souhaitez modifier.'); comment.focus(); return; } await submitDecision('revision_requested'); });

    async function submitDecision(decision) {
        try {
            const res = await fetch(decideUrl, { method: 'POST', headers: { 'X-CSRF-TOKEN': csrfToken, 'Content-Type': 'application/json', 'Accept': 'application/json' }, body: JSON.stringify({ decision, comment: comment.value.trim() || null }) });
            if (res.redirected) { window.location.href = res.url; } else { window.location.href = confirmedUrl; }
        } catch (err) { alert('Erreur reseau. Veuillez reessayer.'); }
    }

    // ─── Accordion ───────────────────────────────
    document.getElementById('toggle-summary').addEventListener('click', () => { document.getElementById('summary-content').classList.toggle('hidden'); document.getElementById('summary-chevron').classList.toggle('rotate-180'); });
})();
</script>
</body>
</html>
