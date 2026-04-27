<x-layouts.app
    title="Techniques de marquage textile"
    metaDescription="Découvrez toutes nos techniques de personnalisation textile : broderie, sérigraphie, transfert, flex, DTF, DTG. Comparatif qualité, prix et quantités minimales.">

    @section('jsonld')
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "CollectionPage",
        "name": "Techniques de marquage textile",
        "description": "Toutes les techniques de personnalisation textile professionnelle",
        "url": "{{ route('technique.index') }}"
    }
    </script>
    @endsection

    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

        {{-- Hero --}}
        <div class="text-center mb-12">
            <h1 class="text-4xl font-bold text-gray-900 mb-4">Techniques de marquage textile</h1>
            <p class="text-lg text-gray-600 max-w-3xl mx-auto">
                Choisissez la technique de <strong>personnalisation textile</strong> adaptée à votre projet.
                Broderie haut de gamme, sérigraphie en série, transfert multicolore... chaque méthode a ses atouts.
                Comparez qualité, budget et quantités minimales pour faire le bon choix.
            </p>
        </div>

        {{-- Grid de techniques --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-16">
            @foreach($techniques as $technique)
                <a href="{{ url('/' . $technique->seo_url) }}"
                   class="group bg-white rounded-xl border border-gray-200 hover:border-bordeaux-200 hover:shadow-lg transition-all overflow-hidden">

                    {{-- Header avec note qualité --}}
                    <div class="bg-gradient-to-r from-bordeaux to-bordeaux-dark p-4 text-white">
                        <div class="flex items-center justify-between">
                            <h2 class="text-lg font-bold">{{ $technique->name }}</h2>
                            <div class="flex items-center gap-1">
                                @for($i = 1; $i <= 5; $i++)
                                    <svg class="w-4 h-4 {{ $i <= round($technique->quality_rating) ? 'text-yellow-300' : 'text-white/30' }}" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                @endfor
                                <span class="text-sm ml-1 font-semibold">{{ number_format($technique->quality_rating, 1) }}/5</span>
                            </div>
                        </div>
                    </div>

                    {{-- Body --}}
                    <div class="p-5">
                        <p class="text-sm text-gray-600 mb-4">{{ $technique->description }}</p>

                        {{-- Infos clés --}}
                        <div class="space-y-2 text-sm">
                            @if($technique->constraint)
                                <div class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-bordeaux flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/></svg>
                                    <span><strong>Quantité min :</strong> {{ $technique->constraint->min_quantity ?? '-' }} pièces</span>
                                </div>
                                @if($technique->constraint->max_colors)
                                    <div class="flex items-center gap-2">
                                        <svg class="w-4 h-4 text-bordeaux flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/></svg>
                                        <span><strong>Couleurs max :</strong> {{ $technique->constraint->max_colors }}</span>
                                    </div>
                                @else
                                    <div class="flex items-center gap-2">
                                        <svg class="w-4 h-4 text-bordeaux flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/></svg>
                                        <span><strong>Couleurs :</strong> illimité (quadrichromie)</span>
                                    </div>
                                @endif
                                @if($technique->constraint->max_format)
                                    <div class="flex items-center gap-2">
                                        <svg class="w-4 h-4 text-bordeaux flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/></svg>
                                        <span><strong>Format max :</strong> {{ $technique->constraint->max_format }}</span>
                                    </div>
                                @endif
                            @endif
                        </div>

                        {{-- CTA --}}
                        <div class="mt-4 pt-3 border-t border-gray-100">
                            <span class="text-sm font-semibold text-bordeaux group-hover:text-bordeaux-dark transition">
                                En savoir plus &rarr;
                            </span>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>

        {{-- SEO content block --}}
        <div class="prose prose-lg max-w-none text-gray-700">
            <h2>Comment choisir sa technique de marquage textile ?</h2>
            <p>Le choix de la <strong>technique de marquage textile</strong> dépend de plusieurs critères : le type de textile, le nombre de couleurs du logo, la quantité commandée et le budget. Chez Marquage Textile, nous vous accompagnons pour trouver la solution de <strong>personnalisation textile</strong> la plus adaptée à votre projet.</p>

            <h3>Marquage textile haut de gamme : broderie et transfert sérigraphique</h3>
            <p>Pour un rendu premium et durable, la <strong>broderie textile</strong> reste la référence. Avec une note qualité de 5/5, elle est idéale pour les <strong>polos brodés</strong>, <strong>sweats brodés</strong> et <strong>softshells personnalisées</strong>. Le <strong>transfert sérigraphique</strong> (4.5/5) offre un toucher léger et un rendu net, parfait pour les logos monochromes en grande série.</p>

            <h3>Marquage en série et petit budget : sérigraphie et DTF</h3>
            <p>La <strong>sérigraphie textile</strong> est la technique reine pour les grandes quantités. Économique dès 20 pièces, elle offre des couleurs éclatantes sur les <strong>t-shirts personnalisés</strong> et <strong>textiles de sport</strong>. Le <strong>transfert DTF</strong> est l'alternative accessible pour les petites séries avec des visuels multicolores.</p>

            <h3>Marquage multicolore et quadrichromie</h3>
            <p>Pour reproduire un logo en couleurs avec une grande fidélité, le <strong>transfert offset</strong> et l'<strong>impression DTG</strong> permettent la quadrichromie complète. Idéal pour les visuels photographiques ou les logos complexes sur <strong>t-shirts blancs</strong>.</p>

            <h3>Écussons et badges personnalisés</h3>
            <p>Le <strong>tissage</strong> et le <strong>badge PVC</strong> produisent des écussons et logos en relief qui se cousent ou se thermocollent. Parfaits pour les <strong>vêtements de travail</strong>, uniformes et casquettes.</p>

            <h3>Marquage textile professionnel : notre expertise</h3>
            <p>Spécialiste du <strong>marquage textile en série</strong>, nous personnalisons plus de 5 000 références textiles : <strong>t-shirts</strong>, <strong>polos</strong>, <strong>sweats</strong>, <strong>softshells</strong>, <strong>vêtements de travail</strong>, <strong>sacs</strong> et <strong>accessoires</strong>. Devis gratuit en ligne, livraison rapide en France et en Europe.</p>
        </div>
    </div>
</x-layouts.app>
