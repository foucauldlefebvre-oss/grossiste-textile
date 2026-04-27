<x-layouts.app
    title="Personnalisation textile professionnelle"
    metaDescription="Marquage Textile : votre expert en personnalisation textile. Broderie, serigraphie, DTG, flocage. Devis en ligne gratuit, livraison rapide en France."
>

@section('jsonld')
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "Organization",
    "name": "Marquage Textile",
    "url": "{{ url('/') }}",
    "logo": "{{ asset('images/og-default.jpg') }}",
    "description": "Grossiste textile et atelier de marquage. Broderie, serigraphie, impression DTG, flocage. Plus de 6000 produits personnalisables.",
    "telephone": "+33320400690",
    "email": "contact@marquage-textile.fr",
    "address": {
        "@type": "PostalAddress",
        "addressCountry": "FR"
    },
    "contactPoint": {
        "@type": "ContactPoint",
        "telephone": "+33320400690",
        "email": "contact@marquage-textile.fr",
        "contactType": "customer service",
        "availableLanguage": "French"
    },
    "sameAs": []
}
</script>
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "HowTo",
    "name": "Comment personnaliser vos textiles",
    "description": "De la selection du produit a la livraison, 4 etapes simples pour personnaliser vos textiles.",
    "step": [
        {"@type": "HowToStep", "position": 1, "name": "Choisissez vos produits", "text": "Parcourez notre catalogue de plus de 6000 references textiles."},
        {"@type": "HowToStep", "position": 2, "name": "Configurez le marquage", "text": "Selectionnez la technique, la zone et le nombre de couleurs."},
        {"@type": "HowToStep", "position": 3, "name": "Validez votre devis", "text": "Recevez un devis instantane et validez le BAT avant production."},
        {"@type": "HowToStep", "position": 4, "name": "Recevez votre commande", "text": "Livraison rapide avec suivi, directement a votre adresse."}
    ]
}
</script>
@endsection

    {{-- Hero --}}
    <section class="relative overflow-hidden bg-gradient-to-br from-bordeaux via-bordeaux-dark to-bordeaux-900 text-white">
        {{-- Background pattern --}}
        <div class="absolute inset-0 opacity-10">
            <svg class="w-full h-full" xmlns="http://www.w3.org/2000/svg">
                <defs>
                    <pattern id="hero-pattern" x="0" y="0" width="80" height="80" patternUnits="userSpaceOnUse">
                        <path d="M0 0h80v80H0z" fill="none"/>
                        <path d="M40 0v80M0 40h80M20 0v80M60 0v80M0 20h80M0 60h80" stroke="white" stroke-width="0.5"/>
                    </pattern>
                </defs>
                <rect width="100%" height="100%" fill="url(#hero-pattern)"/>
            </svg>
        </div>
        <div class="absolute -top-24 -right-24 w-96 h-96 bg-white/5 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-32 -left-32 w-96 h-96 bg-bordeaux-800/30 rounded-full blur-3xl"></div>

        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24 lg:py-32">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                <div class="text-center lg:text-left">
                    <span class="inline-block px-4 py-1.5 bg-white/10 backdrop-blur-sm rounded-full text-sm font-medium text-bordeaux-100 mb-6">
                        A partir de 10 pieces — 8 techniques de marquage
                    </span>
                    <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold mb-6 leading-tight">Grossiste textile <br>&amp; atelier de <span class="text-bordeaux-200">marquage</span></h1>
                    <p class="text-lg text-bordeaux-100 mb-8 max-w-xl">Impression, broderie, serigraphie... Obtenez un devis instantane pour vos projets de personnalisation textile. Plus de 6 000 produits, livraison rapide.</p>
                    <div class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">
                        <a href="{{ route('catalogue.index') }}" class="px-8 py-3.5 bg-white text-bordeaux font-semibold rounded-lg hover:bg-gray-100 transition shadow-lg shadow-bordeaux-900/30">
                            Voir le catalogue
                        </a>
                        <a href="{{ route('devis') }}" class="px-8 py-3.5 border-2 border-white/30 text-white font-semibold rounded-lg hover:bg-white/10 transition backdrop-blur-sm">
                            Demander un devis
                        </a>
                    </div>
                    <div class="mt-10 flex items-center gap-8 justify-center lg:justify-start text-sm text-bordeaux-200">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            Devis gratuit
                        </div>
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            Livraison rapide
                        </div>
                        <div class="hidden sm:flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            Made in France
                        </div>
                    </div>
                </div>
                {{-- Carousel techniques (cadre portrait) --}}
                <div class="hidden lg:block w-96 mx-auto">
                    <x-carousel-techniques />
                </div>
            </div>
        </div>
    </section>

    {{-- Trust bar --}}
    <section class="bg-gray-100 border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6 text-center">
                <div class="flex flex-col items-center gap-2">
                    <div class="w-12 h-12 bg-bordeaux-50 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-bordeaux" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    </div>
                    <p class="font-semibold text-sm text-gray-900">A partir de 10 pieces</p>
                    <p class="text-xs text-gray-500">Petites et grandes series</p>
                </div>
                <div class="flex flex-col items-center gap-2">
                    <div class="w-12 h-12 bg-bordeaux-50 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-bordeaux" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                    </div>
                    <p class="font-semibold text-sm text-gray-900">8 techniques de marquage</p>
                    <p class="text-xs text-gray-500">Broderie, seri, DTG, DTF...</p>
                </div>
                <div class="flex flex-col items-center gap-2">
                    <div class="w-12 h-12 bg-bordeaux-50 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-bordeaux" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
                    </div>
                    <p class="font-semibold text-sm text-gray-900">Livraison France &amp; Europe</p>
                    <p class="text-xs text-gray-500">Expedition rapide</p>
                </div>
                <div class="flex flex-col items-center gap-2">
                    <div class="w-12 h-12 bg-bordeaux-50 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-bordeaux" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>
                    <p class="font-semibold text-sm text-gray-900">Devis instantane</p>
                    <p class="text-xs text-gray-500">Reponse rapide et gratuite</p>
                </div>
            </div>
        </div>
    </section>


    {{-- Featured products --}}
    @if($featuredProducts->count())
    <section class="bg-white py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-2xl font-bold mb-8 text-center">Produits populaires</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach($featuredProducts as $product)
                    @include('catalogue._product-card', ['product' => $product])
                @endforeach
            </div>
        </div>
    </section>
    @endif

    {{-- How it works --}}
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <h2 class="text-2xl font-bold mb-2 text-center">Comment ca marche ?</h2>
        <p class="text-gray-500 text-center mb-12 max-w-xl mx-auto">De la selection du produit a la livraison, 4 etapes simples pour personnaliser vos textiles.</p>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
            <div class="relative text-center">
                <div class="w-14 h-14 mx-auto mb-4 bg-bordeaux text-white rounded-full flex items-center justify-center text-xl font-bold">1</div>
                <h3 class="font-semibold text-gray-900 mb-2">Choisissez vos produits</h3>
                <p class="text-sm text-gray-500">Parcourez notre catalogue de plus de 6 000 references textiles.</p>
                <div class="hidden lg:block absolute top-7 left-[60%] w-[80%] border-t-2 border-dashed border-bordeaux-200"></div>
            </div>
            <div class="relative text-center">
                <div class="w-14 h-14 mx-auto mb-4 bg-bordeaux text-white rounded-full flex items-center justify-center text-xl font-bold">2</div>
                <h3 class="font-semibold text-gray-900 mb-2">Configurez le marquage</h3>
                <p class="text-sm text-gray-500">Selectionnez la technique, la zone et le nombre de couleurs.</p>
                <div class="hidden lg:block absolute top-7 left-[60%] w-[80%] border-t-2 border-dashed border-bordeaux-200"></div>
            </div>
            <div class="relative text-center">
                <div class="w-14 h-14 mx-auto mb-4 bg-bordeaux text-white rounded-full flex items-center justify-center text-xl font-bold">3</div>
                <h3 class="font-semibold text-gray-900 mb-2">Validez votre devis</h3>
                <p class="text-sm text-gray-500">Recevez un devis instantane et validez le BAT avant production.</p>
                <div class="hidden lg:block absolute top-7 left-[60%] w-[80%] border-t-2 border-dashed border-bordeaux-200"></div>
            </div>
            <div class="text-center">
                <div class="w-14 h-14 mx-auto mb-4 bg-green-600 text-white rounded-full flex items-center justify-center text-xl font-bold">4</div>
                <h3 class="font-semibold text-gray-900 mb-2">Recevez votre commande</h3>
                <p class="text-sm text-gray-500">Livraison rapide avec suivi, directement a votre adresse.</p>
            </div>
        </div>
    </section>

    {{-- CTA --}}
    <section class="bg-gradient-to-r from-bordeaux to-bordeaux-dark text-white">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-16 text-center">
            <h2 class="text-2xl sm:text-3xl font-bold mb-4">Vous avez un projet de personnalisation ?</h2>
            <p class="text-bordeaux-100 mb-8 max-w-2xl mx-auto">Obtenez un devis gratuit en quelques minutes. Notre equipe vous accompagne de la conception a la livraison.</p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="{{ route('devis') }}" class="px-8 py-3.5 bg-white text-bordeaux font-semibold rounded-lg hover:bg-gray-100 transition shadow-lg">
                    Demander un devis gratuit
                </a>
                <a href="mailto:contact@marquage-textile.fr" class="px-8 py-3.5 border-2 border-white/30 text-white font-semibold rounded-lg hover:bg-white/10 transition">
                    Nous contacter
                </a>
            </div>
        </div>
    </section>

    {{-- Bloc SEO texte --}}
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <div class="prose prose-sm max-w-none text-gray-500">
            <h2 class="text-xl font-bold text-gray-800">Marquage textile professionnel : broderie, serigraphie et impression pour vos vetements</h2>
            <p>
                <strong>Marquage Textile</strong> est votre partenaire specialise dans la <strong>personnalisation textile</strong> pour professionnels.
                Que vous cherchiez du <strong>marquage vetement de travail</strong>, de la <strong>broderie softshell</strong>, du <strong>transfert t-shirt</strong>
                ou de la <strong>serigraphie t-shirt de sport</strong>, nous proposons 8 techniques de marquage adaptees a chaque besoin.
            </p>
            <p>
                Notre catalogue de plus de 6 000 references couvre l'ensemble des besoins textiles :
                <strong>t-shirts polyester</strong> pour le sport, <strong>sweats personnalises</strong> par broderie ou transfert,
                <strong>doudounes brodees</strong> pour l'hiver, <strong>vetements de travail</strong> personnalises pour les equipes terrain,
                et bien plus encore. Chaque produit est personnalisable a partir de 10 pieces.
            </p>
            <h3 class="text-lg font-semibold text-gray-700">Nos techniques de marquage textile</h3>
            <p>
                Le <strong>marquage textile</strong> regroupe plusieurs techniques adaptees a differents supports et budgets.
                La <strong>broderie</strong> offre un rendu haut de gamme sur les <strong>polos</strong>, <strong>softshells</strong> et <strong>doudounes</strong>.
                La <strong>serigraphie</strong> est ideale pour les grandes series de <strong>t-shirts de sport</strong> et <strong>vetements promotionnels</strong>.
                Le <strong>transfert DTF</strong> et l'<strong>impression DTG</strong> permettent des visuels en couleurs illimitees sur tous types de textiles.
                Le <strong>flocage</strong> donne un aspect velours parfait pour les noms et numeros sur les maillots.
            </p>
            <h3 class="text-lg font-semibold text-gray-700">Marquage vetement de travail et textile professionnel</h3>
            <p>
                Nous sommes specialistes du <strong>marquage textile de travail</strong> : blouses, pantalons, vestes de chantier,
                gilets haute visibilite, tabliers de cuisine. La <strong>broderie sur vetement de travail</strong> garantit
                une tenue du marquage dans le temps, meme apres des centaines de lavages industriels.
                Nos textiles sont selectionnes aupres des meilleurs grossistes europeens pour leur qualite et leur durabilite.
            </p>
            <p>
                <strong>Demandez un devis gratuit</strong> en ligne.
                Livraison rapide partout en France et en Europe. A partir de 10 pieces seulement.
            </p>
        </div>
    </section>

</x-layouts.app>
