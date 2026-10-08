@php $pageTitle = ($product->title ?? 'Product') . ' - Vastutathastu'; @endphp
<!DOCTYPE html>
<html dir="ltr" lang="en">

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="keywords" content="vastu, vastushastra, rudraksha, yantra, mala, crystal tree, astrology, numerology, puja essentials">
  <meta name="description" content="Vastutathastu unites Vedic Vastushastra, astrology and numerology with authentic sacred products for harmonious homes, workplaces and lives.">
  <!-- css file -->
  <link rel="stylesheet" href="{{ asset('frontend/css/bootstrap.min.css') }}">
  <link rel="stylesheet" href="{{ asset('frontend/css/style.css') }}?v=vastu-4">
  <link rel="stylesheet" href="{{ asset('frontend/css/custom.css') }}?v=vastu-3">
  <link rel="stylesheet" href="{{ asset('frontend/css/site-drawers.css') }}?v=vastu-2">
  <link rel="stylesheet" href="{{ asset('frontend/css/journal.css') }}?v=vastu-2">

  <!-- Title -->
  <title>{{ $pageTitle ?? ($product->title ?? 'Product') . ' - Vastutathastu' }}</title>

  <link rel="stylesheet" href="{{ asset('vastu/css/vastu.css') }}?v=216">
  <link rel="icon" type="image/svg+xml" href="{{ asset('vastu/images/favicon.svg') }}?v=vt2">
  <link rel="icon" type="image/png" sizes="50x50" href="{{ asset('vastu/images/favicon-50.png') }}?v=vt2">
  <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('vastu/images/favicon-32.png') }}?v=vt2">
  <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('vastu/images/favicon-16.png') }}?v=vt2">
  <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('vastu/images/apple-touch-icon.png') }}?v=vt2">
  <link rel="manifest" href="{{ asset('site.webmanifest') }}?v=vt2">
  <meta name="theme-color" content="#1c75bc">
</head>

