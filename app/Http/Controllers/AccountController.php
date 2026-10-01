<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\User;
use App\Models\UserAddress;
use App\Models\Wishlist;
use App\Support\Money;
use App\Support\OrderStatuses;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Services\MetaConversionsService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AccountController extends Controller
{
    public function updateProfile(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:120'],
            'last_name' => ['nullable', 'string', 'max:120'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'phone' => ['nullable', 'string', 'max:30'],
            'birth_date' => ['nullable', 'date', 'before:today'],
            'marketing_opt_in' => ['sometimes', 'boolean'],
            'current_password' => ['nullable', 'string'],
            'new_password' => ['nullable', 'string', 'min:6', 'confirmed'],
        ], [
            'email.unique' => 'This email is already registered.',
            'new_password.confirmed' => 'New passwords do not match.',
            'new_password.min' => 'New password must be at least 6 characters.',
        ]);

        if (! empty($data['new_password'])) {
            if (empty($data['current_password']) || ! Hash::check($data['current_password'], $user->password)) {
                throw ValidationException::withMessages([
                    'current_password' => 'Current password is incorrect.',
                ]);
            }
            $user->password = $data['new_password'];
        }

        $user->name = trim($data['first_name'].' '.($data['last_name'] ?? ''));
        $user->email = $data['email'];
        $user->phone = $data['phone'] ?? null;
        $user->birth_date = $data['birth_date'] ?? null;
        if (array_key_exists('marketing_opt_in', $data)) {
            $user->marketing_opt_in = (bool) $data['marketing_opt_in'];
        }
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Your information has been saved.',
            'user' => $this->userPayload($user->fresh()),
        ]);
    }

    public function storeAddress(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'address_line1' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:120'],
            'pincode' => ['required', 'string', 'max:20'],
            'country' => ['required', 'string', 'max:120'],
            'label' => ['nullable', 'string', 'max:50'],
            'phone' => ['nullable', 'string', 'max:30'],
            'state' => ['nullable', 'string', 'max:120'],
            'is_default' => ['sometimes', 'boolean'],
        ]);

        if (! empty($data['is_default'])) {
            $user->addresses()->update(['is_default' => false]);
        }

        $address = $user->addresses()->create([
            'name' => $data['name'],
            'address_line1' => $data['address_line1'],
            'city' => $data['city'],
            'pincode' => $data['pincode'],
            'country' => $data['country'],
            'label' => $data['label'] ?? 'Shipping',
            'phone' => $data['phone'] ?? null,
            'state' => $data['state'] ?? null,
            'is_default' => (bool) ($data['is_default'] ?? false),
        ]);

        if ($user->addresses()->count() === 1) {
            $address->update(['is_default' => true]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Address saved.',
            'address' => $this->addressPayload($address->fresh()),
        ]);
    }

    public function updateAddress(Request $request, UserAddress $address): JsonResponse
    {
        $this->assertOwned($request, $address->user_id);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'address_line1' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:120'],
            'pincode' => ['required', 'string', 'max:20'],
            'country' => ['required', 'string', 'max:120'],
            'label' => ['nullable', 'string', 'max:50'],
            'phone' => ['nullable', 'string', 'max:30'],
            'state' => ['nullable', 'string', 'max:120'],
            'is_default' => ['sometimes', 'boolean'],
        ]);

        if (! empty($data['is_default'])) {
            $request->user()->addresses()->where('id', '!=', $address->id)->update(['is_default' => false]);
        }

        $address->update([
            'name' => $data['name'],
            'address_line1' => $data['address_line1'],
            'city' => $data['city'],
            'pincode' => $data['pincode'],
            'country' => $data['country'],
            'label' => $data['label'] ?? $address->label,
            'phone' => $data['phone'] ?? null,
            'state' => $data['state'] ?? null,
            'is_default' => array_key_exists('is_default', $data) ? (bool) $data['is_default'] : $address->is_default,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Address updated.',
            'address' => $this->addressPayload($address->fresh()),
        ]);
    }

    public function destroyAddress(Request $request, UserAddress $address): JsonResponse
    {
        $this->assertOwned($request, $address->user_id);
        $address->delete();

        return response()->json([
            'success' => true,
            'message' => 'Address deleted.',
        ]);
    }

    public function setDefaultAddress(Request $request, UserAddress $address): JsonResponse
    {
        $this->assertOwned($request, $address->user_id);
        $request->user()->addresses()->update(['is_default' => false]);
        $address->update(['is_default' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Default address updated.',
            'address' => $this->addressPayload($address->fresh()),
        ]);
    }

    public function destroyWishlistItem(Request $request, Wishlist $wishlist): JsonResponse
    {
        $this->assertOwned($request, $wishlist->user_id);
        $wishlist->delete();

        return response()->json([
            'success' => true,
            'message' => 'Removed from wishlist.',
        ]);
    }

    public function storeWishlist(Request $request): JsonResponse
    {
        $data = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
        ]);

        $item = Wishlist::firstOrCreate([
            'user_id' => $request->user()->id,
            'product_id' => $data['product_id'],
        ]);

        $item->load('product');

        if ($item->wasRecentlyCreated && $item->product) {
            app(MetaConversionsService::class)->track(
                'AddToWishlist',
                $request,
                app(MetaConversionsService::class)->productData($item->product)
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Added to wishlist.',
            'item' => $this->wishlistPayload($item),
        ]);
    }

    public function destroyReview(Request $request, ProductReview $review): JsonResponse
    {
        $this->assertOwned($request, (int) $review->user_id);
        $review->delete();

        return response()->json([
            'success' => true,
            'message' => 'Review deleted.',
        ]);
    }

    private function assertOwned(Request $request, ?int $ownerId): void
    {
        if ((int) $ownerId !== (int) $request->user()->id) {
            abort(403);
        }
    }

    public static function bootstrap(User $user): array
    {
        $nameParts = preg_split('/\s+/', trim((string) $user->name), 2) ?: [];

        $addresses = $user->addresses()->latest()->get()->map(fn (UserAddress $a) => (new self)->addressPayload($a))->values();

        $wishlist = $user->wishlists()->with('product')->latest()->get()
            ->filter(fn (Wishlist $w) => $w->product)
            ->map(fn (Wishlist $w) => (new self)->wishlistPayload($w))
            ->values();

        $reviews = ProductReview::query()
            ->with('product')
            ->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                    ->orWhere('reviewer_email', $user->email);
            })
            ->latest()
            ->get()
            ->map(fn (ProductReview $r) => (new self)->reviewPayload($r))
            ->values();

        $orders = $user->orders()
            ->with(['items', 'statusLogs'])
            ->where(function ($q) {
                $q->where('payment_status', 'paid')
                    ->orWhereIn('status', [
                        OrderStatuses::PLACED,
                        OrderStatuses::PACKED,
                        OrderStatuses::SHIPPED,
                        OrderStatuses::DELIVERED,
                    ]);
            })
            ->latest('ordered_at')
            ->latest('id')
            ->get()
            ->map(fn (Order $o) => (new self)->orderPayload($o))
            ->values();

        return [
            'customer' => [
                'id' => $user->id,
                'firstName' => $nameParts[0] ?? $user->name,
                'lastName' => $nameParts[1] ?? '',
                'email' => $user->email,
                'phone' => $user->phone ?? '',
                'birthDate' => optional($user->birth_date)->format('Y-m-d') ?? '',
                'marketing' => (bool) ($user->marketing_opt_in ?? true),
            ],
            'orders' => $orders,
            'addresses' => $addresses,
            'wishlist' => $wishlist,
            'reviews' => $reviews,
            'notifications' => $user->notifications()->latest()->limit(50)->get()->map(fn ($n) => [
                'id' => $n->id,
                'title' => $n->data['title'] ?? 'Notification',
                'body' => $n->data['body'] ?? '',
                'message' => $n->data['message'] ?? null,
                'status' => $n->data['status'] ?? null,
                'statusLabel' => $n->data['status_label'] ?? null,
                'type' => $n->data['type'] ?? 'general',
                'date' => $n->created_at->format('d M Y, h:i A'),
                'read' => $n->read_at !== null,
            ])->values(),
        ];
    }

    /** Mark all of the customer's notifications as read (called when the Notifications page opens). */
    public function readNotifications(Request $request): JsonResponse
    {
        $request->user()->unreadNotifications()->update(['read_at' => now()]);

        return response()->json(['success' => true]);
    }

    public function destroyNotification(Request $request, string $notification): JsonResponse
    {
        $request->user()->notifications()->where('id', $notification)->delete();

        return response()->json(['success' => true, 'message' => 'Notification removed.']);
    }

    private function orderPayload(Order $order): array
    {
        $status = $order->status === 'pending' ? OrderStatuses::PLACED : $order->status;
        $shipParts = array_filter([
            $order->shipping_address,
            $order->shipping_city,
            $order->shipping_state,
            $order->shipping_pincode,
            $order->shipping_country,
        ]);

        return [
            'id' => $order->id,
            'number' => $order->order_number,
            'date' => optional($order->ordered_at ?: $order->created_at)->format('d M Y'),
            'date_long' => optional($order->ordered_at ?: $order->created_at)->format('F j, Y'),
            'status' => OrderStatuses::label($status),
            'status_key' => $status,
            'total' => (float) $order->payable_amount,
            'total_formatted' => Money::format($order->payable_amount),
            'subtotal' => (float) $order->subtotal,
            'subtotal_formatted' => Money::format($order->subtotal),
            'shipping' => (float) $order->delivery_charge,
            'shipping_formatted' => Money::format($order->delivery_charge),
            'payment_mode' => strtoupper((string) ($order->payment_mode ?: 'razorpay')),
            'payment_id' => $order->payment_id,
            'items_count' => (int) $order->items->sum('quantity'),
            'payment_status' => $order->payment_label,
            'expected_delivery' => $order->expected_delivery_date ? $order->expected_delivery_date->format('D, j M Y') : null,
            'tracking' => $this->trackingPayload($order, $status),
            'shipping_name' => $order->shipping_name,
            'shipping_phone' => $order->shipping_phone,
            'shipping_email' => $order->shipping_email ?: $order->user_email,
            'shipping_lines' => array_values($shipParts),
            'items' => $order->items->map(fn ($item) => [
                'title' => $item->product_title,
                'image' => \App\Support\Media::url($item->product_image),
                'color' => $item->color,
                'size' => $item->size,
                'package_label' => $item->package_label,
                'qty' => (int) $item->quantity,
                'price' => (float) $item->price,
                'price_formatted' => Money::format($item->price),
                'total' => (float) $item->total_price,
                'total_formatted' => Money::format($item->total_price),
                'status' => OrderStatuses::label($item->status ?: $status),
            ])->values()->all(),
        ];
    }

    /**
     * Flipkart-style progress for the customer: Ordered → Packed → Shipped → Delivered, each with the
     * date the admin set it (from the order's status history). A later step also marks earlier ones done.
     */
    private function trackingPayload(Order $order, string $status): array
    {
        $logs = $order->relationLoaded('statusLogs') ? $order->statusLogs : $order->statusLogs()->get();
        $dateOf = function (string $key) use ($logs) {
            $log = $logs->where('status', $key)->last();
            $at = $log ? ($log->logged_at ?: $log->created_at) : null;
            return $at ? $at->format('D, j M') : null;
        };
        $steps = [
            ['key' => OrderStatuses::PLACED, 'label' => 'Ordered', 'date' => $dateOf(OrderStatuses::PLACED) ?: optional($order->ordered_at ?: $order->created_at)->format('D, j M')],
            ['key' => OrderStatuses::PACKED, 'label' => 'Packed', 'date' => $dateOf(OrderStatuses::PACKED)],
            ['key' => OrderStatuses::SHIPPED, 'label' => 'Shipped', 'date' => $dateOf(OrderStatuses::SHIPPED)],
            ['key' => OrderStatuses::DELIVERED, 'label' => 'Delivered', 'date' => $dateOf(OrderStatuses::DELIVERED)],
        ];
        $order_ = array_column($steps, 'key');
        $reached = array_search($status, $order_, true);
        $cancelled = $status === OrderStatuses::CANCELLED;
        if ($cancelled) {
            // Show how far it got before cancellation.
            $reached = 0;
            foreach ($order_ as $i => $key) { if ($dateOf($key)) { $reached = $i; } }
        }
        foreach ($steps as $i => &$step) {
            $step['done'] = $reached !== false && $i <= $reached;
            $step['current'] = ! $cancelled && $i === $reached;
        }
        unset($step);
        // A step the admin skipped (e.g. Placed → Shipped) is still done; show the date the order
        // moved past it, i.e. the date of the next step that was recorded.
        for ($i = count($steps) - 1, $next = null; $i >= 0; $i--) {
            if (! $steps[$i]['done']) { continue; }
            if ($steps[$i]['date']) { $next = $steps[$i]['date']; } elseif ($next) { $steps[$i]['date'] = $next; }
        }
        $latest = $logs->last();

        return [
            'steps' => $steps,
            'cancelled' => $cancelled,
            'cancelled_on' => $cancelled ? $dateOf(OrderStatuses::CANCELLED) : null,
            'delivered' => $status === OrderStatuses::DELIVERED,
            'latest_note' => $latest && ! in_array($latest->status, ['pending'], true) ? $latest->description : null,
        ];
    }

    private function userPayload(User $user): array
    {
        $parts = preg_split('/\s+/', trim($user->name), 2) ?: [];

        return [
            'id' => $user->id,
            'name' => $user->name,
            'firstName' => $parts[0] ?? $user->name,
            'lastName' => $parts[1] ?? '',
            'email' => $user->email,
            'phone' => $user->phone ?? '',
            'birthDate' => optional($user->birth_date)->format('Y-m-d') ?? '',
            'marketing' => (bool) ($user->marketing_opt_in ?? true),
        ];
    }

    private function addressPayload(UserAddress $address): array
    {
        return [
            'id' => $address->id,
            'name' => $address->name,
            'line1' => $address->address_line1,
            'line2' => $address->address_line2,
            'city' => $address->city,
            'state' => $address->state,
            'postcode' => $address->pincode,
            'country' => $address->country,
            'phone' => $address->phone,
            'type' => $address->label ?: 'Shipping',
            'default' => (bool) $address->is_default,
        ];
    }

    private function wishlistPayload(Wishlist $item): array
    {
        /** @var Product|null $product */
        $product = $item->product;

        return [
            'id' => $item->id,
            'product_id' => $item->product_id,
            'name' => $product?->title ?? 'Product',
            'price' => $product?->selling_price,
            'mrp' => $product?->mrp,
            'discount_percent' => $product && $product->mrp > $product->selling_price
                ? (int) round((($product->mrp - $product->selling_price) / $product->mrp) * 100)
                : 0,
            'availability' => ($product && $product->is_active) ? 'Available' : 'Unavailable',
            'image' => $product?->featured_image_url ?? asset('vastu/images/placeholder.jpg'),
            'url' => $product ? route('shop.single', $product->slug) : route('shop'),
        ];
    }

    private function reviewPayload(ProductReview $review): array
    {
        $product = $review->product;

        return [
            'id' => $review->id,
            'product_id' => $review->product_id,
            'product_name' => $product?->title ?? 'Product',
            'product_url' => $product ? route('shop.single', $product->slug) : route('shop'),
            'product_image' => $product?->featured_image_url ?? asset('vastu/images/placeholder.jpg'),
            'rating' => (int) $review->rating,
            'comment' => $review->comment,
            'date' => optional($review->created_at)->format('d M Y'),
        ];
    }
}
