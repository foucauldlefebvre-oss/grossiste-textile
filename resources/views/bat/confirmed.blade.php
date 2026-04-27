<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BAT — Réponse enregistrée</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center">
    <div class="max-w-md text-center px-6">
        @if($quote->bat_status === 'approved')
            <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-gray-900 mb-2">BAT approuvé !</h1>
            <p class="text-gray-600">Merci, votre Bon À Tirer a été approuvé. Nous lançons la production de votre commande.</p>
        @elseif($quote->bat_status === 'revision_requested')
            <div class="w-16 h-16 bg-amber-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-gray-900 mb-2">Modification demandée</h1>
            <p class="text-gray-600">Merci, votre demande de modification a été transmise. Nous vous enverrons un nouveau BAT sous peu.</p>
        @else
            <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-gray-900 mb-2">BAT refusé</h1>
            <p class="text-gray-600">Votre réponse a été enregistrée. Notre équipe vous contactera rapidement.</p>
        @endif

        <p class="text-sm text-gray-400 mt-6">Devis {{ $quote->reference }}</p>
    </div>
</body>
</html>
