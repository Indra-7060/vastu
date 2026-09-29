<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Coupon extends Model
{
    use HasFactory;

    public const OFFER_TYPES = [
        'coupon' => 'Coupon code',
        'percent' => 'Percentage discount',
        'flat' => 'Flat discount',
        'new_customer' => 'New customer offer',
        'free_shipping' => 'Free shipping',
        'quantity' => 'Quantity discount',
        'cart_value' => 'Cart value discount',
        'category' => 'Category discount',
        'product' => 'Product-specific discount',
        'seasonal' => 'Seasonal / festival sale',
        'flash' => 'Flash sale',
        'prepaid' => 'Prepaid order discount',
        'member' => 'Member-exclusive offer',
        'bogo' => 'Buy X, Get Y Free',
    ];

    protected $fillable = [
        'code',
        'offer_type',
        'description',
        'discount_type',
        'discount_percent',
        'discount_amount',
        'max_discount_status',
        'max_discount_amount',
        'min_cart_status',
        'min_cart_amount',
        'applies_to',
        'category_ids',
        'product_ids',
        'min_quantity',
        'bogo_buy_quantity',
        'bogo_get_quantity',
        'new_customers_only',
        'members_only',
        'free_shipping',
        'prepaid_only',
        'starts_at',
        'ends_at',
        'usage_limit',
        'used_count',
        'max_use_per_user',
        'image',
        'is_active',
        'is_public',
    ];

    protected $casts = [
        'discount_percent' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'max_discount_amount' => 'decimal:2',
        'min_cart_amount' => 'decimal:2',
        'max_discount_status' => 'boolean',
        'min_cart_status' => 'boolean',
        'max_use_per_user' => 'boolean',
        'new_customers_only' => 'boolean',
        'members_only' => 'boolean',
        'free_shipping' => 'boolean',
        'prepaid_only' => 'boolean',
        'is_active' => 'boolean',
        'is_public' => 'boolean',
        'category_ids' => 'array',
        'product_ids' => 'array',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public function redemptions()
    {
        return $this->hasMany(CouponRedemption::class);
    }

    public function scopeCurrentlyActive($query)
    {
        $now = now();

        return $query->where('is_active', true)
            ->where(function ($q) use ($now) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', $now);
            })
            ->where(function ($q) use ($now) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>=', $now);
            })
            ->where(function ($q) {
                $q->whereNull('usage_limit')
                    ->orWhereColumn('used_count', '<', 'usage_limit');
            });
    }

    public function scopePubliclyVisible($query)
    {
        return $query->where('is_public', true);
    }

    public function getDiscountLabelAttribute(): string
    {
        if ($this->offer_type === 'bogo') {
            return 'Buy '.((int) $this->bogo_buy_quantity ?: 1)
                .', get '.((int) $this->bogo_get_quantity ?: 1).' free';
        }

        if ($this->free_shipping && ! $this->discount_percent && ! $this->discount_amount) {
            return 'Free shipping';
        }

        if ($this->discount_type === 'amount') {
            return 'Discount: ₹ '.rtrim(rtrim(number_format((float) $this->discount_amount, 2, '.', ''), '0'), '.');
        }

        $percent = rtrim(rtrim(number_format((float) $this->discount_percent, 2, '.', ''), '0'), '.');

        return 'Discount: '.$percent.'%';
    }

    /**
     * Short text for the site top announcement bar.
     * Prefers admin Description when set; otherwise builds from discount rules.
     */
    public function getBannerTextAttribute(): string
    {
        $fromDescription = $this->plainDescriptionForBanner();
        if ($fromDescription !== '') {
            return $fromDescription;
        }

        $code = strtoupper((string) $this->code);

        if ($this->offer_type === 'bogo') {
            $buy = (int) ($this->bogo_buy_quantity ?: 1);
            $get = (int) ($this->bogo_get_quantity ?: 1);

            return $code.' | BUY '.$buy.' GET '.$get.' FREE';
        }

        if ($this->free_shipping && ! $this->discount_percent && ! $this->discount_amount) {
            return $code.' | FREE SHIPPING';
        }

        if ($this->discount_type === 'amount' && $this->discount_amount !== null) {
            $amount = rtrim(rtrim(number_format((float) $this->discount_amount, 2, '.', ''), '0'), '.');

            return $code.' | FLAT ₹'.$amount.' OFF';
        }

        if ($this->discount_percent !== null) {
            $percent = rtrim(rtrim(number_format((float) $this->discount_percent, 2, '.', ''), '0'), '.');

            return $code.' | '.$percent.'% OFF';
        }

        return $code.' | SPECIAL OFFER';
    }

    /**
     * Strip rich-text Description down to one clean header line.
     */
    public function plainDescriptionForBanner(): string
    {
        $raw = html_entity_decode(strip_tags((string) $this->description), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $raw = preg_replace('/\s+/u', ' ', $raw) ?? '';

        return trim($raw);
    }

    public function getOfferTypeLabelAttribute(): string
    {
        return self::OFFER_TYPES[$this->offer_type] ?? ucfirst((string) $this->offer_type);
    }

    public function getImageUrlAttribute(): string
    {
        if ($this->image && Storage::disk('public')->exists($this->image)) {
            return asset('storage/'.$this->image);
        }

        return 'https://images.unsplash.com/photo-1607082348824-0a96f2a4b9da?w=800&q=80';
    }
}
