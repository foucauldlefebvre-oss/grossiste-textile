<x-layouts.app title="BAT — Commande {{ $order->reference }}">
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

    <h1 class="text-2xl font-bold mb-2">Bon A Tirer</h1>
    <p class="text-gray-500 mb-8">Commande {{ $order->reference }}</p>

    {{-- Success message --}}
    @if(session('bat-success'))
        <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-xl text-green-800 text-sm font-medium">
            {{ session('bat-success') }}
        </div>
    @endif

    {{-- Status banner --}}
    @if($order->bat_status === 'approved')
        <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-xl flex items-center gap-3">
            <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            <div>
                <p class="font-semibold text-green-800">BAT approuve</p>
                <p class="text-xs text-green-600">Valide le {{ $order->bat_client_decided_at?->format('d/m/Y a H:i') }}. La production va commencer.</p>
            </div>
        </div>
        <div class="mb-6 text-center">
            <a href="{{ route('account.orders.show', $order->reference) }}" class="inline-flex items-center gap-2 px-6 py-3 bg-bordeaux text-white font-semibold rounded-lg hover:bg-bordeaux-700 transition">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Retour a ma commande
            </a>
        </div>
    @elseif($order->bat_status === 'revision_requested')
        <div class="mb-6 p-4 bg-amber-50 border border-amber-200 rounded-xl">
            <p class="font-semibold text-amber-800 mb-1">Modification demandee</p>
            <p class="text-sm text-amber-700">{{ $order->bat_client_comment }}</p>
            <p class="text-xs text-amber-500 mt-2">Un nouveau BAT vous sera envoye prochainement.</p>
        </div>
    @endif

    {{-- Documents BAT --}}
    @if($order->batDocuments->isNotEmpty())
        <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
            <h2 class="font-semibold mb-4">Documents BAT</h2>
            <div class="space-y-3">
                @foreach($order->batDocuments as $doc)
                    <a href="{{ $doc->download_url }}" target="_blank"
                       class="flex items-center gap-4 p-4 rounded-lg bg-gray-50 hover:bg-gray-100 transition">
                        @if(str_contains($doc->mime_type ?? '', 'image'))
                            <img src="{{ $doc->download_url }}" alt="" class="w-20 h-20 object-cover rounded-lg border">
                        @else
                            <div class="w-20 h-20 bg-bordeaux-50 rounded-lg flex items-center justify-center flex-shrink-0">
                                <svg class="w-8 h-8 text-bordeaux" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
                                </svg>
                            </div>
                        @endif
                        <div class="flex-1 min-w-0">
                            <p class="font-medium text-sm text-gray-900">{{ $doc->label ?? $doc->original_name }}</p>
                            <p class="text-xs text-gray-500">{{ $doc->formatted_size }} — {{ $doc->created_at->format('d/m/Y') }}</p>
                        </div>
                        <svg class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Actions (si pas encore approuve) --}}
    @if(!in_array($order->bat_status, ['approved']))
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h2 class="font-semibold mb-4">Votre decision</h2>
            <p class="text-sm text-gray-500 mb-4">
                Verifiez attentivement le BAT. Une fois approuve, la production sera lancee.
            </p>

            {{-- Approuver --}}
            <form action="{{ route('order.bat.approve', $order->bat_token) }}" method="POST" class="mb-4">
                @csrf
                <button type="submit"
                        onclick="return confirm('Confirmez-vous l\'approbation du BAT ? La production sera lancee.')"
                        class="w-full px-6 py-3 bg-green-600 text-white font-semibold rounded-lg hover:bg-green-700 transition">
                    Approuver le BAT
                </button>
            </form>

            {{-- Demander modification --}}
            <form action="{{ route('order.bat.revision', $order->bat_token) }}" method="POST">
                @csrf
                <textarea name="comment" rows="3" required
                          class="w-full px-4 py-3 border border-gray-300 rounded-lg text-sm mb-3 focus:ring-bordeaux focus:border-bordeaux"
                          placeholder="Decrivez les modifications souhaitees...">{{ old('comment') }}</textarea>
                @error('comment') <p class="text-xs text-red-500 mb-2">{{ $message }}</p> @enderror
                <button type="submit"
                        class="w-full px-6 py-3 bg-amber-500 text-white font-semibold rounded-lg hover:bg-amber-600 transition">
                    Demander une modification
                </button>
            </form>
        </div>
    @endif

</div>
</x-layouts.app>
