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
    <linearGradient id="sideShadeSR" x1="0" y1="0" x2="1" y2="0">
        <stop offset="0%" stop-color="rgba(0,0,0,0.12)"/><stop offset="15%" stop-color="rgba(0,0,0,0)"/>
        <stop offset="85%" stop-color="rgba(0,0,0,0)"/><stop offset="100%" stop-color="rgba(0,0,0,0.12)"/>
    </linearGradient>
    <marker id="arrow" markerWidth="8" markerHeight="6" refX="8" refY="3" orient="auto"><path d="M0,0 L8,3 L0,6" fill="{{ $borderColor }}"/></marker>
    <marker id="arrowRev" markerWidth="8" markerHeight="6" refX="0" refY="3" orient="auto"><path d="M8,0 L0,3 L8,6" fill="{{ $borderColor }}"/></marker>
</defs>

<ellipse cx="240" cy="515" rx="148" ry="10" fill="rgba(0,0,0,0.07)"/>

{{-- Left sleeve --}}
<path d="M150,108 L32,180 L54,248 L150,205 Z" fill="{{ $hexColor }}"/>
{{-- Cuff --}}
<rect x="32" y="236" width="28" height="12" rx="2" fill="{{ $hexColor }}" stroke="rgba(0,0,0,0.08)" stroke-width="1"/>

{{-- Right sleeve --}}
<path d="M330,108 L448,180 L426,248 L330,205 Z" fill="{{ $hexColor }}"/>
<rect x="420" y="236" width="28" height="12" rx="2" fill="{{ $hexColor }}" stroke="rgba(0,0,0,0.08)" stroke-width="1"/>

{{-- Body --}}
<path d="M150,108 L150,478 Q150,505 178,505 L302,505 Q330,505 330,478 L330,108 Z" fill="{{ $hexColor }}"/>
<path d="M150,108 L150,478 Q150,505 178,505 L302,505 Q330,505 330,478 L330,108 Z" fill="url(#sideShadeSR)"/>

{{-- Bottom ribbing --}}
<rect x="150" y="488" width="180" height="17" rx="3" fill="{{ $hexColor }}" stroke="rgba(0,0,0,0.06)" stroke-width="1"/>
<line x1="152" y1="494" x2="328" y2="494" stroke="rgba(0,0,0,0.04)" stroke-width="0.5"/>
<line x1="152" y1="499" x2="328" y2="499" stroke="rgba(0,0,0,0.04)" stroke-width="0.5"/>

{{-- Ribbed neckline (thicker) --}}
<path d="M150,108 Q200,86 240,95 Q280,86 330,108" fill="{{ $hexColor }}"/>
<path d="M150,108 Q200,86 240,95 Q280,86 330,108" fill="none" stroke="rgba(0,0,0,0.2)" stroke-width="2"/>
<path d="M162,104 Q200,90 240,96 Q280,90 318,104" fill="none" stroke="rgba(0,0,0,0.08)" stroke-width="1.5"/>

{{-- Outline --}}
<path d="M150,108 L32,180 L54,248 L150,205 L150,505 L330,505 L330,205 L426,248 L448,180 L330,108"
      fill="none" stroke="rgba(0,0,0,0.18)" stroke-width="1.5"/>

{{-- Stitch --}}
<line x1="240" y1="108" x2="240" y2="483" stroke="rgba(0,0,0,0.04)" stroke-width="1" stroke-dasharray="8,6"/>
<line x1="198" y1="200" x2="202" y2="440" stroke="rgba(0,0,0,0.03)" stroke-width="1"/>
<line x1="282" y1="200" x2="278" y2="440" stroke="rgba(0,0,0,0.03)" stroke-width="1"/>

@include('bat.svg._zone', compact('zoneX','zoneY','zoneW','zoneH','borderColor','logoUrl','imgX','imgY','imgW','imgH','isDraggable','markingIndex','logoWidth','logoHeight','maxWidth','maxHeight','positionLabel'))
</svg>
