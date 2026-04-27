<x-layouts.app title="Mes devis">

    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <div class="flex flex-col lg:flex-row gap-8">
            @include('account._sidebar')

            <div class="flex-1">
                <h1 class="text-2xl font-bold mb-6">Mes devis</h1>

                @if($quotes->isEmpty())
                    <div class="bg-white rounded-xl shadow-sm p-8 text-center">
                        <div class="w-16 h-16 mx-auto mb-4 bg-gray-100 rounded-full flex items-center justify-center">
                            <svg class="w-8 h-8 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        </div>
                        <p class="text-gray-500 mb-2">Aucun devis pour le moment.</p>
                        <a href="{{ route('catalogue.index') }}" class="inline-block text-bordeaux hover:underline text-sm font-medium">Parcourir le catalogue</a>
                    </div>
                @else
                    @php
                        $qsc = ['draft'=>'bg-gray-100 text-gray-700','sent'=>'bg-bordeaux-100 text-bordeaux','accepted'=>'bg-green-100 text-green-700','rejected'=>'bg-red-100 text-red-700','expired'=>'bg-yellow-100 text-yellow-700'];
                        $qsl = ['draft'=>'Brouillon','sent'=>'Envoye','accepted'=>'Accepte','rejected'=>'Rejete','expired'=>'Expire'];
                        $bsc = ['pending'=>'bg-gray-100 text-gray-400','sent'=>'bg-bordeaux-100 text-bordeaux','approved'=>'bg-green-100 text-green-600','rejected'=>'bg-red-100 text-red-500'];
                        $bsl = ['pending'=>'-','sent'=>'BAT en attente','approved'=>'BAT approuve','rejected'=>'BAT rejete'];
                    @endphp

                    {{-- Desktop table --}}
                    <div class="bg-white rounded-xl shadow-sm overflow-hidden hidden md:block">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 border-b">
                                <tr>
                                    <th class="px-6 py-3 text-left font-medium text-gray-500">Reference</th>
                                    <th class="px-6 py-3 text-left font-medium text-gray-500">Date</th>
                                    <th class="px-6 py-3 text-left font-medium text-gray-500">Statut</th>
                                    <th class="px-6 py-3 text-left font-medium text-gray-500">Validite</th>
                                    <th class="px-6 py-3 text-right font-medium text-gray-500">Total TTC</th>
                                    <th class="px-6 py-3 text-right font-medium text-gray-500">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                @foreach($quotes as $quote)
                                    <tr class="hover:bg-gray-50 transition">
                                        <td class="px-6 py-4 font-medium text-gray-900">{{ $quote->reference }}</td>
                                        <td class="px-6 py-4 text-gray-500">{{ $quote->created_at->format('d/m/Y') }}</td>
                                        <td class="px-6 py-4">
                                            <span class="px-2 py-1 text-xs rounded-full {{ $qsc[$quote->status] ?? 'bg-gray-100 text-gray-700' }}">
                                                {{ $qsl[$quote->status] ?? $quote->status }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">
                                            @if($quote->expires_at)
                                                @if($quote->isExpired())
                                                    <span class="text-xs text-red-500">Expire</span>
                                                @else
                                                    <span class="text-xs text-gray-500">J-{{ $quote->daysRemaining() }}</span>
                                                @endif
                                            @else
                                                <span class="text-gray-300">-</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-right font-medium">{{ number_format($quote->total_ttc, 2, ',', ' ') }} &euro;</td>
                                        <td class="px-6 py-4 text-right">
                                            <div class="flex items-center justify-end gap-2">
                                                @if($quote->bat_pdf)
                                                    <a href="{{ asset('storage/' . $quote->bat_pdf) }}" download
                                                       class="p-1.5 text-gray-400 hover:text-bordeaux transition" title="Telecharger PDF">
                                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                                        </svg>
                                                    </a>
                                                @endif
                                                @if($quote->status === 'sent' && !$quote->isExpired())
                                                    <a href="{{ route('payment.checkout') }}"
                                                       class="px-3 py-1 bg-green-600 text-white text-xs font-semibold rounded hover:bg-green-700 transition">
                                                        Passer commande
                                                    </a>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Mobile cards --}}
                    <div class="md:hidden space-y-3">
                        @foreach($quotes as $quote)
                            <div class="bg-white rounded-xl shadow-sm p-4">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="font-medium text-gray-900">{{ $quote->reference }}</span>
                                    <span class="font-semibold">{{ number_format($quote->total_ttc, 2, ',', ' ') }} &euro;</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-xs text-gray-400">{{ $quote->created_at->format('d/m/Y') }}</span>
                                    <div class="flex items-center gap-2">
                                        <span class="px-2 py-0.5 text-xs rounded-full {{ $qsc[$quote->status] ?? '' }}">{{ $qsl[$quote->status] ?? $quote->status }}</span>
                                        @if($quote->bat_status && $quote->bat_status !== 'pending')
                                            <span class="px-2 py-0.5 text-xs rounded-full {{ $bsc[$quote->bat_status] ?? '' }}">{{ $bsl[$quote->bat_status] ?? '' }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-4">
                        {{ $quotes->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

</x-layouts.app>
