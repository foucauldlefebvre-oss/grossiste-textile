<a href="{{ $product->url }}" class="group bg-white rounded-xl shadow-sm hover:shadow-md transition overflow-hidden">
    <div class="aspect-square bg-gray-100 relative overflow-hidden">
        @if($product->main_image)
            <img src="{{ $product->imageUrl() }}" alt="{{ $product->name }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-300" loading="lazy">
        @else
            <div class="w-full h-full flex items-center justify-center text-gray-300">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
            </div>
        @endif
        @if($product->is_featured)
            <span class="absolute top-2 left-2 px-2 py-0.5 bg-gradient-to-r from-amber-500 to-amber-600 text-white text-[0.65em] font-semibold rounded-full shadow-sm flex items-center gap-1">
                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                Best-seller
            </span>
        @endif
    </div>
    <div class="p-4">
        <p class="text-xs text-gray-400 mb-1">{{ $product->reference }} &middot; {{ $product->supplier }}</p>
        <h3 class="font-semibold text-gray-900 group-hover:text-bordeaux transition line-clamp-2">{{ $product->name }}</h3>
        <div class="flex items-center justify-between mt-3">
            @if($product->display_price > 0)
                <span class="text-bordeaux font-bold"><span class="text-xs font-normal text-gray-400">A partir de </span>{{ number_format($product->display_price, 2, ',', ' ') }} &euro; <span class="text-xs font-normal">HT</span></span>
            @else
                <span class="text-gray-400 text-sm">Sur devis</span>
            @endif
            @if($product->colors_count ?? $product->colors->count())
                <div class="flex -space-x-1">
                    @foreach($product->colors->take(5) as $color)
                        @if($color->hex_code_secondary)
                            <span class="w-5 h-5 rounded-full border-2 border-white shadow-sm" style="background: linear-gradient(135deg, {{ $color->hex_code }} 50%, {{ $color->hex_code_secondary }} 50%); {{ $color->hex_code === '#FFFFFF' || $color->hex_code_secondary === '#FFFFFF' ? 'box-shadow: inset 0 0 0 1px #d1d5db, 0 1px 2px 0 rgba(0,0,0,0.05);' : '' }}" title="{{ $color->name }}" aria-label="Coloris {{ $color->name }}"></span>
                        @else
                            <span class="w-5 h-5 rounded-full border-2 border-white shadow-sm {{ empty($color->hex_code) ? 'bg-gray-300' : '' }}" @if($color->hex_code) style="background-color: {{ $color->hex_code }}; {{ $color->hex_code === '#FFFFFF' ? 'box-shadow: inset 0 0 0 1px #d1d5db, 0 1px 2px 0 rgba(0,0,0,0.05);' : '' }}" @endif title="{{ $color->name }}" aria-label="Coloris {{ $color->name }}"></span>
                        @endif
                    @endforeach
                    @if($product->colors->count() > 5)
                        <span class="w-5 h-5 rounded-full border-2 border-white shadow-sm bg-gray-200 text-[8px] flex items-center justify-center font-bold text-gray-500">+{{ $product->colors->count() - 5 }}</span>
                    @endif
                </div>
            @endif
        </div>
        @if($product->material)
            <p class="text-xs text-gray-400 mt-2">{{ $product->material }}</p>
        @endif
    </div>
</a>
