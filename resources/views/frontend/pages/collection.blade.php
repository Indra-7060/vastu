@php
  $isShop = $activeCategory === null;
  $heading = $search !== '' ? 'Results for “'.$search.'”' : ($isShop ? 'New Arrivals' : $activeCategory->page_heading_text);
  $intro = $isShop
      ? 'Discover the latest sacred products from Vastutathastu — authentic Rudraksha, energised Yantras, crystal malas and Vastu essentials, chosen to bring harmony to your home and workplace.'
      : ($activeCategory->page_description ?: ($activeCategory->short_description ?: 'Authentic '.$activeCategory->title.' from Vastutathastu, chosen with care and energised before dispatch.'));
  $shopBanner = \App\Support\SiteBanners::media(\App\Support\SiteBanners::first('shop_banner'));
  $bannerImage = (! $isShop && $activeCategory->image)
      ? $activeCategory->image_url
      : ($shopBanner ? \App\Support\SiteBanners::url($shopBanner->image) : null);
  // Category covers: a square / portrait category photo can't fill a wide banner without being
  // zoomed and cropped, so those categories show a row of their own product photos instead.
  // A wide photo (at least 2:1, e.g. a banner uploaded in Admin → Categories) is still used full-width.
  $bannerStrip = collect();
  $bannerSingle = null;
  if (! $isShop) {
      $size = $activeCategory->image && ! str_starts_with($activeCategory->image, 'http')
          ? @getimagesize(public_path('storage/'.ltrim($activeCategory->image, '/'))) : null;
      $isWide = $size && $size[1] > 0 && $size[0] / $size[1] >= 2;
      if (! $isWide) {
          $bannerStrip = \App\Models\Product::query()->active()
              ->where('category_id', $activeCategory->id)
              ->whereNotNull('featured_image')
              ->orderByDesc('is_featured')->orderBy('sort_order')->orderByDesc('id')
              ->take(8)->get()
              ->map(fn ($p) => ['image' => $p->featured_image_url, 'title' => $p->title])
              ->unique('image')->take(4)->values();
          if ($bannerStrip->count() < 2) {
              $bannerStrip = collect();   // one photo: shown whole in the centre (see --single)
              $bannerSingle = $bannerImage;
              $bannerImage = null;
          } else {
              $bannerImage = null;
          }
      }
  }
  $pageTitle = $heading.' - Vastutathastu';
  $vtHeaderOverlay = true;
  $filterGroups = [
      'category' => 'Category',
      'type' => 'Type',
      'material' => 'Material',
      'price' => 'Price',
      'badge' => 'Highlights',
  ];
  $baseUrl = url()->current();
  $keepQuery = array_filter(['q' => $search ?: null]);
@endphp
<!DOCTYPE html>
<html dir="ltr" lang="en">

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="{{ $intro }}">
  <link rel="stylesheet" href="{{ asset('frontend/css/bootstrap.min.css') }}">
  <link rel="stylesheet" href="{{ asset('frontend/css/style.css') }}?v=vastu-4">
  <link rel="stylesheet" href="{{ asset('frontend/css/custom.css') }}?v=vastu-3">
  <link rel="stylesheet" href="{{ asset('frontend/css/site-drawers.css') }}?v=vastu-2">
  <link rel="stylesheet" href="{{ asset('vastu/css/vastu.css') }}?v=203">
  <title>{{ $pageTitle }}</title>
  <link rel="icon" type="image/svg+xml" href="{{ asset('vastu/images/favicon.svg') }}?v=vt2">
  <link rel="icon" type="image/png" sizes="50x50" href="{{ asset('vastu/images/favicon-50.png') }}?v=vt2">
  <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('vastu/images/favicon-32.png') }}?v=vt2">
  <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('vastu/images/favicon-16.png') }}?v=vt2">
  <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('vastu/images/apple-touch-icon.png') }}?v=vt2">
  <link rel="manifest" href="{{ asset('site.webmanifest') }}?v=vt2">
  <meta name="theme-color" content="#1c75bc">
</head>

