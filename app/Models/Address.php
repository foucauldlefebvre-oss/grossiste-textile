<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Address extends Model
{
    protected $fillable = [
        'user_id',
        'label',
        'first_name',
        'last_name',
        'company',
        'address_line_1',
        'address_line_2',
        'postal_code',
        'city',
        'country',
        'phone',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getFullAddressAttribute(): string
    {
        return implode(', ', array_filter([
            $this->address_line_1,
            $this->address_line_2,
            $this->postal_code . ' ' . $this->city,
            $this->country,
        ]));
    }
}
