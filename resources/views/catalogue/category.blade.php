<x-layouts.app
    :title="$category->meta_title ?? $category->name"
    :metaDescription="$category->meta_description ?? $category->description ?? $category->name . ' personnalisables par marquage textile. Broderie, serigraphie, DTG et plus. Devis en ligne.'"
    :ogImage="$category->image ? Storage::url($category->image) : null"
>

@section('jsonld')
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "CollectionPage",
    "name": "{{ $category->meta_title ?? $category->name }}",
    "description": "{{ e(Str::limit(strip_tags($category->description ?? ''), 200)) ?: $category->name . ' personnalisables par marquage textile.' }}",
    "url": "{{ route('catalogue.category', $category) }}",
    "isPartOf": {
        "@type": "WebSite",
        "name": "Marquage Textile",
        "url": "{{ url('/') }}"
    },
    "breadcrumb": {
        "@type": "BreadcrumbList",
        "itemListElement": [
            {"@type": "ListItem", "position": 1, "name": "Accueil", "item": "{{ route('home') }}"},
            {"@type": "ListItem", "position": 2, "name": "Catalogue", "item": "{{ route('catalogue.index') }}"}
            @if($category->parent)
            ,{"@type": "ListItem", "position": 3, "name": "{{ $category->parent->name }}", "item": "{{ route('catalogue.category', $category->parent) }}"}
            ,{"@type": "ListItem", "position": 4, "name": "{{ $category->name }}", "item": "{{ route('catalogue.category', $category) }}"}
            @else
            ,{"@type": "ListItem", "position": 3, "name": "{{ $category->name }}", "item": "{{ route('catalogue.category', $category) }}"}
            @endif
        ]
    }
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
                <span class="text-gray-900 font-medium">{{ $category->name }}</span>
            </nav>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <h1 class="text-3xl font-bold mb-2">{{ $category->name }}</h1>
        @if($category->meta_description)
            <p class="text-gray-500 mb-6">{{ $category->meta_description }}</p>
        @endif

        {{-- Subcategories (compact blocks, horizontal scroll on mobile) --}}
        @if($subcategories->count())
            <div class="flex gap-2 overflow-x-auto pb-2 mb-6 -mx-4 px-4 sm:mx-0 sm:px-0 sm:flex-wrap sm:overflow-visible scrollbar-none">
                @foreach($subcategories as $sub)
                    <a href="{{ route('catalogue.category', $sub) }}"
                       class="flex-shrink-0 inline-flex items-center gap-1.5 px-3 py-1.5 bg-white rounded-full text-xs font-medium text-gray-600 hover:text-bordeaux border border-gray-200 hover:border-bordeaux-200 hover:bg-bordeaux-50 shadow-sm transition">
                        {{ $sub->name }}
                        <span class="text-[10px] text-gray-400">({{ $sub->products_count }})</span>
                    </a>
                @endforeach
            </div>
        @endif

        {{-- Products (includes products from all subcategories) --}}
        <livewire:product-catalog :category-id="$category->id" :category-ids="$categoryIds" />

        {{-- Bloc SEO contextuel --}}
        @if($category->description)
        <div class="mt-16 border-t pt-8">
            <div class="prose prose-sm max-w-none text-gray-500">
                {!! $category->description !!}
            </div>
        </div>
        @endif
    </div>

</x-layouts.app>
