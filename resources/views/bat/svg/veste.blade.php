@php
    use App\Helpers\BatHelper;
    $pos = BatHelper::zonePosition($positionLabel);
    $zoneW = $maxWidth * 8; $zoneH = $maxHeight * 8;
    $zoneX = $pos['x']; $zoneY = $pos['y'];
    $imgW = ($logoWidth ?? $maxWidth) * 8; $imgH = ($logoHeight ?? $maxHeight) * 8;
    $imgX = $zoneX + ($logoX ?? 0) * 8; $imgY = $zoneY + ($logoY ?? 0) * 8;
    $borderColor = $isDark ? '#ffffff' : '#333333';
@endphp
<svg id="{{ $isDraggable ? 'bat-svg' : '' }}"
     viewBox="0 0 480 560" xmlns="http://www.w3.org/2000/svg" class="w-full h-auto"
     @if($isDraggable) data-max-width="{{ $maxWidth }}" data-max-height="{{ $maxHeight }}" data-zone-x="{{ $zoneX }}" data-zone-y="{{ $zoneY }}" data-index="{{ $markingIndex }}" @endif>
<defs>
    <linearGradient id="sideShadeV" x1="0" y1="0" x2="1" y2="0">
        <stop offset="0%" stop-color="rgba(0,0,0,0.10)"/><stop offset="12%" stop-color="rgba(0,0,0,0)"/>
        <stop offset="88%" stop-color="rgba(0,0,0,0)"/><stop offset="100%" stop-color="rgba(0,0,0,0.10)"/>
    </linearGradient>
    <marker id="arrow" markerWidth="8" markerHeight="6" refX="8" refY="3" orient="auto"><path d="M0,0 L8,3 L0,6" fill="{{ $borderColor }}"/></marker>
    <marker id="arrowRev" markerWidth="8" markerHeight="6" refX="0" refY="3" orient="auto"><path d="M8,0 L0,3 L8,6" fill="{{ $borderColor }}"/></marker>
</defs>

<ellipse cx="240" cy="518" rx="150" ry="10" fill="rgba(0,0,0,0.07)"/>

{{-- Sleeves --}}
<path d="M145,108 L25,182 L48,260 L145,212 Z" fill="{{ $hexColor }}"/>
<path d="M335,108 L455,182 L432,260 L335,212 Z" fill="{{ $hexColor }}"/>

{{-- Body --}}
<path d="M145,108 L145,490 Q145,510 172,510 L308,510 Q335,510 335,490 L335,108 Z" fill="{{ $hexColor }}"/>
<path d="M145,108 L145,490 Q145,510 172,510 L308,510 Q335,510 335,490 L335,108 Z" fill="url(#sideShadeV)"/>

{{-- Stand collar --}}
<path d="M180,108 Q200,82 240,76 Q280,82 300,108" fill="{{ $hexColor }}" stroke="rgba(0,0,0,0.18)" stroke-width="1.5"/>

{{-- Central zip --}}
<line x1="240" y1="78" x2="240" y2="510" stroke="rgba(0,0,0,0.15)" stroke-width="2"/>
<line x1="237" y1="78" x2="237" y2="510" stroke="rgba(0,0,0,0.06)" stroke-width="0.5" stroke-dasharray="3,3"/>
<line x1="243" y1="78" x2="243" y2="510" stroke="rgba(0,0,0,0.06)" stroke-width="0.5" stroke-dasharray="3,3"/>
<rect x="236" y="125" width="8" height="12" rx="2" fill="rgba(0,0,0,0.2)"/>

{{-- Chest pockets --}}
<path d="M168,200 L168,260 L225,260 L225,200" fill="none" stroke="rgba(0,0,0,0.1)" stroke-width="1"/>
<path d="M255,200 L255,260 L312,260 L312,200" fill="none" stroke="rgba(0,0,0,0.1)" stroke-width="1"/>

{{-- Outline --}}
<path d="M145,108 L25,182 L48,260 L145,212 L145,510 L335,510 L335,212 L432,260 L455,182 L335,108"
      fill="none" stroke="rgba(0,0,0,0.18)" stroke-width="1.5"/>

@include('bat.svg._zone', compact('zoneX','zoneY','zoneW','zoneH','borderColor','logoUrl','imgX','imgY','imgW','imgH','isDraggable','markingIndex','logoWidth','logoHeight','maxWidth','maxHeight','positionLabel'))
</svg>
