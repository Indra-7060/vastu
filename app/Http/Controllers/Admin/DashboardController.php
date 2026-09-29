<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Offer;
use App\Models\Order;
use App\Models\Product;
use App\Models\SubCategory;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $paidOrders = Order::where('payment_status', 'paid');

        $stats = [
            'revenue' => (clone $paidOrders)->sum('payable_amount'),
            'paid_orders' => (clone $paidOrders)->count(),
            'orders' => Order::count(),
            'open_orders' => Order::whereNotIn('status', ['delivered', 'cancelled'])->count(),
            'customers' => User::where('role', 'customer')->count(),
            'new_customers' => User::where('role', 'customer')->where('created_at', '>=', now()->subDays(30))->count(),
            'products' => Product::count(),
            'active_products' => Product::where('is_active', true)->count(),
        ];

        $catalog = [
            ['label' => 'Categories', 'count' => Category::count(), 'route' => 'admin.categories.index', 'icon' => 'layers'],
            ['label' => 'Sub-categories', 'count' => SubCategory::count(), 'route' => 'admin.sub-categories.index', 'icon' => 'layers'],
            ['label' => 'Products', 'count' => $stats['products'], 'route' => 'admin.products.index', 'icon' => 'catalog'],
            ['label' => 'Offers', 'count' => Offer::count(), 'route' => 'admin.offers.index', 'icon' => 'tag'],
            ['label' => 'Coupons', 'count' => Coupon::count(), 'route' => 'admin.coupons.index', 'icon' => 'coupon'],
            ['label' => 'Journal posts', 'count' => BlogPost::count(), 'route' => 'admin.blog-posts.index', 'icon' => 'journal'],
        ];

        $recentOrders = Order::query()->withCount('items')->latest()->orderByDesc('id')->take(6)->get();

        return view('admin.dashboard', compact('stats', 'catalog', 'recentOrders'));
    }
}
