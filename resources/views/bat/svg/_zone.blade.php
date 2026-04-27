{{--
  Shared marking zone partial.
  Expects: $zoneX, $zoneY, $zoneW, $zoneH, $borderColor,
           $logoUrl, $imgX, $imgY, $imgW, $imgH,
           $isDraggable, $markingIndex, $logoWidth, $logoHeight,
           $maxWidth, $maxHeight, $positionLabel
--}}

{{-- Zone rect --}}
<rect id="{{ $isDraggable ? 'logo-zone-'.$markingIndex : '' }}"
      x="{{ $zoneX }}" y="{{ $zoneY }}"
      width="{{ $zoneW }}" height="{{ $zoneH }}"
      fill="none"
      stroke="{{ $borderColor }}" stroke-width="1" stroke-dasharray="6,4"
      opacity="0.6" />

{{-- Crosshair --}}
<line x1="{{ $zoneX + $zoneW/2 - 8 }}" y1="{{ $zoneY + $zoneH/2 }}"
      x2="{{ $zoneX + $zoneW/2 + 8 }}" y2="{{ $zoneY + $zoneH/2 }}"
      stroke="{{ $borderColor }}" stroke-width="0.5" opacity="0.4" />
<line x1="{{ $zoneX + $zoneW/2 }}" y1="{{ $zoneY + $zoneH/2 - 8 }}"
      x2="{{ $zoneX + $zoneW/2 }}" y2="{{ $zoneY + $zoneH/2 + 8 }}"
      stroke="{{ $borderColor }}" stroke-width="0.5" opacity="0.4" />

{{-- Logo image --}}
@if($logoUrl)
    <image id="{{ $isDraggable ? 'logo-img-'.$markingIndex : '' }}"
           href="{{ $logoUrl }}"
           x="{{ $imgX }}" y="{{ $imgY }}"
           width="{{ $imgW }}" height="{{ $imgH }}"
           preserveAspectRatio="xMidYMid meet"
           style="{{ $isDraggable ? 'cursor: grab;' : '' }}" />
@endif

{{-- Dimension arrow: width --}}
<line x1="{{ $zoneX }}" y1="{{ $zoneY + $zoneH + 14 }}"
      x2="{{ $zoneX + $zoneW }}" y2="{{ $zoneY + $zoneH + 14 }}"
      stroke="{{ $borderColor }}" stroke-width="0.7" opacity="0.5"
      marker-start="url(#arrowRev)" marker-end="url(#arrow)" />
<text id="dim-w-{{ $markingIndex }}"
      x="{{ $zoneX + $zoneW/2 }}" y="{{ $zoneY + $zoneH + 28 }}"
      text-anchor="middle" font-size="10" fill="{{ $borderColor }}" opacity="0.6">
    {{ number_format($logoWidth ?? $maxWidth, 1) }} cm
</text>

{{-- Dimension arrow: height --}}
<line x1="{{ $zoneX + $zoneW + 14 }}" y1="{{ $zoneY }}"
      x2="{{ $zoneX + $zoneW + 14 }}" y2="{{ $zoneY + $zoneH }}"
      stroke="{{ $borderColor }}" stroke-width="0.7" opacity="0.5"
      marker-start="url(#arrowRev)" marker-end="url(#arrow)" />
<text id="dim-h-{{ $markingIndex }}"
      x="{{ $zoneX + $zoneW + 26 }}" y="{{ $zoneY + $zoneH/2 + 4 }}"
      text-anchor="start" font-size="10" fill="{{ $borderColor }}" opacity="0.6">
    {{ number_format($logoHeight ?? $maxHeight, 1) }} cm
</text>

{{-- Position label --}}
<text x="{{ $zoneX + $zoneW/2 }}" y="{{ $zoneY + $zoneH + 44 }}"
      text-anchor="middle" font-size="11" fill="#888" font-style="italic">
    {{ $positionLabel }}
</text>
