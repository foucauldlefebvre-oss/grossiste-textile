<x-layouts.app title="Page introuvable">
    <div class="flex items-center justify-center min-h-[50vh] px-4">
        <div class="text-center">
            <p class="text-6xl font-bold text-bordeaux mb-4">404</p>
            <h1 class="text-2xl font-bold text-gray-900 mb-2">Page introuvable</h1>
            <p class="text-gray-500 mb-8 max-w-md mx-auto">La page que vous recherchez n'existe pas ou a ete deplacee.</p>
            <div class="flex gap-3 justify-center">
                <a href="{{ route('home') }}" class="px-6 py-2.5 bg-bordeaux text-white font-semibold rounded-lg hover:bg-bordeaux-dark transition text-sm">
                    Retour a l'accueil
                </a>
                <a href="{{ route('catalogue.index') }}" class="px-6 py-2.5 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50 transition text-sm">
                    Voir le catalogue
                </a>
            </div>
        </div>
    </div>
</x-layouts.app>