<body class="vt-page vt-page--plp">
  <div class="wrapper ovh">
    @include('frontend.partials.site-header')

    <main class="vt-home vt-plp" id="main-content">
      @if($bannerStrip->isNotEmpty())
      <section class="vt-plp__banner vt-plp__banner--strip" aria-hidden="true" style="--strip-n: {{ $bannerStrip->count() }}">
        @foreach($bannerStrip as $tile)
          <span class="vt-plp__strip-tile"><img src="{{ $tile['image'] }}" alt="" @if($loop->index > 1) loading="lazy" @endif></span>
        @endforeach
      </section>
      @elseif($bannerSingle)
      <section class="vt-plp__banner vt-plp__banner--single" aria-hidden="true">
        <span class="vt-plp__single-bg" style="background-image:url('{{ $bannerSingle }}')"></span>
        <img src="{{ $bannerSingle }}" alt="">
      </section>
      @elseif($bannerImage)
      <section class="vt-plp__banner" aria-hidden="true">
        <picture>
          @if($isShop && $shopBanner && $shopBanner->mobile_image)<source media="(max-width: 767px)" srcset="{{ \App\Support\SiteBanners::url($shopBanner->mobile_image) }}">@endif
          <img src="{{ $bannerImage }}" alt="" data-vt-zoom>
        </picture>
      </section>
      @else
      <div class="vt-plp__banner vt-plp__banner--plain" aria-hidden="true"></div>
      @endif

      <section class="vt-plp__intro">
        <nav class="vt-crumbs" aria-label="Breadcrumb">
          <a href="{{ route('home') }}">Home</a>
          <span aria-hidden="true">›</span>
          @if($isShop)
            <span>New Arrivals</span>
          @else
            <a href="{{ route('shop') }}">Shop</a>
            <span aria-hidden="true">›</span>
            <span>{{ $activeCategory->title }}</span>
          @endif
        </nav>
        <h1 class="vt-plp__title">{{ $heading }}</h1>
        <p class="vt-plp__desc" data-vt-readmore>{{ $intro }}</p>
      </section>

      <div class="vt-plp__toolbar" data-vt-toolbar>
        <p class="vt-plp__count"><span data-vt-total>{{ $products->total() }}</span> {{ \Illuminate\Support\Str::plural('Result', $products->total()) }}</p>
        <div class="vt-plp__actions">
          <button class="vt-pill" type="button" data-vt-open="filters" aria-controls="vt-filters" aria-expanded="false">
            <svg width="18" height="14" viewBox="0 0 18 14" fill="none" aria-hidden="true"><path d="M1 3h9M14 3h3M1 11h3M8 11h9" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"/><circle cx="12" cy="3" r="2" stroke="currentColor" stroke-width="1.2"/><circle cx="6" cy="11" r="2" stroke="currentColor" stroke-width="1.2"/></svg>
            Filters @if($activeFilterCount)<span class="vt-pill__count">{{ $activeFilterCount }}</span>@endif
          </button>
          <button class="vt-pill" type="button" data-vt-open="sort" aria-controls="vt-sort" aria-expanded="false">
            <svg width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M5 2v12M5 2 2 5M5 2l3 3M11 14V2M11 14l-3-3M11 14l3-3" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
            Sort by
          </button>
        </div>
      </div>

      @if($activeFilterCount)
        <div class="vt-plp__chips">
          @foreach($filters as $group => $values)
            @foreach($values as $value)
              @php
                $remaining = $filters; $remaining[$group] = array_values(array_diff($values, [$value]));
                $label = $facets[$group][$value]['label'] ?? ($group === 'price' ? ($priceBands[$value] ?? $value) : $value);
              @endphp
              <a class="vt-chip" href="{{ $baseUrl.'?'.http_build_query($keepQuery + array_filter($remaining) + ['sort' => $sort]) }}">{{ $label }} <span aria-hidden="true">×</span><span class="visually-hidden">Remove filter</span></a>
            @endforeach
          @endforeach
          <a class="vt-chip vt-chip--clear" href="{{ $baseUrl.'?'.http_build_query($keepQuery + ['sort' => $sort]) }}">Clear all</a>
        </div>
      @endif

      <section class="vt-plp__grid" aria-label="Products" data-vt-grid>
        @if($products->count())
          @include('frontend.partials.vastu-product-tiles', ['products' => $products, 'teasers' => $teasers])
        @else
          <div class="collection-page__empty">
            @include('frontend.partials.vastu-empty-state', [
                'title' => 'No products found',
                'text' => $activeFilterCount || $search !== ''
                    ? 'Nothing matches your selection. Try removing a filter or searching for something else.'
                    : 'Products in '.$heading.' will appear here as soon as they are added. Please check back soon.',
            ])
          </div>
        @endif
      </section>

      @if($products->total() > 0)
        <div class="vt-plp__more" data-vt-more>
          <p class="vt-plp__viewed">You've viewed <span data-vt-shown>{{ $products->lastItem() }}</span> of {{ $products->total() }} products</p>
          <span class="vt-plp__meter"><span data-vt-meter style="width: {{ round(($products->lastItem() / max(1, $products->total())) * 100) }}%"></span></span>
          @if($products->hasMorePages())
            <a class="vt-plp__load" href="{{ $products->nextPageUrl() }}" data-vt-load>Load more</a>
          @endif
        </div>
      @endif

      <section class="vt-plp__seo">
        <h2>{{ $isShop ? 'New arrivals at Vastutathastu' : $activeCategory->title.' at Vastutathastu' }}</h2>
        <p>Every Vastutathastu product is selected under the guidance of Makrannd Sardeshmukh, with more than 22 years of experience in Vedic Vastushastra, astrology and numerology. Explore Rudraksha, Yantras, malas, crystal trees and puja essentials — and book a personal consultation if you would like help choosing the right piece for your space.</p>
      </section>

      {{-- "Other Categories": the other categories in the same endless slider as "Sacred Energy, Refined" --}}
      @php
        $moreCategories = $categories->where('slug', '!=', $activeCategory?->slug)->filter(fn ($c) => $c->image)->values()
            ->map(fn ($c) => ['name' => $c->title, 'sub' => '', 'price' => 'Explore', 'image_url' => $c->image_url, 'url' => $c->frontendUrl(), 'badge' => false])
            ->all();
      @endphp
      @if(count($moreCategories) > 1)
        <div class="vt-plp__related">
          @include('frontend.partials.vastu-product-carousel', ['title' => 'Other Categories', 'items' => $moreCategories, 'id' => 'vt-plp-more'])
        </div>
      @endif
    </main>

    @include('frontend.partials.site-footer')
  </div>

  {{-- Filters drawer --}}
  <div class="vt-drawer-overlay" data-vt-overlay hidden></div>
  <aside class="vt-drawer" id="vt-filters" data-vt-drawer="filters" aria-hidden="true" aria-labelledby="vt-filters-title" role="dialog">
    <form class="vt-drawer__form" method="get" action="{{ $baseUrl }}">
      @if($search !== '')<input type="hidden" name="q" value="{{ $search }}">@endif
      <input type="hidden" name="sort" value="{{ $sort }}">
      <header class="vt-drawer__head">
        <h2 id="vt-filters-title">Filters</h2>
        <button class="vt-drawer__close" type="button" data-vt-close aria-label="Close filters">×</button>
      </header>
      <div class="vt-drawer__body">
        @foreach($filterGroups as $group => $groupLabel)
          @continue(empty($facets[$group]))
          <details class="vt-facet" {{ ! empty($filters[$group]) || $loop->first ? 'open' : '' }}>
            <summary>{{ $groupLabel }}@if(count($filters[$group] ?? []))<span class="vt-facet__n">{{ count($filters[$group]) }}</span>@endif</summary>
            <ul>
              @foreach($facets[$group] as $value => $option)
                <li>
                  <label class="vt-check">
                    <input type="checkbox" name="{{ $group }}[]" value="{{ $value }}" {{ in_array((string) $value, $filters[$group] ?? [], true) ? 'checked' : '' }}>
                    <span>{{ $option['label'] }}</span>
                    <small>{{ $option['count'] }}</small>
                  </label>
                </li>
              @endforeach
            </ul>
          </details>
        @endforeach
      </div>
      <footer class="vt-drawer__foot">
        <a class="vt-btn vt-btn--outline" href="{{ $baseUrl.'?'.http_build_query($keepQuery + ['sort' => $sort]) }}">Clear all</a>
        <button class="vt-btn vt-btn--dark" type="submit">Show results</button>
      </footer>
    </form>
  </aside>

  {{-- Sort drawer --}}
  <aside class="vt-drawer" id="vt-sort" data-vt-drawer="sort" aria-hidden="true" aria-labelledby="vt-sort-title" role="dialog">
    <header class="vt-drawer__head">
      <h2 id="vt-sort-title">Sort by</h2>
      <button class="vt-drawer__close" type="button" data-vt-close aria-label="Close sort options">×</button>
    </header>
    <ul class="vt-drawer__body vt-sortlist">
      @foreach($sorts as $key => $label)
        <li>
          <a href="{{ $baseUrl.'?'.http_build_query($keepQuery + array_filter($filters) + ['sort' => $key]) }}" class="{{ $sort === $key ? 'is-active' : '' }}" @if($sort === $key) aria-current="true" @endif>{{ $label }}</a>
        </li>
      @endforeach
    </ul>
  </aside>

  <script src="{{ asset('frontend/js/jquery.js') }}"></script>
  <script src="{{ asset('frontend/js/bootstrap.min.js') }}"></script>
  <script src="{{ asset('frontend/js/bootstrap-select.min.js') }}"></script>
  <script src="{{ asset('frontend/js/mmenu.js') }}"></script>
  <script src="{{ asset('frontend/js/lazysize.min.js') }}"></script>
  <script src="{{ asset('frontend/js/swiper-bundle.min.js') }}"></script>
  <script src="{{ asset('frontend/js/script.js?v=vastu-3') }}"></script>
  <script src="{{ asset('frontend/js/site-drawers.js') }}?v=vastu-6"></script>
  <script src="{{ asset('frontend/js/frontend-search.js') }}?v=live-3"></script>
  @include('frontend.partials.cart-script')
  <script src="{{ asset('frontend/js/wishlist-toggle.js') }}?v=vastu-3"></script>
</body>

</html>
