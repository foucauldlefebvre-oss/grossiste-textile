<x-layouts.app title="Paiement par virement">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <div class="text-center mb-8">
            <div class="w-16 h-16 mx-auto mb-4 bg-blue-100 rounded-full flex items-center justify-center">
                <svg class="w-8 h-8 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-gray-900 mb-2">Paiement par virement bancaire</h1>
            <p class="text-gray-600 mt-3">
                Merci de nous envoyer le virement avec le numero de commande :
                <strong class="text-bordeaux text-lg">{{ $order->reference }}</strong>
            </p>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
            <h2 class="font-semibold mb-4">Coordonnees bancaires</h2>
            <div class="bg-gray-50 rounded-lg p-4 space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-500">Beneficiaire</span>
                    <span class="font-medium">SARL Marquage Textile</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">IBAN</span>
                    <span class="font-mono font-medium">FR76 1350 7000 7230 9723 2213 664</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">BIC</span>
                    <span class="font-mono font-medium">CCBPFRPPLIL</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Banque</span>
                    <span class="font-medium">Banque Populaire Nord</span>
                </div>
                <div class="flex justify-between border-t pt-2 mt-2">
                    <span class="text-gray-500">Reference a indiquer</span>
                    <span class="font-bold text-bordeaux">{{ $order->reference }}</span>
                </div>
                @php
                    $isDeposit = str_contains($order->customer_notes ?? '', 'Acompte');
                    $amountDue = $isDeposit ? round((float) $order->total_ttc / 2, 2) : (float) $order->total_ttc;
                @endphp
                <div class="flex justify-between border-t pt-2 mt-2">
                    <span class="font-semibold">Montant a virer{{ $isDeposit ? ' (acompte 50%)' : '' }}</span>
                    <span class="font-bold text-bordeaux text-lg">{{ number_format($amountDue, 2, ',', ' ') }} &euro;</span>
                </div>
                @if($isDeposit)
                    <div class="text-xs text-gray-400 mt-1">Total commande : {{ number_format($order->total_ttc, 2, ',', ' ') }} &euro; — Solde de {{ number_format($order->total_ttc - $amountDue, 2, ',', ' ') }} &euro; a regler avant expedition.</div>
                @endif
            </div>
            <p class="text-xs text-gray-400 mt-3">
                Votre commande sera traitee des reception du virement (delai habituel 1-2 jours ouvres).
            </p>
        </div>

        <div class="flex justify-center gap-4">
            <form action="{{ route('checkout.virement.cancel', ['order' => $order->reference]) }}" method="POST">
                @csrf
                <button type="submit" onclick="return confirm('Etes-vous sur de vouloir annuler cette commande ?')"
                    class="px-6 py-2.5 border border-red-300 text-red-600 font-medium rounded-lg hover:bg-red-50 transition text-sm">
                    Annuler la commande
                </button>
            </form>
            <form action="{{ route('checkout.virement.confirm', ['order' => $order->reference]) }}" method="POST">
                @csrf
                <button type="submit"
                    class="px-6 py-2.5 bg-bordeaux text-white font-semibold rounded-lg hover:bg-bordeaux-dark transition text-sm">
                    Valider — j'effectue le virement
                </button>
            </form>
        </div>
    </div>
</x-layouts.app>
