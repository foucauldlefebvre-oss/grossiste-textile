<x-layouts.app
    :title="$product->meta_title ?? $product->name"
    :metaDescription="$product->meta_description ?? $product->short_description ?? 'Decouvrez ' . $product->name . ' personnalisable par broderie, serigraphie et plus. Devis en ligne.'"
    :ogImage="$product->main_image ? $product->imageUrl() : null"
    ogType="product"
>

@section('jsonld')
@php
    $hasStock = $product->colors->sum('stock') > 0;
    $displayPrice = $product->display_price;
    $allPrices = [];
    if ($product->supplier_price && (float) $product->supplier_price > 0) {
        $allPrices[] = \App\Models\PricingRule::calculate((float) $product->supplier_price, 500);
        $allPrices[] = \App\Models\PricingRule::calculate((float) $product->supplier_price, 1);
    } elseif ($displayPrice > 0) {
        $allPrices[] = $displayPrice;
    }
    $lowPrice = !empty($allPrices) ? min($allPrices) : 0;
    $highPrice = !empty($allPrices) ? max($allPrices) : 0;
@endphp
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "Product",
    "name": "{{ e($product->name) }}",
    "description": "{{ e(Str::limit(strip_tags($product->description ?? $product->short_description ?? $product->name), 300)) }}",
    @if($product->main_image)
    "image": "{{ url($product->imageUrl()) }}",
    @endif
    "sku": "{{ $product->reference }}",
    "brand": {
        "@type": "Brand",
        "name": "{{ $product->supplier ?? 'Marquage Textile' }}"
    },
    @if($product->material)
    "material": "{{ e($product->material) }}",
    @endif
    "offers": {
        "@type": "AggregateOffer",
        "priceCurrency": "EUR",
        "availability": "https://schema.org/{{ $hasStock ? 'InStock' : 'OutOfStock' }}",
        @if($lowPrice > 0)
        "lowPrice": "{{ number_format($lowPrice, 2, '.', '') }}",
        "highPrice": "{{ number_format($highPrice, 2, '.', '') }}",
        @endif
        "offerCount": "{{ $product->colors->count() }}",
        "url": "{{ $product->url }}",
        "seller": {
            "@type": "Organization",
            "name": "Marquage Textile"
        }
    }
}
</script>
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "BreadcrumbList",
    "itemListElement": [
        {"@type": "ListItem", "position": 1, "name": "Accueil", "item": "{{ route('home') }}"},
        {"@type": "ListItem", "position": 2, "name": "Catalogue", "item": "{{ route('catalogue.index') }}"}
        @if($product->category)
        ,{"@type": "ListItem", "position": 3, "name": "{{ $product->category->name }}", "item": "{{ route('catalogue.category', $product->category) }}"}
        ,{"@type": "ListItem", "position": 4, "name": "{{ $product->name }}", "item": "{{ $product->url }}"}
        @else
        ,{"@type": "ListItem", "position": 3, "name": "{{ $product->name }}", "item": "{{ $product->url }}"}
        @endif
    ]
}
</script>
@endsection

    {{-- Breadcrumb --}}
    <div class="bg-white border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3">
            <nav class="text-sm text-gray-500">
                <a href="{{ route('home') }}" class="hover:text-bordeaux">Accueil</a>
                <span class="mx-2">/</span>
                <a href="{{ route('catalogue.index') }}" class="hover:text-bordeaux">Catalogue</a>
                <span class="mx-2">/</span>
                @if($product->category)
                    <a href="{{ route('catalogue.category', $product->category) }}" class="hover:text-bordeaux">{{ $product->category->name }}</a>
                    <span class="mx-2">/</span>
                @endif
                <span class="text-gray-900 font-medium">{{ $product->name }}</span>
            </nav>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">

            {{-- Images --}}
            <div x-data="{ currentImage: '{{ $product->main_image ? $product->imageUrl() : '' }}', zooming: false, zoomX: 50, zoomY: 50 }"
                 @color-image-changed.window="currentImage = $event.detail.image">
                <div class="aspect-square bg-gray-100 rounded-xl overflow-hidden relative cursor-zoom-in"
                     @mouseenter="zooming = true"
                     @mouseleave="zooming = false"
                     @mousemove="const rect = $el.getBoundingClientRect(); zoomX = ((event.clientX - rect.left) / rect.width) * 100; zoomY = ((event.clientY - rect.top) / rect.height) * 100">
                    <template x-if="currentImage">
                        <img :src="currentImage" alt="{{ $product->name }}"
                             class="w-full h-full object-cover transition-transform duration-200"
                             :style="zooming ? `transform: scale(2); transform-origin: ${zoomX}% ${zoomY}%` : ''">
                    </template>
                    <template x-if="!currentImage">
                        <div class="w-full h-full flex items-center justify-center text-gray-300">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-24 w-24" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                    </template>
                </div>
                @if($product->gallery && count($product->gallery))
                    <div class="grid grid-cols-4 gap-3 mt-3">
                        @if($product->main_image)
                            <div @click="currentImage = '{{ $product->imageUrl() }}'"
                                 class="aspect-square bg-gray-100 rounded-lg overflow-hidden cursor-pointer ring-offset-2 transition"
                                 :class="currentImage === '{{ $product->imageUrl() }}' ? 'ring-2 ring-blue-500' : 'hover:ring-2 ring-bordeaux-200'">
                                <img src="{{ $product->imageUrl() }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                            </div>
                        @endif
                        @foreach($product->gallery as $image)
                            <div @click="currentImage = '{{ $product->imageUrl($image) }}'"
                                 class="aspect-square bg-gray-100 rounded-lg overflow-hidden cursor-pointer ring-offset-2 transition"
                                 :class="currentImage === '{{ $product->imageUrl($image) }}' ? 'ring-2 ring-blue-500' : 'hover:ring-2 ring-bordeaux-200'">
                                <img src="{{ $product->imageUrl($image) }}" alt="{{ $product->name }} - vue {{ $loop->iteration + 1 }}" class="w-full h-full object-cover">
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Product info --}}
            <div>
                <div class="flex items-center gap-2 mb-2">
                    <span class="text-sm text-gray-400">{{ $product->reference }}</span>
                    <span class="text-gray-200">|</span>
                    <span class="text-sm text-gray-400">{{ $product->supplier }}</span>
                    @if($product->is_featured)
                        <span class="px-2 py-0.5 bg-bordeaux-100 text-bordeaux text-xs font-medium rounded-full">Populaire</span>
                    @endif
                </div>
                <h1 class="text-3xl font-bold mb-4">{{ $product->meta_title ? Str::before($product->meta_title, ' |') : $product->name }}</h1>

                @if($product->short_description)
                    <p class="text-gray-500 mb-4">{{ $product->short_description }}</p>
                @endif

                <div class="flex items-baseline gap-2 mb-6">
                    @if($product->display_price > 0)
                        <span class="text-xs text-gray-400">A partir de</span>
                        <span class="text-2xl font-bold text-bordeaux">{{ number_format($product->display_price, 2, ',', ' ') }} &euro;</span>
                        <span class="text-sm text-gray-400">HT / unite</span>
                    @else
                        <span class="text-lg text-gray-400">Prix sur devis</span>
                    @endif
                </div>

                {{-- Specs --}}
                <div class="grid grid-cols-2 gap-3 mb-6">
                    @if($product->material)
                        <div class="bg-gray-50 rounded-lg px-4 py-3 flex items-center gap-3">
                            <svg class="w-5 h-5 text-gray-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/></svg>
                            <div>
                                <p class="text-xs text-gray-400">Matiere</p>
                                <p class="font-medium text-sm">{{ $product->material }}</p>
                            </div>
                        </div>
                    @endif
                    @if($product->grammage)
                        <div class="bg-gray-50 rounded-lg px-4 py-3 flex items-center gap-3">
                            <svg class="w-5 h-5 text-gray-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/></svg>
                            <div>
                                <p class="text-xs text-gray-400">Grammage</p>
                                <p class="font-medium text-sm">{{ $product->grammage }}</p>
                            </div>
                        </div>
                    @endif
                    <div class="bg-gray-50 rounded-lg px-4 py-3 flex items-center gap-3">
                        <svg class="w-5 h-5 text-gray-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6z"/></svg>
                        <div>
                            <p class="text-xs text-gray-400">Coupe</p>
                            <p class="font-medium text-sm capitalize">{{ $product->cut }}</p>
                        </div>
                    </div>
                    @if($product->weight)
                        <div class="bg-gray-50 rounded-lg px-4 py-3 flex items-center gap-3">
                            <svg class="w-5 h-5 text-gray-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/></svg>
                            <div>
                                <p class="text-xs text-gray-400">Poids</p>
                                <p class="font-medium text-sm">{{ $product->weight }} kg</p>
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Sélecteur B2B : couleurs + tailles + qté + ajout panier --}}
                <livewire:product-selector :product="$product" />

            </div>
        </div>

        {{-- SEO description block --}}
        @if($product->description)
            <div class="mt-16 pt-10 border-t">
                <div class="prose prose-lg max-w-none text-gray-700">
                    {!! $product->description !!}
                </div>
            </div>
        @endif

        {{-- Related --}}
        @if($relatedProducts->count())
            <div class="mt-16 pt-8 border-t">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-2xl font-bold">Produits similaires</h2>
                    @if($product->category)
                        <a href="{{ route('catalogue.category', $product->category) }}" class="text-sm text-bordeaux hover:text-blue-800 font-medium transition">
                            Voir la categorie &rarr;
                        </a>
                    @endif
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    @foreach($relatedProducts as $related)
                        @include('catalogue._product-card', ['product' => $related])
                    @endforeach
                </div>
            </div>
        @endif
    </div>

</x-layouts.app>
