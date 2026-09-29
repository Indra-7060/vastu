<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class MetaConversionsService
{
    public const SUPPORTED_EVENTS = [
        'ViewContent', 'Search', 'AddToWishlist', 'AddToCart',
        'InitiateCheckout', 'AddPaymentInfo', 'Purchase',
        'Subscribe', 'StartTrial', 'CompleteRegistration',
        'Contact', 'FindLocation', 'Schedule',
    ];

    public function enabled(): bool
    {
        return (bool) config('services.meta_capi.enabled')
            && filled(config('services.meta_capi.dataset_id'))
            && filled(config('services.meta_capi.access_token'));
    }

    /**
     * Send one website event to Meta. Failures are logged and never interrupt the customer flow.
     *
     * @param array<string, mixed> $customData
     * @param array<string, mixed> $customerData
     */
    public function track(
        string $eventName,
        Request $request,
        array $customData = [],
        array $customerData = [],
        ?string $eventId = null
    ): string {
        $eventId ??= (string) Str::uuid();

        if (! in_array($eventName, self::SUPPORTED_EVENTS, true) || ! $this->enabled()) {
            return $eventId;
        }

        $payload = [
            'data' => [[
                'event_name' => $eventName,
                'event_time' => now()->timestamp,
                'event_id' => $eventId,
                'event_source_url' => $this->sourceUrl($request),
                'action_source' => 'website',
                'user_data' => $this->userData($request, $customerData),
                'custom_data' => array_filter($customData, static fn ($value) => $value !== null && $value !== ''),
            ]],
        ];

        if (filled(config('services.meta_capi.test_event_code'))) {
            $payload['test_event_code'] = config('services.meta_capi.test_event_code');
        }

        try {
            $response = Http::timeout((int) config('services.meta_capi.timeout', 4))
                ->acceptJson()
                ->withToken((string) config('services.meta_capi.access_token'))
                ->post($this->endpoint(), $payload);

            if (! $response->successful()) {
                Log::warning('Meta CAPI event rejected', [
                    'event_name' => $eventName,
                    'event_id' => $eventId,
                    'status' => $response->status(),
                    'response' => Str::limit($response->body(), 1000),
                ]);
            }
        } catch (Throwable $exception) {
            Log::warning('Meta CAPI event failed', [
                'event_name' => $eventName,
                'event_id' => $eventId,
                'error' => $exception->getMessage(),
            ]);
        }

        return $eventId;
    }

    /** @return array<string, mixed> */
    public function productData(Product $product, int $quantity = 1): array
    {
        $price = (float) ($product->selling_price ?: $product->mrp);

        return [
            'content_ids' => [(string) $product->id],
            'content_name' => $product->title,
            'content_category' => $product->category?->title,
            'content_type' => 'product',
            'contents' => [[
                'id' => (string) $product->id,
                'quantity' => $quantity,
                'item_price' => $price,
            ]],
            'value' => round($price * $quantity, 2),
            'currency' => 'INR',
        ];
    }

    /** @return array<string, mixed> */
    public function cartLineData(array $line, ?float $value = null): array
    {
        $quantity = (int) ($line['quantity'] ?? 1);
        $unitPrice = (float) ($line['unit_price'] ?? 0);

        return [
            'content_ids' => [(string) ($line['product_id'] ?? '')],
            'content_name' => $line['title'] ?? null,
            'content_type' => 'product',
            'contents' => [[
                'id' => (string) ($line['product_id'] ?? ''),
                'quantity' => $quantity,
                'item_price' => $unitPrice,
            ]],
            'value' => round($value ?? ($unitPrice * $quantity), 2),
            'currency' => 'INR',
        ];
    }

    /** @return array<string, mixed> */
    public function orderData(Order $order): array
    {
        $order->loadMissing('items');

        return [
            'order_id' => $order->order_number,
            'content_ids' => $order->items->pluck('product_id')->filter()->map(fn ($id) => (string) $id)->values()->all(),
            'content_type' => 'product',
            'contents' => $order->items->map(fn ($item) => [
                'id' => (string) $item->product_id,
                'quantity' => (int) $item->quantity,
                'item_price' => (float) $item->price,
            ])->values()->all(),
            'value' => (float) $order->payable_amount,
            'currency' => 'INR',
        ];
    }

    /** @return array<string, mixed> */
    public function customerFromOrder(Order $order): array
    {
        $names = preg_split('/\s+/', trim((string) $order->shipping_name), 2) ?: [];

        return [
            'email' => $order->shipping_email ?: $order->user_email,
            'phone' => $order->shipping_phone ?: $order->user_phone,
            'first_name' => $names[0] ?? null,
            'last_name' => $names[1] ?? null,
            'city' => $order->shipping_city,
            'state' => $order->shipping_state,
            'zip' => $order->shipping_pincode,
            'country' => $order->shipping_country,
            'external_id' => $order->user_id,
        ];
    }

    /** @param array<string, mixed> $provided @return array<string, mixed> */
    private function userData(Request $request, array $provided): array
    {
        $user = $request->user();
        if ($user instanceof User) {
            $names = preg_split('/\s+/', trim((string) $user->name), 2) ?: [];
            $address = $user->addresses()->where('is_default', true)->first()
                ?? $user->addresses()->latest('id')->first();
            $provided += [
                'email' => $user->email,
                'phone' => $user->phone,
                'first_name' => $names[0] ?? null,
                'last_name' => $names[1] ?? null,
                'date_of_birth' => optional($user->birth_date)->format('Ymd'),
                'city' => $address?->city,
                'state' => $address?->state,
                'zip' => $address?->pincode,
                'country' => $address?->country,
                'external_id' => $user->id,
            ];
        }

        $fbc = $request->cookie('_fbc');
        if (! $fbc && filled($request->query('fbclid'))) {
            $fbc = 'fb.1.'.(int) floor(microtime(true) * 1000).'.'.$request->query('fbclid');
        }

        $data = [
            'client_ip_address' => $request->ip(),
            'client_user_agent' => $request->userAgent(),
            'fbc' => $fbc,
            'fbp' => $request->cookie('_fbp'),
        ];

        $hashFields = [
            'email' => 'em', 'phone' => 'ph', 'first_name' => 'fn', 'last_name' => 'ln',
            'gender' => 'ge', 'date_of_birth' => 'db', 'city' => 'ct', 'state' => 'st',
            'zip' => 'zp', 'country' => 'country', 'external_id' => 'external_id',
        ];

        foreach ($hashFields as $source => $target) {
            $normalized = $this->normalize($source, $provided[$source] ?? null);
            if ($normalized !== null) {
                $data[$target] = [hash('sha256', $normalized)];
            }
        }

        return array_filter($data, static fn ($value) => $value !== null && $value !== '' && $value !== []);
    }

    private function normalize(string $field, mixed $value): ?string
    {
        if ($value === null || trim((string) $value) === '') {
            return null;
        }

        $value = mb_strtolower(trim((string) $value));
        if ($field === 'phone') {
            $value = preg_replace('/\D+/', '', $value) ?: '';
        } elseif ($field === 'date_of_birth') {
            $value = preg_replace('/\D+/', '', $value) ?: '';
        } elseif ($field === 'country') {
            $value = $value === 'india' ? 'in' : $value;
        }

        return $value !== '' ? $value : null;
    }

    private function endpoint(): string
    {
        $version = trim((string) config('services.meta_capi.api_version', 'v26.0'), '/');
        $dataset = rawurlencode((string) config('services.meta_capi.dataset_id'));

        return "https://graph.facebook.com/{$version}/{$dataset}/events";
    }

    private function sourceUrl(Request $request): string
    {
        $referer = (string) $request->headers->get('referer', '');
        $refererHost = parse_url($referer, PHP_URL_HOST);

        return $referer !== '' && $refererHost === $request->getHost()
            ? $referer
            : $request->fullUrl();
    }
}
