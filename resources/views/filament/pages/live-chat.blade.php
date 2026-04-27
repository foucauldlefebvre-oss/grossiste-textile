<x-filament-panels::page>
    <div wire:poll.3s="pollConversations" class="grid grid-cols-1 md:grid-cols-3 gap-4" style="height: 600px;">

        {{-- Liste conversations --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border overflow-y-auto">
            <div class="px-4 py-3 border-b font-semibold text-sm text-gray-700 dark:text-gray-200 sticky top-0 bg-white dark:bg-gray-800 z-10">
                Conversations
            </div>

            @php $conversations = $this->getConversations(); @endphp

            @forelse($conversations as $conv)
                <button wire:click="selectConversation({{ $conv['id'] }})"
                        class="w-full text-left px-4 py-3 border-b hover:bg-gray-50 dark:hover:bg-gray-700 transition
                               {{ $activeConversationId === $conv['id'] ? 'bg-primary-50 dark:bg-primary-900/20 border-l-4 border-l-primary-500' : '' }}">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            @if($conv['status'] === 'waiting')
                                <span class="w-2 h-2 bg-red-500 rounded-full animate-pulse"></span>
                            @else
                                <span class="w-2 h-2 bg-green-500 rounded-full"></span>
                            @endif
                            <span class="text-sm font-medium">
                                {{ $conv['visitor_name'] ?? 'Visiteur #' . $conv['id'] }}
                            </span>
                        </div>
                        @if(! $conv['is_read_admin'])
                            <span class="w-5 h-5 bg-red-500 text-white text-[10px] rounded-full flex items-center justify-center font-bold">!</span>
                        @endif
                    </div>
                    <div class="flex items-center justify-between mt-1">
                        <span class="text-xs text-gray-400">
                            {{ $conv['status'] === 'waiting' ? 'En attente' : 'Active' }}
                            @if($conv['last_message_at'])
                                · {{ \Carbon\Carbon::parse($conv['last_message_at'])->diffForHumans() }}
                            @endif
                        </span>
                        @if($conv['status'] === 'waiting')
                            <button wire:click="declineConversation({{ $conv['id'] }})"
                                    @click.stop
                                    class="text-[10px] px-2 py-0.5 bg-red-100 text-red-600 hover:bg-red-200 rounded transition">
                                Refuser
                            </button>
                        @endif
                    </div>
                </button>
            @empty
                <div class="px-4 py-8 text-center text-sm text-gray-400">
                    Aucune conversation en cours
                </div>
            @endforelse

            {{-- Conversations fermees --}}
            @php $closed = $this->getClosedConversations(); @endphp
            @if(! empty($closed))
                <div class="px-4 py-2 border-t border-b bg-gray-50 dark:bg-gray-700 text-xs font-medium text-gray-500 uppercase">
                    Fermees recemment
                </div>
                @foreach($closed as $conv)
                    <button wire:click="selectConversation({{ $conv['id'] }})"
                            class="w-full text-left px-4 py-2 border-b hover:bg-gray-50 dark:hover:bg-gray-700 transition opacity-60
                                   {{ $activeConversationId === $conv['id'] ? 'bg-gray-100 dark:bg-gray-700' : '' }}">
                        <span class="text-xs">Visiteur #{{ $conv['id'] }}</span>
                        <span class="text-xs text-gray-400 ml-2">{{ \Carbon\Carbon::parse($conv['last_message_at'])->diffForHumans() }}</span>
                    </button>
                @endforeach
            @endif
        </div>

        {{-- Zone de messages --}}
        <div class="md:col-span-2 bg-white dark:bg-gray-800 rounded-xl shadow-sm border flex flex-col">
            @if($activeConversationId)
                {{-- Header conversation --}}
                <div class="px-4 py-3 border-b flex items-center justify-between flex-shrink-0">
                    <span class="font-semibold text-sm">Conversation #{{ $activeConversationId }}</span>
                    <button wire:click="closeConversation" wire:confirm="Fermer cette conversation ?"
                            class="text-xs px-3 py-1 bg-gray-100 hover:bg-red-100 hover:text-red-600 rounded-lg transition">
                        Fermer
                    </button>
                </div>

                {{-- Messages --}}
                <div class="flex-1 overflow-y-auto p-4 space-y-3" id="admin-chat-messages">
                    @php $messages = $this->getMessages(); @endphp
                    @foreach($messages as $msg)
                        <div class="flex {{ $msg['sender'] === 'admin' ? 'justify-end' : 'justify-start' }}">
                            <div class="max-w-[75%] px-3 py-2 rounded-2xl text-sm
                                {{ $msg['sender'] === 'admin'
                                    ? 'bg-primary-600 text-white rounded-br-md'
                                    : ($msg['sender'] === 'bot'
                                        ? 'bg-gray-100 dark:bg-gray-700 text-gray-500 rounded-bl-md italic'
                                        : 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded-bl-md') }}">
                                {{ $msg['body'] }}
                                <div class="text-[10px] mt-1 {{ $msg['sender'] === 'admin' ? 'text-white/60' : 'text-gray-400' }}">
                                    {{ $msg['sender'] === 'visitor' ? 'Client' : ($msg['sender'] === 'bot' ? 'Auto' : 'Vous') }} · {{ $msg['time'] }}
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Input --}}
                <div class="border-t px-4 py-3 flex-shrink-0">
                    <div class="flex gap-2">
                        <input type="text" wire:model="replyMessage" wire:keydown.enter="sendReply"
                               placeholder="Votre reponse..."
                               class="flex-1 border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 dark:bg-gray-700 dark:text-white">
                        <button wire:click="sendReply"
                                class="px-4 py-2 bg-primary-600 text-white rounded-lg text-sm font-medium hover:bg-primary-700 transition">
                            Envoyer
                        </button>
                    </div>
                </div>
            @else
                <div class="flex-1 flex items-center justify-center text-gray-400 text-sm">
                    Selectionnez une conversation
                </div>
            @endif
        </div>
    </div>
</x-filament-panels::page>
