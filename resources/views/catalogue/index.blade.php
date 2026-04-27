<x-layouts.app title="Catalogue">

    {{-- Breadcrumb --}}
    <div class="bg-white border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3">
            <nav class="text-sm text-gray-500">
                <a href="{{ route('home') }}" class="hover:text-bordeaux">Accueil</a>
                <span class="mx-2">/</span>
                <span class="text-gray-900 font-medium">Catalogue</span>
            </nav>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <h1 class="text-2xl font-bold mb-4">Notre catalogue</h1>

        @php
            $catIcons = [
                't-shirts' => '<path d="M20 7L16.5 3.5 12 2 7.5 3.5 4 7l2 2v11a1 1 0 001 1h10a1 1 0 001-1V9l2-2zM12 2v5" stroke-linecap="round" stroke-linejoin="round"/>',
                'polos' => '<path d="M12 2c-1.5 0-2.5 1-2.5 2v1c0 1 1 1.5 2.5 1.5s2.5-.5 2.5-1.5V4c0-1-1-2-2.5-2zM7 5L4 8v2h3V7h10v3h3V8l-3-3M7 12v8a1 1 0 001 1h8a1 1 0 001-1v-8" stroke-linecap="round" stroke-linejoin="round"/>',
                'sweats' => '<path d="M19 5l-3-3h-8L5 5l-3 3v4h3V8h3v3c0 1 1 1.5 2 1.5h4c1 0 2-.5 2-1.5V8h3v4h3V8l-3-3M5 14v6a1 1 0 001 1h12a1 1 0 001-1v-6" stroke-linecap="round" stroke-linejoin="round"/>',
                'chemises' => '<path d="M8 2l-4 4v14a1 1 0 001 1h14a1 1 0 001-1V6l-4-4M12 2v8M9 5l3 3 3-3" stroke-linecap="round" stroke-linejoin="round"/>',
                'coupe-vent-softshell' => '<path d="M6 2L2 6v14a1 1 0 001 1h18a1 1 0 001-1V6l-4-4M12 2v18M8 6h0M16 6h0" stroke-linecap="round" stroke-linejoin="round"/>',
                'manteaux-parkas' => '<path d="M7 2L3 6v13a2 2 0 002 2h14a2 2 0 002-2V6l-4-4M12 2v19M7 8h10M9 12h6" stroke-linecap="round" stroke-linejoin="round"/>',
                'pantalons' => '<path d="M6 2h12v5H6zM6 7l2 15h2l2-10 2 10h2l2-15" stroke-linecap="round" stroke-linejoin="round"/>',
                'polaires' => '<path d="M7 2L3 6v12a2 2 0 002 2h14a2 2 0 002-2V6l-4-4M7 10h10M9 14h6M12 2v4" stroke-linecap="round" stroke-linejoin="round"/>',
                'pulls-gilets' => '<path d="M8 2L4 6v14a1 1 0 001 1h14a1 1 0 001-1V6l-4-4M12 2v6M8 8h8" stroke-linecap="round" stroke-linejoin="round"/>',
                'sport' => '<path d="M12 2a4 4 0 00-4 4v2h8V6a4 4 0 00-4-4zM6 10v10a2 2 0 002 2h8a2 2 0 002-2V10M9 14h6M12 10v4" stroke-linecap="round" stroke-linejoin="round"/>',
                'accessoires' => '<path d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" stroke-linecap="round" stroke-linejoin="round"/>',
                'vetements-de-travail' => '<path d="M14 2l4 4v14a1 1 0 01-1 1H7a1 1 0 01-1-1V6l4-4h4zM10 2v4M14 2v4M8 10h8M8 14h8" stroke-linecap="round" stroke-linejoin="round"/>',
                'bebe' => '<circle cx="12" cy="7" r="4"/><path d="M8 14v5a2 2 0 002 2h4a2 2 0 002-2v-5" stroke-linecap="round" stroke-linejoin="round"/>',
                'bio' => '<path d="M12 22c-4-2-8-6-8-11a8 8 0 0116 0c0 5-4 9-8 11zM12 8v6M9 11h6" stroke-linecap="round" stroke-linejoin="round"/>',
                'sous-vetements' => '<path d="M6 2h12v6l-3 2v10a1 1 0 01-1 1h-4a1 1 0 01-1-1V10L6 8V2z" stroke-linecap="round" stroke-linejoin="round"/>',
                'made-in-france' => '<path d="M4 3h16v14H4zM4 7h16M9 3v14M4 17l4 4 4-4 4 4 4-4" stroke-linecap="round" stroke-linejoin="round"/>',
            ];
        @endphp


        {{-- Categories : pills compacts, masques sur mobile --}}
        <div class="hidden sm:flex flex-wrap gap-1.5 mb-6">
            @foreach($categories as $category)
                <a href="{{ route('catalogue.category', $category) }}"
                   class="group inline-flex items-center gap-1.5 px-2.5 py-1.5 bg-white rounded-lg border border-gray-100 hover:border-bordeaux-200 hover:shadow-sm transition-all">
                    <div class="w-6 h-6 bg-bordeaux-50 rounded flex items-center justify-center group-hover:bg-bordeaux transition-all duration-200 flex-shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="text-bordeaux group-hover:text-white transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" style="width:0.8em;height:0.8em;">
                            {!! $catIcons[$category->slug] ?? '<path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>' !!}
                        </svg>
                    </div>
                    <span class="text-[0.7em] font-medium text-gray-600 group-hover:text-bordeaux transition-colors whitespace-nowrap">{{ $category->name }} <span class="text-gray-400">({{ $category->recursive_products_count }})</span></span>
                </a>
            @endforeach
        </div>

        {{-- All products --}}
        <h2 class="text-xl font-bold mb-4">Tous les produits</h2>
        <livewire:product-catalog />
    </div>

</x-layouts.app>
