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
    <linearGradient id="sideShadeZC" x1="0" y1="0" x2="1" y2="0">
        <stop offset="0%" stop-color="rgba(0,0,0,0.12)"/><stop offset="15%" stop-color="rgba(0,0,0,0)"/>
        <stop offset="85%" stop-color="rgba(0,0,0,0)"/><stop offset="100%" stop-color="rgba(0,0,0,0.12)"/>
    </linearGradient>
    <linearGradient id="hoodShadeZC" x1="0.5" y1="0" x2="0.5" y2="1">
        <stop offset="0%" stop-color="rgba(0,0,0,0)"/><stop offset="100%" stop-color="rgba(0,0,0,0.1)"/>
    </linearGradient>
    <marker id="arrow" markerWidth="8" markerHeight="6" refX="8" refY="3" orient="auto"><path d="M0,0 L8,3 L0,6" fill="{{ $borderColor }}"/></marker>
    <marker id="arrowRev" markerWidth="8" markerHeight="6" refX="0" refY="3" orient="auto"><path d="M8,0 L0,3 L8,6" fill="{{ $borderColor }}"/></marker>
</defs>

<ellipse cx="240" cy="515" rx="148" ry="10" fill="rgba(0,0,0,0.07)"/>

{{-- Hood --}}
<path d="M165,108 Q160,40 240,25 Q320,40 315,108" fill="{{ $hexColor }}" stroke="rgba(0,0,0,0.15)" stroke-width="1.5"/>
<path d="M165,108 Q160,40 240,25 Q320,40 315,108" fill="url(#hoodShadeZC)"/>
<path d="M240,25 L240,105" stroke="rgba(0,0,0,0.06)" stroke-width="1"/>
<path d="M165,108 Q200,120 240,122 Q280,120 315,108" fill="none" stroke="rgba(0,0,0,0.1)" stroke-width="1.5"/>

{{-- Drawstrings --}}
<line x1="225" y1="118" x2="218" y2="195" stroke="rgba(0,0,0,0.12)" stroke-width="1.5"/>
<line x1="255" y1="118" x2="262" y2="195" stroke="rgba(0,0,0,0.12)" stroke-width="1.5"/>
<line x1="215" y1="192" x2="221" y2="198" stroke="rgba(0,0,0,0.12)" stroke-width="2.5" stroke-linecap="round"/>
<line x1="265" y1="192" x2="259" y2="198" stroke="rgba(0,0,0,0.12)" stroke-width="2.5" stroke-linecap="round"/>

{{-- Sleeves --}}
<path d="M148,115 L30,188 L52,252 L148,210 Z" fill="{{ $hexColor }}"/>
<rect x="30" y="240" width="28" height="12" rx="2" fill="{{ $hexColor }}" stroke="rgba(0,0,0,0.08)" stroke-width="1"/>
<path d="M332,115 L450,188 L428,252 L332,210 Z" fill="{{ $hexColor }}"/>
<rect x="422" y="240" width="28" height="12" rx="2" fill="{{ $hexColor }}" stroke="rgba(0,0,0,0.08)" stroke-width="1"/>

{{-- Body --}}
<path d="M148,115 L148,478 Q148,505 178,505 L302,505 Q332,505 332,478 L332,115 Z" fill="{{ $hexColor }}"/>
<path d="M148,115 L148,478 Q148,505 178,505 L302,505 Q332,505 332,478 L332,115 Z" fill="url(#sideShadeZC)"/>

{{-- Bottom ribbing --}}
<rect x="148" y="488" width="184" height="17" rx="3" fill="{{ $hexColor }}" stroke="rgba(0,0,0,0.06)" stroke-width="1"/>

{{-- Central zip (full length) --}}
<line x1="240" y1="115" x2="240" y2="505" stroke="rgba(0,0,0,0.15)" stroke-width="2"/>
<line x1="237" y1="115" x2="237" y2="505" stroke="rgba(0,0,0,0.06)" stroke-width="0.5" stroke-dasharray="3,3"/>
<line x1="243" y1="115" x2="243" y2="505" stroke="rgba(0,0,0,0.06)" stroke-width="0.5" stroke-dasharray="3,3"/>
<rect x="236" y="155" width="8" height="12" rx="2" fill="rgba(0,0,0,0.2)"/>

{{-- Outline --}}
<path d="M148,115 L30,188 L52,252 L148,210 L148,505 L332,505 L332,210 L428,252 L450,188 L332,115"
      fill="none" stroke="rgba(0,0,0,0.18)" stroke-width="1.5"/>

@include('bat.svg._zone', compact('zoneX','zoneY','zoneW','zoneH','borderColor','logoUrl','imgX','imgY','imgW','imgH','isDraggable','markingIndex','logoWidth','logoHeight','maxWidth','maxHeight','positionLabel'))
</svg>
