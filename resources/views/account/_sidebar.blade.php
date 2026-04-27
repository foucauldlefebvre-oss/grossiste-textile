@php
    $links = [
        ['route' => 'account.dashboard', 'label' => 'Tableau de bord', 'short' => 'Accueil', 'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
        ['route' => 'account.orders', 'label' => 'Mes commandes', 'short' => 'Commandes', 'icon' => 'M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z'],
        ['route' => 'account.invoices', 'label' => 'Mes factures', 'short' => 'Factures', 'icon' => 'M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z'],
        ['route' => 'account.quotes', 'label' => 'Mes devis', 'short' => 'Devis', 'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
        ['route' => 'account.addresses', 'label' => 'Mes adresses', 'short' => 'Adresses', 'icon' => 'M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z'],
        ['route' => 'account.profile', 'label' => 'Mon profil', 'short' => 'Profil', 'icon' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'],
    ];
@endphp

{{-- Mobile: horizontal scrollable tabs --}}
<div class="lg:hidden mb-6 -mx-4 px-4 overflow-x-auto">
    <nav class="flex gap-1 bg-white rounded-xl shadow-sm p-1.5 min-w-max">
        @foreach($links as $link)
            <a href="{{ route($link['route']) }}"
               class="flex items-center gap-1.5 px-3 py-2 rounded-lg text-xs font-medium whitespace-nowrap transition
                      {{ request()->routeIs($link['route'] . '*') ? 'bg-bordeaux-50 text-bordeaux' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900' }}">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $link['icon'] }}" />
                </svg>
                {{ $link['short'] }}
            </a>
        @endforeach
    </nav>
</div>

{{-- Desktop: vertical sidebar --}}
<aside class="hidden lg:block lg:w-56 flex-shrink-0">
    <nav class="bg-white rounded-xl shadow-sm p-4">
        <div class="space-y-1">
            @foreach($links as $link)
                <a href="{{ route($link['route']) }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition
                          {{ request()->routeIs($link['route'] . '*') ? 'bg-bordeaux-50 text-bordeaux' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $link['icon'] }}" />
                    </svg>
                    {{ $link['label'] }}
                </a>
            @endforeach
        </div>
        <div class="border-t mt-3 pt-3">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-gray-400 hover:text-red-500 hover:bg-red-50 transition w-full">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    Deconnexion
                </button>
            </form>
        </div>
    </nav>
</aside>
