<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShippingRate extends Model
{
    protected $fillable = [
        'name', 'region', 'price', 'pickup', 'active',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'integer',
            'pickup' => 'boolean',
            'active' => 'boolean',
        ];
    }
}
