<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invoice extends Model
{
    protected $fillable = [
        'number',
        'order_id',
        'user_id',
        'type',
        'status',
        'payment_method',
        'total_ht',
        'total_tva',
        'total_ttc',
        'amount_paid',
        'amount_remaining',
        'vat_number',
        'vat_exemption',
        'customer_siret',
        'billing_address',
        'pdf_path',
        'issued_at',
        'payment_due_at',
        'delivery_date',
    ];

    protected $casts = [
        'total_ht' => 'decimal:2',
        'total_tva' => 'decimal:2',
        'total_ttc' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'amount_remaining' => 'decimal:2',
        'billing_address' => 'array',
        'issued_at' => 'datetime',
        'payment_due_at' => 'datetime',
        'delivery_date' => 'date',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isDeposit(): bool
    {
        return $this->status === 'deposit';
    }

    public function isSettled(): bool
    {
        return $this->status === 'settled';
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'deposit' => 'Acompte',
            'paid' => 'Payee',
            'settled' => 'Soldee',
            default => ucfirst($this->status),
        };
    }

    public function getPaymentMethodLabelAttribute(): string
    {
        return match ($this->payment_method) {
            'cb' => 'Carte bancaire',
            'wire_transfer' => 'Virement',
            default => '-',
        };
    }
}
