<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShippingSetting extends Model
{
    protected $fillable = [
        'free_shipping_threshold',
        'flat_shipping_rate',
    ];

    protected $casts = [
        'free_shipping_threshold' => 'decimal:2',
        'flat_shipping_rate' => 'decimal:2',
    ];

    public static function current(): self
    {
        return static::query()->firstOrCreate([], [
            'free_shipping_threshold' => 899,
            'flat_shipping_rate' => 60,
        ]);
    }
}
