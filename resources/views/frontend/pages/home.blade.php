@php
  $pageTitle = 'Vastutathastu — Sacred Living';
  $vtHeaderOverlay = true;
  $img = fn (string $file) => asset('vastu/images/'.$file);

  // Homepage data comes from the database (see FrontendController@home).
  // Editable sections come from Admin → Home Content → Sections & Images.
  $B = \App\Support\SiteBanners::class;
  $heroSlides = $B::all('home_hero');
  $hero = $heroSlides->first();
  $heroMedia = $B::media($hero);
  $intro = $B::first('home_intro');
  $spotlightBanner = $B::first('home_spotlight');
  $founderBanner = $B::first('home_founder');
  $instaIntro = $B::first('instagram_intro');
  $instaPosts = $B::all('instagram_post');
  $instaUrl = $B::link($instaIntro?->button_link, \App\Support\Instagram::PROFILE_URL);
  $instaHandle = preg_match('~instagram\.com/([A-Za-z0-9_.]+)~', $instaUrl, $hm) ? '@'.$hm[1] : '@vastutathastumakrannd_official';
  $instaCaptions = ['Wisdom for everyday living', 'Rituals for balanced home', 'Guidance, shared with care'];
  $instaPosts = $instaPosts->values()->map(fn ($p, $i) => [
      'url' => \App\Support\Instagram::permalink($p->button_link),
      'caption' => ($p->title && ! preg_match('/^instagram (post|reel)$/i', trim($p->title))) ? $p->title : ($instaCaptions[$i % 3]),
      'image' => ($m = $B::media($p)) ? $B::url($m->image) : null,
  ])->filter(fn ($p) => $p['url'])->values();
  $priceOf = fn ($p) => (float) $p->selling_price > 0 ? (float) $p->selling_price : (float) $p->mrp;
  $carouselItems = $featuredProducts->map(fn ($p) => [
      'name' => $p->title,
      'sub' => $p->material ?: optional($p->subCategory)->title,
      'price' => $priceOf($p) > 0 ? '₹ '.number_format($priceOf($p)) : 'Price on request',
      'image_url' => $p->tile_image_url,
      'url' => route('shop.single', $p->slug),
      'badge' => $p->badge ?: false,
  ])->all();
@endphp
<!DOCTYPE html>
<html dir="ltr" lang="en">

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="keywords" content="vastu, vastushastra, rudraksha, yantra, mala, crystal tree, astrology, numerology, puja essentials">
  <meta name="description" content="Vastutathastu unites Vedic Vastushastra, astrology and numerology with authentic sacred products for harmonious homes, workplaces and lives.">
  <link rel="stylesheet" href="{{ asset('frontend/css/bootstrap.min.css') }}">
  <link rel="stylesheet" href="{{ asset('frontend/css/style.css') }}?v=vastu-3">
  <link rel="stylesheet" href="{{ asset('frontend/css/custom.css') }}?v=vastu-3">
  <link rel="stylesheet" href="{{ asset('frontend/css/site-drawers.css') }}?v=vastu-2">
  <link rel="stylesheet" href="{{ asset('vastu/css/vastu.css') }}?v=117">
  <title>{{ $pageTitle }}</title>
  <link rel="icon" type="image/png" href="{{ asset('vastu/images/favicon.png') }}">
  <link rel="apple-touch-icon" href="{{ asset('vastu/images/favicon.png') }}">
  @if($heroMedia && ($heroMedia->is_video ? $heroMedia->mobile_image : $heroMedia->image))
  <link rel="preload" as="image" href="{{ $B::url($heroMedia->is_video ? $heroMedia->mobile_image : $heroMedia->image) }}">
  @endif
</head>

