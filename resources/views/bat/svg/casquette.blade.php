@php
    use App\Helpers\BatHelper;
    $zoneW = $maxWidth * 8; $zoneH = $maxHeight * 8;
    $zoneX = 240 - $zoneW / 2; $zoneY = 160;
    $imgW = ($logoWidth ?? $maxWidth) * 8; $imgH = ($logoHeight ?? $maxHeight) * 8;
    $imgX = $zoneX + ($logoX ?? 0) * 8; $imgY = $zoneY + ($logoY ?? 0) * 8;
    $borderColor = $isDark ? '#ffffff' : '#333333';
@endphp
<svg id="{{ $isDraggable ? 'bat-svg' : '' }}"
     viewBox="0 0 480 480" xmlns="http://www.w3.org/2000/svg" class="w-full h-auto"
     @if($isDraggable) data-max-width="{{ $maxWidth }}" data-max-height="{{ $maxHeight }}" data-zone-x="{{ $zoneX }}" data-zone-y="{{ $zoneY }}" data-index="{{ $markingIndex }}" @endif>
<defs>
    <marker id="arrow" markerWidth="8" markerHeight="6" refX="8" refY="3" orient="auto"><path d="M0,0 L8,3 L0,6" fill="{{ $borderColor }}"/></marker>
    <marker id="arrowRev" markerWidth="8" markerHeight="6" refX="0" refY="3" orient="auto"><path d="M8,0 L0,3 L8,6" fill="{{ $borderColor }}"/></marker>
</defs>

<ellipse cx="240" cy="410" rx="100" ry="8" fill="rgba(0,0,0,0.06)"/>

{{-- Crown --}}
<path d="M100,250 Q100,75 240,55 Q380,75 380,250" fill="{{ $hexColor }}" stroke="rgba(0,0,0,0.18)" stroke-width="1.5"/>
{{-- Panel seams --}}
<path d="M240,55 L240,250" stroke="rgba(0,0,0,0.06)" stroke-width="1"/>
<path d="M170,70 L155,250" stroke="rgba(0,0,0,0.05)" stroke-width="1"/>
<path d="M310,70 L325,250" stroke="rgba(0,0,0,0.05)" stroke-width="1"/>
{{-- Top button --}}
<circle cx="240" cy="53" r="5" fill="{{ $hexColor }}" stroke="rgba(0,0,0,0.18)" stroke-width="1"/>
{{-- Eyelets --}}
<circle cx="175" cy="100" r="3" fill="none" stroke="rgba(0,0,0,0.1)" stroke-width="1"/>
<circle cx="240" cy="90" r="3" fill="none" stroke="rgba(0,0,0,0.1)" stroke-width="1"/>
<circle cx="305" cy="100" r="3" fill="none" stroke="rgba(0,0,0,0.1)" stroke-width="1"/>

{{-- Sweatband --}}
<path d="M100,242 Q100,260 110,265 L370,265 Q380,260 380,242" fill="{{ $hexColor }}" stroke="rgba(0,0,0,0.12)" stroke-width="1"/>

{{-- Visor --}}
<path d="M100,250 Q80,260 70,292 Q65,322 100,342 Q180,372 280,352 Q350,332 380,250 Z"
      fill="{{ $hexColor }}" stroke="rgba(0,0,0,0.18)" stroke-width="1.5"/>
<path d="M110,260 Q130,312 220,328 Q300,318 360,260" fill="rgba(255,255,255,0.06)"/>
<path d="M115,265 Q175,340 300,328 Q350,312 370,260" fill="none" stroke="rgba(0,0,0,0.05)" stroke-width="1" stroke-dasharray="4,3"/>

{{-- Marking zone --}}
@include('bat.svg._zone', [
    'zoneX' => $zoneX, 'zoneY' => $zoneY, 'zoneW' => $zoneW, 'zoneH' => $zoneH,
    'borderColor' => $borderColor, 'logoUrl' => $logoUrl,
    'imgX' => $imgX, 'imgY' => $imgY, 'imgW' => $imgW, 'imgH' => $imgH,
    'isDraggable' => $isDraggable, 'markingIndex' => $markingIndex,
    'logoWidth' => $logoWidth, 'logoHeight' => $logoHeight,
    'maxWidth' => $maxWidth, 'maxHeight' => $maxHeight,
    'positionLabel' => 'Face avant',
])
</svg>
