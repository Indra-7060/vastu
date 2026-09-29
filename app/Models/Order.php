<?php

namespace App\Models;

use App\Support\OrderStatuses;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'user_id',
        'user_name',
        'user_phone',
        'user_email',
        'shipping_name',
        'shipping_phone',
        'shipping_email',
        'shipping_address',
        'shipping_city',
        'shipping_state',
        'shipping_pincode',
        'shipping_country',
        'order_notes',
        'payment_mode',
        'payment_id',
        'razorpay_order_id',
        'payment_status',
        'subtotal',
        'discount_amount',
        'coupon_code',
        'coupon_id',
        'delivery_charge',
        'payable_amount',
        'status',
        'ordered_at',
        'expected_delivery_date',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'delivery_charge' => 'decimal:2',
        'payable_amount' => 'decimal:2',
        'ordered_at' => 'datetime',
        'expected_delivery_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function statusLogs()
    {
        return $this->hasMany(OrderStatusLog::class)->orderBy('logged_at')->orderBy('id');
    }

    public function getStatusLabelAttribute(): string
    {
        return OrderStatuses::label($this->status);
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return OrderStatuses::badgeClass($this->status);
    }

    public function nextStatusOptions(): array
    {
        return OrderStatuses::nextOptions($this->status);
    }

    public static function generateOrderNumber(): string
    {
        do {
            $number = 'ORD'.substr(str_shuffle('abcdefghijklmnopqrstuvwxyz0123456789'), 0, 9).random_int(100, 999);
        } while (static::where('order_number', $number)->exists());

        return $number;
    }
}
