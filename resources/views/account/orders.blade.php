<x-layouts.app title="Mes commandes">

    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <div class="flex flex-col lg:flex-row gap-8">
            @include('account._sidebar')

            <div class="flex-1">
                <h1 class="text-2xl font-bold mb-6">Mes commandes</h1>

                @if($orders->isEmpty())
                    <div class="bg-white rounded-xl shadow-sm p-8 text-center">
                        <div class="w-16 h-16 mx-auto mb-4 bg-gray-100 rounded-full flex items-center justify-center">
                            <svg class="w-8 h-8 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                        </div>
                        <p class="text-gray-500 mb-2">Aucune commande pour le moment.</p>
                        <a href="{{ route('catalogue.index') }}" class="inline-block text-bordeaux hover:underline text-sm font-medium">Parcourir le catalogue</a>
                    </div>
                @else
                    @php
                        $sc = ['pending'=>'bg-gray-100 text-gray-700','confirmed'=>'bg-bordeaux-100 text-bordeaux','in_production'=>'bg-yellow-100 text-yellow-700','shipped'=>'bg-indigo-100 text-indigo-700','delivered'=>'bg-green-100 text-green-700','cancelled'=>'bg-red-100 text-red-700','refunded'=>'bg-red-100 text-red-700'];
                        $sl = ['pending'=>'En attente','confirmed'=>'Confirmee','in_production'=>'En production','shipped'=>'Expediee','delivered'=>'Livree','cancelled'=>'Annulee','refunded'=>'Remboursee'];
                        $pc = ['pending'=>'text-gray-500','paid'=>'text-green-600','partial'=>'text-yellow-600','refunded'=>'text-red-500'];
                        $pl = ['pending'=>'En attente','paid'=>'Paye','partial'=>'Partiel','refunded'=>'Rembourse'];
                    @endphp

                    {{-- Desktop table --}}
                    <div class="bg-white rounded-xl shadow-sm overflow-hidden hidden md:block">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 border-b">
                                <tr>
                                    <th class="px-6 py-3 text-left font-medium text-gray-500">Reference</th>
                                    <th class="px-6 py-3 text-left font-medium text-gray-500">Date</th>
                                    <th class="px-6 py-3 text-left font-medium text-gray-500">Statut</th>
                                    <th class="px-6 py-3 text-left font-medium text-gray-500">Paiement</th>
                                    <th class="px-6 py-3 text-right font-medium text-gray-500">Total TTC</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                @foreach($orders as $order)
                                    <tr class="hover:bg-gray-50 transition">
                                        <td class="px-6 py-4">
                                            <a href="{{ route('account.orders.show', $order->reference) }}" class="text-bordeaux hover:underline font-medium">{{ $order->reference }}</a>
                                        </td>
                                        <td class="px-6 py-4 text-gray-500">{{ $order->created_at->format('d/m/Y') }}</td>
                                        <td class="px-6 py-4">
                                            <span class="px-2 py-1 text-xs rounded-full {{ $sc[$order->status] ?? '' }}">{{ $sl[$order->status] ?? $order->status }}</span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="{{ $pc[$order->payment_status] ?? '' }}">{{ $pl[$order->payment_status] ?? ucfirst($order->payment_status) }}</span>
                                        </td>
                                        <td class="px-6 py-4 text-right font-medium">{{ number_format($order->total_ttc, 2, ',', ' ') }} &euro;</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Mobile cards --}}
                    <div class="md:hidden space-y-3">
                        @foreach($orders as $order)
                            <a href="{{ route('account.orders.show', $order->reference) }}" class="block bg-white rounded-xl shadow-sm p-4 hover:shadow-md transition">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="font-medium text-bordeaux">{{ $order->reference }}</span>
                                    <span class="font-semibold">{{ number_format($order->total_ttc, 2, ',', ' ') }} &euro;</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-xs text-gray-400">{{ $order->created_at->format('d/m/Y') }}</span>
                                    <div class="flex items-center gap-2">
                                        <span class="px-2 py-0.5 text-xs rounded-full {{ $sc[$order->status] ?? '' }}">{{ $sl[$order->status] ?? $order->status }}</span>
                                        <span class="text-xs {{ $pc[$order->payment_status] ?? '' }}">{{ $pl[$order->payment_status] ?? ucfirst($order->payment_status) }}</span>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>

                    <div class="mt-4">
                        {{ $orders->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

</x-layouts.app>
