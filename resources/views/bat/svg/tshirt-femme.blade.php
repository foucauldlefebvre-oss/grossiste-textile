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
    <linearGradient id="sideShadeF" x1="0" y1="0" x2="1" y2="0">
        <stop offset="0%" stop-color="rgba(0,0,0,0.12)"/><stop offset="15%" stop-color="rgba(0,0,0,0)"/>
        <stop offset="85%" stop-color="rgba(0,0,0,0)"/><stop offset="100%" stop-color="rgba(0,0,0,0.12)"/>
    </linearGradient>
    <marker id="arrow" markerWidth="8" markerHeight="6" refX="8" refY="3" orient="auto"><path d="M0,0 L8,3 L0,6" fill="{{ $borderColor }}"/></marker>
    <marker id="arrowRev" markerWidth="8" markerHeight="6" refX="0" refY="3" orient="auto"><path d="M8,0 L0,3 L8,6" fill="{{ $borderColor }}"/></marker>
</defs>

<ellipse cx="240" cy="495" rx="135" ry="10" fill="rgba(0,0,0,0.07)"/>

{{-- Sleeves (shorter) --}}
<path d="M160,108 L55,168 L73,218 L160,188 Z" fill="{{ $hexColor }}"/>
<path d="M320,108 L425,168 L407,218 L320,188 Z" fill="{{ $hexColor }}"/>

{{-- Body — fitted at waist --}}
<path d="M160,108 L156,248 Q152,316 166,365 L166,455 Q166,482 190,482 L290,482 Q314,482 314,455 L314,365 Q328,316 324,248 L320,108 Z"
      fill="{{ $hexColor }}"/>
<path d="M160,108 L156,248 Q152,316 166,365 L166,455 Q166,482 190,482 L290,482 Q314,482 314,455 L314,365 Q328,316 324,248 L320,108 Z"
      fill="url(#sideShadeF)"/>

{{-- Outline --}}
<path d="M160,108 L55,168 L73,218 L160,188 L156,248 Q152,316 166,365 L166,455 Q166,482 190,482 L290,482 Q314,482 314,455 L314,365 Q328,316 324,248 L320,188 L407,218 L425,168 L320,108"
      fill="none" stroke="rgba(0,0,0,0.2)" stroke-width="1.5"/>

{{-- Neckline --}}
<path d="M160,108 Q200,86 240,95 Q280,86 320,108" fill="{{ $hexColor }}"/>
<path d="M160,108 Q200,86 240,95 Q280,86 320,108" fill="none" stroke="rgba(0,0,0,0.2)" stroke-width="1.5"/>
<path d="M195,103 Q240,120 285,103" fill="none" stroke="rgba(0,0,0,0.08)" stroke-width="1"/>

<line x1="240" y1="108" x2="240" y2="477" stroke="rgba(0,0,0,0.04)" stroke-width="1" stroke-dasharray="8,6"/>
<line x1="202" y1="280" x2="206" y2="400" stroke="rgba(0,0,0,0.03)" stroke-width="1"/>
<line x1="278" y1="280" x2="274" y2="400" stroke="rgba(0,0,0,0.03)" stroke-width="1"/>

@include('bat.svg._zone', compact('zoneX','zoneY','zoneW','zoneH','borderColor','logoUrl','imgX','imgY','imgW','imgH','isDraggable','markingIndex','logoWidth','logoHeight','maxWidth','maxHeight','positionLabel'))
</svg>
