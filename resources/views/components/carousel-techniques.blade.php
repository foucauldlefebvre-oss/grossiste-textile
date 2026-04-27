@php
    $techniques = \App\Models\TechniqueMarquage::actif()->get();
@endphp

@if($techniques->isNotEmpty())
<div class="carousel-wrapper">
    <p class="carousel-label">Techniques de marquage</p>

    <div class="carousel-outer">
        <div class="carousel-track">
            {{-- Premier jeu --}}
            @foreach($techniques as $technique)
                <div class="technique-card" title="{{ $technique->nom }} — {{ $technique->description_courte }}">
                    @if($technique->image)
                        <img src="{{ $technique->image_url }}"
                             alt="{{ $technique->nom }}"
                             loading="lazy">
                    @else
                        <div class="technique-placeholder">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/>
                            </svg>
                        </div>
                    @endif
                    <div class="technique-info">
                        <p class="technique-name">{{ $technique->nom }}</p>
                        <p class="technique-min">{{ $technique->description_courte }}</p>
                    </div>
                </div>
            @endforeach

            {{-- Doublement pour boucle infinie --}}
            @foreach($techniques as $technique)
                <div class="technique-card" title="{{ $technique->nom }}">
                    @if($technique->image)
                        <img src="{{ $technique->image_url }}"
                             alt="{{ $technique->nom }}"
                             loading="lazy">
                    @else
                        <div class="technique-placeholder">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/>
                            </svg>
                        </div>
                    @endif
                    <div class="technique-info">
                        <p class="technique-name">{{ $technique->nom }}</p>
                        <p class="technique-min">{{ $technique->description_courte }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endif
