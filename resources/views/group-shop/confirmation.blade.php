<x-layouts.app title="Commande confirmee - {{ $groupShop->name }}">

    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-16 text-center">
        <div class="w-20 h-20 mx-auto mb-6 bg-green-100 rounded-full flex items-center justify-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
            </svg>
        </div>
        <h1 class="text-2xl font-bold mb-2">Commande enregistree !</h1>
        <p class="text-gray-500 mb-1">Votre commande pour <strong>{{ $groupShop->name }}</strong> a bien ete prise en compte.</p>
        <p class="text-gray-500 mb-8">Vous recevrez un email de confirmation a l'adresse indiquee.</p>
        <a href="{{ route('group-shop.show', $groupShop) }}" class="px-6 py-3 bg-bordeaux text-white font-semibold rounded-lg hover:bg-bordeaux-dark transition">
            Retour a la boutique
        </a>
    </div>

</x-layouts.app>
