<button id="quote-counter-btn" wire:click="$dispatch('toggle-floating-quote')" class="relative p-2 text-gray-500 hover:text-bordeaux transition" title="Mon devis">
    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
    </svg>
    @if($count > 0)
        <span id="quote-badge" class="absolute -top-1 -right-1 w-5 h-5 bg-bordeaux text-white text-xs font-bold rounded-full flex items-center justify-center transition-transform">
            {{ $count }}
        </span>
    @endif
</button>
