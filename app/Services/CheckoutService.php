<?php

namespace App\Services;

use App\Mail\OrderPlacedAdminMail;
use App\Mail\OrderPlacedCustomerMail;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusLog;
use App\Models\User;
use App\Models\UserAddress;
use App\Support\OrderStatuses;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use RuntimeException;

class CheckoutService
{
    public function __construct(
        private CartService $cart,
        private ShippingService $shipping,
        private PromotionService $promotions
    )
    {
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{order: Order, razorpay: array<string, mixed>, guest_password: ?string}
     */
    public function createPendingOrder(array $data): array
    {
        $summary = $this->cart->summary();
        if ($summary['count'] < 1) {
            throw new RuntimeException('Your cart is empty.');
        }

        $guestPassword = null;
        $user = Auth::user();

        if (! $user) {
            $existing = User::query()->where('email', $data['email'])->first();
            if ($existing) {
                throw new RuntimeException('This email is already registered. Please log in to continue checkout.');
            }

            $guestPassword = Str::random(10);
            $fullName = trim($data['first_name'].' '.$data['last_name']);
            $user = User::create([
                'name' => $fullName,
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'password' => $guestPassword,
                'role' => 'customer',
                'is_active' => true,
                'login_provider' => 'email',
            ]);

            Auth::login($user);
            $this->cart->mergeSessionIntoUser($user);
            $summary = $this->cart->summary();
        } else {
            if (empty($user->phone) && ! empty($data['phone'])) {
                $user->phone = $data['phone'];
                $user->save();
            }
        }

        $this->cart->assertInventoryAvailable($summary['items']);

        $this->upsertAddress($user, $data);

        $subtotal = (float) $summary['subtotal'];
        $discountQuote = $summary['discount'] ?? $this->promotions->quote($summary['items'], $subtotal);
        $discount = (float) ($discountQuote['amount'] ?? 0);
        $delivery = (float) ($summary['shipping']['amount'] ?? $this->shipping->quote(max(0, $subtotal - $discount))['amount']);
        if (! empty($discountQuote['free_shipping'])) {
            $delivery = 0.0;
        }
        $payable = max(0, $subtotal - $discount + $delivery);
        $fullName = trim($data['first_name'].' '.$data['last_name']);
        $addressLine = trim(($data['address_line1'] ?? '').($data['address_line2'] ? ', '.$data['address_line2'] : ''));

        $order = DB::transaction(function () use ($user, $data, $summary, $subtotal, $delivery, $discount, $discountQuote, $payable, $fullName, $addressLine) {
            $order = Order::create([
                'order_number' => Order::generateOrderNumber(),
                'user_id' => $user->id,
                'user_name' => $fullName ?: $user->name,
                'user_phone' => $data['phone'] ?? $user->phone,
                'user_email' => $data['email'] ?? $user->email,
                'shipping_name' => $fullName ?: $user->name,
                'shipping_phone' => $data['phone'] ?? $user->phone,
                'shipping_email' => $data['email'] ?? $user->email,
                'shipping_address' => $addressLine,
                'shipping_city' => $data['city'] ?? null,
                'shipping_state' => $data['state'] ?? null,
                'shipping_pincode' => $data['pincode'] ?? null,
                'shipping_country' => $data['country'] ?? 'India',
                'order_notes' => $data['notes'] ?? null,
                'payment_mode' => 'razorpay',
                'payment_status' => 'pending',
                'subtotal' => $subtotal,
                'discount_amount' => $discount,
                'coupon_code' => $discountQuote['code'] ?? null,
                'coupon_id' => $discountQuote['coupon_id'] ?? null,
                'delivery_charge' => $delivery,
                'payable_amount' => $payable,
                'status' => 'pending',
                'ordered_at' => now(),
            ]);

            foreach ($summary['items'] as $item) {
                $unit = (float) ($item['unit_price'] ?? 0);
                $qty = (int) ($item['quantity'] ?? 1);
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'] ?? null,
                    'product_title' => $item['title'] ?? 'Product',
                    'product_image' => $item['image'] ?? null,
                    'color' => $item['color'] ?? null,
                    'size' => $item['size'] ?? null,
                    'package_label' => $item['package_label'] ?? null,
                    'price' => $unit,
                    'mrp' => $unit,
                    'saving' => 0,
                    'quantity' => $qty,
                    'total_price' => $unit * $qty,
                    'status' => OrderStatuses::PLACED,
                ]);
            }

            OrderStatusLog::create([
                'order_id' => $order->id,
                'status' => 'pending',
                'title' => 'Awaiting payment',
                'description' => 'Order created. Waiting for Razorpay payment.',
                'logged_at' => now(),
            ]);

            return $order;
        });

        $razorpay = $this->createRazorpayOrder($order);

        $order->razorpay_order_id = $razorpay['id'];
        $order->save();

