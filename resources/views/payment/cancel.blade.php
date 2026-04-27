<x-layouts.app title="Paiement annule">

    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-16 text-center">
        <div class="w-20 h-20 mx-auto mb-6 bg-orange-100 rounded-full flex items-center justify-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </div>

        <h1 class="text-2xl font-bold mb-2">Paiement annule</h1>
        <p class="text-gray-500 mb-6">Votre commande <strong>{{ $order->reference }}</strong> n'a pas ete finalisee.</p>

        <div class="bg-white rounded-xl shadow-sm p-6 mb-8">
            <p class="text-sm text-gray-500 mb-3">
                Votre commande est toujours en attente. Vous pouvez relancer le paiement ou continuer vos achats.
            </p>
            <p class="text-xs text-gray-400">
                Besoin d'aide ? Contactez-nous a <a href="mailto:contact@marquage-textile.fr" class="text-blue-600 hover:underline">contact@marquage-textile.fr</a> ou au <a href="tel:+33320400690" class="text-blue-600 hover:underline">03 20 40 06 90</a>.
            </p>
        </div>

        <div class="flex flex-col sm:flex-row justify-center gap-3">
            <a href="{{ route('payment.checkout') }}" class="w-full sm:w-auto px-6 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition text-center">
                Reessayer le paiement
            </a>
            <a href="{{ route('catalogue.index') }}" class="px-6 py-3 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50 transition text-center">
                Continuer mes achats
            </a>
        </div>
    </div>

</x-layouts.app>
