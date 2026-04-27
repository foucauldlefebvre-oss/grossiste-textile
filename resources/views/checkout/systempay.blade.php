<x-layouts.app title="Paiement securise">
    <div class="max-w-lg mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <div class="text-center mb-8">
            <div class="w-16 h-16 mx-auto mb-4 bg-blue-100 rounded-full flex items-center justify-center">
                <svg class="w-8 h-8 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-gray-900 mb-2">Paiement securise</h1>
            <p class="text-gray-500">Commande <strong>{{ $order->reference }}</strong> — {{ number_format($amount, 2, ',', ' ') }} &euro; TTC</p>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6">
            {{-- Systempay embedded form --}}
            <div class="kr-embedded" kr-form-token="{{ $formToken }}">
                <div class="kr-pan"></div>
                <div class="kr-expiry"></div>
                <div class="kr-security-code"></div>
                <button class="kr-payment-button" style="background-color: #6b1d2a; border-radius: 8px; padding: 12px 28px; font-weight: 600;">
                    Payer {{ number_format($amount, 2, ',', ' ') }} &euro;
                </button>
                <div class="kr-form-error"></div>
            </div>
        </div>

        <p class="text-xs text-gray-400 text-center mt-4">
            Paiement securise par Systempay (Banque Populaire). Vos donnees bancaires ne transitent jamais par notre serveur.
        </p>
    </div>

    {{-- Systempay JS SDK --}}
    <link rel="stylesheet" href="https://static.systempay.fr/static/js/krypton-client/V4.0/ext/classic-reset.css">
    <script src="{{ config('services.systempay.js_url') }}"
            kr-public-key="{{ $publicKey }}"
            kr-post-url-success="{{ route('payment.success', ['order' => $order->reference]) }}"
            kr-post-url-refused="{{ route('payment.cancel', ['order' => $order->reference]) }}"
            kr-language="fr-FR">
    </script>
</x-layouts.app>
