<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Variant extends Model
{
    protected $fillable = [
        'product_id', 'sku', 'name', 'price', 'stock', 'reserved', 'preorder', 'lead_days', 'active',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'integer',
            'stock' => 'integer',
            'reserved' => 'integer',
            'preorder' => 'boolean',
            'lead_days' => 'integer',
            'active' => 'boolean',
        ];
    }

    public function getAvailableAttribute(): int
    {
        return $this->stock - $this->reserved;
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
