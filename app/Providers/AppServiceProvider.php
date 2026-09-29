<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\Coupon;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::defaultView('vendor.pagination.admin');
        Paginator::defaultSimpleView('vendor.pagination.admin');

        View::composer(['frontend.pages.*', 'frontend.partials.*'], function ($view) {
            if (! isset($view->menuCategories)) {
                $view->with(
                    'menuCategories',
                    Category::query()
                        ->active()
                        ->orderBy('sort_order')
                        ->orderBy('title')
                        ->get(['id', 'title', 'slug'])
                );
            }

            if (! isset($view->headerOffers)) {
                $view->with(
                    'headerOffers',
                    Coupon::query()
                        ->currentlyActive()
                        ->publiclyVisible()
                        ->latest('id')
                        ->take(8)
                        ->get([
                            'id',
                            'code',
                            'description',
                            'offer_type',
                            'discount_type',
                            'discount_percent',
                            'discount_amount',
                            'free_shipping',
                            'bogo_buy_quantity',
                            'bogo_get_quantity',
                        ])
                );
            }
        });

        View::composer(['frontend.partials.site-header', 'frontend.partials.mobile-menu'], function ($view) {
            $view->with('megaMenu', \App\Support\MegaMenu::data());
        });

        // Subdirectory hosting only (e.g. https://example.com/shop).
        // Skip for plain local URLs so session/CSRF cookies stay on 127.0.0.1:8000.
        $root = rtrim((string) config('app.url'), '/');
        $path = parse_url($root, PHP_URL_PATH);
        if ($root && is_string($path) && $path !== '' && $path !== '/') {
            \Illuminate\Support\Facades\URL::forceRootUrl($root);
        }

        if ($root && str_starts_with($root, 'https://')) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }
    }
}