<body class="product-detail-page product-size-closed{{ $product->category ? ' category-' . \Illuminate\Support\Str::slug($product->category->title ?? $product->category->name) : '' }}">
  <!-- header-area -->

  <!-- header-area-end -->


  <div class="wrapper ovh">
    <div class="preloader"></div>

    <!-- header-area -->
    @include('frontend.partials.site-header')
    <!-- header-area-end -->


    <!-- main-area -->
    <main class="body_content_wrapper position-relative product-detail-main">
      <section class="product-design" aria-labelledby="product-design-title" data-product-id="{{ $product->id }}" data-max-unit-buy="{{ $product->max_unit_buy ?: 99 }}">
        <script type="application/json" data-colour-galleries>
          @json($colourGalleries)
        </script>
        @php
        $productMeta = collect([
            'Material' => $product->material,
            'Type' => optional($product->subCategory)->title,
            'Category' => optional($product->category)->title,
        ])->filter();
        $subtitle = collect([$product->material, optional($product->subCategory)->title])->filter()->unique()->implode(', ');
        $descriptionParagraphs = collect(preg_split('/\r\n|\r|\n/', (string) $product->short_description))->map(fn ($l) => trim($l))->filter()->values();
        // Product's own sections, then the category's shared sections (Admin → Categories → "Sections
        // for every product"); a product section with the same title replaces the shared one.
        $validSection = fn ($item) => filled($item['title'] ?? null) && filled($item['text'] ?? null);
        $ownSections = collect($product->information_items ?? [])->filter($validSection)->values();
        $ownTitles = $ownSections->map(fn ($item) => mb_strtolower(trim($item['title'])))->all();
        $sharedSections = collect(optional($product->category)->product_sections ?? [])->filter($validSection)
            ->reject(fn ($item) => in_array(mb_strtolower(trim($item['title'])), $ownTitles, true));
        $sectionItems = $ownSections->concat($sharedSections)->values();
        $SS = \App\Support\SiteSettings::class;
        $shipTitle = $SS::get('pdp_shipping_title') ?: 'Shipping & returns';
        $shipText = $SS::get('pdp_shipping_text');
        $consultTitle = $SS::get('pdp_consult_title') ?: 'Book a consultation';
        $consultText = str_replace(':product', $product->title, $SS::get('pdp_consult_text'));
        $paras = fn (?string $text) => collect(preg_split('/\r\n|\r|\n/', (string) $text))->map(fn ($l) => trim($l))->filter();
        $priceOnRequest = (float) $product->selling_price <= 0 && (float) $product->mrp <= 0;
        $introText = $descriptionParagraphs->first() ? \Illuminate\Support\Str::limit($descriptionParagraphs->first(), 200) : null;
        $descriptionFeatureLines = collect(preg_split('/\r\n|\r|\n/', (string) $product->features))->map(fn ($line) => trim($line))->filter()->values();
        $specificationItems = collect($product->specifications ?? [])->filter(fn ($item) => filled($item['key'] ?? null) || filled($item['value'] ?? null))->values();
        $shipping = \App\Models\ShippingSetting::query()->first();
        $freeShipping = $shipping && (float) $shipping->free_shipping_threshold > 0 ? (float) $shipping->free_shipping_threshold : null;
        $flatShipping = $shipping ? (float) $shipping->flat_shipping_rate : null;
        $galleryCount = count($gallery);
        @endphp
        <div class="product-design__top">
          {{-- Gallery: one large image, then pairs (click to open the full-screen viewer) --}}
          <div class="product-design__gallery-wrap">
            <div class="product-design__gallery" aria-label="Product image gallery" data-pdp-gallery>
              @foreach($gallery as $gi => $imageUrl)
              <button class="product-design__image{{ $gi === 0 ? ' is-active' : '' }}" type="button" data-product-image="{{ $imageUrl }}" data-pdp-zoom="{{ $gi }}" aria-label="Open image {{ $gi + 1 }} of {{ $galleryCount }} in full screen">
                <img src="{{ $imageUrl }}" alt="{{ $gi === 0 ? $product->title : '' }}" @if($gi > 0) loading="lazy" @endif>
              </button>
              @endforeach
            </div>
            <button class="product-design__wish pdp-gallery-wish" type="button" data-wishlist-product="{{ $product->id }}" aria-label="Add {{ $product->title }} to wishlist">@include('frontend.partials.vt-icon', ['name' => 'heart', 'size' => 22, 'stroke' => 1.6])</button>
            @if($galleryCount > 1)
            <div class="pdp-gallery-dots" aria-hidden="true">
              @foreach($gallery as $gi => $imageUrl)<span class="{{ $gi === 0 ? 'is-active' : '' }}"></span>@endforeach
            </div>
            @endif
          </div>

          <div class="product-design__details" data-product-id="{{ $product->id }}">
            {{-- Panel 1: name, price, delivery, add to bag --}}
            <div class="pdp-panel pdp-panel--info">
              <div class="product-design__head">
                @if($product->badge)<p class="pdp-flag">{{ $product->badge }}</p>@elseif($product->is_new_arrival)<p class="pdp-flag">New</p>@endif
                <h1 id="product-design-title" class="pdp-title"><span data-vt-orig="{{ $product->title }}">{{ $product->title }}</span>@if($subtitle)<span class="pdp-subtitle">{{ $subtitle }}</span>@endif</h1>
                <div class="pdp-price">
                  <p class="product-design__price" data-product-price>
                    @include('frontend.partials.product-price', ['product' => $product, 'hideLabels' => true])
                  </p>
                  @unless($priceOnRequest)<p class="pdp-price__note">MRP (incl. of all taxes)</p>@endunless
                </div>
              </div>

              @if($introText)
              <p class="product-design__intro">{{ $introText }}</p>
              @endif

              @if($priceOnRequest)
              <p class="pdp-enquire-note">Price and availability are shared on request — our team will guide you personally.</p>
              <a class="pdp-add pdp-enquire" href="{{ route('info', 'book-a-consultation') }}" data-pdp-add>Enquire now</a>
              @else
              <div class="pdp-buy">
                <div class="product-design__quantity" aria-label="Quantity selector"><button type="button" data-product-qty="minus" aria-label="Decrease quantity">−</button><span data-product-qty-value>1</span><button type="button" data-product-qty="plus" aria-label="Increase quantity">+</button></div>
                <button class="product-design__cart pdp-add" type="button" data-add-to-cart data-product-id="{{ $product->id }}" data-default-color="" data-default-size="" data-default-package="" data-pdp-add>Add to cart</button>
              </div>
              <a class="product-design__buy pdp-buy-now" href="{{ route('checkout') }}" data-buy-now data-product-id="{{ $product->id }}" data-default-color="" data-default-size="" data-default-package="">Buy now</a>

              @endif
              @if($freeShipping && ! $priceOnRequest)
              <p class="pdp-shipping-note">Free standard shipping over ₹{{ number_format($freeShipping) }}.</p>
              @endif
            </div>

            {{-- Panel 2: expandable information --}}
            <div class="pdp-panel pdp-panel--more">
              @php $firstOpen = true; @endphp
              @if($descriptionParagraphs->isNotEmpty() || $descriptionFeatureLines->isNotEmpty())
              <div class="pdp-acc is-open" data-pdp-acc>
                <h2 class="pdp-acc__head"><button type="button" aria-expanded="true" aria-controls="pdp-acc-desc">Description<span class="pdp-acc__chev" aria-hidden="true"></span></button></h2>
                <div class="pdp-acc__body" id="pdp-acc-desc">
                  <div class="pdp-acc__inner">
                    @foreach($descriptionParagraphs as $para)<p>{{ $para }}</p>@endforeach
                    @if($descriptionFeatureLines->isNotEmpty())
                    @if($descriptionParagraphs->isNotEmpty())<h3 class="pdp-acc__sub">Features</h3>@endif
                    <ul class="pdp-list">
                      @foreach($descriptionFeatureLines as $line)
                        @php [$fLabel, $fText] = str_contains($line, ':') ? array_map('trim', explode(':', $line, 2)) : [null, $line]; @endphp
                        <li>@if($fLabel && strlen($fLabel) <= 32)<strong>{{ $fLabel }}:</strong> {{ $fText }}@else{{ $line }}@endif</li>
                      @endforeach
                    </ul>
                    @endif
                  </div>
                </div>
              </div>
              @php $firstOpen = false; @endphp
              @endif

              @if($specificationItems->isNotEmpty())
              <div class="pdp-acc{{ $firstOpen ? ' is-open' : '' }}" data-pdp-acc>
                <h2 class="pdp-acc__head"><button type="button" aria-expanded="{{ $firstOpen ? 'true' : 'false' }}" aria-controls="pdp-acc-details">Product details<span class="pdp-acc__chev" aria-hidden="true"></span></button></h2>
                <div class="pdp-acc__body" id="pdp-acc-details" @unless($firstOpen) hidden @endunless>
                  <div class="pdp-acc__inner">
                    <dl class="pdp-specs">
                      @foreach($specificationItems as $spec)
                      <div><dt>{{ $spec['key'] ?? '' }}</dt><dd>{{ $spec['value'] ?? '' }}</dd></div>
                      @endforeach
                    </dl>
                  </div>
                </div>
              </div>
              @php $firstOpen = false; @endphp
              @endif

              @foreach($sectionItems as $si => $section)
              <div class="pdp-acc{{ $firstOpen ? ' is-open' : '' }}" data-pdp-acc>
                <h2 class="pdp-acc__head"><button type="button" aria-expanded="{{ $firstOpen ? 'true' : 'false' }}" aria-controls="pdp-acc-sec-{{ $si }}">{{ $section['title'] }}<span class="pdp-acc__chev" aria-hidden="true"></span></button></h2>
                <div class="pdp-acc__body" id="pdp-acc-sec-{{ $si }}" @unless($firstOpen) hidden @endunless>
                  <div class="pdp-acc__inner">
                    @foreach(preg_split('/\r\n|\r|\n/', (string) $section['text']) as $para)
                      @if(trim($para) !== '')<p>{{ trim($para) }}</p>@endif
                    @endforeach
                  </div>
                </div>
              </div>
              @php $firstOpen = false; @endphp
              @endforeach

              @if(trim($shipText) !== '')
              <div class="pdp-acc" data-pdp-acc>
                <h2 class="pdp-acc__head"><button type="button" aria-expanded="false" aria-controls="pdp-acc-ship">{{ $shipTitle }}<span class="pdp-acc__chev" aria-hidden="true"></span></button></h2>
                <div class="pdp-acc__body" id="pdp-acc-ship" hidden>
                  <div class="pdp-acc__inner">
                    @foreach($paras($shipText) as $para)<p>{{ $para }}</p>@endforeach
                    @if($flatShipping !== null)<p>Standard shipping: ₹{{ number_format($flatShipping) }}@if($freeShipping) · Free standard shipping over ₹{{ number_format($freeShipping) }}@endif.</p>@endif
                    <p><a href="{{ route('info', 'shipping-and-returns') }}">Read our shipping &amp; returns policy</a></p>
                  </div>
                </div>
              </div>
              @endif

              @if(trim($consultText) !== '')
              <div class="pdp-acc" data-pdp-acc>
                <h2 class="pdp-acc__head"><button type="button" aria-expanded="false" aria-controls="pdp-acc-consult">{{ $consultTitle }}<span class="pdp-acc__chev" aria-hidden="true"></span></button></h2>
                <div class="pdp-acc__body" id="pdp-acc-consult" hidden>
                  <div class="pdp-acc__inner">
                    @foreach($paras($consultText) as $para)<p>{{ $para }}</p>@endforeach
                    <a class="pdp-outline-btn" href="{{ route('info', 'book-a-consultation') }}">Book a consultation</a>
                  </div>
                </div>
              </div>
              @endif
            </div>
          </div>
        </div>

      </section>

      {{-- Sticky add-to-bag bar (slides in once the main button scrolls away) --}}
      <div class="pdp-sticky" data-pdp-sticky aria-hidden="true">
        <div class="pdp-sticky__inner">
          <div class="pdp-sticky__left">
            @if($galleryCount)<img src="{{ $gallery[0] }}" alt="" width="48" height="48">@endif
            <div><strong>{{ $product->title }}</strong>@if($subtitle)<span>{{ $subtitle }}</span>@endif<span class="pdp-sticky__inbag" data-pdp-sticky-inbag aria-live="polite" hidden></span></div>
          </div>
          <div class="pdp-sticky__right">
            <span class="pdp-sticky__price" data-pdp-sticky-price></span>
            @if($priceOnRequest)
            <a class="pdp-add pdp-sticky__btn" href="{{ route('info', 'book-a-consultation') }}" tabindex="-1">Enquire now</a>
            @else
            <button type="button" class="pdp-add pdp-sticky__btn" data-pdp-sticky-add tabindex="-1">Add to cart</button>
            @endif
          </div>
        </div>
      </div>

      {{-- Full-screen image viewer --}}
      <div class="pdp-viewer" data-pdp-viewer hidden role="dialog" aria-modal="true" aria-label="{{ $product->title }} images">
        <button type="button" class="pdp-viewer__close" data-pdp-viewer-close aria-label="Close"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"><path d="M5 5l14 14M19 5 5 19"/></svg></button>
        <p class="pdp-viewer__count" data-pdp-viewer-count></p>
        <div class="pdp-viewer__stage" data-pdp-viewer-stage><img alt="" data-pdp-viewer-img></div>
        <button type="button" class="pdp-viewer__nav pdp-viewer__nav--prev" data-pdp-viewer-prev aria-label="Previous image">@include('frontend.partials.vt-icon', ['name' => 'chevron-left', 'size' => 26])</button>
        <button type="button" class="pdp-viewer__nav pdp-viewer__nav--next" data-pdp-viewer-next aria-label="Next image">@include('frontend.partials.vt-icon', ['name' => 'chevron-right', 'size' => 26])</button>
        <div class="pdp-viewer__thumbs" data-pdp-viewer-thumbs></div>
      </div>




      @include('frontend.partials.product-recommendations', ['items' => $relatedProducts, 'heading' => 'YOU MAY ALSO LIKE', 'titleId' => 'related-products-title', 'variant' => 'related'])

      @include('frontend.partials.product-recommendations', ['items' => $recentlyViewed, 'heading' => 'RECENTLY VIEWED', 'titleId' => 'recent-products-title', 'variant' => 'recent'])



      @include('frontend.partials.site-footer')
    </main>
  </div>
  <!-- JS here -->
  <script src="{{ asset('frontend/js/jquery.js') }}"></script>
  <script src="{{ asset('frontend/js/bootstrap.min.js') }}"></script>
  <script src="{{ asset('frontend/js/bootstrap-select.min.js') }}"></script>
  <script src="{{ asset('frontend/js/mmenu.js') }}"></script>
  <script src="{{ asset('frontend/js/lazysize.min.js') }}"></script>
  <script src="{{ asset('frontend/js/jquery.magnific-popup.min.js') }}"></script>
  <script src="{{ asset('frontend/js/jquery.odometer.min.js') }}"></script>
  <script src="{{ asset('frontend/js/jquery.appear.js') }}"></script>
  <script src="{{ asset('frontend/js/swiper-bundle.min.js') }}"></script>
  <script src="{{ asset('frontend/js/jquery.countdown.js') }}"></script>
  <script src="{{ asset('frontend/js/jquery-scrolltofixed-min.js') }}"></script>
  <script src="{{ asset('frontend/js/jarallax.js') }}"></script>
  <script src="{{ asset('frontend/js/wow.min.js') }}"></script>
  <script src="{{ asset('frontend/js/script.js?v=vastu-4') }}"></script>
  <script src="{{ asset('frontend/js/site-drawers.js') }}?v=vastu-6"></script>
  <script src="{{ asset('frontend/js/frontend-search.js') }}?v=live-3"></script>
  @include('frontend.partials.cart-script')
  <script src="{{ asset('frontend/js/wishlist-toggle.js') }}?v=vastu-3"></script>
  <script src="{{ asset('vastu/js/pdp.js') }}?v=11"></script>
</body>

</html>
