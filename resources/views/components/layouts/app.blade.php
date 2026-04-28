<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ isset($title) ? $title . ' - ' : '' }}Marquage Textile</title>

    {{-- SEO Meta --}}
    <meta name="description" content="{{ $metaDescription ?? 'Marquage Textile : broderie, serigraphie, impression textile personnalisee pour professionnels. Devis en ligne, livraison rapide.' }}">
    @php
        // Canonical: strip pagination params to avoid duplicate content
        $canonicalUrl = $canonical ?? url()->current();
        if (request()->has('page') && request()->get('page') == 1) {
            $canonicalUrl = url()->current();
        }
        $canonicalUrl = preg_replace('/[\?&]page=1\b/', '', $canonicalUrl);
        $canonicalUrl = rtrim(str_replace('?&', '?', $canonicalUrl), '?');
    @endphp
    <link rel="canonical" href="{{ $canonicalUrl }}">

    {{-- Open Graph --}}
    <meta property="og:title" content="{{ isset($title) ? $title . ' - Marquage Textile' : 'Marquage Textile - Personnalisation textile professionnelle' }}">
    <meta property="og:description" content="{{ $metaDescription ?? 'Broderie, serigraphie, impression textile personnalisee pour professionnels.' }}">
    <meta property="og:type" content="{{ $ogType ?? 'website' }}">
    <meta property="og:url" content="{{ $canonical ?? url()->current() }}">
    <meta property="og:site_name" content="Marquage Textile">
    <meta property="og:locale" content="fr_FR">
    @isset($ogImage)
        <meta property="og:image" content="{{ $ogImage }}">
    @endisset

    {{-- Twitter Card --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ isset($title) ? $title . ' - Marquage Textile' : 'Marquage Textile' }}">
    <meta name="twitter:description" content="{{ $metaDescription ?? 'Broderie, serigraphie, impression textile personnalisee pour professionnels.' }}">

    {{-- Structured Data --}}
    @hasSection('jsonld')
        @yield('jsonld')
    @else
        <script type="application/ld+json">
        {
            "@context": "https://schema.org",
            "@type": "Organization",
            "name": "Marquage Textile",
            "url": "{{ url('/') }}",
            "description": "Personnalisation textile professionnelle",
            "contactPoint": {
                "@type": "ContactPoint",
                "email": "contact@marquage-textile.fr",
                "contactType": "customer service"
            }
        }
        </script>
    @endif

    <link rel="icon" href="{{ asset('images/logo-icon.png') }}" type="image/png">
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon" sizes="32x32">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .sidebar-scroll { scrollbar-width: thin; scrollbar-color: #8B1A1A #f5f5f5; }
        .sidebar-scroll::-webkit-scrollbar { width: 5px; }
        .sidebar-scroll::-webkit-scrollbar-track { background: #f5f5f5; }
        .sidebar-scroll::-webkit-scrollbar-thumb { background: #8B1A1A; border-radius: 3px; }
        .sidebar-scroll::-webkit-scrollbar-thumb:hover { background: #6B1414; }
    </style>
    @livewireStyles
</head>
<body class="min-h-screen flex flex-col bg-gray-50 text-gray-900 antialiased">

    {{-- Top bar --}}
    <div class="bg-gray-900 text-gray-300 text-xs hidden sm:block">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between h-8">
            <div class="flex items-center gap-4">
                <a href="tel:+33320400690" class="flex items-center gap-1 hover:text-white transition">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                    03 20 40 06 90
                </a>
                <a href="mailto:contact@marquage-textile.fr" class="flex items-center gap-1 hover:text-white transition">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    contact@marquage-textile.fr
                </a>
            </div>
            <div class="flex items-center gap-3 text-gray-400">
                <span>Lun-Ven 9h-18h</span>
                <span class="text-gray-600">|</span>
                <span>Livraison France & Europe</span>
            </div>
        </div>
    </div>

    {{-- Header --}}
    @php
        $navCategories = \App\Models\Category::active()
            ->roots()
            ->where('slug', '!=', 'techniques-de-marquage')
            ->with(['children' => fn ($q) => $q->active()->orderBy('sort_order')->with(['children' => fn ($q2) => $q2->active()->orderBy('sort_order')])])
            ->orderBy('sort_order')
            ->get();
    @endphp
    <header class="bg-white shadow-sm sticky top-0 z-50">
        {{-- Main header bar --}}
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                {{-- Logo --}}
                <a href="{{ route('home') }}" class="flex items-center gap-1.5 shrink-0" style="position:relative;left:-20px;">
                    <img src="{{ asset('images/logo-icon.png') }}" alt="MT" class="h-7">
                    <img src="{{ asset('images/logo-full.svg') }}" alt="Marquage Textile" class="h-10 hidden sm:block">
                </a>

                {{-- Search + Actions --}}
                <div class="flex items-center gap-4">
                    <livewire:search-bar />
                    <livewire:cart-counter />

                    @auth
                        <div class="hidden lg:flex items-center gap-3">
                            <a href="{{ route('account.dashboard') }}" class="text-sm text-gray-600 hover:text-bordeaux transition font-medium">
                                {{ Auth::user()->name }}
                            </a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="text-sm text-gray-400 hover:text-red-500 transition">
                                    Deconnexion
                                </button>
                            </form>
                        </div>
                    @else
                        <a href="{{ route('login') }}" class="hidden lg:inline-flex text-sm text-gray-600 hover:text-bordeaux transition font-medium">
                            Connexion
                        </a>
                    @endauth

                    {{-- Mobile menu toggle --}}
                    <button id="mobile-menu-btn" class="lg:hidden p-2 text-gray-500 hover:text-bordeaux">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        {{-- Horizontal navigation bar --}}
        <div class="hidden lg:block border-t border-gray-100 bg-gray-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <nav class="flex items-center gap-1 h-10 text-sm">
                    <a href="{{ route('home') }}" class="px-3 py-1.5 text-gray-600 hover:text-bordeaux font-medium rounded transition">Accueil</a>
                    <a href="{{ route('catalogue.index') }}" class="px-3 py-1.5 text-gray-600 hover:text-bordeaux font-medium rounded transition">Catalogue</a>
                    {{-- TODO 2b: lien Techniques de marquage supprimé + sous-menu Contact (devis) supprimé --}}
                    @auth
                        <a href="{{ route('account.dashboard') }}" class="px-3 py-1.5 text-gray-600 hover:text-bordeaux font-medium rounded transition">Mon compte</a>
                    @endauth
                </nav>
            </div>
        </div>

        {{-- Mobile navigation --}}
        <div id="mobile-menu" class="hidden lg:hidden border-t bg-white max-h-[70vh] overflow-y-auto"
             x-data="{ expanded: null }">
            <div class="px-4 py-3 space-y-1">
                <a href="{{ route('home') }}" class="block py-2.5 text-gray-700 font-semibold">Accueil</a>
                <a href="{{ route('catalogue.index') }}" class="block py-2.5 text-gray-700 font-semibold border-t border-gray-50">Catalogue</a>

                @foreach($navCategories as $cat)
                    @if($cat->children->count())
                        <div class="border-t border-gray-50">
                            <button @click="expanded === {{ $cat->id }} ? expanded = null : expanded = {{ $cat->id }}"
                                    class="flex items-center justify-between w-full py-2.5 text-left">
                                <span class="text-sm font-medium text-bordeaux">{{ $cat->name }}</span>
                                <svg class="w-4 h-4 text-gray-400 transition-transform duration-200"
                                     :class="expanded === {{ $cat->id }} && 'rotate-180'"
                                     fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>
                            <div x-show="expanded === {{ $cat->id }}" x-collapse>
                                <div class="pb-2 space-y-0.5">
                                    <a href="{{ route('catalogue.category', $cat) }}"
                                       class="block py-1.5 pl-4 text-xs font-semibold text-bordeaux">
                                        Tout {{ $cat->name }}
                                    </a>
                                    @foreach($cat->children as $sub)
                                        <a href="{{ route('catalogue.category', $sub) }}"
                                           class="block py-1.5 pl-4 text-sm text-gray-600">
                                            {{ $sub->name }}
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @else
                        <a href="{{ route('catalogue.category', $cat) }}"
                           class="block py-2.5 text-sm font-medium text-bordeaux border-t border-gray-50">
                            {{ $cat->name }}
                        </a>
                    @endif
                @endforeach

                <div class="border-t pt-2 mt-2">
                    {{-- TODO 2b: sous-menu Contact mobile (devis) supprimé --}}
                    @auth
                        <a href="{{ route('account.dashboard') }}" class="block py-2 text-gray-700 font-medium">Mon compte</a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="block py-2 text-gray-500 text-sm">Deconnexion</button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="block py-2 text-gray-700 font-medium">Connexion</a>
                        <a href="{{ route('register') }}" class="block py-2 text-gray-500 text-sm">Creer un compte</a>
                    @endauth
                </div>
            </div>
        </div>
    </header>

    {{-- Content with sidebar --}}
    <div class="flex-1 flex flex-col lg:flex-row" x-data="{ mobileCats: false }">

        {{-- Mobile categories toggle --}}
        <div class="lg:hidden">
            <button @click="mobileCats = !mobileCats"
                    class="w-full flex items-center justify-between px-4 py-2.5 bg-bordeaux text-white text-sm font-semibold">
                <span class="flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                    Categories
                </span>
                <svg class="w-4 h-4 transition-transform" :class="mobileCats && 'rotate-180'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            <div x-show="mobileCats" x-collapse class="bg-white border-b" x-data="{ open: null }">
                @foreach($navCategories as $cat)
                    <div class="border-b border-gray-100 last:border-b-0">
                        @if($cat->children->count())
                            <button @click="open = open === {{ $cat->id }} ? null : {{ $cat->id }}"
                                    class="w-full flex items-center justify-between px-4 py-2.5 text-sm font-medium text-gray-800 hover:text-bordeaux">
                                {{ $cat->name }}
                                <svg class="w-4 h-4 text-gray-400 transition-transform" :class="open === {{ $cat->id }} && 'rotate-180'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                            </button>
                            <div x-show="open === {{ $cat->id }}" x-collapse class="bg-gray-50 pb-1">
                                <a href="{{ route('catalogue.category', $cat) }}"
                                   class="block px-6 py-1.5 text-xs font-semibold text-bordeaux">
                                    Tout {{ $cat->name }}
                                </a>
                                @foreach($cat->children as $sub)
                                    <a href="{{ route('catalogue.category', $sub) }}"
                                       class="block px-6 py-1.5 text-xs text-gray-600 hover:text-bordeaux">
                                        {{ $sub->name }}
                                    </a>
                                @endforeach
                            </div>
                        @else
                            <a href="{{ route('catalogue.category', $cat) }}"
                               class="block px-4 py-2.5 text-sm font-medium text-gray-800 hover:text-bordeaux">
                                {{ $cat->name }}
                            </a>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Left sidebar : categories (desktop only) --}}
        <aside class="hidden lg:flex lg:flex-col w-64 flex-shrink-0"
               x-data="{ open: null, leaveTimer: null, scrolling: false, scrollTimer: null }"
               style="height: calc(100vh - 7rem); position: sticky; top: 7rem;">
            <div style="background: linear-gradient(135deg, #6b1d2a 0%, #8b2a3a 100%); box-shadow: 0 2px 8px rgba(107,29,42,0.3);"
                 class="px-4 py-3 text-white text-xs font-bold uppercase tracking-widest flex items-center gap-2 flex-shrink-0">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
                Categories
            </div>
            <nav @scroll="scrolling = true; open = null; clearTimeout(scrollTimer); scrollTimer = setTimeout(() => { scrolling = false }, 500)"
                 :style="scrolling && 'pointer-events: none'"
                 class="py-1 pr-2 overflow-y-auto overscroll-contain flex-1 sidebar-scroll" style="background: linear-gradient(180deg, #fafafa 0%, #f3f4f6 100%); box-shadow: inset -1px 0 0 #e5e7eb, 2px 0 8px rgba(0,0,0,0.04);">
                @foreach($navCategories as $cat)
                    <div class="last:border-b-0"
                         @mouseenter="if(!scrolling) { clearTimeout(leaveTimer); leaveTimer = setTimeout(() => { if(!scrolling) open = {{ $cat->id }} }, 80) }"
                         @mouseleave="clearTimeout(leaveTimer); leaveTimer = setTimeout(() => { if (open === {{ $cat->id }}) open = null }, 300)">
                        <a href="{{ route('catalogue.category', $cat) }}"
                           class="flex items-center justify-between px-4 py-3 text-[12.5px] font-semibold transition-all duration-200 mx-1.5 my-1.5 rounded-lg"
                           :class="open === {{ $cat->id }}
                               ? 'bg-bordeaux text-white shadow-md'
                               : 'text-gray-700 hover:bg-white hover:text-bordeaux hover:shadow-sm'">
                            <span class="truncate">{{ $cat->name }}</span>
                            @if($cat->children->count())
                                <svg class="w-3 h-3 flex-shrink-0 ml-1 transition-transform duration-200"
                                     :class="open === {{ $cat->id }} && 'rotate-90'"
                                     fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                                </svg>
                            @endif
                        </a>

                        @if($cat->children->count())
                            <div x-show="open === {{ $cat->id }}" x-collapse.duration.100ms
                                 class="sub-panel mx-1.5 mb-1 rounded-lg bg-white shadow-inner border border-gray-100/50">
                                <div class="py-1 px-1">
                                @foreach($cat->children as $sub)
                                    <a href="{{ route('catalogue.category', $sub) }}"
                                       class="flex items-center gap-2 px-3 py-[3px] text-[11.5px] text-gray-600 hover:text-bordeaux hover:bg-bordeaux-50 rounded-md transition-all duration-150">
                                        <span class="w-1.5 h-1.5 rounded-full flex-shrink-0" style="background: linear-gradient(135deg, #6b1d2a, #b44d5e);"></span>
                                        {{ $sub->name }}
                                    </a>
                                    @if($sub->children->count())
                                        @foreach($sub->children as $subsub)
                                            <a href="{{ route('catalogue.category', $subsub) }}"
                                               class="flex items-center gap-2 pl-8 pr-3 py-[2px] text-[10.5px] text-gray-400 hover:text-bordeaux hover:bg-bordeaux-50/50 rounded-md transition-all duration-150">
                                                {{ $subsub->name }}
                                            </a>
                                        @endforeach
                                    @endif
                                @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                @endforeach
                {{-- Espacement invisible pour que les dernières catégories puissent déployer leurs sous-menus --}}
                @for($i = 0; $i < 6; $i++)
                    <div class="mx-1.5 my-1.5 px-4 py-3 rounded-lg" aria-hidden="true">&nbsp;</div>
                @endfor
            </nav>
        </aside>

        {{-- Main content --}}
        <main class="flex-1 min-w-0">
            {{ $slot }}
        </main>
    </div>

    {{-- Footer --}}
    <footer class="bg-gray-900 text-gray-300 mt-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-8">
                {{-- Brand --}}
                <div class="sm:col-span-2 lg:col-span-2">
                    <div class="mb-4">
                        <img src="{{ asset('images/logo-full.svg') }}" alt="Marquage Textile" class="h-7 brightness-0 invert opacity-90">
                    </div>
                    <p class="text-sm text-gray-400 mb-4 max-w-xs">Votre partenaire pour la personnalisation textile professionnelle. Broderie, serigraphie, impression et bien plus.</p>
                    <div class="flex items-center gap-3 mb-4">
                        <a href="#" class="w-9 h-9 bg-gray-800 hover:bg-bordeaux rounded-lg flex items-center justify-center transition" aria-label="Facebook">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3z"/></svg>
                        </a>
                        <a href="#" class="w-9 h-9 bg-gray-800 hover:bg-bordeaux rounded-lg flex items-center justify-center transition" aria-label="Instagram">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg>
                        </a>
                        <a href="#" class="w-9 h-9 bg-gray-800 hover:bg-bordeaux-dark rounded-lg flex items-center justify-center transition" aria-label="LinkedIn">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 01-2.063-2.065 2.064 2.064 0 112.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>
                        </a>
                    </div>
                    <div class="text-xs text-gray-500 space-y-1">
                        <p>EIRL LEFEBVRE — LCS Marquage Textile</p>
                        <p>19 rue de la Resistance, 59155 Faches-Thumesnil</p>
                        <p>SIRET : 794 260 954 00033</p>
                        <p>TVA : FR54790260954</p>
                    </div>
                </div>

                {{-- Catalogue --}}
                <div>
                    <h4 class="text-white font-semibold mb-4">Catalogue</h4>
                    <ul class="space-y-2 text-sm">
                        @foreach($navCategories->take(6) as $cat)
                            <li><a href="{{ route('catalogue.category', $cat) }}" class="hover:text-white transition">{{ $cat->name }}</a></li>
                        @endforeach
                        <li><a href="{{ route('catalogue.index') }}" class="text-bordeaux-200 hover:text-bordeaux-200 transition">Voir tout &rarr;</a></li>
                    </ul>
                </div>

                {{-- Informations --}}
                <div>
                    <h4 class="text-white font-semibold mb-4">Informations</h4>
                    <ul class="space-y-2 text-sm">
                        {{-- TODO 2b: liens "Demander un devis" + bloc footerTechniques supprimés --}}
                        <li><a href="{{ route('legal.cgv') }}" class="hover:text-white transition">Conditions generales de vente</a></li>
                        <li><a href="{{ route('legal.privacy') }}" class="hover:text-white transition">Politique de confidentialite</a></li>
                        <li><a href="{{ route('legal.terms') }}" class="hover:text-white transition">Mentions legales</a></li>
                    </ul>
                </div>

                {{-- Contact --}}
                <div>
                    <h4 class="text-white font-semibold mb-4">Contact</h4>
                    <ul class="space-y-3 text-sm">
                        <li class="flex items-start gap-2">
                            <svg class="w-4 h-4 mt-0.5 text-gray-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                            <a href="tel:+33320400690" class="hover:text-white transition">03 20 40 06 90</a>
                        </li>
                        <li class="flex items-start gap-2">
                            <svg class="w-4 h-4 mt-0.5 text-gray-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            <a href="mailto:contact@marquage-textile.fr" class="hover:text-white transition">contact@marquage-textile.fr</a>
                        </li>
                        <li class="flex items-start gap-2">
                            <svg class="w-4 h-4 mt-0.5 text-gray-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>Lun-Ven, 9h-18h</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <svg class="w-4 h-4 mt-0.5 text-gray-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <span>Paris, France</span>
                        </li>
                    </ul>
                </div>
            </div>

            {{-- Newsletter --}}
            <div class="border-t border-gray-800 mt-10 pt-8">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-6">
                    <div>
                        <h4 class="text-white font-semibold mb-1">Restez informe</h4>
                        <p class="text-sm text-gray-400">Recevez nos offres et actualites par email.</p>
                    </div>
                    <form class="flex w-full sm:w-auto gap-2" onsubmit="event.preventDefault();">
                        <input type="email" placeholder="votre@email.fr" class="flex-1 sm:w-64 px-4 py-2.5 bg-gray-800 border border-gray-700 rounded-lg text-sm text-white placeholder-gray-500 focus:ring-2 focus:ring-bordeaux focus:border-bordeaux">
                        <button type="submit" class="px-5 py-2.5 bg-bordeaux text-white text-sm font-semibold rounded-lg hover:bg-bordeaux-dark transition flex-shrink-0">S'inscrire</button>
                    </form>
                </div>
            </div>

            {{-- Bottom bar --}}
            <div class="border-t border-gray-800 mt-8 pt-8 text-sm text-gray-500 flex flex-col sm:flex-row items-center justify-between gap-4">
                <span>&copy; {{ date('Y') }} Marquage Textile. {{ __('All rights reserved.') }}</span>
                <div class="flex items-center gap-4">
                    <a href="{{ route('legal.privacy') }}" class="hover:text-white transition">{{ __('Privacy Policy') }}</a>
                    <a href="{{ route('legal.terms') }}" class="hover:text-white transition">{{ __('Legal Notice') }}</a>
                    <span class="text-gray-700">|</span>
                    <div class="flex items-center gap-2">
                        <a href="{{ route('locale.switch', 'fr') }}" class="hover:text-white transition {{ app()->getLocale() === 'fr' ? 'text-white font-semibold' : '' }}">FR</a>
                        <span class="text-gray-700">/</span>
                        <a href="{{ route('locale.switch', 'en') }}" class="hover:text-white transition {{ app()->getLocale() === 'en' ? 'text-white font-semibold' : '' }}">EN</a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    {{-- Cookie consent banner --}}
    <div x-data="{ show: !localStorage.getItem('cookie_consent') }"
         x-show="show"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="translate-y-full opacity-0"
         x-transition:enter-end="translate-y-0 opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="translate-y-0 opacity-100"
         x-transition:leave-end="translate-y-full opacity-0"
         class="fixed bottom-0 inset-x-0 z-50 p-4"
         x-cloak>
        <div class="max-w-4xl mx-auto bg-white rounded-xl shadow-lg border p-5 flex flex-col sm:flex-row items-start sm:items-center gap-4">
            <div class="flex-1 text-sm text-gray-600">
                Ce site utilise des cookies techniques necessaires a son fonctionnement (session, securite, preferences de langue). Aucun cookie publicitaire n'est utilise.
                <a href="{{ route('legal.privacy') }}" class="text-bordeaux hover:underline ml-1">En savoir plus</a>
            </div>
            <div class="flex gap-2 flex-shrink-0">
                <button @click="localStorage.setItem('cookie_consent', 'accepted'); show = false"
                        class="px-5 py-2 bg-bordeaux text-white text-sm font-semibold rounded-lg hover:bg-bordeaux-dark transition">
                    Accepter
                </button>
                <button @click="localStorage.setItem('cookie_consent', 'minimal'); show = false"
                        class="px-5 py-2 border border-gray-300 text-gray-700 text-sm rounded-lg hover:bg-gray-50 transition">
                    Continuer
                </button>
            </div>
        </div>
    </div>

    {{-- Cart sidebar (drawer) --}}
    <livewire:cart-sidebar />

    {{-- Chat widget --}}
    <livewire:chat-widget />

    @livewireScripts
    <script>
        document.getElementById('mobile-menu-btn')?.addEventListener('click', () => {
            document.getElementById('mobile-menu')?.classList.toggle('hidden');
        });
    </script>
</body>
</html>
