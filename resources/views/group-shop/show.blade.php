<x-layouts.app title="{{ $groupShop->name }}">

    {{-- Header banner --}}
    <div class="bg-gradient-to-r from-blue-600 to-blue-800 text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex items-center gap-4">
                @if($groupShop->logo)
                    <img src="{{ Storage::url($groupShop->logo) }}" alt="" class="h-14 w-14 object-contain bg-white rounded-lg p-1">
                @endif
                <div>
                    <h1 class="text-2xl font-bold">{{ $groupShop->name }}</h1>
                    @if($groupShop->description)
                        <p class="text-blue-100 text-sm mt-1">{{ $groupShop->description }}</p>
                    @endif
                </div>
            </div>
            @if($groupShop->closes_at)
                <div class="mt-3 flex items-center gap-2 text-sm text-blue-200">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Fermeture le {{ $groupShop->closes_at->format('d/m/Y a H:i') }}</span>
                </div>
            @endif
        </div>
    </div>

    {{-- Products grid --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @if($groupShop->products->isEmpty())
            <div class="text-center py-16 text-gray-400">
                <p class="text-lg">Aucun produit disponible pour le moment.</p>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($groupShop->products as $gsProduct)
                    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                        {{-- Product image --}}
                        <div class="aspect-square bg-gray-100 relative">
                            @if($gsProduct->product->main_image)
                                <img src="{{ Storage::url($gsProduct->product->main_image) }}" alt="" class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-gray-300">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                            @endif
                            @if($gsProduct->technique)
                                <span class="absolute top-2 right-2 px-2 py-1 bg-blue-600 text-white text-xs font-medium rounded">
                                    {{ $gsProduct->technique->name }}
                                </span>
                            @endif
                        </div>

                        {{-- Product info --}}
                        <div class="p-4">
                            <h3 class="font-semibold text-gray-900">{{ $gsProduct->product->name }}</h3>
                            <p class="text-sm text-gray-500 mt-0.5">{{ $gsProduct->product->reference }}</p>

                            @if($gsProduct->fixed_price)
                                <p class="mt-2 text-lg font-bold text-blue-600">
                                    {{ number_format($gsProduct->fixed_price, 2, ',', ' ') }} &euro; HT
                                </p>
                            @else
                                <p class="mt-2 text-sm text-gray-500">Prix selon quantite</p>
                            @endif

                            {{-- Available colors preview --}}
                            @php
                                $colors = $gsProduct->allowed_colors
                                    ? $gsProduct->product->colors->whereIn('id', $gsProduct->allowed_colors)
                                    : $gsProduct->product->colors;
                            @endphp
                            @if($colors->isNotEmpty())
                                <div class="flex flex-wrap gap-1 mt-2">
                                    @foreach($colors->take(8) as $color)
                                        <span class="w-5 h-5 rounded-full border border-gray-200" style="background-color: {{ $color->hex_code }}" title="{{ $color->name }}"></span>
                                    @endforeach
                                    @if($colors->count() > 8)
                                        <span class="text-xs text-gray-400 self-center">+{{ $colors->count() - 8 }}</span>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Order form --}}
            <div class="mt-12">
                <livewire:group-shop-order-form :group-shop-id="$groupShop->id" />
            </div>
        @endif
    </div>

</x-layouts.app>
