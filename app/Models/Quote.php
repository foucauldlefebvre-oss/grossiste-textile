<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Quote extends Model
{
    protected $fillable = [
        'reference',
        'user_id',
        'session_id',
        'status',
        'total_ht',
        'shipping_ht',
        'shipping_parcels',
        'shipping_zone',
        'shipping_per_parcel',
        'total_tva',
        'total_ttc',
        'notes',
        'markings',
        'expires_at',
        'accepted_at',
        'bat_pdf',
        'bat_status',
        'bat_sent_at',
        'bat_token',
        'bat_rejection_reason',
        'bat_logo_path',
        'bat_logo_x',
        'bat_logo_y',
        'bat_logo_width',
        'bat_logo_height',
        'bat_format',
        'bat_position_label',
        'bat_svg_template',
        'bat_client_comment',
        'bat_decided_at',
        'active_marking_group',
    ];

    protected $casts = [
        'total_ht' => 'decimal:2',
        'total_tva' => 'decimal:2',
        'total_ttc' => 'decimal:2',
        'markings' => 'array',
        'expires_at' => 'datetime',
        'accepted_at' => 'datetime',
        'bat_sent_at' => 'datetime',
        'bat_decided_at' => 'datetime',
        'bat_logo_x' => 'decimal:2',
        'bat_logo_y' => 'decimal:2',
        'bat_logo_width' => 'decimal:2',
        'bat_logo_height' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(QuoteItem::class);
    }

    public function order(): HasOne
    {
        return $this->hasOne(Order::class);
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function getBatClientUrlAttribute(): ?string
    {
        if (! $this->bat_token) {
            return null;
        }

        return route('bat.review', ['token' => $this->bat_token]);
    }

    public function isBatApproved(): bool
    {
        return $this->bat_status === 'approved';
    }

    public function hasPendingBat(): bool
    {
        return $this->bat_status === 'sent';
    }

    public function productColorForBat(): ?ProductColor
    {
        $item = $this->items()
            ->where('marking_group', $this->active_marking_group)
            ->with('color')
            ->first();

        return $item?->color;
    }

    // ─── Quote lifecycle ─────────────────────────────────────────

    public function isExpired(): bool
    {
        return $this->status === 'expired'
            || ($this->expires_at && $this->expires_at->isPast());
    }

    public function daysRemaining(): int
    {
        if (! $this->expires_at || $this->expires_at->isPast()) {
            return 0;
        }

        return (int) now()->diffInDays($this->expires_at, false);
    }

    public function markAsSent(): void
    {
        $this->update([
            'status' => 'sent',
            'expires_at' => $this->expires_at ?? now()->addDays(30),
        ]);
    }

    public function scopeExpired($query)
    {
        return $query->where('status', 'sent')
            ->where('expires_at', '<', now());
    }
}
