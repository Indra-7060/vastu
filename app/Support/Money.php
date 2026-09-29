<?php

namespace App\Support;

class Money
{
    public static function format(float|int|string|null $amount, int $decimals = 2): string
    {
        return '₹ '.number_format((float) $amount, $decimals);
    }
}
