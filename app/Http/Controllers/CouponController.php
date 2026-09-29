<?php

namespace App\Http\Controllers;

use App\Services\CartService;
use App\Services\PromotionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

class CouponController extends Controller
{
    public function __construct(
        private CartService $cart,
        private PromotionService $promotions
    ) {
    }

    public function apply(Request $request): JsonResponse
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:50'],
        ]);

        $items = $this->cart->lines();
        if ($items->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Your cart is empty.',
                'cart' => $this->cart->summary(),
            ], 422);
        }

        try {
            $subtotal = (float) $items->sum('line_total');
            $this->promotions->applyCode($data['code'], $items, $subtotal);

            return response()->json([
                'success' => true,
                'message' => 'Coupon applied successfully.',
                'cart' => $this->cart->summary(),
            ]);
        } catch (RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'cart' => $this->cart->summary(),
            ], 422);
        }
    }

    public function remove(): JsonResponse
    {
        $this->promotions->clear();

        return response()->json([
            'success' => true,
            'message' => 'Coupon removed.',
            'cart' => $this->cart->summary(),
        ]);
    }
}
