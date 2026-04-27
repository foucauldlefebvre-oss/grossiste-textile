<div x-data="{
    qtys: @js($sizeQuantities),
    grid: @js($this->priceGrid),
    get total() {
        return Object.values(this.qtys).reduce((s, q) => s + (parseInt(q) || 0), 0);
    },
    isActive(i) {
        const tier = this.grid[i];
        if (!tier || this.total <= 0) return false;
        return this.total >= tier.min_qty && (tier.max_qty === null || this.total <= tier.max_qty);
    },
    unitPrice: @js($estimate['unit_price_ht'] ?? 0),
    get estimateHt() {
        return Math.round(this.total * this.unitPrice * 100) / 100;
    },
    get estimateTva() {
        return Math.round(this.estimateHt * 0.20 * 100) / 100;
    },
    get estimateTtc() {
        return Math.round(this.estimateHt * 1.20 * 100) / 100;
    },
    updatePrice() {
        if (this.total <= 0) { this.unitPrice = 0; return; }
        for (let i = this.grid.length - 1; i >= 0; i--) {
            if (this.total >= this.grid[i].min_qty) {
                this.unitPrice = this.grid[i].unit_price;
                return;
            }
        }
        this.unitPrice = this.grid.length > 0 ? this.grid[0].unit_price : 0;
    },
    sync() {
        $wire.syncQuantities({...this.qtys});
    }
}" x-init="$watch('qtys', () => { updatePrice(); })" @quantities-reset.window="qtys = @js($sizeQuantities); updatePrice()">
    {{-- Success message --}}
    @if(session('quote-success'))
        <div class="mb-4 p-3 bg-green-50 border border-green-200 text-green-700 rounded-lg text-sm font-medium">
            {{ session('quote-success') }}
            <a href="{{ route('devis') }}" class="underline ml-1">Voir le devis</a>
        </div>
    @endif

    {{-- Stepper --}}
    @php
        $step = 1;
        if ($selectedColorId) $step = 2;
        if ($selectedColorId && ($this->hasAnySizeQuantity || !$this->availableSizes->count())) $step = 3;
        $steps = [
            ['label' => 'Coloris', 'num' => 1],
            ['label' => 'Tailles', 'num' => 2],
            ['label' => 'Récapitulatif', 'num' => 3],
        ];
    @endphp
    <div class="flex items-center gap-1 mb-6 overflow-x-auto">
        @foreach($steps as $i => $s)
            <div class="flex items-center {{ $i > 0 ? 'flex-1' : '' }}">
                @if($i > 0)
                    <div class="flex-1 h-0.5 {{ $step > $s['num'] - 1 ? 'bg-blue-500' : 'bg-gray-200' }}"></div>
                @endif
                <div class="flex flex-col items-center gap-1 flex-shrink-0">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold {{ $step >= $s['num'] ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-400' }}">
                        @if($step > $s['num'])
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        @else
                            {{ $s['num'] }}
                        @endif
                    </div>
                    <span class="text-[10px] font-medium {{ $step >= $s['num'] ? 'text-blue-600' : 'text-gray-400' }}">{{ $s['label'] }}</span>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Colors --}}
    @if($product->colors->count())
        <div class="mb-5">
            <h3 class="font-semibold text-sm text-gray-700 mb-2">
                Coloris
                @if($this->selectedColor)
                    <span class="font-normal text-gray-400">- {{ $this->selectedColor->name }}</span>
                @endif
            </h3>
            <div class="flex flex-wrap gap-2">
                @foreach($product->colors as $color)
                    <button wire:click="selectColor({{ $color->id }})"
                            wire:loading.class="opacity-50"
                            wire:loading.attr="disabled"
                            class="w-10 h-10 rounded-full border-2 transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 {{ $selectedColorId === $color->id ? 'border-blue-600 ring-2 ring-blue-200' : 'border-gray-200 hover:border-gray-400' }}"
                            style="background-color: {{ $color->hex_code }}; {{ $color->hex_code === '#FFFFFF' ? 'box-shadow: inset 0 0 0 1px #d1d5db;' : '' }}"
                            title="{{ $color->name }}"
                            aria-label="Coloris {{ $color->name }}">
                    </button>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Degressive price grid --}}
    @if(count($this->priceGrid))
        <div class="mb-5">
            <h3 class="font-semibold text-sm text-gray-700 mb-2">Prix unitaire HT selon la quantité</h3>
            <div class="overflow-x-auto -mx-1">
                <table class="w-full text-xs border-collapse">
                    <thead>
                        <tr>
                            @foreach($this->priceGrid as $i => $tier)
                                <th class="px-1.5 py-1.5 text-center font-medium border transition-colors"
                                    :class="isActive({{ $i }}) ? 'bg-blue-600 text-white' : 'bg-gray-50 text-gray-500'">
                                    {{ $tier['label'] }}
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            @foreach($this->priceGrid as $i => $tier)
                                <td class="px-1.5 py-2 text-center font-semibold border transition-colors"
                                    :class="isActive({{ $i }}) ? 'bg-blue-50 text-blue-700 ring-1 ring-inset ring-blue-200' : 'text-gray-700'">
                                    {{ number_format($tier['unit_price'], 2, ',', ' ') }}&euro;
                                </td>
                            @endforeach
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- Sizes / Quantities --}}
    @if($this->availableSizes->count())
        <div class="mb-5">
            <h3 class="font-semibold text-sm text-gray-700 mb-2">
                Tailles & Quantités
                <span class="font-normal text-gray-400" x-show="total > 0" x-cloak>- Total : <span x-text="total"></span> pièce(s)</span>
            </h3>

            {{-- Desktop: horizontal table --}}
            <div class="hidden sm:block overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr>
                            @foreach($this->availableSizes as $size)
                                <th class="px-2 py-1 text-center font-medium text-gray-600 border-b">{{ $size->size }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            @foreach($this->availableSizes as $size)
                                <td class="px-2 py-2 text-center">
                                    <input type="number"
                                           x-model.number="qtys[{{ $size->id }}]"
                                           @change="sync()"
                                           @input.debounce.800ms="sync()"
                                           min="0"
                                           class="w-16 text-center border rounded-lg py-1.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                           placeholder="0">
                                </td>
                            @endforeach
                        </tr>
                        <tr>
                            @foreach($this->availableSizes as $size)
                                <td class="px-1 py-0.5 text-center">
                                    @if($size->stock > 0)
                                        <span class="text-[10px] {{ $size->stock > 20 ? 'text-green-600' : 'text-amber-600' }} font-medium">{{ $size->stock }} dispo</span>
                                    @else
                                        <span class="text-[10px] text-red-500 font-medium">Rupture</span>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    </tbody>
                </table>
            </div>

            {{-- Mobile: vertical list --}}
            <div class="sm:hidden space-y-2">
                @foreach($this->availableSizes as $size)
                    <div class="flex items-center justify-between gap-3 py-1.5">
                        <div class="min-w-[5rem]">
                            <label class="text-sm font-medium text-gray-700">{{ $size->size }}</label>
                            <div>
                                @if($size->stock > 0)
                                    <span class="text-[10px] {{ $size->stock > 20 ? 'text-green-600' : 'text-amber-600' }} font-medium">{{ $size->stock }} dispo</span>
                                @else
                                    <span class="text-[10px] text-red-500 font-medium">Rupture</span>
                                @endif
                            </div>
                        </div>
                        <input type="number"
                               x-model.number="qtys[{{ $size->id }}]"
                               @change="sync()"
                               @input.debounce.800ms="sync()"
                               min="0"
                               class="w-20 text-center border rounded-lg py-1.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               placeholder="0">
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Live price estimate --}}
    <div class="mb-5 bg-gray-50 rounded-lg p-4 space-y-2" x-show="total > 0" x-cloak>
        <div class="flex justify-between text-sm">
            <span class="text-gray-500">Prix unitaire HT</span>
            <span x-text="unitPrice.toFixed(2).replace('.', ',') + ' \u20AC'"></span>
        </div>
        <div class="border-t pt-2 flex justify-between font-semibold">
            <span>Total HT (<span x-text="total"></span> piece(s))</span>
            <span x-text="estimateHt.toFixed(2).replace('.', ',') + ' \u20AC'"></span>
        </div>
        <div class="flex justify-between text-sm text-gray-500">
            <span>TVA (20%)</span>
            <span x-text="estimateTva.toFixed(2).replace('.', ',') + ' \u20AC'"></span>
        </div>
        <div class="flex justify-between font-bold text-blue-600 text-lg">
            <span>Total TTC</span>
            <span x-text="estimateTtc.toFixed(2).replace('.', ',') + ' \u20AC'"></span>
        </div>
    </div>

    {{-- Add to quote button --}}
    <button @click="sync(); $nextTick(() => $wire.addToQuote())"
            wire:loading.attr="disabled"
            :disabled="total < 1"
            class="w-full py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition disabled:opacity-50 disabled:cursor-not-allowed">
        <span wire:loading.remove wire:target="addToQuote">Ajouter au devis</span>
        <span wire:loading wire:target="addToQuote">Ajout en cours...</span>
    </button>
</div>
