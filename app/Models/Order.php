<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [
        'public_id', 'user_id', 'name', 'email', 'phone', 'address', 'shipping_name', 'shipping_fee', 'subtotal', 'total', 'eta_days', 'status', 'payment_due_at', 'tracking_number', 'idempotency_key',
    ];

    protected function casts(): array
    {
        return [
            'address' => 'array',
            'shipping_fee' => 'integer',
            'subtotal' => 'integer',
            'total' => 'integer',
            'eta_days' => 'integer',
            'payment_due_at' => 'immutable_datetime',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'public_id';
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }
}
