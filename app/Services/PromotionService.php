<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\CouponRedemption;
use App\Models\Order;
use App\Models\User;
use App\Support\Money;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use RuntimeException;

class PromotionService
{
    public const SESSION_KEY = 'applied_coupon_code';

    public function appliedCode(): ?string
    {
        $code = Session::get(self::SESSION_KEY);

        return $code ? strtoupper(trim((string) $code)) : null;
    }

    public function applyCode(string $code, Collection $items, float $subtotal): array
    {
        $coupon = $this->findActiveCoupon($code);
        $quote = $this->quoteCoupon($coupon, $items, $subtotal, Auth::user());
        Session::put(self::SESSION_KEY, $coupon->code);

        return $quote;
    }

    public function clear(): void
    {
        Session::forget(self::SESSION_KEY);
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $items
     * @return array{
     *   code: ?string,
     *   coupon_id: ?int,
     *   label: ?string,
     *   amount: float,
     *   amount_formatted: string,
     *   free_shipping: bool,
     *   message: ?string
     * }
     */
    public function quote(?Collection $items, float $subtotal): array
    {
        $empty = [
            'code' => null,
            'coupon_id' => null,
            'label' => null,
            'amount' => 0.0,
            'amount_formatted' => Money::format(0),
            'free_shipping' => false,
            'message' => null,
        ];

        $code = $this->appliedCode();
        if ($items === null || $items->isEmpty()) {
            // Empty cart should not keep a sticky coupon for the next add-to-cart.
            $this->clear();

            return $empty;
        }

        if (! $code) {
            return $empty;
        }

        try {
            $coupon = $this->findActiveCoupon($code);
            return $this->quoteCoupon($coupon, $items, $subtotal, Auth::user());
        } catch (RuntimeException $e) {
            $this->clear();

            return array_merge($empty, ['message' => $e->getMessage()]);
        }
    }

    public function recordRedemption(Order $order, ?User $user = null): void
    {
        if (! $order->coupon_id || ! $order->coupon_code) {
            return;
        }

        CouponRedemption::create([
            'coupon_id' => $order->coupon_id,
            'user_id' => $user?->id ?? $order->user_id,
            'order_id' => $order->id,
            'code' => $order->coupon_code,
            'discount_amount' => (float) $order->discount_amount,
        ]);

        Coupon::query()->whereKey($order->coupon_id)->increment('used_count');
    }

    protected function findActiveCoupon(string $code): Coupon
    {
        $coupon = Coupon::query()
            ->where('code', strtoupper(trim($code)))
            ->where('is_active', true)
            ->first();

        if (! $coupon) {
            throw new RuntimeException('Invalid coupon code.');
        }

        if ($coupon->starts_at && now()->lt($coupon->starts_at)) {
            throw new RuntimeException('This coupon is not active yet.');
        }

        if ($coupon->ends_at && now()->gt($coupon->ends_at)) {
            throw new RuntimeException('This coupon has expired.');
        }

        if ($coupon->usage_limit !== null && (int) $coupon->used_count >= (int) $coupon->usage_limit) {
            throw new RuntimeException('This coupon has reached its usage limit.');
        }

        return $coupon;
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $items
     */
    protected function quoteCoupon(Coupon $coupon, Collection $items, float $subtotal, ?User $user): array
    {
        if ($coupon->members_only && ! $user) {
            throw new RuntimeException('Please log in to use this member offer.');
        }

        if ($coupon->new_customers_only) {
            // Guest checkout creates a customer account after validating that the
            // supplied email is not already registered. Until then, a guest is
            // eligible; signed-in customers can be checked immediately.
            if ($user) {
                $hasOrder = Order::query()
                    ->where('user_id', $user->id)
                    ->where('payment_status', 'paid')
                    ->exists();
                if ($hasOrder) {
                    throw new RuntimeException('This offer is only for first-time customers.');
                }
            }
        }

        if ($coupon->max_use_per_user && $user) {
            $used = CouponRedemption::query()
                ->where('coupon_id', $coupon->id)
                ->where('user_id', $user->id)
                ->exists();
            if ($used) {
                throw new RuntimeException('You have already used this coupon.');
            }
        }

        $eligibleItems = $this->eligibleItems($coupon, $items);
        if ($eligibleItems->isEmpty()) {
            throw new RuntimeException('This coupon does not apply to items in your cart.');
        }

        $eligibleQty = (int) $eligibleItems->sum('quantity');
        if ($coupon->min_quantity && $eligibleQty < (int) $coupon->min_quantity) {
            throw new RuntimeException('Add at least '.$coupon->min_quantity.' eligible item(s) to use this coupon.');
        }

        $eligibleSubtotal = (float) $eligibleItems->sum('line_total');
        if ($coupon->min_cart_status && $eligibleSubtotal < (float) $coupon->min_cart_amount) {
            throw new RuntimeException('Minimum cart amount for this coupon is '.Money::format($coupon->min_cart_amount).'.');
        }

        $discount = 0.0;
        if ($coupon->offer_type === 'bogo') {
            $buyQuantity = max(1, (int) $coupon->bogo_buy_quantity);
            $getQuantity = max(1, (int) $coupon->bogo_get_quantity);
            $groupSize = $buyQuantity + $getQuantity;

            // Each eligible product/variant line earns its own free units. This
            // prevents a higher-priced item being made free by a cheaper item.
            $discount = (float) $eligibleItems->sum(function (array $item) use ($groupSize, $getQuantity) {
                $quantity = (int) ($item['quantity'] ?? 0);
                $unitPrice = (float) ($item['unit_price'] ?? 0);
                $freeQuantity = intdiv($quantity, $groupSize) * $getQuantity;

                return $freeQuantity * $unitPrice;
            });
        } elseif ($coupon->discount_type === 'percent') {
            $discount = $eligibleSubtotal * ((float) $coupon->discount_percent / 100);
            if ($coupon->max_discount_status && $coupon->max_discount_amount !== null) {
                $discount = min($discount, (float) $coupon->max_discount_amount);
            }
        } elseif ($coupon->discount_type === 'amount') {
            $discount = min((float) $coupon->discount_amount, $eligibleSubtotal);
        }

        $discount = max(0, round($discount, 2));
        if ($discount <= 0 && ! $coupon->free_shipping) {
            if ($coupon->offer_type === 'bogo') {
                $required = max(1, (int) $coupon->bogo_buy_quantity) + max(1, (int) $coupon->bogo_get_quantity);
                throw new RuntimeException('Add '.$required.' matching eligible item(s) to use this Buy X, Get Y offer.');
            }

            throw new RuntimeException('This coupon does not provide a discount for your cart.');
        }

        return [
            'code' => $coupon->code,
            'coupon_id' => $coupon->id,
            'label' => $coupon->discount_label.($coupon->free_shipping ? ' + Free shipping' : ''),
            'amount' => $discount,
            'amount_formatted' => Money::format($discount),
            'free_shipping' => (bool) $coupon->free_shipping,
            'message' => null,
        ];
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $items
     */
    protected function eligibleItems(Coupon $coupon, Collection $items): Collection
    {
        $appliesTo = $coupon->applies_to ?: 'all';
        $categoryIds = collect($coupon->category_ids ?? [])->map(fn ($id) => (int) $id)->filter()->all();
        $productIds = collect($coupon->product_ids ?? [])->map(fn ($id) => (int) $id)->filter()->all();

        return $items->filter(function (array $item) use ($appliesTo, $categoryIds, $productIds) {
            $productId = (int) ($item['product_id'] ?? 0);
            $categoryId = (int) ($item['category_id'] ?? 0);

            if ($appliesTo === 'products') {
                return in_array($productId, $productIds, true);
            }
            if ($appliesTo === 'categories') {
                return in_array($categoryId, $categoryIds, true);
            }

            return true;
        })->values();
    }
}
