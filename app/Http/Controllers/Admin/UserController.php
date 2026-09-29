<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\ProductReview;
use App\Models\User;
use App\Models\Wishlist;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $this->perPage($request);
        [$sort, $dir] = $this->sortParams($request, [
            'platform', 'name', 'email', 'created_at', 'is_active',
        ], 'created_at', 'desc');

        $query = User::query()->where('role', 'customer');

        if ($search = trim((string) $request->get('search'))) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('platform', 'like', "%{$search}%");
            });
        }

        $query->orderBy($sort, $dir)->orderBy('id', 'desc');

        $users = $query->paginate($perPage)->withQueryString();

        return view('admin.users.index', compact('users', 'perPage', 'sort', 'dir'));
    }

    public function create()
    {
        return view('admin.users.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'string', 'min:6'],
            'platform' => ['nullable', 'string', 'max:50'],
            'avatar' => ['nullable', 'image', 'mimes:png,jpg,jpeg', 'max:2048'],
        ], [
            'email.unique' => 'This email already exists.',
        ]);

        $data['role'] = 'customer';
        $data['login_provider'] = 'email';
        $data['is_active'] = true;
        $data['platform'] = $data['platform'] ?: 'Web';

        if ($request->hasFile('avatar')) {
            $data['avatar'] = $request->file('avatar')->store('users', 'public');
        }

        User::create($data);

        return redirect()->route('admin.users.index')->with('success', 'User created successfully.');
    }

    public function show(Request $request, User $user)
    {
        $this->ensureCustomer($user);

        $tab = $request->get('tab', 'wishlist');
        if (! in_array($tab, ['wishlist', 'cart', 'orders', 'profile', 'reviews'], true)) {
            $tab = 'wishlist';
        }

        $perPage = $this->perPage($request);
        $tabSearch = trim((string) $request->get('q'));

        $wishlist = null;
        $cartItems = null;
        $orders = null;
        $reviews = null;
        $addresses = null;
        $bankAccounts = null;
        $sort = null;
        $dir = null;

        if ($tab === 'wishlist') {
            [$sort, $dir] = $this->sortParams($request, [
                'product', 'price', 'created_at',
            ], 'created_at', 'desc');

            $query = Wishlist::query()
                ->where('wishlists.user_id', $user->id)
                ->leftJoin('products', 'products.id', '=', 'wishlists.product_id')
                ->select('wishlists.*')
                ->with('product');

            if ($tabSearch !== '') {
                $query->where(function ($q) use ($tabSearch) {
                    $q->where('products.title', 'like', "%{$tabSearch}%")
                        ->orWhere('products.selling_price', 'like', "%{$tabSearch}%");
                });
            }

            $this->applyWishlistSort($query, $sort, $dir);
            $wishlist = $query->paginate($perPage)->withQueryString();
        } elseif ($tab === 'cart') {
            [$sort, $dir] = $this->sortParams($request, [
                'product', 'quantity', 'price', 'total', 'created_at',
            ], 'created_at', 'desc');

            $query = CartItem::query()
                ->where('cart_items.user_id', $user->id)
                ->leftJoin('products', 'products.id', '=', 'cart_items.product_id')
                ->select('cart_items.*')
                ->with('product');

            if ($tabSearch !== '') {
                $query->where(function ($q) use ($tabSearch) {
                    $q->where('products.title', 'like', "%{$tabSearch}%")
                        ->orWhere('cart_items.quantity', 'like', "%{$tabSearch}%")
                        ->orWhere('products.selling_price', 'like', "%{$tabSearch}%");
                });
            }

            $this->applyCartSort($query, $sort, $dir);
            $cartItems = $query->paginate($perPage)->withQueryString();
        } elseif ($tab === 'orders') {
            [$sort, $dir] = $this->sortParams($request, [
                'order_number', 'user_name', 'user_phone', 'payable_amount', 'ordered_at', 'status',
            ], 'ordered_at', 'desc');

            $query = Order::query()->where('user_id', $user->id);

            if ($tabSearch !== '') {
                $query->where(function ($q) use ($tabSearch) {
                    $q->where('order_number', 'like', "%{$tabSearch}%")
                        ->orWhere('user_name', 'like', "%{$tabSearch}%")
                        ->orWhere('user_phone', 'like', "%{$tabSearch}%")
                        ->orWhere('status', 'like', "%{$tabSearch}%")
                        ->orWhere('payable_amount', 'like', "%{$tabSearch}%");
                });
            }

            $query->orderBy($sort === 'ordered_at' ? 'ordered_at' : $sort, $dir)->orderBy('id', 'desc');
            $orders = $query->paginate($perPage)->withQueryString();
        } elseif ($tab === 'reviews') {
            [$sort, $dir] = $this->sortParams($request, [
                'product', 'rating', 'comment', 'created_at',
            ], 'created_at', 'desc');

            $query = ProductReview::query()
                ->where('product_reviews.user_id', $user->id)
                ->leftJoin('products', 'products.id', '=', 'product_reviews.product_id')
                ->select('product_reviews.*')
                ->with('product');

            if ($tabSearch !== '') {
                $query->where(function ($q) use ($tabSearch) {
                    $q->where('product_reviews.comment', 'like', "%{$tabSearch}%")
                        ->orWhere('product_reviews.reviewer_name', 'like', "%{$tabSearch}%")
                        ->orWhere('product_reviews.rating', 'like', "%{$tabSearch}%")
                        ->orWhere('products.title', 'like', "%{$tabSearch}%");
                });
            }

            $this->applyReviewSort($query, $sort, $dir);
            $reviews = $query->paginate($perPage)->withQueryString();
        } elseif ($tab === 'profile') {
            $addresses = $user->addresses()->latest()->get();
            $bankAccounts = $user->bankAccounts()->latest()->get();
        }

        return view('admin.users.show', compact(
            'user',
            'tab',
            'perPage',
            'wishlist',
            'cartItems',
            'orders',
            'reviews',
            'addresses',
            'bankAccounts',
            'sort',
            'dir'
        ));
    }

    public function edit(User $user)
    {
        $this->ensureCustomer($user);

        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $this->ensureCustomer($user);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['nullable', 'string', 'min:6'],
            'platform' => ['nullable', 'string', 'max:50'],
            'avatar' => ['nullable', 'image', 'mimes:png,jpg,jpeg', 'max:2048'],
        ], [
            'email.unique' => 'This email already exists.',
        ]);

        if (empty($data['password'])) {
            unset($data['password']);
        }

        if ($request->hasFile('avatar')) {
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            $data['avatar'] = $request->file('avatar')->store('users', 'public');
        }

        $user->update($data);

        return redirect()->route('admin.users.index')->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        $this->ensureCustomer($user);

        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
        }

        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'User deleted successfully.');
    }

    public function toggleStatus(User $user)
    {
        $this->ensureCustomer($user);
        $user->update(['is_active' => ! $user->is_active]);

        return back()->with('success', 'User status updated.');
    }

    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => ['required', 'in:enable,disable,delete'],
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:users,id'],
        ]);

        $users = User::where('role', 'customer')->whereIn('id', $request->ids)->get();
        $count = $users->count();

        if ($count === 0) {
            return back()->with('error', 'No valid users selected.');
        }

        switch ($request->action) {
            case 'enable':
                User::whereIn('id', $users->pluck('id'))->update(['is_active' => true]);
                $message = "{$count} user(s) enabled.";
                break;
            case 'disable':
                User::whereIn('id', $users->pluck('id'))->update(['is_active' => false]);
                $message = "{$count} user(s) disabled.";
                break;
            case 'delete':
                foreach ($users as $user) {
                    if ($user->avatar) {
                        Storage::disk('public')->delete($user->avatar);
                    }
                    $user->delete();
                }
                $message = "{$count} user(s) deleted.";
                break;
            default:
                $message = 'Action completed.';
        }

        return back()->with('success', $message);
    }

    private function ensureCustomer(User $user): void
    {
        abort_unless($user->role === 'customer', 404);
    }

    private function perPage(Request $request): int
    {
        $perPage = (int) $request->get('per_page', 10);

        return in_array($perPage, [10, 25, 50, 100], true) ? $perPage : 10;
    }

    /**
     * @param  array<int, string>  $allowed
     * @return array{0:string,1:string}
     */
    private function sortParams(Request $request, array $allowed, string $defaultSort, string $defaultDir = 'asc'): array
    {
        $sort = (string) $request->get('sort', $defaultSort);
        if (! in_array($sort, $allowed, true)) {
            $sort = $defaultSort;
        }

        $dir = strtolower((string) $request->get('dir', $defaultDir)) === 'desc' ? 'desc' : 'asc';

        return [$sort, $dir];
    }

    private function applyWishlistSort(Builder $query, string $sort, string $dir): void
    {
        match ($sort) {
            'product' => $query->orderBy('products.title', $dir),
            'price' => $query->orderBy('products.selling_price', $dir),
            default => $query->orderBy('wishlists.created_at', $dir),
        };
        $query->orderBy('wishlists.id', 'desc');
    }

    private function applyCartSort(Builder $query, string $sort, string $dir): void
    {
        match ($sort) {
            'product' => $query->orderBy('products.title', $dir),
            'quantity' => $query->orderBy('cart_items.quantity', $dir),
            'price' => $query->orderBy('products.selling_price', $dir),
            'total' => $query->orderByRaw('(cart_items.quantity * COALESCE(products.selling_price, 0)) '.$dir),
            default => $query->orderBy('cart_items.created_at', $dir),
        };
        $query->orderBy('cart_items.id', 'desc');
    }

    private function applyReviewSort(Builder $query, string $sort, string $dir): void
    {
        match ($sort) {
            'product' => $query->orderBy('products.title', $dir),
            'rating' => $query->orderBy('product_reviews.rating', $dir),
            'comment' => $query->orderBy('product_reviews.comment', $dir),
            default => $query->orderBy('product_reviews.created_at', $dir),
        };
        $query->orderBy('product_reviews.id', 'desc');
    }
}
