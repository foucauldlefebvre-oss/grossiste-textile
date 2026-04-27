<x-layouts.app title="Mes factures">

    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <div class="flex flex-col lg:flex-row gap-8">
            @include('account._sidebar')

            <div class="flex-1">
                <h1 class="text-2xl font-bold mb-6">Mes factures</h1>

                @if($invoices->isEmpty())
                    <div class="bg-white rounded-xl shadow-sm p-8 text-center">
                        <div class="w-16 h-16 mx-auto mb-4 bg-gray-100 rounded-full flex items-center justify-center">
                            <svg class="w-8 h-8 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                        </div>
                        <p class="text-gray-500 mb-2">Aucune facture pour le moment.</p>
                    </div>
                @else
                    @php
                        $sc = ['deposit'=>'bg-yellow-100 text-yellow-700','paid'=>'bg-green-100 text-green-700','settled'=>'bg-blue-100 text-blue-700'];
                        $sl = ['deposit'=>'Acompte','paid'=>'Payee','settled'=>'Soldee'];
                    @endphp

                    {{-- Desktop table --}}
                    <div class="bg-white rounded-xl shadow-sm overflow-hidden hidden md:block">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 border-b">
                                <tr>
                                    <th class="px-6 py-3 text-left font-medium text-gray-500">Numero</th>
                                    <th class="px-6 py-3 text-left font-medium text-gray-500">Date</th>
                                    <th class="px-6 py-3 text-left font-medium text-gray-500">Commande</th>
                                    <th class="px-6 py-3 text-left font-medium text-gray-500">Statut</th>
                                    <th class="px-6 py-3 text-right font-medium text-gray-500">Total TTC</th>
                                    <th class="px-6 py-3 text-center font-medium text-gray-500">PDF</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                @foreach($invoices as $invoice)
                                    <tr class="hover:bg-gray-50 transition">
                                        <td class="px-6 py-4 font-medium">{{ $invoice->number }}</td>
                                        <td class="px-6 py-4 text-gray-500">{{ $invoice->issued_at->format('d/m/Y') }}</td>
                                        <td class="px-6 py-4">
                                            @if($invoice->order)
                                                <a href="{{ route('account.orders.show', $invoice->order->reference) }}" class="text-bordeaux hover:underline">{{ $invoice->order->reference }}</a>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="px-2 py-1 text-xs rounded-full {{ $sc[$invoice->status] ?? 'bg-gray-100 text-gray-700' }}">{{ $sl[$invoice->status] ?? $invoice->status }}</span>
                                        </td>
                                        <td class="px-6 py-4 text-right font-medium">{{ number_format($invoice->total_ttc, 2, ',', ' ') }} &euro;</td>
                                        <td class="px-6 py-4 text-center">
                                            <a href="{{ route('account.invoices.download', $invoice) }}" class="inline-flex items-center gap-1 text-bordeaux hover:text-bordeaux-700 text-sm font-medium">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                                PDF
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Mobile cards --}}
                    <div class="md:hidden space-y-3">
                        @foreach($invoices as $invoice)
                            <div class="bg-white rounded-xl shadow-sm p-4">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="font-medium">{{ $invoice->number }}</span>
                                    <span class="font-semibold">{{ number_format($invoice->total_ttc, 2, ',', ' ') }} &euro;</span>
                                </div>
                                <div class="flex items-center justify-between mb-3">
                                    <span class="text-xs text-gray-400">{{ $invoice->issued_at->format('d/m/Y') }}</span>
                                    <span class="px-2 py-0.5 text-xs rounded-full {{ $sc[$invoice->status] ?? 'bg-gray-100 text-gray-700' }}">{{ $sl[$invoice->status] ?? $invoice->status }}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    @if($invoice->order)
                                        <a href="{{ route('account.orders.show', $invoice->order->reference) }}" class="text-xs text-bordeaux hover:underline">{{ $invoice->order->reference }}</a>
                                    @endif
                                    <a href="{{ route('account.invoices.download', $invoice) }}" class="inline-flex items-center gap-1 text-xs text-bordeaux hover:underline font-medium">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                        Telecharger
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-4">
                        {{ $invoices->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

</x-layouts.app>
