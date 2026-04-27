<x-layouts.app title="Paiement confirme">

    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-16 text-center">
        <div class="w-20 h-20 mx-auto mb-6 bg-green-100 rounded-full flex items-center justify-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
            </svg>
        </div>

        <h1 class="text-2xl font-bold mb-2">Commande confirmee !</h1>
        <p class="text-gray-500 mb-1">Votre commande <strong>{{ $order->reference }}</strong> a bien ete enregistree.</p>

        @if($order->payment_status === 'paid')
            <p class="text-green-600 font-medium mb-6">Paiement recu avec succes.</p>
        @else
            <p class="text-gray-500 mb-6">Votre paiement est en cours de traitement.</p>
        @endif

        <div class="bg-white rounded-xl shadow-sm p-6 text-left mb-8">
            <h2 class="font-semibold mb-4">Recapitulatif</h2>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-500">Reference</span>
                    <span class="font-medium">{{ $order->reference }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Sous-total HT</span>
                    <span>{{ number_format($order->subtotal_ht, 2, ',', ' ') }} &euro;</span>
                </div>
                @if($order->shipping_ht > 0)
                    <div class="flex justify-between">
                        <span class="text-gray-500">Livraison HT</span>
                        <span>{{ number_format($order->shipping_ht, 2, ',', ' ') }} &euro;</span>
                    </div>
                @endif
                <div class="flex justify-between">
                    <span class="text-gray-500">TVA (20%)</span>
                    <span>{{ number_format($order->total_tva, 2, ',', ' ') }} &euro;</span>
                </div>
                <div class="flex justify-between font-bold text-base pt-2 border-t">
                    <span>Total TTC</span>
                    <span class="text-blue-600">{{ number_format($order->total_ttc, 2, ',', ' ') }} &euro;</span>
                </div>
            </div>
        </div>

        <div class="flex flex-col sm:flex-row justify-center gap-3">
            @auth
                @if($order->has_marking)
                    <a href="{{ route('account.orders.upload', $order->reference) }}" class="px-6 py-3 bg-bordeaux text-white font-semibold rounded-lg hover:bg-bordeaux-dark transition text-center">
                        Charger mes fichiers (logos)
                    </a>
                @else
                    <a href="{{ route('account.orders.show', $order->reference) }}" class="px-6 py-3 bg-bordeaux text-white font-semibold rounded-lg hover:bg-bordeaux-dark transition text-center">
                        Voir ma commande
                    </a>
                @endif
            @endauth
            <a href="{{ route('catalogue.index') }}" class="px-6 py-3 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50 transition text-center">
                Continuer mes achats
            </a>
        </div>
    </div>

</x-layouts.app>
