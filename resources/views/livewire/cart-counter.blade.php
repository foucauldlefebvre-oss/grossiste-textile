<div>
    @auth
        <button wire:click="openSidebar"
                type="button"
                class="relative p-2 text-gray-600 hover:text-bordeaux transition"
                aria-label="Ouvrir le panier">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
            @if($count > 0)
                <span class="absolute -top-0.5 -right-0.5 bg-bordeaux text-white text-xs font-bold rounded-full min-w-[18px] h-[18px] flex items-center justify-center px-1">
                    {{ $count > 99 ? '99+' : $count }}
                </span>
            @endif
        </button>
    @endauth
</div>
