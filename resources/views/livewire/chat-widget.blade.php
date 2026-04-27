<div style="position:fixed;bottom:16px;right:16px;z-index:9999;">
    {{-- Toggle button --}}
    @if(! $open)
        <button wire:click="toggle"
                class="w-14 h-14 bg-bordeaux rounded-full shadow-lg flex items-center justify-center text-white hover:bg-bordeaux-700 transition group">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
            </svg>
        </button>
    @else
        {{-- Chat window --}}
        <div class="w-80 sm:w-96 bg-white rounded-2xl shadow-2xl border border-gray-200 overflow-hidden flex flex-col" style="height: 520px;">
            {{-- Header --}}
            <div class="bg-bordeaux text-white px-4 py-3 flex items-center justify-between flex-shrink-0">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 bg-white/20 rounded-full flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                        </svg>
                    </div>
                    <div>
                        <div class="font-semibold text-sm">Marquage Textile</div>
                        <div class="text-xs text-white/70">Comment pouvons-nous vous aider ?</div>
                    </div>
                </div>
                <button wire:click="toggle" class="text-white/70 hover:text-white">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            {{-- Content --}}
            <div class="flex-1 overflow-y-auto">
                @if($screen === 'menu')
                    {{-- Suivi commande en haut --}}
                    <div class="px-4 pt-4 pb-2">
                        <button wire:click="goTo('order_lookup')"
                                class="w-full text-left px-4 py-3 bg-bordeaux-50 hover:bg-bordeaux-100 rounded-xl text-sm font-medium transition flex items-center gap-3 border border-bordeaux-200">
                            <span class="w-8 h-8 bg-bordeaux text-white rounded-lg flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            </span>
                            <span class="text-bordeaux">Ou en est ma commande ?</span>
                        </button>
                    </div>

                    {{-- FAQ --}}
                    <div class="px-4 pb-2">
                        <p class="text-xs text-gray-400 uppercase font-semibold tracking-wider mb-2">Questions frequentes</p>
                        <div class="space-y-1.5" x-data="{ openFaq: null }">
                            @foreach($this->getFaqItems() as $i => $faq)
                                @if($faq['a'] === '__delais__')
                                    <button wire:click="goTo('delais')"
                                            class="w-full text-left px-3 py-2.5 text-sm font-medium hover:bg-gray-50 transition flex items-center justify-between gap-2 border border-gray-200 rounded-xl">
                                        <span>{{ $faq['q'] }}</span>
                                        <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                                    </button>
                                @elseif($faq['a'] === '__file_formats__')
                                    <button wire:click="goTo('file_formats')"
                                            class="w-full text-left px-3 py-2.5 text-sm font-medium hover:bg-gray-50 transition flex items-center justify-between gap-2 border border-gray-200 rounded-xl">
                                        <span>{{ $faq['q'] }}</span>
                                        <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                                    </button>
                                @else
                                    <div class="border border-gray-200 rounded-xl overflow-hidden">
                                        <button @click="openFaq = openFaq === {{ $i }} ? null : {{ $i }}"
                                                class="w-full text-left px-3 py-2.5 text-sm font-medium hover:bg-gray-50 transition flex items-center justify-between gap-2">
                                            <span>{{ $faq['q'] }}</span>
                                            <svg class="w-4 h-4 text-gray-400 transition-transform flex-shrink-0" :class="openFaq === {{ $i }} && 'rotate-180'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                                        </button>
                                        <div x-show="openFaq === {{ $i }}" x-collapse class="px-3 pb-2.5 text-sm text-gray-600 leading-relaxed">
                                            {{ $faq['a'] }}
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>

                    {{-- Chat live en bas --}}
                    <div class="px-4 pb-4 pt-2">
                        <div class="border-t pt-3">
                            <button wire:click="goTo('live_intro')"
                                    class="w-full text-left px-4 py-3 bg-gray-50 hover:bg-gray-100 rounded-xl text-sm text-gray-500 transition flex items-center gap-3">
                                <span class="w-8 h-8 bg-gray-200 text-gray-500 rounded-lg flex items-center justify-center flex-shrink-0">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z"/></svg>
                                </span>
                                Vous n'avez pas trouve votre reponse ? Discuter avec un conseiller
                            </button>
                        </div>
                    </div>

                @elseif($screen === 'delais')
                    {{-- Delais --}}
                    <div class="p-4">
                        <button wire:click="goTo('menu')" class="text-xs text-bordeaux hover:underline mb-3 flex items-center gap-1">
                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                            Retour
                        </button>
                        <p class="text-sm text-gray-500 mb-3">Selectionnez votre type de commande :</p>
                        <div class="space-y-2" x-data="{ openDelai: null }">
                            <div class="border border-gray-200 rounded-xl overflow-hidden">
                                <button @click="openDelai = openDelai === 0 ? null : 0"
                                        class="w-full text-left px-3 py-2.5 text-sm font-medium hover:bg-gray-50 transition flex items-center justify-between gap-2">
                                    <span>Textile sans marquage</span>
                                    <svg class="w-4 h-4 text-gray-400 transition-transform flex-shrink-0" :class="openDelai === 0 && 'rotate-180'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                                </button>
                                <div x-show="openDelai === 0" x-collapse class="px-3 pb-2.5 text-sm text-gray-600 leading-relaxed">
                                    <div class="flex items-center gap-2 text-green-700 bg-green-50 rounded-lg px-3 py-2">
                                        <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                        Expediee le lendemain. Delai d'expedition de 24h a 96h ouvrees selon le produit.
                                    </div>
                                </div>
                            </div>
                            <div class="border border-gray-200 rounded-xl overflow-hidden">
                                <button @click="openDelai = openDelai === 1 ? null : 1"
                                        class="w-full text-left px-3 py-2.5 text-sm font-medium hover:bg-gray-50 transition flex items-center justify-between gap-2">
                                    <span>Serigraphie, flex, transferts</span>
                                    <svg class="w-4 h-4 text-gray-400 transition-transform flex-shrink-0" :class="openDelai === 1 && 'rotate-180'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                                </button>
                                <div x-show="openDelai === 1" x-collapse class="px-3 pb-2.5 text-sm text-gray-600 leading-relaxed">
                                    Serigraphie, flex, transfert offset, DTF, transfert serigraphique : <strong>7 a 15 jours ouvres</strong> apres validation du BAT.
                                </div>
                            </div>
                            <div class="border border-gray-200 rounded-xl overflow-hidden">
                                <button @click="openDelai = openDelai === 2 ? null : 2"
                                        class="w-full text-left px-3 py-2.5 text-sm font-medium hover:bg-gray-50 transition flex items-center justify-between gap-2">
                                    <span>Broderie</span>
                                    <svg class="w-4 h-4 text-gray-400 transition-transform flex-shrink-0" :class="openDelai === 2 && 'rotate-180'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                                </button>
                                <div x-show="openDelai === 2" x-collapse class="px-3 pb-2.5 text-sm text-gray-600 leading-relaxed">
                                    <strong>2 a 6 semaines</strong> selon la periode, apres validation du BAT.
                                </div>
                            </div>
                            <div class="border border-gray-200 rounded-xl overflow-hidden">
                                <button @click="openDelai = openDelai === 3 ? null : 3"
                                        class="w-full text-left px-3 py-2.5 text-sm font-medium hover:bg-gray-50 transition flex items-center justify-between gap-2">
                                    <span>Badge tisse ou PVC</span>
                                    <svg class="w-4 h-4 text-gray-400 transition-transform flex-shrink-0" :class="openDelai === 3 && 'rotate-180'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                                </button>
                                <div x-show="openDelai === 3" x-collapse class="px-3 pb-2.5 text-sm text-gray-600 leading-relaxed">
                                    <strong>4 a 6 semaines</strong> apres validation du BAT.
                                </div>
                            </div>
                            <div class="border border-gray-200 rounded-xl overflow-hidden">
                                <button @click="openDelai = openDelai === 4 ? null : 4"
                                        class="w-full text-left px-3 py-2.5 text-sm font-medium hover:bg-gray-50 transition flex items-center justify-between gap-2">
                                    <span>Commande urgente</span>
                                    <svg class="w-4 h-4 text-gray-400 transition-transform flex-shrink-0" :class="openDelai === 4 && 'rotate-180'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                                </button>
                                <div x-show="openDelai === 4" x-collapse class="px-3 pb-2.5 text-sm text-gray-600 leading-relaxed">
                                    Contactez-nous directement pour etudier la faisabilite d'une commande urgente.
                                </div>
                            </div>
                        </div>
                    </div>

                @elseif($screen === 'file_formats')
                    {{-- Formats fichier par technique --}}
                    <div class="p-4">
                        <button wire:click="goTo('menu')" class="text-xs text-bordeaux hover:underline mb-3 flex items-center gap-1">
                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                            Retour
                        </button>
                        <p class="text-sm text-gray-500 mb-3">Selectionnez une technique pour voir les formats acceptes :</p>
                        <div class="space-y-1.5" x-data="{ openTech: null }">
                            @foreach($this->getFileFormats() as $j => $tech)
                                <div class="border border-gray-200 rounded-xl overflow-hidden">
                                    <button @click="openTech = openTech === {{ $j }} ? null : {{ $j }}"
                                            class="w-full text-left px-3 py-2.5 text-sm font-medium hover:bg-gray-50 transition flex items-center justify-between gap-2">
                                        <span>{{ $tech['name'] }}</span>
                                        <svg class="w-4 h-4 text-gray-400 transition-transform flex-shrink-0" :class="openTech === {{ $j }} && 'rotate-180'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                                    </button>
                                    <div x-show="openTech === {{ $j }}" x-collapse class="px-3 pb-2.5">
                                        <div class="flex items-start gap-2 text-sm text-gray-600">
                                            <svg class="w-4 h-4 text-bordeaux flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                            <span>{{ $tech['formats'] }}</span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                @elseif($screen === 'order_lookup')
                    {{-- Suivi commande --}}
                    <div class="p-4">
                        <button wire:click="goTo('menu')" class="text-xs text-bordeaux hover:underline mb-3 flex items-center gap-1">
                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                            Retour
                        </button>
                        <p class="text-sm text-gray-500 mb-3">Entrez votre reference de commande :</p>
                        <div class="flex gap-2">
                            <input type="text" wire:model="orderRef" wire:keydown.enter="lookupOrder"
                                   placeholder="Ex: CMD2026040001"
                                   maxlength="20"
                                   class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-bordeaux/30 focus:border-bordeaux">
                            <button wire:click="lookupOrder" class="px-4 py-2 bg-bordeaux text-white rounded-lg text-sm font-medium hover:bg-bordeaux-700 transition">
                                OK
                            </button>
                        </div>
                        @if($orderStatus)
                            <div class="mt-3 p-3 bg-gray-50 rounded-xl text-sm text-gray-700">
                                {{ $orderStatus }}
                            </div>
                        @endif
                    </div>

                @elseif($screen === 'live_intro')
                    {{-- Intro chat live --}}
                    <div class="p-4">
                        <button wire:click="goTo('menu')" class="text-xs text-bordeaux hover:underline mb-3 flex items-center gap-1">
                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                            Retour
                        </button>
                        <div class="text-center py-4">
                            <div class="w-12 h-12 mx-auto mb-3 bg-bordeaux-100 rounded-full flex items-center justify-center">
                                <svg class="w-6 h-6 text-bordeaux" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z"/></svg>
                            </div>
                            <p class="text-sm text-gray-600 mb-4">Un conseiller vous repondra dans les meilleurs delais.</p>
                            <button wire:click="startLiveChat"
                                    class="px-6 py-3 bg-bordeaux text-white rounded-xl text-sm font-semibold hover:bg-bordeaux-700 transition">
                                Demarrer la conversation
                            </button>
                        </div>
                    </div>

                @elseif($screen === 'live')
                    {{-- Chat live --}}
                    <div class="flex flex-col h-full" wire:poll.5s="pollMessages">
                        <div class="flex-1 overflow-y-auto p-4 space-y-3">
                            @forelse($chatMessages as $msg)
                                <div class="flex {{ $msg['sender'] === 'visitor' ? 'justify-end' : 'justify-start' }}">
                                    <div class="max-w-[80%] px-3 py-2 rounded-2xl text-sm
                                        {{ $msg['sender'] === 'visitor' ? 'bg-bordeaux text-white rounded-br-md' : ($msg['sender'] === 'admin' ? 'bg-gray-100 text-gray-800 rounded-bl-md' : 'bg-blue-50 text-blue-700 rounded-bl-md') }}">
                                        {{ $msg['body'] }}
                                        <div class="text-[10px] mt-1 {{ $msg['sender'] === 'visitor' ? 'text-white/60' : 'text-gray-400' }}">{{ $msg['time'] }}</div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-6">
                                    <p class="text-sm text-gray-400">En attente d'un conseiller...</p>
                                    <div class="flex justify-center gap-1 mt-2">
                                        <span class="w-2 h-2 bg-gray-300 rounded-full animate-bounce" style="animation-delay:0ms"></span>
                                        <span class="w-2 h-2 bg-gray-300 rounded-full animate-bounce" style="animation-delay:150ms"></span>
                                        <span class="w-2 h-2 bg-gray-300 rounded-full animate-bounce" style="animation-delay:300ms"></span>
                                    </div>
                                </div>
                            @endforelse
                        </div>
                    </div>
                @endif
            </div>

            {{-- Input (live chat only) --}}
            @if($screen === 'live')
                <div class="border-t px-3 py-2 flex-shrink-0">
                    <div class="flex gap-2">
                        <input type="text" wire:model="message" wire:keydown.enter="sendMessage"
                               placeholder="Votre message..."
                               maxlength="2000"
                               class="flex-1 border border-gray-200 rounded-full px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-bordeaux/30 focus:border-bordeaux">
                        <button wire:click="sendMessage"
                                class="w-9 h-9 bg-bordeaux text-white rounded-full flex items-center justify-center hover:bg-bordeaux-700 transition flex-shrink-0">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                        </button>
                    </div>
                </div>
            @endif
        </div>
    @endif
</div>
