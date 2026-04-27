<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TechniqueMarquage extends Model
{
    protected $table = 'techniques_marquage';

    protected $fillable = [
        'nom',
        'description_courte',
        'image',
        'ordre',
        'actif',
    ];

    protected $casts = [
        'actif' => 'boolean',
    ];

    public function scopeActif($query)
    {
        return $query->where('actif', true)->orderBy('ordre');
    }

    public function getImageUrlAttribute(): ?string
    {
        if (! $this->image) {
            return null;
        }

        return asset('storage/' . $this->image);
    }
}
