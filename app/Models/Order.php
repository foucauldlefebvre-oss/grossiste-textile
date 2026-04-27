<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    protected $fillable = [
        'reference',
        'user_id',
        'quote_id',
        'shipping_address_id',
        'billing_address_id',
        'status',
        'payment_status',
        'amount_paid',
        'has_marking',
        'status_bat',
        'bat_done_at',
        'bat_status',
        'bat_client_comment',
        'bat_client_decided_at',
        'bat_token',
        'status_prep',
        'prep_done_at',
        'status_production',
        'production_done_at',
        'status_shipping',
        'shipping_done_at',
        'subtotal_ht',
        'shipping_ht',
        'shipping_zone',
        'shipping_per_parcel',
        'total_ht',
        'total_tva',
        'total_ttc',
        'vat_exemption',
        'customer_vat_number',
        'customer_siret',
        'stripe_payment_intent_id',
        'carrier',
        'tracking_number',
        'tracking_url',
        'shipped_at',
        'delivered_at',
        'admin_notes',
        'customer_notes',
    ];

    protected $casts = [
        'subtotal_ht' => 'decimal:2',
        'shipping_ht' => 'decimal:2',
        'total_ht' => 'decimal:2',
        'total_tva' => 'decimal:2',
        'total_ttc' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'has_marking' => 'boolean',
        'bat_client_decided_at' => 'datetime',
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
        'bat_done_at' => 'datetime',
        'prep_done_at' => 'datetime',
        'production_done_at' => 'datetime',
        'shipping_done_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function quote(): BelongsTo
    {
        return $this->belongsTo(Quote::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class);
    }

    public function shippingAddress(): BelongsTo
    {
        return $this->belongsTo(Address::class, 'shipping_address_id');
    }

    public function billingAddress(): BelongsTo
    {
        return $this->belongsTo(Address::class, 'billing_address_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(OrderDocument::class);
    }

    public function batDocuments()
    {
        return $this->documents()->where('type', 'bat');
    }

    public function getBatReviewUrlAttribute(): ?string
    {
        return $this->bat_token
            ? route('order.bat.review', $this->bat_token)
            : null;
    }

    // ─── Payment helpers ──────────────────────────────────────────

    protected function paymentPercent(): Attribute
    {
        return Attribute::get(function () {
            if (! $this->total_ttc || (float) $this->total_ttc <= 0) {
                return 0;
            }
            $paid = (float) ($this->amount_paid ?? 0);

            return min(100, (int) round($paid / (float) $this->total_ttc * 100));
        });
    }

    public function updatePaymentStatus(): void
    {
        $percent = $this->payment_percent;

        $status = match (true) {
            $percent >= 100 => 'paid',
            $percent > 0 => 'partial',
            default => 'pending',
        };

        if ($this->payment_status !== $status) {
            $this->update(['payment_status' => $status]);
        }
    }

    // ─── Step tracking ────────────────────────────────────────────

    public function markStepDone(string $step): void
    {
        $statusCol = "status_{$step}";
        $doneAtCol = "{$step}_done_at";

        $update = [$statusCol => 'done'];
        if (in_array($doneAtCol, $this->fillable)) {
            $update[$doneAtCol] = now();
        }

        $this->update($update);
    }

    public function markStepPending(string $step): void
    {
        $statusCol = "status_{$step}";
        $doneAtCol = "{$step}_done_at";

        $update = [$statusCol => 'pending'];
        if (in_array($doneAtCol, $this->fillable)) {
            $update[$doneAtCol] = null;
        }

        $this->update($update);
    }
}
