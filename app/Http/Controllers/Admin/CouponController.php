<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CouponController extends Controller
{
    public function index(Request $request)
    {
        $query = Coupon::query()->latest();

        if ($search = trim((string) $request->get('search'))) {
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('offer_type', 'like', "%{$search}%")
                    ->orWhere('discount_percent', 'like', "%{$search}%")
                    ->orWhere('discount_amount', 'like', "%{$search}%");
            });
        }

        $coupons = $query->paginate(9)->withQueryString();

        return view('admin.coupons.index', compact('coupons'));
    }

    public function create()
    {
        return view('admin.coupons.create', $this->formData());
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['image'] = $request->file('image')->store('coupons', 'public');

        Coupon::create($data);

        return redirect()->route('admin.coupons.index')->with('success', 'Coupon created successfully.');
    }

    public function show(Coupon $coupon)
    {
        return view('admin.coupons.show', compact('coupon'));
    }

    public function edit(Coupon $coupon)
    {
        return view('admin.coupons.edit', array_merge($this->formData(), compact('coupon')));
    }

    public function update(Request $request, Coupon $coupon)
    {
        $data = $this->validated($request, $coupon);

        if ($request->hasFile('image')) {
            if ($coupon->image) {
                Storage::disk('public')->delete($coupon->image);
            }
            $data['image'] = $request->file('image')->store('coupons', 'public');
        }

        $coupon->update($data);

        return redirect()->route('admin.coupons.index')->with('success', 'Coupon updated successfully.');
    }

    public function destroy(Coupon $coupon)
    {
        if ($coupon->image) {
            Storage::disk('public')->delete($coupon->image);
        }

        $coupon->delete();

        return redirect()->route('admin.coupons.index')->with('success', 'Coupon deleted successfully.');
    }

    public function toggleStatus(Coupon $coupon)
    {
        $coupon->update(['is_active' => ! $coupon->is_active]);

        return back()->with('success', 'Coupon status updated.');
    }

    private function formData(): array
    {
        return [
            'offerTypes' => Coupon::OFFER_TYPES,
            'categories' => Category::query()->orderBy('title')->get(['id', 'title']),
            'products' => Product::query()->orderBy('title')->get(['id', 'title']),
        ];
    }

    private function validated(Request $request, ?Coupon $coupon = null): array
    {
        $imageRule = $coupon ? ['nullable'] : ['required'];

        $data = $request->validate([
            'description' => ['nullable', 'string'],
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('coupons', 'code')->ignore($coupon?->id),
            ],
            'offer_type' => ['required', 'string', Rule::in(array_keys(Coupon::OFFER_TYPES))],
            'discount_percent' => ['nullable', 'numeric', 'min:0.01', 'max:100'],
            'discount_amount' => ['nullable', 'numeric', 'min:0.01'],
            'max_discount_status' => ['required', 'boolean'],
            'max_discount_amount' => ['nullable', 'numeric', 'min:0.01'],
            'min_cart_status' => ['required', 'boolean'],
            'min_cart_amount' => ['nullable', 'numeric', 'min:0.01'],
            'applies_to' => ['required', Rule::in(['all', 'categories', 'products'])],
            'category_ids' => ['nullable', 'array'],
            'category_ids.*' => ['integer', 'exists:categories,id'],
            'product_ids' => ['nullable', 'array'],
            'product_ids.*' => ['integer', 'exists:products,id'],
            'min_quantity' => ['nullable', 'integer', 'min:1'],
            'bogo_buy_quantity' => ['nullable', 'integer', 'min:1'],
            'bogo_get_quantity' => ['nullable', 'integer', 'min:1'],
            'new_customers_only' => ['required', 'boolean'],
            'members_only' => ['required', 'boolean'],
            'free_shipping' => ['required', 'boolean'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'usage_limit' => ['nullable', 'integer', 'min:1'],
            'max_use_per_user' => ['required', 'boolean'],
            'is_public' => ['required', 'boolean'],
            'image' => array_merge($imageRule, ['image', 'mimes:png,jpg,jpeg', 'max:2048']),
        ], [
            'code.unique' => 'This coupon code already exists.',
            'image.required' => 'Please select a coupon image.',
        ]);

        $percent = $request->filled('discount_percent') ? (float) $request->discount_percent : null;
        $amount = $request->filled('discount_amount') ? (float) $request->discount_amount : null;
        $freeShipping = $request->boolean('free_shipping');
        $isBogo = $data['offer_type'] === 'bogo';

        if ($percent === null && $amount === null && ! $freeShipping && ! $isBogo) {
            throw ValidationException::withMessages([
                'discount_amount' => 'Enter discount in % OR amount, or enable free shipping.',
            ]);
        }

        if (! $isBogo && $percent !== null && $amount !== null) {
            throw ValidationException::withMessages([
                'discount_amount' => 'Use either discount in % OR amount, not both.',
            ]);
        }

        $maxDiscountStatus = $request->boolean('max_discount_status');
        $minCartStatus = $request->boolean('min_cart_status');
        $appliesTo = $data['applies_to'];

        if ($isBogo && (! $request->filled('bogo_buy_quantity') || ! $request->filled('bogo_get_quantity'))) {
            throw ValidationException::withMessages([
                'bogo_buy_quantity' => 'Enter both Buy quantity and Get free quantity for a BOGO offer.',
            ]);
        }

        if (! $isBogo && $maxDiscountStatus && $percent !== null && ! $request->filled('max_discount_amount')) {
            throw ValidationException::withMessages([
                'max_discount_amount' => 'Maximum discount amount is required when status is True.',
            ]);
        }

        if ($minCartStatus && ! $request->filled('min_cart_amount')) {
            throw ValidationException::withMessages([
                'min_cart_amount' => 'Minimum cart amount is required when status is True.',
            ]);
        }

        if ($appliesTo === 'categories' && empty($data['category_ids'] ?? [])) {
            throw ValidationException::withMessages([
                'category_ids' => 'Select at least one category.',
            ]);
        }

        if ($appliesTo === 'products' && empty($data['product_ids'] ?? [])) {
            throw ValidationException::withMessages([
                'product_ids' => 'Select at least one product.',
            ]);
        }

        $discountType = $percent !== null ? 'percent' : 'amount';

        return [
            'code' => strtoupper(trim($data['code'])),
            'offer_type' => $data['offer_type'],
            'description' => $data['description'] ?? null,
            'discount_type' => $discountType,
            'discount_percent' => $isBogo ? null : $percent,
            'discount_amount' => $isBogo ? null : $amount,
            'max_discount_status' => ! $isBogo && $maxDiscountStatus,
            'max_discount_amount' => ! $isBogo && $maxDiscountStatus && $percent !== null
                ? (float) $request->max_discount_amount
                : null,
            'min_cart_status' => $minCartStatus,
            'min_cart_amount' => $minCartStatus ? (float) $request->min_cart_amount : null,
            'applies_to' => $appliesTo,
            'category_ids' => $appliesTo === 'categories' ? array_values(array_map('intval', $data['category_ids'] ?? [])) : null,
            'product_ids' => $appliesTo === 'products' ? array_values(array_map('intval', $data['product_ids'] ?? [])) : null,
            'min_quantity' => $request->filled('min_quantity') ? (int) $request->min_quantity : null,
            'bogo_buy_quantity' => $isBogo ? (int) $request->bogo_buy_quantity : null,
            'bogo_get_quantity' => $isBogo ? (int) $request->bogo_get_quantity : null,
            'new_customers_only' => $request->boolean('new_customers_only'),
            'members_only' => $request->boolean('members_only'),
            'free_shipping' => $freeShipping,
            'starts_at' => $data['starts_at'] ?? null,
            'ends_at' => $data['ends_at'] ?? null,
            'usage_limit' => $request->filled('usage_limit') ? (int) $request->usage_limit : null,
            'max_use_per_user' => $request->boolean('max_use_per_user'),
            'is_public' => $request->boolean('is_public'),
        ];
    }
}
