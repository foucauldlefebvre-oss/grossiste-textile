<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationLog extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'channel',
        'recipient',
        'subject',
        'content',
        'status',
        'error',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
