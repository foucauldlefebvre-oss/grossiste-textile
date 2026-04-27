<x-layouts.app title="Charger vos fichiers — {{ $order->reference }}">

    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

        {{-- Success banner --}}
        <div class="bg-green-50 border border-green-200 rounded-xl p-4 mb-6 flex items-center gap-3">
            <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            <div>
                <p class="font-semibold text-green-800">Commande {{ $order->reference }} confirmee !</p>
                <p class="text-xs text-green-600">Paiement enregistre. Chargez vos fichiers ci-dessous pour lancer la production.</p>
            </div>
        </div>

        {{-- Stepper --}}
        <div class="flex items-center justify-center gap-2 mb-8 text-xs">
            @foreach([
                ['label' => 'Adresse', 'done' => true],
                ['label' => 'Paiement', 'done' => true],
                ['label' => 'Fichiers', 'done' => false, 'active' => true],
                ['label' => 'BAT', 'done' => false],
                ['label' => 'Production', 'done' => false],
                ['label' => 'Expedition', 'done' => false],
            ] as $step)
                @if(!$loop->first)
                    <div class="w-6 h-0.5 {{ ($step['done'] ?? false) ? 'bg-green-500' : 'bg-gray-200' }}"></div>
                @endif
                <div class="flex items-center gap-1">
                    <div class="w-6 h-6 rounded-full flex items-center justify-center text-[10px] font-bold
                        {{ ($step['done'] ?? false) ? 'bg-green-500 text-white' : (($step['active'] ?? false) ? 'bg-bordeaux text-white' : 'bg-gray-200 text-gray-400') }}">
                        @if($step['done'] ?? false)
                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        @else
                            {{ $loop->index + 1 }}
                        @endif
                    </div>
                    <span class="{{ ($step['active'] ?? false) ? 'text-bordeaux font-semibold' : (($step['done'] ?? false) ? 'text-green-700' : 'text-gray-400') }}">{{ $step['label'] }}</span>
                </div>
            @endforeach
        </div>

        {{-- Upload section --}}
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h2 class="text-lg font-bold mb-2">Chargez vos fichiers de marquage</h2>
            <p class="text-sm text-gray-500 mb-6">Envoyez vos logos et visuels pour que nous puissions preparer votre Bon A Tirer (BAT). Formats acceptes : PNG, JPG, SVG, AI, EPS, PDF.</p>

            <livewire:order-file-upload :order="$order" />
        </div>

        <div class="mt-6 flex justify-between">
            <a href="{{ route('account.orders') }}" class="px-6 py-2.5 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50 transition text-sm">
                Mes commandes
            </a>
            <a href="{{ route('catalogue.index') }}" class="px-6 py-2.5 bg-bordeaux text-white font-semibold rounded-lg hover:bg-bordeaux-dark transition text-sm">
                Continuer mes achats
            </a>
        </div>
    </div>

</x-layouts.app>
