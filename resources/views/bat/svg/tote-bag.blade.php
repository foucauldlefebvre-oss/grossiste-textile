@php
    use App\Helpers\BatHelper;
    $zoneW = $maxWidth * 8; $zoneH = $maxHeight * 8;
    $zoneX = 240 - $zoneW / 2; $zoneY = 230;
    $imgW = ($logoWidth ?? $maxWidth) * 8; $imgH = ($logoHeight ?? $maxHeight) * 8;
    $imgX = $zoneX + ($logoX ?? 0) * 8; $imgY = $zoneY + ($logoY ?? 0) * 8;
    $borderColor = $isDark ? '#ffffff' : '#333333';
@endphp
<svg id="{{ $isDraggable ? 'bat-svg' : '' }}"
     viewBox="0 0 480 560" xmlns="http://www.w3.org/2000/svg" class="w-full h-auto"
     @if($isDraggable) data-max-width="{{ $maxWidth }}" data-max-height="{{ $maxHeight }}" data-zone-x="{{ $zoneX }}" data-zone-y="{{ $zoneY }}" data-index="{{ $markingIndex }}" @endif>
<defs>
    <linearGradient id="bagGrad" x1="0" y1="0" x2="1" y2="0">
        <stop offset="0%" stop-color="rgba(0,0,0,0.08)"/><stop offset="10%" stop-color="rgba(0,0,0,0)"/>
        <stop offset="90%" stop-color="rgba(0,0,0,0)"/><stop offset="100%" stop-color="rgba(0,0,0,0.08)"/>
    </linearGradient>
    <marker id="arrow" markerWidth="8" markerHeight="6" refX="8" refY="3" orient="auto"><path d="M0,0 L8,3 L0,6" fill="{{ $borderColor }}"/></marker>
    <marker id="arrowRev" markerWidth="8" markerHeight="6" refX="0" refY="3" orient="auto"><path d="M8,0 L0,3 L8,6" fill="{{ $borderColor }}"/></marker>
</defs>

{{-- Handles --}}
<path d="M175,130 Q175,50 210,50 L210,130" fill="none" stroke="{{ $hexColor }}" stroke-width="6"/>
<path d="M175,130 Q175,50 210,50 L210,130" fill="none" stroke="rgba(0,0,0,0.12)" stroke-width="1"/>
<path d="M270,130 Q270,50 305,50 L305,130" fill="none" stroke="{{ $hexColor }}" stroke-width="6"/>
<path d="M270,130 Q270,50 305,50 L305,130" fill="none" stroke="rgba(0,0,0,0.12)" stroke-width="1"/>

{{-- Body --}}
<rect x="115" y="130" width="250" height="340" rx="4" fill="{{ $hexColor }}"/>
<rect x="115" y="130" width="250" height="340" rx="4" fill="url(#bagGrad)"/>
<rect x="115" y="130" width="250" height="340" rx="4" fill="none" stroke="rgba(0,0,0,0.18)" stroke-width="1.5"/>

{{-- Stitching --}}
<line x1="118" y1="145" x2="362" y2="145" stroke="rgba(0,0,0,0.05)" stroke-width="1" stroke-dasharray="4,3"/>
<line x1="118" y1="465" x2="362" y2="465" stroke="rgba(0,0,0,0.05)" stroke-width="1"/>
<line x1="200" y1="150" x2="200" y2="460" stroke="rgba(0,0,0,0.02)" stroke-width="1"/>
<line x1="280" y1="150" x2="280" y2="460" stroke="rgba(0,0,0,0.02)" stroke-width="1"/>

@include('bat.svg._zone', [
    'zoneX' => $zoneX, 'zoneY' => $zoneY, 'zoneW' => $zoneW, 'zoneH' => $zoneH,
    'borderColor' => $borderColor, 'logoUrl' => $logoUrl,
    'imgX' => $imgX, 'imgY' => $imgY, 'imgW' => $imgW, 'imgH' => $imgH,
    'isDraggable' => $isDraggable, 'markingIndex' => $markingIndex,
    'logoWidth' => $logoWidth, 'logoHeight' => $logoHeight,
    'maxWidth' => $maxWidth, 'maxHeight' => $maxHeight,
    'positionLabel' => $positionLabel,
])
</svg>
