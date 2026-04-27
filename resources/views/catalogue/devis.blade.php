<x-layouts.app title="Mon devis">

    {{-- Breadcrumb --}}
    <div class="bg-white border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3">
            <nav class="text-sm text-gray-500">
                <a href="{{ route('home') }}" class="hover:text-bordeaux">Accueil</a>
                <span class="mx-2">/</span>
                <span class="text-gray-900 font-medium">Mon devis</span>
            </nav>
        </div>
    </div>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <h1 class="text-3xl font-bold mb-8">Mon devis</h1>
        <livewire:quote-page />
    </div>

</x-layouts.app>
