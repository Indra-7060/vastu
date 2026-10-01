<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\CartService;
use App\Services\CheckoutService;
use App\Services\MetaConversionsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use RuntimeException;
use Throwable;

class CheckoutController extends Controller
{
    public function __construct(
        private CartService $cart,
        private CheckoutService $checkout
    ) {
    }

    public function show(Request $request): View|RedirectResponse
    {
        $summary = $this->cart->summary();
        if ($summary['count'] < 1) {
            return redirect()->route('cart')->with('error', 'Your cart is empty.');
        }

        app(MetaConversionsService::class)->track('InitiateCheckout', $request, [
            'content_ids' => $summary['items']->pluck('product_id')->map(fn ($id) => (string) $id)->values()->all(),
            'content_type' => 'product',
            'contents' => $summary['items']->map(fn ($item) => [
                'id' => (string) $item['product_id'],
                'quantity' => (int) $item['quantity'],
                'item_price' => (float) $item['unit_price'],
            ])->values()->all(),
            'value' => (float) $summary['total'],
            'currency' => 'INR',
            'num_items' => (int) $summary['count'],
        ]);

        $user = Auth::user();
        $address = $user
            ? $user->addresses()->where('is_default', true)->first()
                ?? $user->addresses()->latest('id')->first()
            : null;

        $nameParts = $user ? (preg_split('/\s+/', trim((string) $user->name), 2) ?: []) : [];

        return view('frontend.pages.checkout', [
            'cartItems' => $summary['items'],
            'cartCount' => $summary['count'],
            'cartSubtotal' => $summary['subtotal'],
            'cartSubtotalFormatted' => $summary['subtotal_formatted'],
            'discount' => $summary['discount'],
            'shipping' => $summary['shipping'],
            'cartTotal' => $summary['total'],
            'cartTotalFormatted' => $summary['total_formatted'],
            'checkoutUser' => $user ? [
                'first_name' => $nameParts[0] ?? $user->name,
                'last_name' => $nameParts[1] ?? '',
                'email' => $user->email,
                'phone' => $user->phone ?? ($address->phone ?? ''),
            ] : null,
            'checkoutAddress' => $address ? [
                'address_line1' => $address->address_line1,
                'address_line2' => $address->address_line2,
                'city' => $address->city,
                'state' => $address->state,
                'pincode' => $address->pincode,
                'country' => $address->country ?: 'India',
            ] : null,
            'razorpayKey' => config('razorpay.key_id'),
            'isLoggedIn' => (bool) $user,
        ]);
    }

    public function place(Request $request): JsonResponse
    {
        $wasGuest = ! Auth::check();
        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'company' => ['nullable', 'string', 'max:150'],
            'country' => ['nullable', 'string', 'max:100'],
            'address_line1' => ['required', 'string', 'max:255'],
            'address_line2' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:120'],
            'state' => ['required', 'string', 'max:120'],
            'pincode' => ['required', 'string', 'max:20'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        // Store ships only within India — ignore any client-submitted country.
        $data['country'] = 'India';

        try {
            $result = $this->checkout->createPendingOrder($data);
            $order = $result['order'];
            $rzp = $result['razorpay'];

            $meta = app(MetaConversionsService::class);
            if ($wasGuest) {
                $meta->track(
                    'CompleteRegistration',
                    $request,
                    ['content_name' => 'Checkout account', 'status' => true],
                    $meta->customerFromOrder($order)
                );
            }
            $meta->track(
                'AddPaymentInfo',
                $request,
                $meta->orderData($order),
                $meta->customerFromOrder($order)
            );

            if ($result['guest_password']) {
                $request->session()->put('checkout_guest_password_'.$order->id, $result['guest_password']);
            }

            return response()->json([
                'success' => true,
                'csrf' => csrf_token(),
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'razorpay' => [
                    'key' => config('razorpay.key_id'),
                    'amount' => $rzp['amount'],
                    'currency' => $rzp['currency'] ?? config('razorpay.currency', 'INR'),
                    'order_id' => $rzp['id'],
                    'name' => config('app.name', 'Vastutathastu'),
                    'description' => 'Order '.$order->order_number,
                    'prefill' => [
                        'name' => $order->shipping_name,
                        'email' => $order->shipping_email,
                        'contact' => $order->shipping_phone,
                    ],
                ],
            ]);
        } catch (RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Could not start checkout. Please try again.',
            ], 500);
        }
    }

    public function verify(Request $request): JsonResponse
    {
        $data = $request->validate([
            'order_id' => ['required', 'integer', 'exists:orders,id'],
            'razorpay_payment_id' => ['required', 'string'],
            'razorpay_order_id' => ['required', 'string'],
            'razorpay_signature' => ['required', 'string'],
        ]);

        $order = Order::query()->with('items')->findOrFail($data['order_id']);
        $wasPaid = $order->payment_status === 'paid';

        if (Auth::check() && (int) $order->user_id !== (int) Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized order.'], 403);
        }

        try {
            $guestPassword = $request->session()->pull('checkout_guest_password_'.$order->id);
            $order = $this->checkout->verifyAndComplete($order, $data, $guestPassword);

            if (! $wasPaid) {
                $meta = app(MetaConversionsService::class);
                $meta->track(
                    'Purchase',
                    $request,
                    $meta->orderData($order),
                    $meta->customerFromOrder($order),
                    'purchase_'.$order->order_number
                );
            }

            // Lets the order page know this visit comes straight from checkout (it then offers to
            // take the customer back to the home page after a short countdown).
            session()->flash('vt_order_placed', $order->order_number);

            return response()->json([
                'success' => true,
                'message' => 'Payment successful. Order placed.',
                'redirect' => route('order', ['order' => $order->order_number]),
                'order_number' => $order->order_number,
            ]);
        } catch (RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Payment verification failed.',
            ], 500);
        }
    }
}
