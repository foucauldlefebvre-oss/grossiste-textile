<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Quote extends Model
{
    // TODO 2b: tout le modèle Quote sera supprimé / refactor en Cart (Q1).
    // Pour l'instant on commente les colonnes BAT/markings pour préparer le drop column en 2c.
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
        // 'markings',                  // TODO 2c: drop column
        'expires_at',
        'accepted_at',
        // 'bat_pdf',                   // TODO 2c: drop column
        // 'bat_status',                // TODO 2c: drop column
        // 'bat_sent_at',               // TODO 2c: drop column
        // 'bat_token',                 // TODO 2c: drop column
        // 'bat_rejection_reason',      // TODO 2c: drop column
        // 'bat_logo_path',             // TODO 2c: drop column
        // 'bat_logo_x',                // TODO 2c: drop column
        // 'bat_logo_y',                // TODO 2c: drop column
        // 'bat_logo_width',            // TODO 2c: drop column
        // 'bat_logo_height',           // TODO 2c: drop column
        // 'bat_format',                // TODO 2c: drop column
        // 'bat_position_label',        // TODO 2c: drop column
        // 'bat_svg_template',          // TODO 2c: drop column
        // 'bat_client_comment',        // TODO 2c: drop column
        // 'bat_decided_at',            // TODO 2c: drop column
        // 'active_marking_group',      // TODO 2c: drop column
    ];

    protected $casts = [
        'total_ht' => 'decimal:2',
        'total_tva' => 'decimal:2',
        'total_ttc' => 'decimal:2',
        // 'markings' => 'array',
        'expires_at' => 'datetime',
        'accepted_at' => 'datetime',
        // 'bat_sent_at' => 'datetime',
        // 'bat_decided_at' => 'datetime',
        // 'bat_logo_x' => 'decimal:2',
        // 'bat_logo_y' => 'decimal:2',
        // 'bat_logo_width' => 'decimal:2',
        // 'bat_logo_height' => 'decimal:2',
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

    // TODO 2b: méthodes BAT supprimées (workflow BAT dégagé).
    // Anciennes : getBatClientUrlAttribute, isBatApproved, hasPendingBat, productColorForBat

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
