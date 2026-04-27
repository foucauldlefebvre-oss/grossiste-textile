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
    <linearGradient id="sideShadeCH" x1="0" y1="0" x2="1" y2="0">
        <stop offset="0%" stop-color="rgba(0,0,0,0.10)"/><stop offset="12%" stop-color="rgba(0,0,0,0)"/>
        <stop offset="88%" stop-color="rgba(0,0,0,0)"/><stop offset="100%" stop-color="rgba(0,0,0,0.10)"/>
    </linearGradient>
    <marker id="arrow" markerWidth="8" markerHeight="6" refX="8" refY="3" orient="auto"><path d="M0,0 L8,3 L0,6" fill="{{ $borderColor }}"/></marker>
    <marker id="arrowRev" markerWidth="8" markerHeight="6" refX="0" refY="3" orient="auto"><path d="M8,0 L0,3 L8,6" fill="{{ $borderColor }}"/></marker>
</defs>

<ellipse cx="240" cy="510" rx="148" ry="10" fill="rgba(0,0,0,0.07)"/>

{{-- Left sleeve (long) --}}
<path d="M155,110 L28,190 L42,280 L155,225 Z" fill="{{ $hexColor }}"/>
{{-- Left cuff with button --}}
<rect x="28" y="268" width="22" height="14" rx="2" fill="{{ $hexColor }}" stroke="rgba(0,0,0,0.12)" stroke-width="1"/>
<circle cx="44" cy="275" r="2" fill="none" stroke="rgba(0,0,0,0.15)" stroke-width="0.8"/>

{{-- Right sleeve (long) --}}
<path d="M325,110 L452,190 L438,280 L325,225 Z" fill="{{ $hexColor }}"/>
{{-- Right cuff with button --}}
<rect x="430" y="268" width="22" height="14" rx="2" fill="{{ $hexColor }}" stroke="rgba(0,0,0,0.12)" stroke-width="1"/>
<circle cx="436" cy="275" r="2" fill="none" stroke="rgba(0,0,0,0.15)" stroke-width="0.8"/>

{{-- Body (straight/structured) --}}
<path d="M155,110 L155,475 Q155,500 180,500 L300,500 Q325,500 325,475 L325,110 Z" fill="{{ $hexColor }}"/>
<path d="M155,110 L155,475 Q155,500 180,500 L300,500 Q325,500 325,475 L325,110 Z" fill="url(#sideShadeCH)"/>

{{-- Outline --}}
<path d="M155,110 L28,190 L42,280 L155,225 L155,500 L325,500 L325,225 L438,280 L452,190 L325,110"
      fill="none" stroke="rgba(0,0,0,0.18)" stroke-width="1.5"/>

{{-- Italian collar (pointed, spread) --}}
<path d="M155,110 Q200,92 240,100 Q280,92 325,110" fill="{{ $hexColor }}"/>
{{-- Left collar flap --}}
<path d="M195,106 L180,72 L210,80 L240,96" fill="{{ $hexColor }}" stroke="rgba(0,0,0,0.15)" stroke-width="1"/>
{{-- Right collar flap --}}
<path d="M285,106 L300,72 L270,80 L240,96" fill="{{ $hexColor }}" stroke="rgba(0,0,0,0.15)" stroke-width="1"/>
{{-- Collar fold shadow --}}
<line x1="192" y1="100" x2="200" y2="78" stroke="rgba(0,0,0,0.06)" stroke-width="1"/>
<line x1="288" y1="100" x2="280" y2="78" stroke="rgba(0,0,0,0.06)" stroke-width="1"/>

{{-- Button placket --}}
<line x1="240" y1="96" x2="240" y2="495" stroke="rgba(0,0,0,0.08)" stroke-width="1.5"/>
{{-- Placket edges --}}
<line x1="235" y1="96" x2="235" y2="495" stroke="rgba(0,0,0,0.04)" stroke-width="0.5"/>
<line x1="245" y1="96" x2="245" y2="495" stroke="rgba(0,0,0,0.04)" stroke-width="0.5"/>

{{-- 6 buttons --}}
<circle cx="240" cy="130" r="3" fill="none" stroke="rgba(0,0,0,0.15)" stroke-width="1"/>
<circle cx="240" cy="180" r="3" fill="none" stroke="rgba(0,0,0,0.15)" stroke-width="1"/>
<circle cx="240" cy="230" r="3" fill="none" stroke="rgba(0,0,0,0.15)" stroke-width="1"/>
<circle cx="240" cy="310" r="3" fill="none" stroke="rgba(0,0,0,0.15)" stroke-width="1"/>
<circle cx="240" cy="390" r="3" fill="none" stroke="rgba(0,0,0,0.15)" stroke-width="1"/>
<circle cx="240" cy="460" r="3" fill="none" stroke="rgba(0,0,0,0.15)" stroke-width="1"/>

{{-- Chest pocket (left side = right of observer) --}}
<rect x="256" y="165" width="42" height="38" rx="2" fill="none" stroke="rgba(0,0,0,0.08)" stroke-width="1"/>
{{-- Pocket flap --}}
<line x1="256" y1="165" x2="298" y2="165" stroke="rgba(0,0,0,0.1)" stroke-width="1"/>

{{-- Shoulder seams --}}
<line x1="155" y1="110" x2="200" y2="105" stroke="rgba(0,0,0,0.05)" stroke-width="1"/>
<line x1="325" y1="110" x2="280" y2="105" stroke="rgba(0,0,0,0.05)" stroke-width="1"/>

{{-- Back yoke seam --}}
<line x1="160" y1="145" x2="320" y2="145" stroke="rgba(0,0,0,0.04)" stroke-width="1"/>

{{-- Subtle side darts --}}
<line x1="175" y1="230" x2="178" y2="420" stroke="rgba(0,0,0,0.03)" stroke-width="1"/>
<line x1="305" y1="230" x2="302" y2="420" stroke="rgba(0,0,0,0.03)" stroke-width="1"/>

{{-- Marking zone --}}
@include('bat.svg._zone', compact('zoneX','zoneY','zoneW','zoneH','borderColor','logoUrl','imgX','imgY','imgW','imgH','isDraggable','markingIndex','logoWidth','logoHeight','maxWidth','maxHeight','positionLabel'))
</svg>