        return [
            'order' => $order->fresh('items'),
            'razorpay' => $razorpay,
            'guest_password' => $guestPassword,
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function verifyAndComplete(Order $order, array $payload, ?string $guestPassword = null): Order
    {
        $paymentId = (string) ($payload['razorpay_payment_id'] ?? '');
        $razorpayOrderId = (string) ($payload['razorpay_order_id'] ?? '');
        $signature = (string) ($payload['razorpay_signature'] ?? '');

        if ($order->razorpay_order_id && $razorpayOrderId && $order->razorpay_order_id !== $razorpayOrderId) {
            throw new RuntimeException('Razorpay order mismatch.');
        }

        $expected = hash_hmac(
            'sha256',
            $razorpayOrderId.'|'.$paymentId,
            (string) config('razorpay.key_secret')
        );

        if (! hash_equals($expected, $signature)) {
            throw new RuntimeException('Payment signature verification failed.');
        }

        if ($order->payment_status === 'paid') {
            return $order->load('items');
        }

        $order->payment_id = $paymentId;
        $order->razorpay_order_id = $razorpayOrderId ?: $order->razorpay_order_id;
        $order->payment_mode = 'razorpay';
        $order->payment_status = 'paid';
        $order->status = OrderStatuses::PLACED;
        $order->ordered_at = $order->ordered_at ?: now();
        $order->save();

        OrderStatusLog::create([
            'order_id' => $order->id,
            'status' => OrderStatuses::PLACED,
            'title' => 'Payment received',
            'description' => 'Payment successful via Razorpay. Payment ID: '.$paymentId,
            'logged_at' => now(),
        ]);

        $this->promotions->recordRedemption($order);
        $this->promotions->clear();
        $this->cart->clear();
        $this->sendOrderEmails($order->fresh('items', 'user'), $guestPassword);

        return $order->fresh('items', 'user');
    }

    public function sendOrderEmails(Order $order, ?string $guestPassword = null): void
    {
        $customerEmail = $order->shipping_email ?: $order->user_email ?: optional($order->user)->email;
        $adminEmail = config('mail.admin_address') ?: config('mail.from.address');

        if ($customerEmail) {
            try {
                Mail::to($customerEmail)->send(new OrderPlacedCustomerMail($order, $guestPassword));
                Log::info('Order customer email sent', [
                    'order' => $order->order_number,
                    'to' => $customerEmail,
                ]);
            } catch (\Throwable $e) {
                Log::error('Order customer email failed: '.$e->getMessage(), [
                    'order' => $order->order_number,
                    'to' => $customerEmail,
                ]);
            }
        } else {
            Log::warning('Order customer email skipped — no recipient', ['order' => $order->order_number]);
        }

        if ($adminEmail) {
            try {
                Mail::to($adminEmail)->send(new OrderPlacedAdminMail($order));
                Log::info('Order admin email sent', [
                    'order' => $order->order_number,
                    'to' => $adminEmail,
                ]);
            } catch (\Throwable $e) {
                Log::error('Order admin email failed: '.$e->getMessage(), [
                    'order' => $order->order_number,
                    'to' => $adminEmail,
                ]);
            }
        }
    }

    /**
     * @return array<string, mixed>
     */
    protected function createRazorpayOrder(Order $order): array
    {
        $key = (string) config('razorpay.key_id');
        $secret = (string) config('razorpay.key_secret');
        if ($key === '' || $secret === '') {
            throw new RuntimeException('Razorpay is not configured.');
        }

        $amountPaise = (int) round(((float) $order->payable_amount) * 100);
        if ($amountPaise < 100) {
            throw new RuntimeException('Order amount is too low for payment.');
        }

        $response = Http::withBasicAuth($key, $secret)
            ->acceptJson()
            ->asJson()
            ->timeout(30)
            ->post('https://api.razorpay.com/v1/orders', [
                'amount' => $amountPaise,
                'currency' => config('razorpay.currency', 'INR'),
                'receipt' => substr($order->order_number, 0, 40),
                'payment_capture' => 1,
                'notes' => [
                    'order_number' => $order->order_number,
                    'order_id' => (string) $order->id,
                ],
            ]);

        if (! $response->successful()) {
            $body = $response->json();
            $rzpMessage = data_get($body, 'error.description', 'Unable to start Razorpay payment.');
            Log::error('Razorpay order create failed', [
                'status' => $response->status(),
                'body' => $response->body(),
                'key_id' => $key,
            ]);

            if ($response->status() === 401) {
                throw new RuntimeException('Razorpay authentication failed. Please check RAZORPAY_KEY_ID and RAZORPAY_KEY_SECRET in .env (Dashboard → API Keys → Test mode).');
            }

            throw new RuntimeException($rzpMessage.' Please try again.');
        }

        return $response->json();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function upsertAddress(User $user, array $data): void
    {
        $fullName = trim(($data['first_name'] ?? '').' '.($data['last_name'] ?? ''));
        $payload = [
            'label' => 'Checkout',
            'name' => $fullName ?: $user->name,
            'phone' => $data['phone'] ?? $user->phone,
            'address_line1' => $data['address_line1'] ?? '',
            'address_line2' => $data['address_line2'] ?? null,
            'city' => $data['city'] ?? '',
            'state' => $data['state'] ?? '',
            'pincode' => $data['pincode'] ?? '',
            'country' => $data['country'] ?? 'India',
            'is_default' => true,
        ];

        $address = UserAddress::query()
            ->where('user_id', $user->id)
            ->where('is_default', true)
            ->first();

        if ($address) {
            $address->fill($payload)->save();
        } else {
            UserAddress::query()
                ->where('user_id', $user->id)
                ->update(['is_default' => false]);
            $user->addresses()->create($payload);
        }
    }
}
