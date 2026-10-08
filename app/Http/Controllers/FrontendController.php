<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use App\Models\BlogPost;
use App\Models\Category;
use App\Models\NewsType;
use App\Models\Page;
use App\Models\Product;
use App\Models\ProductReview;
use App\Services\CartService;
use App\Support\InfoPages;
use App\Support\VastuCatalog;
use App\Services\MetaConversionsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class FrontendController extends Controller
{
    public function home(): View
    {
        // Every homepage section comes from the database and is managed in the admin panel:
        // categories ("Show on Home"), featured products ("Featured") and the latest journal
        // articles. Hero, introduction, spotlight and founder come from Sections & Images.
        $homeCategories = Category::query()
            ->active()
            ->where('show_on_home', true)
            ->orderBy('sort_order')
            ->orderBy('title')
            ->take(8)
            ->get();

        $featuredProducts = Product::query()
            ->active()
            ->with('subCategory')
            ->where('is_featured', true)
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->take(8)
            ->get();

        if ($featuredProducts->isEmpty()) {
            $featuredProducts = Product::query()->active()->with('subCategory')
                ->orderByDesc('is_new_arrival')->orderByDesc('id')->take(8)->get();
        }

        $journalPosts = BlogPost::query()
            ->published()
            ->with('newsType')
            ->orderByDesc('published_at')
            ->orderBy('sort_order')
            ->take(3)
            ->get();

        // Photo strip above the consultation banner: every active photo from Admin → Gallery.
        $galleryStrip = collect(\App\Support\GalleryCategories::all())
            ->flatMap(fn ($meta, $slug) => \App\Support\SiteBanners::all($meta['section'])
                ->map(function ($banner) use ($meta, $slug) {
                    $m = \App\Support\SiteBanners::media($banner);
                    // the photo's own shape, so each card shows the whole photo (no heads cut off)
                    $size = $m && ! str_starts_with((string) $m->image, 'http') ? @getimagesize(public_path('storage/'.ltrim($m->image, '/'))) : null;
                    return [
                        'image' => $m ? \App\Support\SiteBanners::url($m->image) : null,
                        'alt' => $banner->title ?: $meta['label'],
                        'url' => route('gallery', $slug),
                        'w' => $size[0] ?? 1,
                        'h' => $size[1] ?? 1,
                    ];
                }))
            ->filter(fn ($photo) => $photo['image'])
            ->values();

        return view('frontend.pages.home', compact('homeCategories', 'featuredProducts', 'journalPosts', 'galleryStrip'));
    }

    /** Price bands offered in the listing filter: key => [label, min, max]. */
    private const PRICE_BANDS = [
        'under-1000' => ['Under ₹1,000', 0, 999.99],
        '1000-2500' => ['₹1,000 – ₹2,500', 1000, 2500],
        '2500-5000' => ['₹2,500 – ₹5,000', 2500.01, 5000],
        'above-5000' => ['Above ₹5,000', 5000.01, null],
    ];

    private const SORTS = [
        'recommended' => 'Recommended',
        'newest' => 'Newest',
        'price-asc' => 'Price: low to high',
        'price-desc' => 'Price: high to low',
        'name' => 'Name: A to Z',
    ];

    private const PER_PAGE = 24;

    public function collection(Request $request, ?string $categorySlug = null): View|RedirectResponse|JsonResponse
    {
        // Legacy: /collection?category=accessories → /accessories
        if ($request->filled('category') && ! is_array($request->query('category'))) {
            $legacySlug = trim((string) $request->query('category'));
            $query = $request->except('category');

            return redirect()->to('/'.$legacySlug.($query !== [] ? '?'.http_build_query($query) : ''), 301);
        }

        if ($categorySlug === null && $request->is('collection')) {
            $categorySlug = 'collection';
        }
        if ($request->is('shop')) {
            $categorySlug = null;
        }

        $categories = Category::query()->active()->orderBy('sort_order')->orderBy('title')->get();

        $activeCategory = null;
        if ($categorySlug) {
            $activeCategory = $categories->firstWhere('slug', $categorySlug);

            if (! $activeCategory) {
                // Menu categories that the admin hasn't created yet show an empty listing.
                $label = VastuCatalog::label($categorySlug);
                abort_if($label === null, 404);
                $activeCategory = new Category(['title' => $label, 'slug' => $categorySlug]);
            }
        }

        $search = trim((string) $request->query('q', $request->query('search', '')));
        $filters = [
            'category' => array_values(array_filter((array) $request->query('category', []))),
            'type' => array_values(array_filter((array) $request->query('type', []))),
            'material' => array_values(array_filter((array) $request->query('material', []))),
            'price' => array_values(array_intersect((array) $request->query('price', []), array_keys(self::PRICE_BANDS))),
            'badge' => array_values(array_filter((array) $request->query('badge', []))),
        ];
        $sort = array_key_exists($request->query('sort'), self::SORTS) ? $request->query('sort') : 'recommended';

        // Base query: the page's scope (category + search) before the visitor's filters.
        $base = Product::query()->active();
        if ($activeCategory) {
            $base->where('category_id', $activeCategory->id ?? 0);
        }
        if ($request->boolean('featured')) {
            $base->where('is_featured', true);   // "Top picks" link in the Shop menu (Admin → Products → Featured)
        }
        $searchIds = [];
        if ($search !== '') {
            $searchIds = $this->applyProductSearch($base, $search);
        }

        $facets = $this->listingFacets(clone $base, $activeCategory === null);

        $productsQuery = (clone $base)->with(['category', 'subCategory', 'images']);
        $this->applyListingFilters($productsQuery, $filters);
        if ($searchIds && $sort === 'recommended') {
            // Search results: best match first.
            $productsQuery->orderByRaw('FIELD(id, '.implode(',', array_map('intval', $searchIds)).')');
        } else {
            $this->applyListingSort($productsQuery, $sort);
        }

        $products = $productsQuery->paginate(self::PER_PAGE)->withQueryString();

        // "Load more" requests only need the next batch of tiles.
        if ($request->boolean('partial')) {
            return response()->json([
                'html' => view('frontend.partials.vastu-product-tiles', ['products' => $products, 'teasers' => []])->render(),
                'next' => $products->nextPageUrl(),
                'shown' => $products->lastItem() ?? 0,
                'total' => $products->total(),
            ]);
        }

        $activeFilterCount = collect($filters)->flatten()->filter()->count();
        // Product grids show products only (the "Discover more" promo tiles were removed).
        $teasers = [];

        return view('frontend.pages.collection', [
            'categories' => $categories,
            'activeCategory' => $activeCategory,
            'products' => $products,
            'search' => $search,
            'filters' => $filters,
            'facets' => $facets,
            'sort' => $sort,
            'sorts' => self::SORTS,
            'priceBands' => array_map(fn ($band) => $band[0], self::PRICE_BANDS),
            'activeFilterCount' => $activeFilterCount,
            'teasers' => $teasers,
        ]);
    }

    private function applyListingFilters($query, array $filters): void
    {
        if ($filters['category']) {
            $query->whereHas('category', fn ($q) => $q->whereIn('slug', $filters['category']));
        }
        if ($filters['type']) {
            $query->whereHas('subCategory', fn ($q) => $q->whereIn('slug', $filters['type']));
        }
        if ($filters['material']) {
            $query->whereIn('material', $filters['material']);
        }
        if ($filters['badge']) {
            $query->where(function ($q) use ($filters) {
                $q->whereIn('badge', $filters['badge']);
                if (in_array('New', $filters['badge'], true)) {
                    $q->orWhere('is_new_arrival', true);
                }
            });
        }
        if ($filters['price']) {
            $price = 'COALESCE(NULLIF(selling_price, 0), mrp)';
            $query->where(function ($q) use ($filters, $price) {
                foreach ($filters['price'] as $key) {
                    [, $min, $max] = self::PRICE_BANDS[$key];
                    $q->orWhere(function ($band) use ($price, $min, $max) {
                        $band->whereRaw("$price >= ?", [$min]);
                        if ($max !== null) {
                            $band->whereRaw("$price <= ?", [$max]);
                        }
                    });
                }
            });
        }
    }

    private function applyListingSort($query, string $sort): void
    {
        $price = 'COALESCE(NULLIF(selling_price, 0), mrp)';

        match ($sort) {
            'newest' => $query->orderByDesc('is_new_arrival')->orderByDesc('created_at')->orderByDesc('id'),
            'price-asc' => $query->orderByRaw("$price asc"),
            'price-desc' => $query->orderByRaw("$price desc"),
            'name' => $query->orderBy('title'),
            default => $query->orderByDesc('is_new_arrival')->orderBy('sort_order')->orderByDesc('id'),
        };
    }

    /** Filter options (with counts) for the current page scope. */
    private function listingFacets($base, bool $includeCategories): array
    {
        $rows = $base->with(['category:id,title,slug', 'subCategory:id,title,slug'])
            ->get(['id', 'category_id', 'sub_category_id', 'material', 'badge', 'is_new_arrival', 'mrp', 'selling_price']);

        $count = fn ($items) => $items->countBy()->sortKeys()->all();

        $prices = [];
        foreach (self::PRICE_BANDS as $key => [$label, $min, $max]) {
            $n = $rows->filter(function ($p) use ($min, $max) {
                $value = (float) $p->selling_price > 0 ? (float) $p->selling_price : (float) $p->mrp;

                return $value >= $min && ($max === null || $value <= $max);
            })->count();
            if ($n > 0) {
                $prices[$key] = ['label' => $label, 'count' => $n];
            }
        }

        $badges = $rows->map(fn ($p) => $p->is_new_arrival ? 'New' : $p->badge)
            ->merge($rows->pluck('badge'))->filter()->unique()->values();

        return [
            'category' => $includeCategories
                ? $rows->filter->category->groupBy(fn ($p) => $p->category->slug)
                    ->map(fn ($group) => ['label' => $group->first()->category->title, 'count' => $group->count()])->all()
                : [],
            'type' => $rows->filter->subCategory->groupBy(fn ($p) => $p->subCategory->slug)
                ->map(fn ($group) => ['label' => $group->first()->subCategory->title, 'count' => $group->count()])->all(),
            'material' => collect($count($rows->pluck('material')->filter()))
                ->map(fn ($n, $label) => ['label' => $label, 'count' => $n])->all(),
            'price' => $prices,
            'badge' => $badges->mapWithKeys(fn ($badge) => [$badge => [
                'label' => $badge === 'New' ? 'New arrivals' : $badge,
                'count' => $rows->filter(fn ($p) => $p->badge === $badge || ($badge === 'New' && $p->is_new_arrival))->count(),
            ]])->all(),
        ];
    }

    /** Spelling-tolerant match (kapoor = kapur, rudraksha = rudraksh …); returns ids, best match first. */
    protected function applyProductSearch($query, string $search): array
    {
        $ids = \App\Support\ProductSearch::rank($search)->pluck('id')->all();
        $query->whereIn('id', $ids ?: [0]);

        return $ids;
    }

    public function shopSingle(Request $request, string $slug): View
    {
        $product = Product::query()
            ->active()
            ->with([
                'category',
                'colors',
                'sizes',
                'images.color',
                'offer',
                'reviews' => fn ($q) => $q->where('is_active', true)->latest(),
            ])
            ->where('slug', $slug)
            ->firstOrFail();

        $gallery = collect([$product->featured_image_url]);
        if ($product->featured_image_2) {
            $gallery->push($product->hover_image_url);
        }
        foreach ($product->images->whereNull('color_id') as $img) {
            $gallery->push(asset('storage/'.$img->image));
        }
        // Only real photos (no repeated padding): one large image, then pairs — like the reference PDP.
        $gallery = $gallery->filter()->unique()->take(9)->values();
        $colourGalleries = $product->images
            ->filter(fn ($image) => $image->color_id && $image->color)
            ->groupBy(fn ($image) => strtoupper($image->color->name))
            ->map(fn ($images) => $images->map(fn ($image) => asset('storage/'.$image->image))->values())
            ->all();
        $firstColour = strtoupper((string) optional($product->colors->first())->name);
        if ($firstColour !== '' && ! empty($colourGalleries[$firstColour])) {
            $gallery = collect($colourGalleries[$firstColour])->unique()->take(9)->values();
        }

        $activeReviews = $product->reviews;
        $reviewCount = $activeReviews->count();
        $avgRating = $reviewCount ? round($activeReviews->avg('rating'), 1) : 0;
        $ratingBreakdown = [];
        for ($star = 5; $star >= 1; $star--) {
            $count = $activeReviews->where('rating', $star)->count();
            $ratingBreakdown[$star] = [
                'count' => $count,
                'percent' => $reviewCount ? (int) round(($count / $reviewCount) * 100) : 0,
            ];
        }

        $relatedProducts = Product::query()
            ->active()
            ->with(['offer', 'colors', 'sizes', 'category'])
            ->where('id', '!=', $product->id)
            ->when($product->category_id, fn ($q) => $q->where('category_id', $product->category_id))
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->take(8)
            ->get();

        if ($relatedProducts->count() < 4) {
            $extra = Product::query()
                ->active()
                ->with(['offer', 'colors', 'sizes', 'category'])
                ->where('id', '!=', $product->id)
                ->whereNotIn('id', $relatedProducts->pluck('id'))
                ->orderByDesc('id')
                ->take(8 - $relatedProducts->count())
                ->get();
            $relatedProducts = $relatedProducts->concat($extra);
        }

        $recentIds = collect(session('recently_viewed', []))
            ->reject(fn ($id) => (int) $id === (int) $product->id)
            ->take(8)
            ->values()
            ->all();

        $recentlyViewed = empty($recentIds)
            ? collect()
            : Product::query()
                ->active()
                ->with(['offer', 'colors', 'sizes', 'category'])
                ->whereIn('id', $recentIds)
                ->get()
                ->sortBy(fn ($p) => array_search($p->id, $recentIds))
                ->values();

        $viewed = collect(session('recently_viewed', []))
            ->reject(fn ($id) => (int) $id === (int) $product->id)
            ->prepend($product->id)
            ->unique()
            ->take(12)
            ->values()
            ->all();
        session(['recently_viewed' => $viewed]);

        app(MetaConversionsService::class)->track(
            'ViewContent',
            $request,
            app(MetaConversionsService::class)->productData($product)
        );

        return view('frontend.pages.shop-single', compact(
            'product',
            'gallery',
            'colourGalleries',
            'activeReviews',
            'reviewCount',
            'avgRating',
            'ratingBreakdown',
            'relatedProducts',
            'recentlyViewed'
        ));
    }

    public function storeProductReview(Request $request, string $slug): RedirectResponse
    {
        $product = Product::query()->active()->where('slug', $slug)->firstOrFail();

        $data = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['required', 'string', 'min:10', 'max:2000'],
            'reviewer_name' => ['required', 'string', 'max:255'],
            'reviewer_email' => ['required', 'email', 'max:255'],
        ]);

        $userId = Auth::id();
        if (! $userId) {
            $userId = \App\Models\User::query()
                ->where('email', $data['reviewer_email'])
                ->where('role', 'customer')
                ->value('id');
        }

        ProductReview::create([
            'product_id' => $product->id,
            'user_id' => $userId,
            'reviewer_name' => $data['reviewer_name'],
            'reviewer_email' => $data['reviewer_email'],
            'rating' => $data['rating'],
            'comment' => $data['comment'],
            'is_active' => true,
        ]);

        return redirect()
            ->route('shop.single', $product->slug)
            ->with('success', 'Thank you! Your review has been submitted.')
            ->withFragment('write-review');
    }

    public function cart(CartService $cart): View
    {
        $summary = $cart->summary();
        $cartProductIds = $summary['items']->pluck('product_id')->filter()->unique()->values();

        $recommendedProducts = Product::query()
            ->active()
            ->with(['offer', 'colors', 'sizes', 'category'])
            ->when($cartProductIds->isNotEmpty(), fn ($q) => $q->whereNotIn('id', $cartProductIds))
            ->orderByDesc('is_new_arrival')
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->take(4)
            ->get();

        if ($recommendedProducts->count() < 4) {
            $extra = Product::query()
                ->active()
                ->with(['offer', 'colors', 'sizes', 'category'])
                ->whereNotIn('id', $recommendedProducts->pluck('id')->merge($cartProductIds))
                ->orderByDesc('id')
                ->take(4 - $recommendedProducts->count())
                ->get();
            $recommendedProducts = $recommendedProducts->concat($extra);
        }

        return view('frontend.pages.cart', [
            'cartItems' => $summary['items'],
            'cartCount' => $summary['count'],
            'cartSubtotal' => $summary['subtotal'],
            'cartSubtotalFormatted' => $summary['subtotal_formatted'],
            'discount' => $summary['discount'],
            'shipping' => $summary['shipping'],
            'cartTotal' => $summary['total'],
            'cartTotalFormatted' => $summary['total_formatted'],
            'recommendedProducts' => $recommendedProducts,
        ]);
    }

    public function checkout(CartService $cart): View|RedirectResponse
    {
        $summary = $cart->summary();
        if ($summary['count'] < 1) {
            return redirect()->route('cart')->with('error', 'Your cart is empty.');
        }

        return view('frontend.pages.checkout', [
            'cartItems' => $summary['items'],
            'cartCount' => $summary['count'],
            'cartSubtotal' => $summary['subtotal'],
            'cartSubtotalFormatted' => $summary['subtotal_formatted'],
            'discount' => $summary['discount'],
            'shipping' => $summary['shipping'],
            'cartTotal' => $summary['total'],
            'cartTotalFormatted' => $summary['total_formatted'],
        ]);
    }

    public function order(?string $order = null): View|RedirectResponse
    {
        // Order Complete is only available for a real placed/paid order.
        if (! $order) {
            return redirect()->route('cart')->with('error', 'Order details are available after a successful payment.');
        }

        $orderModel = \App\Models\Order::query()
            ->with('items')
            ->where('order_number', $order)
            ->first();

        if (! $orderModel) {
            return redirect()->route('cart')->with('error', 'Order not found.');
        }

        $isPaid = $orderModel->payment_status === 'paid'
            || in_array($orderModel->status, [
                \App\Support\OrderStatuses::PLACED,
                \App\Support\OrderStatuses::PACKED,
                \App\Support\OrderStatuses::SHIPPED,
                \App\Support\OrderStatuses::DELIVERED,
            ], true);

        if (! $isPaid) {
            return redirect()->route('checkout')->with('error', 'Complete payment to view your order.');
        }

        if (Auth::check() && (int) $orderModel->user_id !== (int) Auth::id() && ! optional(Auth::user())->isAdmin()) {
            abort(403);
        }

        return view('frontend.pages.order', [
            'placedOrder' => $orderModel,
        ]);
    }

    public function about(): View
    {
        // "World of Vastutathastu" is edited in Admin → Page Settings → About Us.
        return view('frontend.pages.about', ['page' => $this->publishedPage('about-us')]);
    }

    public function founder(): View
    {
        // Optional extra biography from Admin → Page Settings → About the Founder.
        return view('frontend.pages.founder', ['page' => $this->publishedPage('founder')]);
    }

    public function gallery(?string $category = null): View
    {
        // Gallery categories (Our Product Users, Awards, Celebrity, Others); /gallery opens the first.
        // Photos: Admin → Gallery → one section per category (one banner per photo).
        $categories = \App\Support\GalleryCategories::all();
        $activeSlug = $category ?: array_key_first($categories);
        $active = $categories[$activeSlug];

        $photos = \App\Support\SiteBanners::all($active['section'])
            ->map(fn ($banner) => [
                'image' => ($m = \App\Support\SiteBanners::media($banner)) ? \App\Support\SiteBanners::url($m->image) : null,
                'caption' => $banner->title,
                'detail' => $banner->description,
            ])
            ->filter(fn ($photo) => $photo['image'])
            ->values();

        $galleryLinks = \App\Support\GalleryCategories::links();

        return view('frontend.pages.gallery', compact('photos', 'galleryLinks', 'activeSlug', 'active'));
    }

    public function stores(): View
    {
        // Admin → Stores (only locations switched on are listed).
        $stores = \App\Models\Store::active()->orderBy('sort_order')->orderBy('name')->get();
        $cities = $stores->pluck('city')->unique()->values();

        return view('frontend.pages.stores', compact('stores', 'cities'));
    }

    /**
     * Live search for the header search panel (JSON). Every word typed must match the product's
     * name, category, material, description or features; results are ranked so names that start
     * with the query come first. A normal (non-AJAX) visit goes to the full results page.
     */
    public function search(Request $request): JsonResponse|RedirectResponse
    {
        $query = trim(preg_replace('/\s+/', ' ', (string) $request->query('q', '')));
        if (! $request->expectsJson() && ! $request->ajax()) {
            return redirect()->route('shop', $query !== '' ? ['q' => $query] : []);
        }

        $query = mb_substr($query, 0, 80);
        $needle = mb_strtolower($query);
        if ($needle === '') {
            return response()->json(['query' => '', 'total' => 0, 'products' => [], 'categories' => [], 'see_all_url' => null]);
        }

        $escape = fn (string $v) => str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $v);
        $ranking = \App\Support\ProductSearch::rank($query);
        $topIds = $ranking->take(8)->pluck('id')->all();
        $found = Product::query()->with('category:id,title,slug')->whereIn('id', $topIds ?: [0])
            ->get(['id', 'title', 'slug', 'category_id', 'featured_image', 'mrp', 'selling_price'])->keyBy('id');
        $ranked = collect($topIds)->map(fn ($id) => ['product' => $found->get($id)])->filter(fn ($r) => $r['product'])->values();

        $money = fn (float $v) => \App\Support\Money::format($v);
        $products = $ranked->take(8)->map(function ($row) use ($money) {
            $p = $row['product'];
            $mrp = (float) $p->mrp;
            $selling = (float) $p->selling_price;
            $discount = $mrp > $selling && $selling > 0 ? (int) round((($mrp - $selling) / $mrp) * 100) : 0;

            return [
                'title' => $p->title,
                'url' => route('shop.single', $p->slug),
                'image' => $p->tile_image_url,
                'category' => optional($p->category)->title,
                'price_on_request' => $selling <= 0 && $mrp <= 0,
                'has_selling_price' => $selling > 0,
                'mrp' => $money($mrp),
                'selling_price' => $money($selling),
                'discount_percent' => $discount,
            ];
        })->all();

        $categories = Category::query()->active()
            ->where('title', 'like', '%'.$escape($needle).'%')
            ->orderBy('sort_order')->take(6)->get()
            ->map(fn (Category $c) => ['title' => $c->title, 'url' => $c->frontendUrl()])
            ->all();

        return response()->json([
            'query' => $query,
            'total' => $ranking->count(),
            'products' => $products,
            'categories' => $categories,
            'see_all_url' => route('shop', ['q' => $query]),
        ])->header('Cache-Control', 'private, max-age=60');
    }

    /**
     * Services page: the Services menu groups (Astrology, Numerology, Vastushastra) with each service
     * product as a card. Services and prices are managed in Admin → Products.
     */
    public function services(): View
    {
        $groups = \App\Support\MegaMenu::data()['services'];
        $products = Product::query()->active()
            ->whereIn('id', $groups->pluck('products')->flatten()->pluck('id')->all())
            ->get()->keyBy('id');

        $groups = $groups->map(fn ($g) => $g + [
            'anchor' => $g['page'],
            'cards' => $g['products']->map(fn ($p) => $products->get($p->id))->filter()->values(),
        ])->filter(fn ($g) => $g['cards']->isNotEmpty())->values();

        return view('frontend.pages.services', compact('groups'));
    }

    public function info(string $slug): View|RedirectResponse
    {
        $title = InfoPages::title($slug);
        abort_if($title === null, 404);

        // Content written in Admin → Settings → Web Settings (empty → "No information available").
        $page = $this->publishedPage($slug);

        // Astrology / Numerology / Vastu consultation without their own text yet: show that group on the Services page.
        if (! $page && in_array($slug, ['astrology', 'numerology', 'vastu-consultation'], true)) {
            return redirect()->to(route('services').'#'.$slug);
        }
        // "Book a consultation" opens a pop-up form on the site; a direct visit without page text goes to the enquiry form.
        if (! $page && $slug === 'book-a-consultation') {
            return redirect()->to(route('contact').'#vt-contact-form-title');
        }
        if ($page && filled($page->title)) {
            $title = $page->title;
        }

        return view('frontend.pages.info', compact('title', 'page'));
    }

    private function publishedPage(string $slug): ?Page
    {
        $page = Page::query()->where('slug', $slug)->where('is_active', true)->first();

        return $page && trim(strip_tags((string) $page->content)) !== '' ? $page : null;
    }

    public function account(string $page = 'overview'): View|RedirectResponse
    {
        $allowed = [
            'overview' => 'Overview',
            'orders' => 'Orders',
            'information' => 'Account Information',
            'addresses' => 'Addresses',
            'wishlist' => 'Wishlist',
            'reviews' => 'My Reviews',
            'notifications' => 'Notifications',
        ];

        if ($page === 'favorites' || $page === 'wishlists') {
            return redirect()->route('account', 'wishlist');
        }

        if (! array_key_exists($page, $allowed)) {
            abort(404);
        }

        $user = auth()->user();
        $bootstrap = \App\Http\Controllers\AccountController::bootstrap($user);
        $nameParts = preg_split('/\s+/', trim((string) $user->name), 2) ?: [];

        return view('frontend.pages.account', [
            'accountPage' => $page,
            'accountTitle' => $allowed[$page],
            'accountUser' => [
                'id' => $user->id,
                'name' => $user->name,
                'firstName' => $nameParts[0] ?? $user->name,
                'lastName' => $nameParts[1] ?? '',
                'email' => $user->email,
                'phone' => $user->phone ?? '',
                'birthDate' => optional($user->birth_date)->format('Y-m-d') ?? '',
                'marketing' => (bool) ($user->marketing_opt_in ?? true),
            ],
            'accountBootstrap' => $bootstrap,
        ]);
    }

    public function support(string $page = 'faqs'): View
    {
        $allowed = [
            'shipping' => 'Shipping',
            'returns' => 'Returns & Exchanges',
            'start-return' => 'Start a Return',
            'international' => 'International Customers',
            'size-guide' => 'Size Guide',
            'faqs' => 'FAQs',
            'terms' => 'Terms & Conditions',
            'privacy' => 'Privacy & Cookies',
            'affiliates' => 'Affiliates',
        ];

        if (! array_key_exists($page, $allowed)) {
            abort(404);
        }

        return view('frontend.pages.support', [
            'supportPage' => $page,
            'supportTitle' => $allowed[$page],
        ]);
    }

    public function blog(Request $request): View
    {
        $newsTypes = NewsType::query()
            ->active()
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get();

        $activeType = null;
        if ($typeSlug = $request->query('type')) {
            $activeType = NewsType::query()->active()->where('slug', $typeSlug)->first();
        }

        $featuredQuery = BlogPost::query()
            ->published()
            ->with('newsType')
            ->when($activeType, fn ($q) => $q->where('news_type_id', $activeType->id));

        $featuredPost = (clone $featuredQuery)
            ->featured()
            ->orderBy('sort_order')
            ->orderByDesc('published_at')
            ->first();

        if (! $featuredPost) {
            $featuredPost = (clone $featuredQuery)
                ->orderByDesc('published_at')
                ->first();
        }

        $journalBanner = Banner::query()
            ->where('section', 'journal_page_banner')
            ->where('is_active', true)
            ->with(['images' => fn ($q) => $q->where('is_active', true)->orderBy('sort_order')])
            ->orderBy('sort_order')
            ->first();

        $postsQuery = BlogPost::query()
            ->published()
            ->with('newsType')
            ->orderBy('sort_order')
            ->orderByDesc('published_at');

        if ($activeType) {
            $postsQuery->where('news_type_id', $activeType->id);
        }

        if ($featuredPost) {
            $postsQuery->where('id', '!=', $featuredPost->id);
        }

        $posts = $postsQuery->paginate(9)->withQueryString();

        return view('frontend.pages.blog', compact(
            'newsTypes',
            'activeType',
            'featuredPost',
            'journalBanner',
            'posts'
        ));
    }

    public function blogSingle(string $slug): View
    {
        $post = BlogPost::query()
            ->published()
            ->with('newsType')
            ->where('slug', $slug)
            ->firstOrFail();

        $relatedPosts = BlogPost::query()
            ->published()
            ->with('newsType')
            ->where('id', '!=', $post->id)
            ->when($post->news_type_id, fn ($q) => $q->where('news_type_id', $post->news_type_id))
            ->orderByDesc('published_at')
            ->take(2)
            ->get();

        $prevPost = BlogPost::query()
            ->published()
            ->where('id', '<', $post->id)
            ->orderByDesc('id')
            ->first();

        $nextPost = BlogPost::query()
            ->published()
            ->where('id', '>', $post->id)
            ->orderBy('id')
            ->first();

        return view('frontend.pages.blog-single', compact('post', 'relatedPosts', 'prevPost', 'nextPost'));
    }
}
