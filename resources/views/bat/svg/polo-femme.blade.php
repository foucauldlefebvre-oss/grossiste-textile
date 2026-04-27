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
    <linearGradient id="sideShadePF" x1="0" y1="0" x2="1" y2="0">
        <stop offset="0%" stop-color="rgba(0,0,0,0.12)"/><stop offset="15%" stop-color="rgba(0,0,0,0)"/>
        <stop offset="85%" stop-color="rgba(0,0,0,0)"/><stop offset="100%" stop-color="rgba(0,0,0,0.12)"/>
    </linearGradient>
    <marker id="arrow" markerWidth="8" markerHeight="6" refX="8" refY="3" orient="auto"><path d="M0,0 L8,3 L0,6" fill="{{ $borderColor }}"/></marker>
    <marker id="arrowRev" markerWidth="8" markerHeight="6" refX="0" refY="3" orient="auto"><path d="M8,0 L0,3 L8,6" fill="{{ $borderColor }}"/></marker>
</defs>

<ellipse cx="240" cy="495" rx="140" ry="10" fill="rgba(0,0,0,0.07)"/>

{{-- Sleeves (shorter, feminine) --}}
<path d="M160,112 L55,172 L73,222 L160,192 Z" fill="{{ $hexColor }}"/>
<path d="M320,112 L425,172 L407,222 L320,192 Z" fill="{{ $hexColor }}"/>

{{-- Body — fitted at waist --}}
<path d="M160,112 L156,248 Q152,316 166,360 L166,452 Q166,480 190,480 L290,480 Q314,480 314,452 L314,360 Q328,316 324,248 L320,112 Z"
      fill="{{ $hexColor }}"/>
<path d="M160,112 L156,248 Q152,316 166,360 L166,452 Q166,480 190,480 L290,480 Q314,480 314,452 L314,360 Q328,316 324,248 L320,112 Z"
      fill="url(#sideShadePF)"/>

{{-- Outline --}}
<path d="M160,112 L55,172 L73,222 L160,192 L156,248 Q152,316 166,360 L166,480 L314,480 L314,360 Q328,316 324,248 L320,192 L407,222 L425,172 L320,112"
      fill="none" stroke="rgba(0,0,0,0.2)" stroke-width="1.5"/>

{{-- Polo collar — pointed flaps --}}
<path d="M160,112 Q200,96 240,104 Q280,96 320,112" fill="{{ $hexColor }}"/>
<path d="M193,108 L200,74 L240,88 L280,74 L287,108" fill="{{ $hexColor }}" stroke="rgba(0,0,0,0.18)" stroke-width="1.2"/>
<line x1="205" y1="82" x2="198" y2="108" stroke="rgba(0,0,0,0.08)" stroke-width="1"/>
<line x1="275" y1="82" x2="282" y2="108" stroke="rgba(0,0,0,0.08)" stroke-width="1"/>

{{-- Button placket --}}
<line x1="240" y1="88" x2="240" y2="180" stroke="rgba(0,0,0,0.08)" stroke-width="1.5"/>
<circle cx="240" cy="115" r="2.5" fill="none" stroke="rgba(0,0,0,0.15)" stroke-width="1"/>
<circle cx="240" cy="135" r="2.5" fill="none" stroke="rgba(0,0,0,0.15)" stroke-width="1"/>
<circle cx="240" cy="155" r="2.5" fill="none" stroke="rgba(0,0,0,0.15)" stroke-width="1"/>

{{-- Stitch + waist folds --}}
<line x1="240" y1="180" x2="240" y2="475" stroke="rgba(0,0,0,0.04)" stroke-width="1" stroke-dasharray="8,6"/>
<line x1="202" y1="280" x2="206" y2="400" stroke="rgba(0,0,0,0.03)" stroke-width="1"/>
<line x1="278" y1="280" x2="274" y2="400" stroke="rgba(0,0,0,0.03)" stroke-width="1"/>

@include('bat.svg._zone', compact('zoneX','zoneY','zoneW','zoneH','borderColor','logoUrl','imgX','imgY','imgW','imgH','isDraggable','markingIndex','logoWidth','logoHeight','maxWidth','maxHeight','positionLabel'))
</svg>
