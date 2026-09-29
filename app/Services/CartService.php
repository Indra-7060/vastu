<?php

namespace App\Services;

use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class CartService
{
    public const SESSION_KEY = 'cart';

    public function __construct(
        private ShippingService $shipping,
        private PromotionService $promotions
    ) {
    }

    public function count(): int
    {
        return (int) $this->lines()->sum('quantity');
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function lines(): Collection
    {
        if ($this->customer()) {
            return CartItem::query()
                ->with(['product.offer', 'product.category'])
                ->where('user_id', $this->customer()->id)
                ->latest('id')
                ->get()
                ->map(fn (CartItem $item) => $this->formatDbLine($item))
                ->filter()
                ->values();
        }

        $session = Session::get(self::SESSION_KEY, []);
        if (! is_array($session) || $session === []) {
            return collect();
        }

        $productIds = collect($session)->pluck('product_id')->filter()->unique()->values();
        $products = Product::query()
            ->active()
            ->with(['offer', 'category'])
            ->whereIn('id', $productIds)
            ->get()
            ->keyBy('id');

        return collect($session)
            ->map(function (array $row) use ($products) {
                $product = $products->get((int) ($row['product_id'] ?? 0));
                if (! $product) {
                    return null;
                }

                return $this->formatLine(
                    $product,
                    (int) ($row['quantity'] ?? 1),
                    $row['color'] ?? null,
                    $row['size'] ?? null,
                    $this->resolvePackage($product, $row['package_key'] ?? null),
                    null
                );
            })
            ->filter()
            ->values();
    }

    /**
     * @return array{items: Collection, count: int, subtotal: float, subtotal_formatted: string, shipping: array<string, mixed>, discount: array<string, mixed>, total: float, total_formatted: string}
     */
    public function summary(): array
    {
        $items = $this->lines();
        $subtotal = (float) $items->sum('line_total');
        $discount = $this->promotions->quote($items, $subtotal);
        $shippingSubtotal = max(0, $subtotal - (float) $discount['amount']);
        $shipping = $this->shipping->quote($shippingSubtotal);
        if ($items->isEmpty() || ! empty($discount['free_shipping'])) {
            $shipping = array_merge($shipping, [
                'amount' => 0.0,
                'is_free' => true,
                'amount_formatted' => 'Free',
            ]);
        }
        $total = max(0, $subtotal - (float) $discount['amount'] + (float) $shipping['amount']);

        return [
            'items' => $items,
            'count' => (int) $items->sum('quantity'),
            'subtotal' => $subtotal,
            'subtotal_formatted' => \App\Support\Money::format($subtotal),
            'discount' => $discount,
            'shipping' => $shipping,
            'total' => $total,
            'total_formatted' => \App\Support\Money::format($total),
        ];
    }

    /**
     * @return array{item: array<string, mixed>, count: int, message: string}
     */
    public function add(int $productId, int $quantity = 1, ?string $color = null, ?string $size = null, ?string $packageKey = null): array
    {
        $product = Product::query()->active()->with(['colors', 'sizes'])->findOrFail($productId);
        // "Price on request" products (no price set) are enquiry-only.
        if ((float) $product->selling_price <= 0 && (float) $product->mrp <= 0) {
            abort(422, 'This product is available on request. Please enquire and our team will help you.');
        }
        $quantity = max(1, $quantity);
        [$color, $size] = $this->resolveVariants($product, $color, $size);
        $max = $this->maximumQuantity($product, $color, $size);
        $package = $this->resolvePackage($product, $packageKey);

        if ($user = $this->customer()) {
            $item = $this->findDbLine($user->id, $product->id, $color, $size, $package['key']) ?? new CartItem([
                'user_id' => $user->id,
                'product_id' => $product->id,
                'color' => $color,
                'size' => $size,
                'package_key' => $package['key'],
            ]);
            $requestedQuantity = ((int) $item->quantity) + $quantity;
            $this->ensureQuantityIsAvailable($requestedQuantity, $max);
            $item->quantity = $requestedQuantity;
            $item->color = $color;
            $item->size = $size;
            $item->package_key = $package['key'];
            $item->package_label = $package['label'];
            $item->package_price = $package['price'];
            $item->save();
            $line = $this->formatDbLine($item->fresh('product'));
        } else {
            $cart = Session::get(self::SESSION_KEY, []);
            $key = $this->lineKey($product->id, $color, $size, $package['key']);
            $existing = (int) ($cart[$key]['quantity'] ?? 0);
            $requestedQuantity = $existing + $quantity;
            $this->ensureQuantityIsAvailable($requestedQuantity, $max);
            $cart[$key] = [
                'product_id' => $product->id,
                'quantity' => $requestedQuantity,
                'color' => $color,
                'size' => $size,
                'package_key' => $package['key'],
            ];
            Session::put(self::SESSION_KEY, $cart);
            $line = $this->formatLine($product, $cart[$key]['quantity'], $color, $size, $package, null);
        }

        return [
            'item' => $line,
            'count' => $this->count(),
            'message' => 'Added to cart.',
        ];
    }

    /**
     * @return array{item: ?array<string, mixed>, count: int, message: string}
     */
    public function update(int $productId, int $quantity, ?string $color = null, ?string $size = null, ?string $packageKey = null): array
    {
        $quantity = max(0, $quantity);
        [$color, $size] = $this->normalizeVariant($color, $size);

        if ($quantity === 0) {
            return $this->remove($productId, $color, $size, $packageKey);
        }

        $product = Product::query()->active()->with(['colors', 'sizes'])->findOrFail($productId);
        $max = $this->maximumQuantity($product, $color, $size);
        $this->ensureQuantityIsAvailable($quantity, $max);
        $package = $this->resolvePackage($product, $packageKey);

        if ($user = $this->customer()) {
            $item = $this->findDbLine($user->id, $product->id, $color, $size, $package['key']);
            if (! $item) {
                abort(404, 'Cart item not found.');
            }
            $item->quantity = $quantity;
            $item->save();
            $line = $this->formatDbLine($item->fresh('product'));
        } else {
            $cart = Session::get(self::SESSION_KEY, []);
            $key = $this->lineKey($product->id, $color, $size, $package['key']);
            if (! isset($cart[$key])) {
                abort(404, 'Cart item not found.');
            }
            $cart[$key]['quantity'] = $quantity;
            Session::put(self::SESSION_KEY, $cart);
            $line = $this->formatLine(
                $product,
                $quantity,
                $cart[$key]['color'] ?? $color,
                $cart[$key]['size'] ?? $size,
                $package,
                null
            );
        }

        return [
            'item' => $line,
            'count' => $this->count(),
            'message' => 'Cart updated.',
        ];
    }

    public function assertInventoryAvailable(iterable $items): void
    {
        foreach ($items as $item) {
            $product = Product::query()->active()->with(['colors', 'sizes'])->find($item['product_id'] ?? null);
            if (! $product) {
                throw new \RuntimeException('A product in your cart is no longer available.');
            }

            $maximum = $this->maximumQuantity($product, $item['color'] ?? null, $item['size'] ?? null);
            $quantity = (int) ($item['quantity'] ?? 0);
            if ($maximum < 1) {
                throw new \RuntimeException("{$product->title} is out of stock.");
            }
            if ($quantity > $maximum) {
                throw new \RuntimeException("Only {$maximum} item(s) of {$product->title} are available.");
            }
        }
    }

    /**
     * @return array{item: null, count: int, message: string}
     */
    public function remove(int $productId, ?string $color = null, ?string $size = null, ?string $packageKey = null): array
    {
        [$color, $size] = $this->normalizeVariant($color, $size);

        if ($user = $this->customer()) {
            $item = $this->findDbLine($user->id, $productId, $color, $size, $packageKey);
            if ($item) {
                $item->delete();
            }
        } else {
            $cart = Session::get(self::SESSION_KEY, []);
            unset($cart[$this->lineKey($productId, $color, $size, $packageKey)]);
            Session::put(self::SESSION_KEY, $cart);
        }

        $count = $this->count();
        if ($count < 1) {
            $this->promotions->clear();
        }

        return [
            'item' => null,
            'count' => $count,
            'message' => 'Removed from cart.',
        ];
    }

    public function clear(): void
    {
        if ($user = $this->customer()) {
            CartItem::query()->where('user_id', $user->id)->delete();
        }
        Session::forget(self::SESSION_KEY);
        $this->promotions->clear();
    }

    public function mergeSessionIntoUser(User $user): void
    {
        $session = Session::get(self::SESSION_KEY, []);
        if (! is_array($session) || $session === []) {
            return;
        }

        foreach ($session as $row) {
            $productId = (int) ($row['product_id'] ?? 0);
            $quantity = max(1, (int) ($row['quantity'] ?? 1));
            if ($productId < 1) {
                continue;
            }

            $product = Product::query()->active()->find($productId);
            if (! $product) {
                continue;
            }

            [$color, $size] = $this->normalizeVariant($row['color'] ?? null, $row['size'] ?? null);
            $package = $this->resolvePackage($product, $row['package_key'] ?? null);
            $max = max(1, (int) ($product->max_unit_buy ?: 99));
            $item = $this->findDbLine($user->id, $product->id, $color, $size, $package['key']) ?? new CartItem([
                'user_id' => $user->id,
                'product_id' => $product->id,
                'color' => $color,
                'size' => $size,
                'package_key' => $package['key'],
            ]);
            $item->quantity = min($max, ((int) $item->quantity) + $quantity);
            $item->color = $color;
            $item->size = $size;
            $item->package_key = $package['key'];
            $item->package_label = $package['label'];
            $item->package_price = $package['price'];
            $item->save();
        }

        Session::forget(self::SESSION_KEY);
    }

    protected function customer(): ?User
    {
        $user = Auth::user();
        if ($user && method_exists($user, 'isCustomer') && $user->isCustomer()) {
            return $user;
        }

        return null;
    }

    /**
     * @return array{0:?string,1:?string}
     */
    protected function normalizeVariant(?string $color, ?string $size): array
    {
        $color = $color !== null ? trim($color) : null;
        $size = $size !== null ? trim($size) : null;

        if ($color === '' || $color === '?' || strcasecmp((string) $color, 'select') === 0) {
            $color = null;
        }
        if ($size === '' || $size === '?' || strcasecmp((string) $size, 'select') === 0) {
            $size = null;
        }

        return [
            $color !== null && $color !== '' ? $color : null,
            $size !== null && $size !== '' ? $size : null,
        ];
    }

    /**
     * Use selected variants when provided; otherwise default to first colour / size on the product.
     *
     * @return array{0:?string,1:?string}
     */
    protected function resolveVariants(Product $product, ?string $color, ?string $size): array
    {
        [$color, $size] = $this->normalizeVariant($color, $size);
        $product->loadMissing(['colors', 'sizes']);

        if ($color === null && $product->colors->isNotEmpty()) {
            $color = strtoupper((string) $product->colors->first()->name);
        }

        if ($size === null && $product->sizes->isNotEmpty()) {
            $size = (string) $product->sizes->first()->name;
        }

        return $this->normalizeVariant($color, $size);
    }

    protected function maximumQuantity(Product $product, ?string $color, ?string $size): int
    {
        $product->loadMissing(['colors', 'sizes']);
        $limits = [max(1, (int) ($product->max_unit_buy ?: 99))];

        if ($color !== null && $product->colors->isNotEmpty()) {
            $selectedColor = $product->colors->first(
                fn ($item) => mb_strtolower($item->name) === mb_strtolower($color)
            );
            if ($selectedColor) {
                $limits[] = (int) $selectedColor->pivot->quantity;
            }
        }

        if ($size !== null && $product->sizes->isNotEmpty()) {
            $selectedSize = $product->sizes->first(
                fn ($item) => mb_strtolower($item->name) === mb_strtolower($size)
            );
            if ($selectedSize) {
                $limits[] = (int) $selectedSize->pivot->quantity;
            }
        }

        return min($limits);
    }

    protected function ensureQuantityIsAvailable(int $quantity, int $maximum): void
    {
        if ($maximum < 1) {
            abort(422, 'This product option is out of stock.');
        }

        if ($quantity > $maximum) {
            abort(422, "Only {$maximum} item(s) are available for this product option.");
        }
    }

    /**
     * Never trust a package price sent by the browser. Resolve it from the product
     * record so the cart, order, and Razorpay payment all use the configured amount.
     *
     * @return array{key: ?string, label: ?string, mrp: ?float, price: ?float}
     */
    protected function resolvePackage(Product $product, ?string $packageKey): array
    {
        $product->loadMissing('category');
        if (optional($product->category)->slug !== 'accessories') {
            return ['key' => null, 'label' => null, 'mrp' => null, 'price' => null];
        }

        $packages = collect($product->accessoryPackages());
        $package = $packages->firstWhere('key', $packageKey) ?? $packages->first();
        if (! $package) {
            return ['key' => null, 'label' => null, 'mrp' => null, 'price' => null];
        }

        return [
            'key' => $package['key'],
            'label' => $package['label'],
            'mrp' => (float) ($package['mrp'] ?? $package['price']),
            'price' => (float) $package['price'],
        ];
    }

    protected function lineKey(int $productId, ?string $color, ?string $size, ?string $packageKey = null): string
    {
        return $productId.'|'.mb_strtolower((string) ($color ?? '')).'|'.mb_strtolower((string) ($size ?? '')).'|'.mb_strtolower((string) ($packageKey ?? ''));
    }

    protected function findDbLine(int $userId, int $productId, ?string $color, ?string $size, ?string $packageKey = null): ?CartItem
    {
        $query = CartItem::query()
            ->where('user_id', $userId)
            ->where('product_id', $productId);

        if ($color === null) {
            $query->where(function ($q) {
                $q->whereNull('color')->orWhere('color', '');
            });
        } else {
            $query->where('color', $color);
        }

        if ($size === null) {
            $query->where(function ($q) {
                $q->whereNull('size')->orWhere('size', '');
            });
        } else {
            $query->where('size', $size);
        }

        if ($packageKey === null) {
            $query->where(function ($q) {
                $q->whereNull('package_key')->orWhere('package_key', '');
            });
        } else {
            $query->where('package_key', $packageKey);
        }

        return $query->first();
    }

    protected function formatDbLine(?CartItem $item): ?array
    {
        if (! $item || ! $item->product) {
            return null;
        }

        $package = $this->resolvePackage($item->product, $item->package_key);
        if ($item->package_key && $item->package_price !== null) {
            $package = [
                'key' => $item->package_key,
                'label' => $item->package_label ?: ($package['label'] ?? null),
                'mrp' => $package['mrp'] ?? (float) $item->package_price,
                'price' => (float) $item->package_price,
            ];
        }

        return $this->formatLine(
            $item->product,
            (int) $item->quantity,
            $item->color,
            $item->size,
            $package,
            $item->id
        );
    }

    protected function formatLine(Product $product, int $quantity, ?string $color, ?string $size, array $package, ?int $cartItemId): array
    {
        $unit = $package['price'] ?? (float) $product->selling_price;
        $mrp = $package['mrp'] ?? (float) $product->mrp;
        $lineTotal = $unit * $quantity;
        [$color, $size] = $this->normalizeVariant($color, $size);

        return [
            'id' => $cartItemId,
            'product_id' => $product->id,
            'category_id' => $product->category_id,
            'title' => $product->title,
            'slug' => $product->slug,
            'url' => route('shop.single', $product->slug),
            'image' => $product->featured_image_url,
            'color' => $color,
            'size' => $size,
            'package_key' => $package['key'],
            'package_label' => $package['label'],
            'line_key' => $this->lineKey($product->id, $color, $size, $package['key']),
            'quantity' => $quantity,
            'unit_price' => $unit,
            'mrp' => $mrp,
            'mrp_formatted' => \App\Support\Money::format($mrp),
            'unit_price_formatted' => \App\Support\Money::format($unit),
            'discount_percent' => $mrp > $unit ? (int) round((($mrp - $unit) / $mrp) * 100) : 0,
            'line_total' => $lineTotal,
            'line_total_formatted' => \App\Support\Money::format($lineTotal),
            'max_quantity' => $this->maximumQuantity($product, $color, $size),
        ];
    }
}
