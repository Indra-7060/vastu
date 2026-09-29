<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ColorController;
use App\Http\Controllers\Admin\ContactController;
use App\Http\Controllers\Admin\CouponController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\OfferController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\PageSettingController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\ShippingSettingController;
use App\Http\Controllers\Admin\SizeController;
use App\Http\Controllers\Admin\SubCategoryController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\CustomerAuthController;
use App\Http\Controllers\FrontendController;
use App\Http\Controllers\MetaCatalogFeedController;
use Illuminate\Support\Facades\Route;

Route::get('/', [FrontendController::class, 'home'])->name('home');
Route::get('/csrf-token', function () {
    return response()
        ->json(['token' => csrf_token()])
        ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
})->name('csrf.refresh');
Route::get('/shop', [FrontendController::class, 'collection'])->name('shop');
Route::get('/collection', [FrontendController::class, 'collection'])->name('collection');
Route::get('/catalog/meta/products.csv', MetaCatalogFeedController::class)
    ->name('catalog.meta.products');

// Legacy relative account links from product pages (must be before /product/{slug})
Route::get('/product/account-overview.html', fn () => redirect()->route('account', 'overview'));
Route::get('/product/account-orders.html', fn () => redirect()->route('account', 'orders'));
Route::get('/product/account-information.html', fn () => redirect()->route('account', 'information'));
Route::get('/product/account-addresses.html', fn () => redirect()->route('account', 'addresses'));
Route::get('/product/account-favorites.html', fn () => redirect()->route('account', 'wishlist'));
Route::get('/product/account-wishlists.html', fn () => redirect()->route('account', 'wishlist'));

Route::get('/product/{slug}', [FrontendController::class, 'shopSingle'])->name('shop.single')->where('slug', '^(?!account-).+');
Route::post('/product/{slug}/review', [FrontendController::class, 'storeProductReview'])->name('shop.review.store');
Route::get('/product', function () {
    $first = \App\Models\Product::query()->active()->orderBy('sort_order')->orderByDesc('id')->value('slug');

    return $first
        ? redirect()->route('shop.single', $first)
        : redirect()->route('shop');
});
Route::get('/cart', [FrontendController::class, 'cart'])->name('cart');
Route::get('/checkout', [\App\Http\Controllers\CheckoutController::class, 'show'])->name('checkout');
Route::post('/checkout/place', [\App\Http\Controllers\CheckoutController::class, 'place'])->name('checkout.place');
Route::post('/checkout/verify', [\App\Http\Controllers\CheckoutController::class, 'verify'])->name('checkout.verify');
Route::get('/order/{order?}', [FrontendController::class, 'order'])->name('order');


Route::prefix('cart-api')->name('cart.api.')->group(function () {
    Route::get('/', [\App\Http\Controllers\CartController::class, 'index'])->name('index');
    Route::post('/', [\App\Http\Controllers\CartController::class, 'store'])->name('store');
    Route::put('/{productId}', [\App\Http\Controllers\CartController::class, 'update'])->name('update')->whereNumber('productId');
    Route::delete('/{productId}', [\App\Http\Controllers\CartController::class, 'destroy'])->name('destroy')->whereNumber('productId');
    Route::post('/buy-now', [\App\Http\Controllers\CartController::class, 'buyNow'])->name('buy-now');
    Route::post('/coupon', [\App\Http\Controllers\CouponController::class, 'apply'])->name('coupon.apply');
    Route::delete('/coupon', [\App\Http\Controllers\CouponController::class, 'remove'])->name('coupon.remove');
});
Route::get('/search', [FrontendController::class, 'search'])->name('search');
Route::post('/newsletter/subscribe', [\App\Http\Controllers\NewsletterController::class, 'store'])->name('newsletter.subscribe');
Route::post('/newsletter/check', [\App\Http\Controllers\NewsletterController::class, 'check'])->name('newsletter.check');
Route::get('/blog', [FrontendController::class, 'blog'])->name('blog');
Route::get('/blog/{slug}', [FrontendController::class, 'blogSingle'])->name('blog.single');
Route::get('/about', [FrontendController::class, 'about'])->name('about');
Route::get('/founder', [FrontendController::class, 'founder'])->name('founder');
Route::get('/stores', [FrontendController::class, 'stores'])->name('stores');
Route::get('/gallery', [FrontendController::class, 'gallery'])->name('gallery');
Route::post('/consultation', [\App\Http\Controllers\ConsultationController::class, 'store'])->middleware('throttle:6,1')->name('consultation.store');
Route::get('/info/stores', fn () => redirect()->route('stores', [], 301));
Route::get('/info/{slug}', [FrontendController::class, 'info'])->name('info');

