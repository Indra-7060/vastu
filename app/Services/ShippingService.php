<?php

namespace App\Services;

use App\Models\ShippingSetting;
use App\Support\Money;

class ShippingService
{
    /**
     * @return array{amount: float, free_shipping_threshold: float, flat_shipping_rate: float, is_free: bool, amount_formatted: string}
     */
    public function quote(float|int|string $subtotal): array
    {
        $settings = ShippingSetting::current();
        $threshold = (float) $settings->free_shipping_threshold;
        $rate = (float) $settings->flat_shipping_rate;
        $isFree = (float) $subtotal >= $threshold;
        $amount = $isFree ? 0.0 : $rate;

        return [
            'amount' => $amount,
            'free_shipping_threshold' => $threshold,
            'flat_shipping_rate' => $rate,
            'is_free' => $isFree,
            'amount_formatted' => $isFree ? 'Free' : Money::format($amount),
        ];
    }
}
