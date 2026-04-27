<?php

namespace App\Filament\Pages;

use App\Models\ChatConversation;
use App\Models\ChatMessage;
use Filament\Pages\Page;

class LiveChat extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static ?string $navigationGroup = 'Commandes';

    protected static ?string $navigationLabel = 'Chat';

    protected static ?string $title = 'Chat en direct';

    protected static ?int $navigationSort = 11;

    protected static string $view = 'filament.pages.live-chat';

    public ?int $activeConversationId = null;

    public string $replyMessage = '';

    public static function getNavigationBadge(): ?string
    {
        $count = ChatConversation::whereIn('status', ['waiting', 'active'])
            ->where('is_read_admin', false)
            ->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'danger';
    }

    public function getConversations(): array
    {
        return ChatConversation::whereIn('status', ['waiting', 'active'])
            ->orderByDesc('last_message_at')
            ->get()
            ->toArray();
    }

    public function getClosedConversations(): array
    {
        return ChatConversation::where('status', 'closed')
            ->orderByDesc('last_message_at')
            ->limit(20)
            ->get()
            ->toArray();
    }

    public function getMessages(): array
    {
        if (! $this->activeConversationId) {
            return [];
        }

        return ChatMessage::where('chat_conversation_id', $this->activeConversationId)
            ->orderBy('created_at')
            ->get()
            ->map(fn ($m) => [
                'id' => $m->id,
                'sender' => $m->sender,
                'body' => e($m->body),
                'time' => $m->created_at->format('d/m H:i'),
            ])
            ->toArray();
    }

    public function selectConversation(int $id): void
    {
        $this->activeConversationId = $id;

        $conv = ChatConversation::find($id);
        if ($conv) {
            $conv->update(['is_read_admin' => true]);
            if ($conv->status === 'waiting') {
                $conv->update(['status' => 'active']);
            }
        }
    }

    public function sendReply(): void
    {
        $msg = strip_tags(trim($this->replyMessage));
        if ($msg === '' || ! $this->activeConversationId) {
            return;
        }

        $conv = ChatConversation::find($this->activeConversationId);
        if (! $conv) {
            return;
        }

        $conv->addMessage($msg, 'admin');
        $this->replyMessage = '';
    }

    public function declineConversation(int $id): void
    {
        $conv = ChatConversation::find($id);
        if ($conv) {
            $conv->addMessage('Aucun conseiller n\'est disponible pour le moment. Laissez-nous un message a contact@marquage-textile.fr et nous vous recontacterons.', 'bot');
            $conv->update(['status' => 'closed', 'is_read_admin' => true]);
        }
        if ($this->activeConversationId === $id) {
            $this->activeConversationId = null;
        }
    }

    public function closeConversation(): void
    {
        if (! $this->activeConversationId) {
            return;
        }

        $conv = ChatConversation::find($this->activeConversationId);
        if ($conv) {
            $conv->addMessage('La conversation a ete fermee. Merci de nous avoir contactes !', 'bot');
            $conv->update(['status' => 'closed']);
        }

        $this->activeConversationId = null;
    }

    public function pollConversations(): void
    {
        // Livewire polling - just triggers re-render
    }
}
