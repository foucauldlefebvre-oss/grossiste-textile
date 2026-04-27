<x-layouts.app title="Devis {{ $quote->reference }}">

    <x-quote-validity-modal :quote="$quote" />

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        {{-- Status banner --}}
        @php
            $bannerConfig = match($quote->status) {
                'draft'    => ['bg' => 'bg-gray-100', 'text' => 'text-gray-700', 'label' => 'Brouillon'],
                'sent'     => ['bg' => 'bg-bordeaux-50', 'text' => 'text-bordeaux', 'label' => 'Envoye — en attente de validation'],
                'accepted' => ['bg' => 'bg-green-50', 'text' => 'text-green-700', 'label' => 'Accepte'],
                'refused'  => ['bg' => 'bg-red-50', 'text' => 'text-red-700', 'label' => 'Refuse'],
                'expired'  => ['bg' => 'bg-yellow-50', 'text' => 'text-yellow-700', 'label' => 'Expire'],
                default    => ['bg' => 'bg-gray-100', 'text' => 'text-gray-700', 'label' => $quote->status],
            };
        @endphp

        <div class="{{ $bannerConfig['bg'] }} {{ $bannerConfig['text'] }} rounded-xl p-4 mb-6 flex items-center justify-between">
            <div>
                <span class="font-semibold">{{ $bannerConfig['label'] }}</span>
                @if($quote->expires_at && $quote->status === 'sent')
                    <span class="text-sm ml-2">— expire le {{ $quote->expires_at->format('d/m/Y') }}
                        ({{ $quote->daysRemaining() }} jour{{ $quote->daysRemaining() > 1 ? 's' : '' }} restants)
                    </span>
                @endif
            </div>
            @if($quote->bat_pdf)
                <a href="{{ route('quote.download', $quote->reference) }}"
                   class="px-3 py-1.5 bg-white border rounded-lg text-sm font-medium hover:bg-gray-50 transition">
                    Telecharger PDF
                </a>
            @endif
        </div>

        <h1 class="text-2xl font-bold mb-6">Devis {{ $quote->reference }}</h1>

        {{-- Client info --}}
        <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
            <h2 class="font-semibold mb-3">Informations</h2>
            <dl class="grid grid-cols-2 gap-4 text-sm">
                <div>
                    <dt class="text-gray-500">Client</dt>
                    <dd class="font-medium">{{ $quote->user?->name ?? 'Non connecte' }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">Email</dt>
                    <dd>{{ $quote->user?->email ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">Date</dt>
                    <dd>{{ $quote->created_at->format('d/m/Y') }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">Reference</dt>
                    <dd class="font-medium">{{ $quote->reference }}</dd>
                </div>
            </dl>
        </div>

        {{-- Items --}}
        @if($quote->items->isNotEmpty())
        <div class="bg-white rounded-xl shadow-sm overflow-hidden mb-6">
            <div class="px-6 py-4 border-b bg-gray-50">
                <h2 class="font-semibold">Articles</h2>
            </div>
            <div class="divide-y">
                @foreach($quote->items as $item)
                    <div class="px-6 py-4 flex items-center gap-4">
                        @if($item->color?->hex_code)
                            <span class="w-5 h-5 rounded-full border border-gray-200 flex-shrink-0"
                                  style="background-color: {{ $item->color->hex_code }}"></span>
                        @endif
                        <div class="flex-1 min-w-0">
                            <p class="font-medium text-sm">{{ $item->product?->name ?? 'Produit' }}</p>
                            <p class="text-xs text-gray-500">
                                @if($item->color) {{ $item->color->name }} @endif
                                @if($item->size) &middot; {{ $item->size->size }} @endif
                                @if($item->technique) &middot; {{ $item->technique->name }} @endif
                                &middot; x{{ $item->quantity }}
                            </p>
                        </div>
                        <span class="text-sm font-semibold">{{ number_format($item->line_total_ht, 2, ',', ' ') }} &euro; HT</span>
                    </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Totals --}}
        <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
            <div class="max-w-xs ml-auto space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-500">Total HT</span>
                    <span>{{ number_format($quote->total_ht, 2, ',', ' ') }} &euro;</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">TVA (20%)</span>
                    <span>{{ number_format($quote->total_tva, 2, ',', ' ') }} &euro;</span>
                </div>
                <div class="border-t pt-2 flex justify-between font-bold text-lg">
                    <span>Total TTC</span>
                    <span class="text-bordeaux">{{ number_format($quote->total_ttc, 2, ',', ' ') }} &euro;</span>
                </div>
            </div>
        </div>

        {{-- Notes --}}
        @if($quote->notes)
        <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4 text-sm text-yellow-800">
            <strong>Notes :</strong> {{ $quote->notes }}
        </div>
        @endif
    </div>
</x-layouts.app>