// Legacy static customer-care URLs now use the shared Laravel support UI.
Route::redirect('/faqs.html', '/support/faqs', 301);
Route::redirect('/privacy-cookies.html', '/support/privacy', 301);
Route::redirect('/returns-exchanges.html', '/support/returns', 301);
Route::redirect('/shipping.html', '/support/shipping', 301);
Route::redirect('/size-guide.html', '/support/size-guide', 301);
Route::redirect('/start-return.html', '/support/start-return', 301);
Route::redirect('/terms-conditions.html', '/support/terms', 301);
Route::get('/support/{page?}', [FrontendController::class, 'support'])->name('support');

Route::middleware('guest')->group(function () {
    Route::get('/login', [CustomerAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [CustomerAuthController::class, 'login'])->name('login.submit');
    Route::get('/signup', [CustomerAuthController::class, 'showRegister'])->name('signup');
    Route::post('/signup', [CustomerAuthController::class, 'register'])->name('signup.submit');

    Route::get('/forgot-password', [\App\Http\Controllers\CustomerPasswordController::class, 'showForgotForm'])->name('customer.password.request');
    Route::post('/forgot-password', [\App\Http\Controllers\CustomerPasswordController::class, 'sendResetLink'])->name('customer.password.email');
    Route::get('/reset-password/{token}', [\App\Http\Controllers\CustomerPasswordController::class, 'showResetForm'])->name('customer.password.reset');
    Route::post('/reset-password', [\App\Http\Controllers\CustomerPasswordController::class, 'reset'])->name('customer.password.update');
});

Route::post('/check-email', [CustomerAuthController::class, 'checkEmail'])->name('check.email');
Route::post('/logout', [CustomerAuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware(['auth', 'customer'])->group(function () {
    Route::get('/account/{page?}', [FrontendController::class, 'account'])->name('account');

    Route::prefix('account-api')->name('account.api.')->group(function () {
        Route::put('/profile', [\App\Http\Controllers\AccountController::class, 'updateProfile'])->name('profile');
        Route::post('/addresses', [\App\Http\Controllers\AccountController::class, 'storeAddress'])->name('addresses.store');
        Route::put('/addresses/{address}', [\App\Http\Controllers\AccountController::class, 'updateAddress'])->name('addresses.update');
        Route::delete('/addresses/{address}', [\App\Http\Controllers\AccountController::class, 'destroyAddress'])->name('addresses.destroy');
        Route::post('/addresses/{address}/default', [\App\Http\Controllers\AccountController::class, 'setDefaultAddress'])->name('addresses.default');
        Route::post('/wishlist', [\App\Http\Controllers\AccountController::class, 'storeWishlist'])->name('wishlist.store');
        Route::delete('/wishlist/{wishlist}', [\App\Http\Controllers\AccountController::class, 'destroyWishlistItem'])->name('wishlist.destroy');
        Route::delete('/reviews/{review}', [\App\Http\Controllers\AccountController::class, 'destroyReview'])->name('reviews.destroy');
        Route::post('/notifications/read', [\App\Http\Controllers\AccountController::class, 'readNotifications'])->name('notifications.read');
        Route::delete('/notifications/{notification}', [\App\Http\Controllers\AccountController::class, 'destroyNotification'])->name('notifications.destroy');
    });
});

Route::get('/account-overview.html', fn () => redirect()->route('account', 'overview'));
Route::get('/account-orders.html', fn () => redirect()->route('account', 'orders'));
Route::get('/account-information.html', fn () => redirect()->route('account', 'information'));
Route::get('/account-addresses.html', fn () => redirect()->route('account', 'addresses'));
Route::get('/account-favorites.html', fn () => redirect()->route('account', 'wishlist'));
Route::get('/account-wishlists.html', fn () => redirect()->route('account', 'wishlist'));
Route::get('/account/favorites', fn () => redirect()->route('account', 'wishlist'));
Route::get('/account/wishlists', fn () => redirect()->route('account', 'wishlist'));

Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest:admin')->group(function () {
        Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
        Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
        Route::get('/forgot-password', [AuthController::class, 'showForgotForm'])->name('password.request');
        Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->name('password.email');
        Route::get('/reset-password/{token}', [AuthController::class, 'showResetForm'])->name('password.reset');
        Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
    });

    Route::middleware(['auth:admin', 'admin'])->group(function () {
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

        Route::resource('categories', CategoryController::class)->except(['show']);
        Route::patch('categories/{category}/toggle', [CategoryController::class, 'toggleStatus'])->name('categories.toggle');

        Route::get('sub-categories', [SubCategoryController::class, 'index'])->name('sub-categories.index');
        Route::post('sub-categories', [SubCategoryController::class, 'store'])->name('sub-categories.store');
        Route::put('sub-categories/{subCategory}', [SubCategoryController::class, 'update'])->name('sub-categories.update');
        Route::delete('sub-categories/{subCategory}', [SubCategoryController::class, 'destroy'])->name('sub-categories.destroy');
        Route::patch('sub-categories/{subCategory}/toggle', [SubCategoryController::class, 'toggleStatus'])->name('sub-categories.toggle');

        Route::get('brands', [BrandController::class, 'index'])->name('brands.index');
        Route::post('brands', [BrandController::class, 'store'])->name('brands.store');
        Route::put('brands/{brand}', [BrandController::class, 'update'])->name('brands.update');
        Route::delete('brands/{brand}', [BrandController::class, 'destroy'])->name('brands.destroy');
        Route::patch('brands/{brand}/toggle', [BrandController::class, 'toggleStatus'])->name('brands.toggle');

        Route::get('colors', [ColorController::class, 'index'])->name('colors.index');
        Route::post('colors', [ColorController::class, 'store'])->name('colors.store');
        Route::put('colors/{color}', [ColorController::class, 'update'])->name('colors.update');
        Route::delete('colors/{color}', [ColorController::class, 'destroy'])->name('colors.destroy');
        Route::patch('colors/{color}/toggle', [ColorController::class, 'toggleStatus'])->name('colors.toggle');

        Route::get('sizes', [SizeController::class, 'index'])->name('sizes.index');
        Route::post('sizes', [SizeController::class, 'store'])->name('sizes.store');
        Route::put('sizes/{size}', [SizeController::class, 'update'])->name('sizes.update');
        Route::delete('sizes/{size}', [SizeController::class, 'destroy'])->name('sizes.destroy');
        Route::patch('sizes/{size}/toggle', [SizeController::class, 'toggleStatus'])->name('sizes.toggle');

        Route::resource('offers', OfferController::class)->except(['show']);
        Route::patch('offers/{offer}/toggle', [OfferController::class, 'toggleStatus'])->name('offers.toggle');

        Route::get('products/check-title', [ProductController::class, 'checkTitle'])->name('products.check-title');
        Route::get('products/sub-categories', [ProductController::class, 'subCategories'])->name('products.sub-categories');
        Route::post('products/bulk', [ProductController::class, 'bulkAction'])->name('products.bulk');
        Route::resource('products', ProductController::class);
        Route::patch('products/{product}/toggle', [ProductController::class, 'toggleStatus'])->name('products.toggle');
        Route::patch('products/{product}/featured', [ProductController::class, 'toggleFeatured'])->name('products.featured');
        Route::get('products/{product}/reviews', [ProductController::class, 'reviews'])->name('products.reviews');
        Route::post('products/{product}/reviews', [ProductController::class, 'storeReview'])->name('products.reviews.store');
        Route::patch('products/{product}/reviews/{review}/toggle', [ProductController::class, 'toggleReview'])->name('products.reviews.toggle');
        Route::delete('products/{product}/reviews/{review}', [ProductController::class, 'destroyReview'])->name('products.reviews.destroy');

        // POST save (not PUT) — Hostinger/ModSecurity often blocks PUT and returns 500
        Route::resource('banners', BannerController::class)->except(['update']);
        Route::post('banners/{banner}', [BannerController::class, 'update'])->name('banners.update');
        Route::patch('banners/{banner}/toggle', [BannerController::class, 'toggleStatus'])->name('banners.toggle');

        Route::get('news-types', [\App\Http\Controllers\Admin\NewsTypeController::class, 'index'])->name('news-types.index');
        Route::post('news-types', [\App\Http\Controllers\Admin\NewsTypeController::class, 'store'])->name('news-types.store');
        Route::put('news-types/{newsType}', [\App\Http\Controllers\Admin\NewsTypeController::class, 'update'])->name('news-types.update');
        Route::delete('news-types/{newsType}', [\App\Http\Controllers\Admin\NewsTypeController::class, 'destroy'])->name('news-types.destroy');
        Route::patch('news-types/{newsType}/toggle', [\App\Http\Controllers\Admin\NewsTypeController::class, 'toggleStatus'])->name('news-types.toggle');

        Route::get('consultations', [\App\Http\Controllers\Admin\ConsultationController::class, 'index'])->name('consultations.index');
        Route::get('consultations/{consultation}', [\App\Http\Controllers\Admin\ConsultationController::class, 'show'])->name('consultations.show');
        Route::put('consultations/{consultation}', [\App\Http\Controllers\Admin\ConsultationController::class, 'update'])->name('consultations.update');
        Route::patch('consultations/{consultation}/status', [\App\Http\Controllers\Admin\ConsultationController::class, 'updateStatus'])->name('consultations.status');
        Route::delete('consultations/{consultation}', [\App\Http\Controllers\Admin\ConsultationController::class, 'destroy'])->name('consultations.destroy');
        Route::resource('stores', \App\Http\Controllers\Admin\StoreController::class)->except(['show']);
        Route::patch('stores/{store}/toggle', [\App\Http\Controllers\Admin\StoreController::class, 'toggleStatus'])->name('stores.toggle');

        Route::resource('blog-posts', \App\Http\Controllers\Admin\BlogPostController::class)->except(['show']);
        Route::patch('blog-posts/{blogPost}/toggle', [\App\Http\Controllers\Admin\BlogPostController::class, 'toggleStatus'])->name('blog-posts.toggle');
        Route::patch('blog-posts/{blogPost}/featured', [\App\Http\Controllers\Admin\BlogPostController::class, 'toggleFeatured'])->name('blog-posts.featured');

        Route::resource('coupons', CouponController::class);
        Route::patch('coupons/{coupon}/toggle', [CouponController::class, 'toggleStatus'])->name('coupons.toggle');

        Route::get('contacts', [ContactController::class, 'index'])->name('contacts.index');
        Route::post('contacts/subscribers/bulk', [ContactController::class, 'bulkSubscribers'])->name('contacts.subscribers.bulk');
        Route::delete('contacts/subscribers/{subscriber}', [ContactController::class, 'destroySubscriber'])->name('contacts.subscribers.destroy');
        Route::post('contacts/messages/bulk', [ContactController::class, 'bulkMessages'])->name('contacts.messages.bulk');
        Route::delete('contacts/messages/{message}', [ContactController::class, 'destroyMessage'])->name('contacts.messages.destroy');

        Route::get('settings/pages', [PageSettingController::class, 'index'])->name('settings.pages');
        Route::put('settings/pages/{page}', [PageSettingController::class, 'update'])->name('settings.pages.update');
        Route::get('settings/site', [\App\Http\Controllers\Admin\SiteSettingController::class, 'edit'])->name('settings.site.edit');
        Route::put('settings/site', [\App\Http\Controllers\Admin\SiteSettingController::class, 'update'])->name('settings.site.update');
        Route::get('settings/shipping', [ShippingSettingController::class, 'edit'])->name('settings.shipping.edit');
        Route::put('settings/shipping', [ShippingSettingController::class, 'update'])->name('settings.shipping.update');

        Route::post('users/bulk', [UserController::class, 'bulkAction'])->name('users.bulk');
        Route::resource('users', UserController::class);
        Route::patch('users/{user}/toggle', [UserController::class, 'toggleStatus'])->name('users.toggle');

        Route::post('orders/bulk', [OrderController::class, 'bulkAction'])->name('orders.bulk');
        Route::get('orders/{order}/print', [OrderController::class, 'print'])->name('orders.print');
        Route::get('orders/{order}/status-data', [OrderController::class, 'statusPayload'])->name('orders.status-data');
        Route::patch('orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.status');
        Route::patch('orders/{order}/delivery-date', [OrderController::class, 'updateDeliveryDate'])->name('orders.delivery-date');
        Route::delete('orders/{order}/status-logs/{statusLog}', [OrderController::class, 'destroyStatusLog'])->name('orders.status-logs.destroy');
        Route::resource('orders', OrderController::class)->only(['index', 'show', 'destroy']);
    });
});

// Clean category URLs: /accessories, /shirts, etc. (keep after all fixed routes)
Route::get('/{categorySlug}', [FrontendController::class, 'collection'])
    ->where('categorySlug', '^(?!shop|collection|founder|stores|gallery|consultation|info|product|cart|checkout|order|search|blog|about|support|login|signup|admin|account|newsletter|forgot-password|reset-password|check-email|logout|cart-api|account-api|storage|frontend|css|js|images|vendor|build).*$')
    ->name('category');
