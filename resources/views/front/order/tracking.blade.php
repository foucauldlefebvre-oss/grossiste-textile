<x-layouts.app title="Suivi commande {{ $order->reference }}">

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        <h1 class="text-2xl font-bold mb-2">Suivi de commande</h1>
        <p class="text-gray-500 mb-8">Reference : <strong>{{ $order->reference }}</strong></p>

        {{-- Timeline --}}
        @php
            $steps = [
                ['key' => 'bat', 'label' => 'Bon a tirer', 'icon' => 'M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z'],
                ['key' => 'prep', 'label' => 'Preparation', 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4'],
                ['key' => 'production', 'label' => 'Production', 'icon' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z'],
                ['key' => 'shipping', 'label' => 'Expedition', 'icon' => 'M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0'],
            ];
        @endphp

        <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
            <div class="flex items-center justify-between">
                @foreach($steps as $i => $step)
                    @php
                        $statusCol = "status_{$step['key']}";
                        $isDone = ($order->$statusCol ?? null) === 'done';
                    @endphp

                    <div class="flex flex-col items-center flex-1">
                        <div class="w-12 h-12 rounded-full flex items-center justify-center {{ $isDone ? 'bg-green-100' : 'bg-gray-100' }}">
                            @if($isDone)
                                <svg class="w-6 h-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                </svg>
                            @else
                                <svg class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $step['icon'] }}" />
                                </svg>
                            @endif
                        </div>
                        <span class="mt-2 text-xs font-medium {{ $isDone ? 'text-green-700' : 'text-gray-500' }}">{{ $step['label'] }}</span>
                    </div>

                    @if(!$loop->last)
                        <div class="flex-1 h-0.5 {{ $isDone ? 'bg-green-300' : 'bg-gray-200' }} mx-2 mt-[-20px]"></div>
                    @endif
                @endforeach
            </div>
        </div>

        {{-- Tracking info --}}
        @if($order->tracking_number)
        <div class="bg-bordeaux-50 border border-bordeaux-200 rounded-xl p-4 mb-6">
            <h3 class="font-semibold text-blue-900 mb-2">Suivi d'expedition</h3>
            <div class="text-sm space-y-1">
                @if($order->carrier)
                    <p><span class="text-bordeaux">Transporteur :</span> {{ $order->carrier }}</p>
                @endif
                <p><span class="text-bordeaux">N&deg; suivi :</span> {{ $order->tracking_number }}</p>
                @if($order->tracking_url)
                    <a href="{{ $order->tracking_url }}" target="_blank"
                       class="inline-flex items-center gap-1 mt-2 px-4 py-2 bg-bordeaux text-white rounded-lg text-sm font-medium hover:bg-bordeaux-dark transition">
                        Suivre le colis
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                        </svg>
                    </a>
                @endif
            </div>
        </div>
        @endif

        {{-- Documents visible to client --}}
        @if($order->documents->isNotEmpty())
        <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
            <h3 class="font-semibold mb-3">Documents</h3>
            <div class="space-y-2">
                @foreach($order->documents as $doc)
                    <a href="{{ $doc->download_url }}" target="_blank"
                       class="flex items-center gap-3 p-3 rounded-lg bg-gray-50 hover:bg-gray-100 transition">
                        <svg class="w-5 h-5 text-gray-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                        </svg>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900">{{ $doc->label ?? $doc->original_name }}</p>
                            <p class="text-xs text-gray-500">{{ $doc->type_label }} &middot; {{ $doc->formatted_size }}</p>
                        </div>
                        <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                    </a>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Order summary --}}
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="font-semibold mb-3">Resume</h3>
            <div class="max-w-xs ml-auto space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-500">Total HT</span>
                    <span>{{ number_format($order->total_ht, 2, ',', ' ') }} &euro;</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">TVA</span>
                    <span>{{ number_format($order->total_tva, 2, ',', ' ') }} &euro;</span>
                </div>
                <div class="border-t pt-2 flex justify-between font-bold text-lg">
                    <span>Total TTC</span>
                    <span class="text-bordeaux">{{ number_format($order->total_ttc, 2, ',', ' ') }} &euro;</span>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
