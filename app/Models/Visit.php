<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Visit extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'path',
        'ip_hash',
        'user_agent',
        'referer',
        'is_bot',
        'visited_at',
    ];

    protected $casts = [
        'is_bot' => 'boolean',
        'visited_at' => 'datetime',
    ];
}
