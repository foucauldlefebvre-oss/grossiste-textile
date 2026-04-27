@php
    use App\Helpers\BatHelper;
    $pos = BatHelper::zonePosition($positionLabel);
    $zoneW = $maxWidth * 8; $zoneH = $maxHeight * 8;
    $zoneX = $pos['x']; $zoneY = $pos['y'];
    $imgW = ($logoWidth ?? $maxWidth) * 8;
    $imgH = ($logoHeight ?? $maxHeight) * 8;
    $imgX = $zoneX + ($logoX ?? 0) * 8;
    $imgY = $zoneY + ($logoY ?? 0) * 8;
    $borderColor = $isDark ? '#ffffff' : '#333333';
@endphp
<svg id="{{ $isDraggable ? 'bat-svg' : '' }}"
     viewBox="0 0 480 560" xmlns="http://www.w3.org/2000/svg" class="w-full h-auto"
     @if($isDraggable) data-max-width="{{ $maxWidth }}" data-max-height="{{ $maxHeight }}" data-zone-x="{{ $zoneX }}" data-zone-y="{{ $zoneY }}" data-index="{{ $markingIndex }}" @endif>
<defs>
    <linearGradient id="sideShade" x1="0" y1="0" x2="1" y2="0">
        <stop offset="0%" stop-color="rgba(0,0,0,0.12)"/><stop offset="15%" stop-color="rgba(0,0,0,0)"/>
        <stop offset="85%" stop-color="rgba(0,0,0,0)"/><stop offset="100%" stop-color="rgba(0,0,0,0.12)"/>
    </linearGradient>
    <linearGradient id="slL" x1="0" y1="0" x2="1" y2="0.3">
        <stop offset="0%" stop-color="rgba(0,0,0,0.15)"/><stop offset="100%" stop-color="rgba(0,0,0,0)"/>
    </linearGradient>
    <linearGradient id="slR" x1="1" y1="0" x2="0" y2="0.3">
        <stop offset="0%" stop-color="rgba(0,0,0,0.15)"/><stop offset="100%" stop-color="rgba(0,0,0,0)"/>
    </linearGradient>
    <marker id="arrow" markerWidth="8" markerHeight="6" refX="8" refY="3" orient="auto"><path d="M0,0 L8,3 L0,6" fill="{{ $borderColor }}"/></marker>
    <marker id="arrowRev" markerWidth="8" markerHeight="6" refX="0" refY="3" orient="auto"><path d="M8,0 L0,3 L8,6" fill="{{ $borderColor }}"/></marker>
</defs>

{{-- Shadow --}}
<ellipse cx="240" cy="500" rx="145" ry="10" fill="rgba(0,0,0,0.07)"/>

{{-- Left sleeve --}}
<path d="M155,105 L42,172 L62,230 L155,192 Z" fill="{{ $hexColor }}"/>
<path d="M155,105 L42,172 L62,230 L155,192 Z" fill="url(#slL)"/>

{{-- Right sleeve --}}
<path d="M325,105 L438,172 L418,230 L325,192 Z" fill="{{ $hexColor }}"/>
<path d="M325,105 L438,172 L418,230 L325,192 Z" fill="url(#slR)"/>

{{-- Body --}}
<path d="M155,105 L155,460 Q155,488 182,488 L298,488 Q325,488 325,460 L325,105 Z" fill="{{ $hexColor }}"/>
<path d="M155,105 L155,460 Q155,488 182,488 L298,488 Q325,488 325,460 L325,105 Z" fill="url(#sideShade)"/>

{{-- Outline --}}
<path d="M155,105 L42,172 L62,230 L155,192 L155,460 Q155,488 182,488 L298,488 Q325,488 325,460 L325,192 L418,230 L438,172 L325,105"
      fill="none" stroke="rgba(0,0,0,0.2)" stroke-width="1.5"/>

{{-- Neckline --}}
<path d="M155,105 Q200,85 240,94 Q280,85 325,105" fill="{{ $hexColor }}"/>
<path d="M155,105 Q200,85 240,94 Q280,85 325,105" fill="none" stroke="rgba(0,0,0,0.2)" stroke-width="1.5"/>
<path d="M195,100 Q240,118 285,100" fill="none" stroke="rgba(0,0,0,0.08)" stroke-width="1"/>

{{-- Stitch + folds --}}
<line x1="240" y1="105" x2="240" y2="483" stroke="rgba(0,0,0,0.04)" stroke-width="1" stroke-dasharray="8,6"/>
<line x1="198" y1="200" x2="202" y2="440" stroke="rgba(0,0,0,0.03)" stroke-width="1"/>
<line x1="282" y1="200" x2="278" y2="440" stroke="rgba(0,0,0,0.03)" stroke-width="1"/>

{{-- Marking zone --}}
@include('bat.svg._zone', compact('zoneX','zoneY','zoneW','zoneH','borderColor','logoUrl','imgX','imgY','imgW','imgH','isDraggable','markingIndex','logoWidth','logoHeight','maxWidth','maxHeight','positionLabel'))
</svg>
