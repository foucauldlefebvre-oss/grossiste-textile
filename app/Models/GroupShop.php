<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Hash;

class GroupShop extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'password',
        'description',
        'logo',
        'pricing_mode',
        'payment_mode',
        'status',
        'opens_at',
        'closes_at',
        'created_by',
    ];

    protected $hidden = ['password'];

    protected $casts = [
        'opens_at' => 'datetime',
        'closes_at' => 'datetime',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    protected static function booted(): void
    {
        static::saving(function (self $shop) {
            if ($shop->isDirty('password') && $shop->password && ! str_starts_with($shop->password, '$2y$')) {
                $shop->password = Hash::make($shop->password);
            }
        });
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function products(): HasMany
    {
        return $this->hasMany(GroupShopProduct::class)->orderBy('sort_order');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(GroupShopOrder::class);
    }

    public function isOpen(): bool
    {
        return $this->status === 'open'
            && (! $this->closes_at || $this->closes_at->isFuture());
    }
}
