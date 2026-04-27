@props(['quote'])

@if($quote->status === 'sent' && $quote->expires_at)
    @php
        $days = $quote->daysRemaining();
        $urgent = $days <= 3;
    @endphp

    <div x-data="{ open: true }" x-show="open" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
        <div class="bg-white rounded-2xl shadow-xl max-w-md w-full mx-4 p-6 relative"
             @click.outside="open = false">
            <button @click="open = false" class="absolute top-3 right-3 text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>

            @if($urgent)
                <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700 font-medium">
                    Attention : votre devis expire dans {{ $days }} jour{{ $days > 1 ? 's' : '' }} !
                </div>
            @endif

            <div class="text-center mb-4">
                <div class="w-14 h-14 mx-auto mb-3 bg-bordeaux-100 rounded-full flex items-center justify-center">
                    <svg class="w-7 h-7 text-bordeaux" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-gray-900">Devis {{ $quote->reference }}</h3>
                <p class="text-sm text-gray-500 mt-1">Valable 2 semaines a compter de l'envoi</p>
            </div>

            <div class="space-y-2 text-sm mb-5">
                <div class="flex justify-between">
                    <span class="text-gray-500">Expire le</span>
                    <span class="font-medium">{{ $quote->expires_at->format('d/m/Y') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Jours restants</span>
                    <span class="font-bold {{ $urgent ? 'text-red-600' : 'text-green-600' }}">{{ $days }} jour{{ $days > 1 ? 's' : '' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Montant TTC</span>
                    <span class="font-bold text-bordeaux">{{ number_format($quote->total_ttc, 2, ',', ' ') }} &euro;</span>
                </div>
            </div>

            <div class="flex gap-3">
                <a href="{{ route('quote.download', $quote->reference) }}"
                   class="flex-1 px-4 py-2.5 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50 transition text-center text-sm">
                    Telecharger PDF
                </a>
                <button @click="open = false"
                        class="flex-1 px-4 py-2.5 bg-bordeaux text-white font-semibold rounded-lg hover:bg-bordeaux-dark transition text-sm">
                    Compris
                </button>
            </div>
        </div>
    </div>
@endif
