<x-layouts.app
    :title="$technique->meta_title ?? $technique->name . ' - Personnalisation textile'"
    :metaDescription="$technique->meta_description ?? 'Decouvrez la ' . $technique->name . ' pour personnaliser vos textiles. Devis en ligne gratuit.'"
    :canonical="url('/' . $technique->seo_url)"
>

@section('jsonld')
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "Service",
    "name": "{{ $technique->name }}",
    "description": "{{ $technique->meta_description ?? $technique->description }}",
    "provider": {
        "@type": "Organization",
        "name": "Marquage Textile",
        "url": "{{ url('/') }}"
    },
    "areaServed": {
        "@type": "Country",
        "name": "France"
    }
}
</script>
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "BreadcrumbList",
    "itemListElement": [
        {
            "@type": "ListItem",
            "position": 1,
            "name": "Accueil",
            "item": "{{ route('home') }}"
        },
        {
            "@type": "ListItem",
            "position": 2,
            "name": "{{ $technique->name }}",
            "item": "{{ url('/' . $technique->seo_url) }}"
        }
    ]
}
</script>
@endsection

    {{-- Breadcrumb --}}
    <div class="bg-white border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3">
            <nav class="text-sm text-gray-500">
                <a href="{{ route('home') }}" class="hover:text-blue-600">Accueil</a>
                <span class="mx-2">/</span>
                <span class="text-gray-900 font-medium">{{ $technique->name }}</span>
            </nav>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        {{-- Hero section --}}
        <div class="bg-white rounded-2xl shadow-sm border p-8 md:p-12 mb-12">
            <div class="max-w-3xl">
                <h1 class="text-4xl font-bold text-gray-900 mb-4">{{ $technique->meta_title ?? $technique->name }}</h1>
                @if($technique->description)
                    <p class="text-lg text-gray-600 mb-6">{{ $technique->description }}</p>
                @endif

                {{-- Key specs --}}
                <div class="flex flex-wrap gap-4">
                    @if($technique->constraint)
                        @if($technique->constraint->min_quantity)
                            <div class="flex items-center gap-2 px-4 py-2 bg-blue-50 rounded-lg">
                                <svg class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/></svg>
                                <span class="text-sm font-medium text-blue-800">Min. {{ $technique->constraint->min_quantity }} pieces</span>
                            </div>
                        @endif
                        @if($technique->constraint->max_colors)
                            <div class="flex items-center gap-2 px-4 py-2 bg-purple-50 rounded-lg">
                                <svg class="w-5 h-5 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/></svg>
                                <span class="text-sm font-medium text-purple-800">Jusqu'a {{ $technique->constraint->max_colors }} couleurs</span>
                            </div>
                        @else
                            <div class="flex items-center gap-2 px-4 py-2 bg-purple-50 rounded-lg">
                                <svg class="w-5 h-5 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/></svg>
                                <span class="text-sm font-medium text-purple-800">Couleurs illimitees</span>
                            </div>
                        @endif
                    @endif
                    @if($technique->quality_rating)
                        <div class="flex items-center gap-2 px-4 py-2 bg-amber-50 rounded-lg">
                            <svg class="w-5 h-5 text-amber-600" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                            <span class="text-sm font-medium text-amber-800">Qualite {{ number_format($technique->quality_rating, 1) }}/5</span>
                        </div>
                    @endif
                </div>

                @if($technique->file_formats)
                    <div class="mt-4 flex items-start gap-2 px-4 py-3 bg-gray-50 rounded-lg">
                        <svg class="w-5 h-5 text-gray-500 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                        <div>
                            <span class="text-sm font-medium text-gray-700">Formats de fichier acceptes</span>
                            <p class="text-sm text-gray-500 mt-0.5">{{ $technique->file_formats }}</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- SEO content --}}
        @if($technique->seo_content)
            <div class="prose prose-lg max-w-none text-gray-700 mb-12">
                {!! $technique->seo_content !!}
            </div>
        @endif

        {{-- Compatible products --}}
        @if($products->count())
            <div class="mb-12">
                <h2 class="text-2xl font-bold mb-6">Produits compatibles avec la {{ strtolower($technique->name) }}</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    @foreach($products as $product)
                        @include('catalogue._product-card', ['product' => $product])
                    @endforeach
                </div>
                <div class="text-center mt-8">
                    <a href="{{ route('catalogue.index') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition">
                        Voir tout le catalogue
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>
            </div>
        @endif

        {{-- CTA --}}
        <div class="bg-blue-600 rounded-2xl p-8 md:p-12 text-center text-white">
            <h2 class="text-2xl md:text-3xl font-bold mb-4">Un projet de {{ strtolower($technique->name) }} ?</h2>
            <p class="text-blue-100 mb-6 max-w-2xl mx-auto">Demandez un devis gratuit en ligne. Notre equipe vous accompagne de la creation a la livraison.</p>
            <a href="{{ route('devis') }}" class="inline-flex items-center gap-2 px-8 py-3 bg-white text-blue-600 font-bold rounded-lg hover:bg-blue-50 transition">
                Demander un devis gratuit
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>
    </div>

</x-layouts.app>
