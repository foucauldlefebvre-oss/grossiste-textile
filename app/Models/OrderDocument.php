<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class OrderDocument extends Model
{
    protected $fillable = [
        'order_id',
        'type',
        'label',
        'path',
        'original_name',
        'mime_type',
        'size',
        'visible_to_client',
        'uploaded_by',
        'user_id',
    ];

    protected $casts = [
        'visible_to_client' => 'boolean',
        'size' => 'integer',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'bat' => 'Bon à tirer',
            'client_file' => 'Fichier client',
            'invoice' => 'Facture',
            'quote_pdf' => 'Devis PDF',
            'other' => 'Autre',
            default => $this->type,
        };
    }

    public function getTypeColorAttribute(): string
    {
        return match ($this->type) {
            'bat' => 'warning',
            'client_file' => 'info',
            'invoice' => 'success',
            'quote_pdf' => 'primary',
            'other' => 'gray',
            default => 'gray',
        };
    }

    public function getFormattedSizeAttribute(): string
    {
        $bytes = $this->size;
        if ($bytes < 1024) {
            return $bytes . ' o';
        }
        if ($bytes < 1048576) {
            return round($bytes / 1024, 1) . ' Ko';
        }

        return round($bytes / 1048576, 1) . ' Mo';
    }

    public function getDownloadUrlAttribute(): string
    {
        return Storage::disk('public')->url($this->path);
    }
}
