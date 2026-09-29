<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\CartService;
use App\Services\MetaConversionsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(private CartService $cart)
    {
    }

    public function index(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'cart' => $this->cart->summary(),
        ])->header('Cache-Control', 'no-store, private');
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'quantity' => ['nullable', 'integer', 'min:1', 'max:99'],
            'color' => ['nullable', 'string', 'max:80'],
            'size' => ['nullable', 'string', 'max:40'],
            'package_key' => ['nullable', 'string', 'max:50'],
        ]);

        $result = $this->cart->add(
            (int) $data['product_id'],
            (int) ($data['quantity'] ?? 1),
            $data['color'] ?? null,
            $data['size'] ?? null,
            $data['package_key'] ?? null
        );

        $product = Product::query()->with('category')->find((int) $data['product_id']);
        if ($product) {
            app(MetaConversionsService::class)->track(
                'AddToCart',
                $request,
                app(MetaConversionsService::class)->productData($product, (int) ($data['quantity'] ?? 1))
            );
        }

        return response()->json([
            'success' => true,
            'message' => $result['message'],
            'item' => $result['item'],
            'count' => $result['count'],
            'cart' => $this->cart->summary(),
        ]);
    }

    public function update(Request $request, int $productId): JsonResponse
    {
        $data = $request->validate([
            'quantity' => ['required', 'integer', 'min:0', 'max:99'],
            'color' => ['nullable', 'string', 'max:80'],
            'size' => ['nullable', 'string', 'max:40'],
            'package_key' => ['nullable', 'string', 'max:50'],
        ]);

        $result = $this->cart->update(
            $productId,
            (int) $data['quantity'],
            $data['color'] ?? null,
            $data['size'] ?? null,
            $data['package_key'] ?? null
        );

        return response()->json([
            'success' => true,
            'message' => $result['message'],
            'item' => $result['item'],
            'count' => $result['count'],
            'cart' => $this->cart->summary(),
        ]);
    }

    public function destroy(Request $request, int $productId): JsonResponse
    {
        $data = $request->validate([
            'color' => ['nullable', 'string', 'max:80'],
            'size' => ['nullable', 'string', 'max:40'],
            'package_key' => ['nullable', 'string', 'max:50'],
        ]);

        $result = $this->cart->remove(
            $productId,
            $data['color'] ?? null,
            $data['size'] ?? null,
            $data['package_key'] ?? null
        );

        return response()->json([
            'success' => true,
            'message' => $result['message'],
            'count' => $result['count'],
            'cart' => $this->cart->summary(),
        ]);
    }

    public function buyNow(Request $request): JsonResponse|RedirectResponse
    {
        $data = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'quantity' => ['nullable', 'integer', 'min:1', 'max:99'],
            'color' => ['nullable', 'string', 'max:80'],
            'size' => ['nullable', 'string', 'max:40'],
            'package_key' => ['nullable', 'string', 'max:50'],
        ]);

        $result = $this->cart->add(
            (int) $data['product_id'],
            (int) ($data['quantity'] ?? 1),
            $data['color'] ?? null,
            $data['size'] ?? null,
            $data['package_key'] ?? null
        );

        $product = Product::query()->with('category')->find((int) $data['product_id']);
        if ($product) {
            app(MetaConversionsService::class)->track(
                'AddToCart',
                $request,
                app(MetaConversionsService::class)->productData($product, (int) ($data['quantity'] ?? 1))
            );
        }

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'count' => $result['count'],
                'redirect' => route('checkout'),
                'cart' => $this->cart->summary(),
            ]);
        }

        return redirect()->route('checkout');
    }
}