<body class="vt-page vt-page--home">
  <div class="wrapper ovh">
    @include('frontend.partials.site-header')

    <main class="vt-home body_content">
      {{-- Hero slideshow (Sections & Images → Home — Hero; one banner per slide) --}}
      @if($heroSlides->isNotEmpty())
      <section class="vt-hero{{ $heroSlides->count() > 1 ? ' vt-hero--slideshow' : '' }}" aria-roledescription="carousel" aria-label="Featured" data-vt-hero tabindex="-1">
        <div class="vt-hero__viewport">
          <div class="vt-hero__track" data-vt-hero-track>
            @foreach($heroSlides as $slide)
              @php
                $media = $B::media($slide);
                // Small photos would turn blurry stretched across the screen: show them framed at
                // close to their real size over a softly blurred copy instead.
                $heroSize = ($media && ! $media->is_video && ! str_starts_with((string) $media->image, 'http'))
                    ? @getimagesize(public_path('storage/'.ltrim($media->image, '/'))) : null;
                $framed = $heroSize && $heroSize[0] < 1400;
                $srcW = $framed ? (int) $heroSize[0] : 0;
                // The founder slide (its button opens the founder page) keeps his portrait on the left clear,
                // so its text sits on the right.
                $textRight = str_contains(strtolower((string) $slide->button_link), '/founder');
              @endphp
              <div class="vt-hero__slide{{ $loop->first ? ' is-selected' : '' }}{{ $framed ? ' vt-hero__slide--framed' : '' }}{{ $textRight ? ' vt-hero__slide--text-right' : '' }}" role="group" aria-roledescription="slide" aria-label="{{ $loop->iteration }} of {{ $loop->count }}" data-vt-hero-slide>
                @if($media && $media->is_video)
                  <video class="vt-hero__media" autoplay muted loop playsinline preload="{{ $loop->first ? 'metadata' : 'none' }}" @if($media->mobile_image) poster="{{ $B::url($media->mobile_image) }}" @endif aria-hidden="true">
                    <source src="{{ $B::url($media->image) }}" type="{{ $media->video_mime_type }}">
                  </video>
                @elseif($media && $framed)
                  <div class="vt-hero__ambient" style="background-image:url('{{ $B::url($media->image) }}')" aria-hidden="true"></div>
                  <img class="vt-hero__framed" src="{{ $B::url($media->image) }}" alt="{{ $slide->title }}" draggable="false" style="--src-w: {{ $srcW }}px" @unless($loop->first) loading="lazy" @endunless>
                @elseif($media)
                  <picture>
                    @if($media->mobile_image)<source media="(max-width: 767px)" srcset="{{ $B::url($media->mobile_image) }}">@endif
                    <img class="vt-hero__media" src="{{ $B::url($media->image) }}" alt="" draggable="false" @unless($loop->first) loading="lazy" @endunless>
                  </picture>
                @endif
                <div class="vt-hero__shade"></div>
                <div class="vt-hero__content">
                  @if($slide->subtitle)<p class="vt-hero__kicker">{{ $slide->subtitle }}</p>@endif
                  @if($loop->first)
                    <h1 class="vt-hero__title" id="vt-hero-title">{{ $slide->title }}</h1>
                  @else
                    <h2 class="vt-hero__title">{{ $slide->title }}</h2>
                  @endif
                  @if($slide->button_text || $slide->button_text_2)
                  <div class="vt-hero__actions">
                    @if($slide->button_text)<a class="vt-btn vt-btn--solid" href="{{ $B::link($slide->button_link, route('shop')) }}">{{ $slide->button_text }}</a>@endif
                    @if($slide->button_text_2)<a class="vt-btn vt-btn--ghost" href="{{ $B::link($slide->button_link_2) }}">{{ $slide->button_text_2 }}</a>@endif
                  </div>
                  @endif
                </div>
              </div>
            @endforeach
          </div>
        </div>
        @if($heroSlides->count() > 1)
          <div class="vt-hero__dots" role="tablist" aria-label="Choose slide">
            @foreach($heroSlides as $slide)
              <button type="button" class="vt-hero__dot{{ $loop->first ? ' is-selected' : '' }}" role="tab" aria-selected="{{ $loop->first ? 'true' : 'false' }}" aria-label="Slide {{ $loop->iteration }}: {{ $slide->title }}" data-vt-hero-dot="{{ $loop->index }}"></button>
            @endforeach
          </div>
        @endif
      </section>
      @endif

      {{-- Introduction (Sections & Images → Home — Introduction) --}}
      @if($intro)
      <section class="vt-intro">
        <div class="vt-intro__inner">
          <h2 class="vt-h2">{{ $intro->title }}</h2>
          @if($intro->description)<p class="vt-body">{{ $intro->description }}</p>@endif
        </div>
      </section>
      @endif

      {{-- Shop by Category --}}
      @if($homeCategories->isNotEmpty())
      <section class="vt-categories" aria-labelledby="vt-cat-title">
        <div class="vt-container">
          <div class="vt-section-head"><h2 class="vt-h2" id="vt-cat-title">Shop by Category</h2></div>
          <div class="vt-cat-grid">
            @foreach($homeCategories as $category)
              <a class="vt-cat" href="{{ $category->frontendUrl() }}">
                <span class="vt-cat__img"><img src="{{ $category->image_url }}" alt="{{ $category->title }}" loading="lazy" width="359" height="360"></span>
                <span class="vt-cat__label">{{ $category->title }}</span>
              </a>
            @endforeach
          </div>
        </div>
      </section>
      @endif

      {{-- Shree Yantra spotlight + customer reviews (Figma 576:2361).
           Text/link: Sections & Images → Home — Spotlight. Reviews: App\Support\Reviews (sample until Google is connected). --}}
      @php
        $spotLink = $B::link($spotlightBanner?->button_link, route('shop'));
        $spotTitle = $spotlightBanner?->title ?: 'Shree Yantra';
        $spotText = $spotlightBanner?->description ?: 'Discover the most sacred and powerful yantra. Shree Yantra embodies wealth, prosperity, and harmony. Artfully crafted in your choice of pure Brass, CNC-precision metals, or natural Sphatik (crystal). A sacred center for your home, sanctuary, or professional space.';
        $spotCta = $spotlightBanner?->button_text ?: 'Shop Shree Yantras';
        $reviewSummary = \App\Support\Reviews::summary();
        $reviews = \App\Support\Reviews::top(3);
        $stars = fn (int $n) => str_repeat('★', $n).str_repeat('☆', 5 - $n);
      @endphp
      <section class="vt-yantra" aria-labelledby="vt-yantra-title">
        <div class="vt-yantra__inner">
          <a class="vt-yantra__media" href="{{ $spotLink }}" tabindex="-1" aria-hidden="true">
            <img src="{{ asset('vastu/images/reviews/shree-yantra.jpg') }}?v=1" width="1536" height="1024" alt="" loading="lazy" decoding="async">
          </a>

          <div class="vt-yantra__copy">
            <div class="vt-yantra__head">
              <h2 class="vt-yantra__title" id="vt-yantra-title">{{ $spotTitle }}</h2>
              <span class="vt-yantra__rule" aria-hidden="true"></span>
            </div>

            <div class="vt-yantra__intro">
              <p class="vt-yantra__text">{{ $spotText }}</p>
              <div class="vt-yantra__badge">
                <p class="vt-yantra__score">
                  <img src="{{ asset('vastu/images/reviews/google.svg') }}" width="16" height="16" alt="Google">
                  <strong>{{ rtrim(rtrim(number_format((float) $reviewSummary['rating'], 1), '0'), '.') }}/5</strong>
                </p>
                <p class="vt-yantra__stars" aria-hidden="true">★★★★★</p>
                <p class="vt-yantra__count">Based on {{ (int) $reviewSummary['count'] }}+ reviews</p>
                <a class="vt-yantra__all" href="{{ $reviewSummary['url'] }}" target="_blank" rel="noopener">View all reviews <span aria-hidden="true">→</span></a>
              </div>
            </div>

            @if($reviews)
            <div class="vt-yantra__reviews">
              <p class="vt-yantra__kicker">What our customers say</p>
              <div class="vt-yantra__cards">
                @foreach($reviews as $review)
                <article class="vt-review">
                  <div class="vt-review__top">
                    <span class="vt-review__who">
                      <span class="vt-review__avatar" aria-hidden="true">
                        @if($review['photo'])<img src="{{ $review['photo'] }}" alt="" width="28" height="28" loading="lazy" referrerpolicy="no-referrer">@else{{ mb_strtoupper(mb_substr($review['name'], 0, 1)) }}@endif
                      </span>
                      <span class="vt-review__name notranslate" translate="no">{{ $review['name'] }}</span>
                    </span>
                    <img class="vt-review__g" src="{{ asset('vastu/images/reviews/google-small.svg') }}" width="14" height="14" alt="Google review">
                  </div>
                  <p class="vt-review__stars" aria-label="{{ $review['rating'] }} out of 5 stars">{{ $stars($review['rating']) }}</p>
                  <p class="vt-review__text">"{{ \Illuminate\Support\Str::limit($review['text'], 160) }}"</p>
                  @if($review['date'])<p class="vt-review__date">{{ $review['date']->format('j M Y') }}</p>@endif
                </article>
                @endforeach
              </div>
            </div>
            @endif

            <div class="vt-yantra__actions">
              <div class="vt-yantra__btns">
                <a class="vt-yantra__btn vt-yantra__btn--primary" href="{{ $spotLink }}">
                  {{ $spotCta }}
                  <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 12H4"/><path d="M15 17s5-3.68 5-5-5-5-5-5"/></svg>
                </a>
                <a class="vt-yantra__btn vt-yantra__btn--ghost" href="{{ $spotLink }}">Discover Material Guide</a>
              </div>
            </div>
          </div>
        </div>
      </section>

      {{-- Sacred Energy, Refined --}}
      @if($carouselItems)
        @include('frontend.partials.vastu-product-carousel', ['title' => 'Sacred Energy, Refined', 'items' => $carouselItems, 'id' => 'vt-sacred-energy'])
      @endif

      {{-- Founder (Sections & Images → Home — Founder) --}}
      @if($founderBanner)
      @php $founderMedia = $B::media($founderBanner); $founderLink = $B::link($founderBanner->button_link, route('founder')); @endphp
      <section class="vt-split vt-split--founder">
        @if($founderMedia)
        <a class="vt-split__media vt-split__media--contain" href="{{ $founderLink }}" tabindex="-1" aria-hidden="true">
          <img src="{{ $B::url($founderMedia->image) }}" alt="" loading="lazy">
        </a>
        @endif
        <div class="vt-split__copy">
          @if($founderBanner->subtitle)<p class="vt-kicker">{{ $founderBanner->subtitle }}</p>@endif
          <h2 class="vt-h3">{{ $founderBanner->title }}</h2>
          @if($founderBanner->description)<p class="vt-body vt-body--sm">{{ $founderBanner->description }}</p>@endif
          @if($founderBanner->button_text)<div class="vt-split__links"><a class="vt-link" href="{{ $founderLink }}">{{ $founderBanner->button_text }}</a></div>@endif
        </div>
      </section>
      @endif

      {{-- Wisdom for Harmonious Living (latest journal articles) --}}
      @if($journalPosts->isNotEmpty())
      <section class="vt-wisdom" aria-labelledby="vt-wisdom-title">
        <div class="vt-container">
          <div class="vt-wisdom__head">
            <h2 class="vt-h2" id="vt-wisdom-title">Wisdom for Harmonious Living</h2>
            <p class="vt-body">Explore expert insights on Rudraksha, Vastu, Yantras, crystals, and meaningful spiritual practices.</p>
          </div>
          <div class="vt-wisdom__grid">
            @foreach($journalPosts as $post)
              <article class="vt-article">
                <a class="vt-article__img" href="{{ route('blog.single', $post->slug) }}" tabindex="-1" aria-hidden="true"><img src="{{ $post->image_url }}" alt="" loading="lazy"></a>
                <p class="vt-article__kicker">{{ optional($post->newsType)->title ?? 'Journal' }}</p>
                <h3 class="vt-article__title"><a href="{{ route('blog.single', $post->slug) }}">{{ $post->title }}</a></h3>
                <p class="vt-article__text">{{ $post->excerpt }}</p>
                <a class="vt-article__link" href="{{ route('blog.single', $post->slug) }}">Read article <span aria-hidden="true">→</span></a>
              </article>
            @endforeach
          </div>
        </div>
      </section>
      @endif

      {{-- Instagram (Sections & Images → Home — Instagram Heading / Posts) --}}
      @if($instaIntro || $instaPosts->isNotEmpty())
      <section class="vt-insta vt-journal" aria-labelledby="vt-insta-title">
        <div class="vt-journal__inner">
          <p class="vt-journal__eyebrow">
            <svg width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.2" aria-hidden="true"><rect x="1.33" y="1.33" width="13.33" height="13.33" rx="3.33"/><circle cx="8" cy="8" r="2.67"/><path d="M11.67 4.33h.01" stroke-linecap="round" stroke-width="1.6"/></svg>
            <span>{{ $instaIntro?->subtitle && strcasecmp(trim($instaIntro->subtitle), 'On Instagram') !== 0 ? $instaIntro->subtitle : 'Our Instagram Journal' }}</span>
            <span class="vt-journal__line" aria-hidden="true"></span>
          </p>
          <div class="vt-journal__head">
            <h2 class="vt-journal__title" id="vt-insta-title">{{ $instaIntro?->title ?: 'Moments of mindful living' }}</h2>
            <div class="vt-journal__follow">
              <a class="vt-journal__btn" href="{{ $instaUrl }}" target="_blank" rel="noopener">
                <svg width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.2" aria-hidden="true"><rect x="1.33" y="1.33" width="13.33" height="13.33" rx="3.33"/><circle cx="8" cy="8" r="2.67"/><path d="M11.67 4.33h.01" stroke-linecap="round" stroke-width="1.6"/></svg>
                <span>{{ $instaIntro?->button_text ?: 'Follow on Instagram' }}</span>
              </a>
              <span class="vt-journal__handle notranslate" translate="no">{{ $instaHandle }}</span>
            </div>
          </div>
          @if($instaPosts->isNotEmpty())
          <div class="vt-journal__grid">
            @foreach($instaPosts as $post)
              @php $kind = \App\Support\Instagram::kind($post['url']); @endphp
              <a class="vt-journal__card" href="{{ $post['url'] }}" target="_blank" rel="noopener" aria-label="{{ $post['caption'] }} — view this {{ strtolower($kind) }} on Instagram (opens in a new tab)">
                <span class="vt-journal__media">
                  @if($post['image'])
                    <img src="{{ $post['image'] }}" alt="" loading="lazy" width="370" height="376">
                  @else
                    <span class="vt-insta__placeholder" aria-hidden="true"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="1" fill="currentColor" stroke="none"/></svg></span>
                  @endif
                  @if($kind === 'Reel')<span class="vt-insta__play" aria-hidden="true"><svg viewBox="0 0 24 24" width="22" height="22"><path d="M8 5.2v13.6L19 12z" fill="currentColor"/></svg></span>@endif
                </span>
                <span class="vt-journal__caption">
                  <span class="vt-journal__name">{{ $post['caption'] }}</span>
                  <span class="vt-journal__more">Explore on Instagram <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M8.5 6.15s6.94-.54 7.92.43c.97.98.43 7.92.43 7.92M16 7 6 17"/></svg></span>
                </span>
              </a>
            @endforeach
          </div>
          @endif
        </div>
      </section>
      @endif
    </main>

    @include('frontend.partials.site-footer')
  </div>

  <script src="{{ asset('frontend/js/jquery.js') }}"></script>
  <script src="{{ asset('frontend/js/popper.min.js') }}"></script>
  <script src="{{ asset('frontend/js/bootstrap.min.js') }}"></script>
  <script src="{{ asset('frontend/js/bootstrap-select.min.js') }}"></script>
  <script src="{{ asset('frontend/js/mmenu.js') }}"></script>
  <script src="{{ asset('frontend/js/lazysize.min.js') }}"></script>
  <script src="{{ asset('frontend/js/jquery.magnific-popup.min.js') }}"></script>
  <script src="{{ asset('frontend/js/jquery.odometer.min.js') }}"></script>
  <script src="{{ asset('frontend/js/jquery.appear.js') }}"></script>
  <script src="{{ asset('frontend/js/swiper-bundle.min.js') }}"></script>
  <script src="{{ asset('frontend/js/jquery.countdown.js') }}"></script>
  <script src="{{ asset('frontend/js/jarallax.js') }}"></script>
  <script src="{{ asset('frontend/js/wow.min.js') }}"></script>
  <script src="{{ asset('frontend/js/script.js?v=vastu-3') }}"></script>
  <script src="{{ asset('frontend/js/site-drawers.js') }}?v=vastu-6"></script>
  <script src="{{ asset('frontend/js/frontend-search.js') }}?v=live-3"></script>
  @include('frontend.partials.cart-script')
</body>

</html>
