<x-layouts.app title="Mon compte">

    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <div class="flex flex-col lg:flex-row gap-8">

            {{-- Sidebar --}}
            @include('account._sidebar')

            {{-- Content --}}
            <div class="flex-1">
                <h1 class="text-2xl font-bold mb-6">Bonjour, {{ Auth::user()->name }}</h1>

                {{-- Stats --}}
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
                    <a href="{{ route('account.orders') }}" class="bg-white rounded-xl shadow-sm p-5 hover:shadow-md hover:-translate-y-0.5 transition-all duration-200">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-bordeaux-50 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-bordeaux" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Commandes</p>
                                <p class="text-2xl font-bold text-bordeaux">{{ $stats['orders'] }}</p>
                            </div>
                        </div>
                    </a>
                    {{-- TODO 2b: bloc Devis supprimé (route account.quotes dégagée) --}}
                    <a href="{{ route('account.dashboard') }}" class="hidden bg-white rounded-xl shadow-sm p-5">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-purple-50 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Devis</p>
                                <p class="text-2xl font-bold text-purple-600">0</p>
                            </div>
                        </div>
                    </a>
                    <a href="{{ route('account.orders') }}" class="bg-white rounded-xl shadow-sm p-5 hover:shadow-md hover:-translate-y-0.5 transition-all duration-200">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-orange-50 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">En cours</p>
                                <p class="text-2xl font-bold text-orange-500">{{ $stats['pending_orders'] }}</p>
                            </div>
                        </div>
                    </a>
                </div>

                @if($stats['orders'] === 0 && $stats['quotes'] === 0)
                    {{-- Welcome state for new users --}}
                    <div class="bg-white rounded-xl shadow-sm p-8 text-center">
                        <div class="w-20 h-20 mx-auto mb-5 bg-bordeaux-50 rounded-full flex items-center justify-center">
                            <svg class="w-10 h-10 text-bordeaux" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/></svg>
                        </div>
                        <h2 class="text-xl font-bold text-gray-900 mb-2">Bienvenue sur votre espace !</h2>
                        <p class="text-gray-500 mb-6 max-w-md mx-auto">Commencez par explorer notre catalogue de plus de 500 produits textiles personnalisables. Configurez votre marquage et obtenez un devis instantane.</p>
                        <div class="flex flex-col sm:flex-row gap-3 justify-center">
                            <a href="{{ route('catalogue.index') }}" class="px-6 py-3 bg-bordeaux text-white font-semibold rounded-lg hover:bg-bordeaux-dark transition">
                                Parcourir le catalogue
                            </a>
                            <a href="{{ route('account.profile') }}" class="px-6 py-3 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50 transition">
                                Completer mon profil
                            </a>
                        </div>
                    </div>
                @else

                {{-- Recent orders --}}
                <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b bg-gray-50 flex items-center justify-between">
                        <h2 class="font-semibold">Dernieres commandes</h2>
                        <a href="{{ route('account.orders') }}" class="text-sm text-bordeaux hover:underline">Voir tout</a>
                    </div>

                    @if($recentOrders->isEmpty())
                        <div class="px-6 py-8 text-center text-gray-400">
                            <p>Aucune commande pour le moment.</p>
                        </div>
                    @else
                        <div class="divide-y">
                            @foreach($recentOrders as $order)
                                <a href="{{ route('account.orders.show', $order->reference) }}" class="block px-6 py-4 hover:bg-gray-50 transition">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <span class="font-medium">{{ $order->reference }}</span>
                                            <span class="text-sm text-gray-500 ml-2">{{ $order->created_at->format('d/m/Y') }}</span>
                                        </div>
                                        <div class="flex items-center gap-3">
                                            <span class="text-sm font-medium">{{ number_format($order->total_ttc, 2, ',', ' ') }} &euro;</span>
                                            @php
                                                $statusColors = [
                                                    'pending' => 'bg-gray-100 text-gray-700',
                                                    'confirmed' => 'bg-bordeaux-100 text-bordeaux',
                                                    'in_production' => 'bg-yellow-100 text-yellow-700',
                                                    'shipped' => 'bg-indigo-100 text-indigo-700',
                                                    'delivered' => 'bg-green-100 text-green-700',
                                                    'cancelled' => 'bg-red-100 text-red-700',
                                                    'refunded' => 'bg-red-100 text-red-700',
                                                ];
                                                $statusLabels = [
                                                    'pending' => 'En attente',
                                                    'confirmed' => 'Confirmee',
                                                    'in_production' => 'En production',
                                                    'shipped' => 'Expediee',
                                                    'delivered' => 'Livree',
                                                    'cancelled' => 'Annulee',
                                                    'refunded' => 'Remboursee',
                                                ];
                                            @endphp
                                            <span class="px-2 py-1 text-xs rounded-full {{ $statusColors[$order->status] ?? 'bg-gray-100 text-gray-700' }}">
                                                {{ $statusLabels[$order->status] ?? $order->status }}
                                            </span>
                                        </div>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
                @endif
            </div>
        </div>
    </div>

</x-layouts.app>
