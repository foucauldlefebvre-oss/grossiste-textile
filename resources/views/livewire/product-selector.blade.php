<div class="space-y-5">
    {{-- Couleurs --}}
    @if($colors->count() > 0)
        <div>
            <p class="text-xs uppercase tracking-wider text-gray-500 mb-2">Couleur</p>
            <div class="flex flex-wrap gap-2">
                @foreach($colors as $c)
                    <button type="button"
                            wire:click="selectColor({{ $c->id }})"
                            class="w-8 h-8 rounded-full border-2 transition {{ $selectedColorId === $c->id ? 'border-bordeaux ring-2 ring-bordeaux/30' : 'border-gray-200 hover:border-gray-400' }}"
                            style="background: {{ $c->hex_code ?: '#ddd' }}"
                            title="{{ $c->name }}"
                            aria-label="{{ $c->name }}"></button>
                @endforeach
            </div>
            @if($color)
                <p class="text-sm text-gray-700 mt-2">{{ $color->name }}</p>
            @endif
        </div>
    @endif

    {{-- Tailles + quantités --}}
    @auth
        @if($color && $color->sizes->count() > 0)
            <div>
                <p class="text-xs uppercase tracking-wider text-gray-500 mb-2">Quantité par taille</p>
                <div class="grid grid-cols-3 sm:grid-cols-4 gap-2">
                    @foreach($color->sizes as $s)
                        <label class="flex flex-col items-center gap-1 border rounded-lg p-2 {{ ($quantities[$s->id] ?? 0) > 0 ? 'border-bordeaux bg-bordeaux-50' : 'border-gray-200' }}">
                            <span class="text-sm font-medium">{{ $s->name }}</span>
                            <span class="text-[10px] text-gray-400">{{ $s->stock ?? 0 }} dispo</span>
                            <input type="number"
                                   min="0"
                                   max="{{ $s->stock ?? 9999 }}"
                                   wire:model.live.debounce.500ms="quantities.{{ $s->id }}"
                                   class="w-full text-center text-sm border-gray-300 rounded">
                        </label>
                    @endforeach
                </div>
            </div>

            {{-- Récap --}}
            <div class="bg-gray-50 rounded-lg p-4 space-y-2">
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600">Quantité totale</span>
                    <span class="font-medium">{{ $this->totalQuantity }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600">Prix unitaire HT</span>
                    <span class="font-medium">{{ number_format($this->previewUnitPrice, 2, ',', ' ') }} €</span>
                </div>
                <div class="flex justify-between text-base pt-2 border-t">
                    <span class="font-semibold">Total HT</span>
                    <span class="font-bold text-bordeaux">{{ number_format($this->previewUnitPrice * max(1, $this->totalQuantity), 2, ',', ' ') }} €</span>
                </div>
            </div>

            @if($flash)
                <p class="text-sm text-green-700">{{ $flash }}</p>
            @endif

            <button type="button"
                    wire:click="addToCart"
                    @disabled($this->totalQuantity < 1)
                    class="w-full px-4 py-3 bg-bordeaux text-white font-semibold rounded-lg hover:bg-bordeaux-dark transition disabled:opacity-50 disabled:cursor-not-allowed">
                Ajouter au panier
            </button>
        @else
            <p class="text-sm text-gray-500 italic">Aucune taille disponible pour cette couleur.</p>
        @endif
    @else
        {{-- Visiteur non connecté : pas de prix, pas d'ajout --}}
        <div class="bg-bordeaux-50 border border-bordeaux-200 rounded-lg p-5 text-center">
            <p class="text-sm text-gray-700 mb-3">
                Tarifs grossiste réservés aux comptes professionnels.
            </p>
            <a href="{{ route('register') }}" class="inline-block px-5 py-2.5 bg-bordeaux text-white font-semibold rounded-lg hover:bg-bordeaux-dark transition text-sm">
                Créer un compte gratuit pour voir les prix
            </a>
            <p class="text-xs text-gray-500 mt-3">
                Déjà inscrit ? <a href="{{ route('login') }}" class="underline hover:text-bordeaux">Connectez-vous</a>
            </p>
        </div>
    @endauth
</div>
