<?php

return [
    'key_id' => trim((string) env('RAZORPAY_KEY_ID', '')),
    'key_secret' => trim((string) env('RAZORPAY_KEY_SECRET', '')),
    'currency' => env('RAZORPAY_CURRENCY', 'INR'),
];
