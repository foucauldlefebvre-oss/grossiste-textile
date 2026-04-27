<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChatConversation extends Model
{
    protected $fillable = [
        'session_token',
        'visitor_name',
        'visitor_email',
        'status',
        'is_read_admin',
        'last_message_at',
    ];

    protected $casts = [
        'is_read_admin' => 'boolean',
        'last_message_at' => 'datetime',
    ];

    public function messages(): HasMany
    {
        return $this->hasMany(ChatMessage::class);
    }

    public function addMessage(string $body, string $sender = 'visitor'): ChatMessage
    {
        $cleaned = strip_tags(trim($body));
        if ($cleaned === '') {
            throw new \InvalidArgumentException('Message vide.');
        }

        $message = $this->messages()->create([
            'body' => mb_substr($cleaned, 0, 2000),
            'sender' => $sender,
        ]);

        $this->update([
            'last_message_at' => now(),
            'is_read_admin' => $sender === 'admin',
        ]);

        return $message;
    }
}
