@php
  $pageTitle = 'Vastutathastu — Sacred Living';
  $vtHeaderOverlay = true;
  $img = fn (string $file) => asset('vastu/images/'.$file);

  // Homepage data comes from the database (see FrontendController@home).
  // Editable sections come from Admin → Home Content → Sections & Images.
  $B = \App\Support\SiteBanners::class;
  $heroSlides = $B::all('home_hero');
  // Hero slides are pictures only (text, logo and button are designed into the images):
  // the header sits solid above them instead of over them.
  $heroDesigned = $heroSlides->isNotEmpty();
  if ($heroDesigned) { $vtHeaderOverlay = false; }
  // Hero shape = the most common shape of the slide PICTURES (desktop images on desktop, mobile images on
  // phones). Videos are ignored (they are trimmed to fit). A picture of another shape is shown whole (no crop).
  $sizeOf = function (?string $path) {
      if (! $path || str_starts_with($path, 'http')) return null;
      $size = @getimagesize(public_path('storage/'.ltrim($path, '/')));
      return $size && $size[0] > 0 && $size[1] > 0 ? [$size[0], $size[1]] : null;
  };
  $heroSizes = $heroSlides->map(function ($slide) use ($B, $sizeOf) {
      $m = $B::media($slide);
      if (! $m || $m->is_video) return ['d' => null, 'm' => null, 'mobileOnly' => null];
      $d = $sizeOf($m->image);
      $mob = $sizeOf($m->mobile_image);
      return ['d' => $d, 'm' => $mob ?: $d, 'mobileOnly' => $mob];
  })->values();
  $commonShape = function (string $key, ?array $fallback) use ($heroSizes) {
      $shapes = $heroSizes->pluck($key)->filter();
      if ($shapes->isEmpty()) return $fallback;
      return $shapes->groupBy(fn ($s) => round($s[0] / $s[1], 2))->sortByDesc(fn ($g) => $g->count())->first()->first();
  };
  $shapeD = $commonShape('d', [16, 9]);
  // phones: the shape of the real mobile images when there are any
  $shapeM = $commonShape('mobileOnly', null) ?? $commonShape('m', $shapeD);
  $heroAspectD = $shapeD[0].' / '.$shapeD[1];
  $heroAspectM = $shapeM[0].' / '.$shapeM[1];
  $offShape = fn (?array $s, array $hero) => $s && abs(($s[0] / $s[1]) / ($hero[0] / $hero[1]) - 1) > 0.08;
  $hero = $heroSlides->first();
  $heroMedia = $B::media($hero);
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
  // Live feed: the newest posts straight from Instagram (when INSTAGRAM_ACCESS_TOKEN is set);
  // otherwise the posts chosen in Admin → Sections & Images are shown.
  if ($livePosts = \App\Support\InstagramFeed::latest(3)) {
      $instaPosts = collect($livePosts)->values()->map(fn ($p, $i) => array_merge($p, [
          'caption' => $p['caption'] !== '' ? $p['caption'] : $instaCaptions[$i % 3],
      ]));
  }
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
  <link rel="stylesheet" href="{{ asset('frontend/css/style.css') }}?v=vastu-4">
  <link rel="stylesheet" href="{{ asset('frontend/css/custom.css') }}?v=vastu-3">
  <link rel="stylesheet" href="{{ asset('frontend/css/site-drawers.css') }}?v=vastu-2">
  <link rel="stylesheet" href="{{ asset('vastu/css/vastu.css') }}?v=205">
  <title>{{ $pageTitle }}</title>
  <link rel="icon" type="image/svg+xml" href="{{ asset('vastu/images/favicon.svg') }}?v=vt2">
  <link rel="icon" type="image/png" sizes="50x50" href="{{ asset('vastu/images/favicon-50.png') }}?v=vt2">
  <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('vastu/images/favicon-32.png') }}?v=vt2">
  <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('vastu/images/favicon-16.png') }}?v=vt2">
  <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('vastu/images/apple-touch-icon.png') }}?v=vt2">
  <link rel="manifest" href="{{ asset('site.webmanifest') }}?v=vt2">
  <meta name="theme-color" content="#1c75bc">
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
      <section class="vt-hero{{ $heroSlides->count() > 1 ? ' vt-hero--slideshow' : '' }}{{ $heroDesigned ? ' vt-hero--below-header' : '' }}" style="--hero-ar-d: {{ $heroAspectD }}; --hero-ar-m: {{ $heroAspectM }};" aria-roledescription="carousel" aria-label="Featured" data-vt-hero tabindex="-1">
        <h1 class="vt-sr-only" id="vt-hero-title">Vastutathastu — Sacred Living</h1>
        <div class="vt-hero__viewport">
          <div class="vt-hero__track" data-vt-hero-track>
            @foreach($heroSlides as $slide)
              @php
                // Each slide is a picture (or video) with an optional link; no text is added on top.
                $media = $B::media($slide);
                $mobileUrl = $media && $media->mobile_image ? $B::url($media->mobile_image) : null;
                $slideLink = trim((string) $slide->button_link) !== '' ? $B::link($slide->button_link) : null;
                $slideName = ($slide->title && $slide->title !== 'Hero slide') ? $slide->title : 'Vastutathastu';
                // "Show text & buttons" on: the website draws label, heading and buttons over the picture / video
                $withText = $slide->show_text;
                $heading = ($slide->title && $slide->title !== 'Hero slide') ? $slide->title : null;
              @endphp
              @if($withText)
              <div class="vt-hero__slide vt-hero__slide--text{{ $loop->first ? ' is-selected' : '' }}" role="group" aria-roledescription="slide" aria-label="{{ $loop->iteration }} of {{ $loop->count }}" data-vt-hero-slide>
                @if($media && $media->is_video)
                  <video class="vt-hero__media" autoplay muted loop playsinline preload="{{ $loop->first ? 'metadata' : 'none' }}" @if($media->mobile_image) poster="{{ $mobileUrl }}?v={{ @filemtime(public_path('storage/'.$media->mobile_image)) }}" @endif aria-hidden="true">
                    <source src="{{ $B::url($media->image) }}?v={{ @filemtime(public_path('storage/'.$media->image)) }}" type="{{ $media->video_mime_type }}">
                  </video>
                @elseif($media)
                  <picture>
                    @if($mobileUrl)<source media="(max-width: 767px)" srcset="{{ $mobileUrl }}">@endif
                    <img class="vt-hero__media" src="{{ $B::url($media->image) }}" alt="" draggable="false" @unless($loop->first) loading="lazy" @endunless>
                  </picture>
                @endif
                @if($slide->subtitle || $heading || $slide->button_text || $slide->button_text_2)
                <div class="vt-hero__shade"></div>
                <div class="vt-hero__content">
                  @if($slide->subtitle)<p class="vt-hero__kicker">{{ $slide->subtitle }}</p>@endif
                  @if($heading)<h2 class="vt-hero__title">{{ $heading }}</h2>@endif
                  @if($slide->button_text || $slide->button_text_2)
                  <div class="vt-hero__actions">
                    @if($slide->button_text)<a class="vt-btn vt-btn--solid" href="{{ $B::link($slide->button_link, route('shop')) }}">{{ $slide->button_text }}</a>@endif
                    @if($slide->button_text_2)<a class="vt-btn vt-btn--ghost" href="{{ $B::link($slide->button_link_2) }}">{{ $slide->button_text_2 }}</a>@endif
                  </div>
                  @endif
                </div>
                @endif
              </div>
              @else
              @php $sz = $heroSizes[$loop->index] ?? ['d' => null, 'm' => null]; @endphp
              <div class="vt-hero__slide vt-hero__slide--designed{{ $loop->first ? ' is-selected' : '' }}{{ $media && $media->is_video ? ' vt-hero__slide--video' : '' }}{{ $offShape($sz['d'], $shapeD) ? ' vt-hero__slide--fit-d' : '' }}{{ $offShape($sz['m'], $shapeM) ? ' vt-hero__slide--fit-m' : '' }}" role="group" aria-roledescription="slide" aria-label="{{ $loop->iteration }} of {{ $loop->count }}" data-vt-hero-slide>
                @if($media && $media->is_video)
                  <video class="vt-hero__media" autoplay muted loop playsinline preload="{{ $loop->first ? 'metadata' : 'none' }}" @if($media->mobile_image) poster="{{ $mobileUrl }}?v={{ @filemtime(public_path('storage/'.$media->mobile_image)) }}" @endif aria-hidden="true">
                    <source src="{{ $B::url($media->image) }}?v={{ @filemtime(public_path('storage/'.$media->image)) }}" type="{{ $media->video_mime_type }}">
                  </video>
                  @if($slideLink)<a class="vt-hero__designed" href="{{ $slideLink }}" aria-label="{{ $slideName }}" draggable="false"></a>@endif
                @elseif($media)
                  {{-- the whole picture (desktop or mobile version), never cropped, over a soft blurred fill --}}
                  <div class="vt-hero__ambient" style="--amb-d:url('{{ $B::url($media->image) }}');@if($mobileUrl)--amb-m:url('{{ $mobileUrl }}');@endif" aria-hidden="true"></div>
                  @if($slideLink)<a class="vt-hero__designed" href="{{ $slideLink }}" draggable="false">@else<div class="vt-hero__designed">@endif
                    <picture>
                      @if($mobileUrl)<source media="(max-width: 767px)" srcset="{{ $mobileUrl }}">@endif
                      <img src="{{ $B::url($media->image) }}" alt="{{ $slideName }}" draggable="false" @unless($loop->first) loading="lazy" @endunless>
                    </picture>
                  @if($slideLink)</a>@else</div>@endif
                @endif
              </div>
              @endif
            @endforeach
          </div>
        </div>
        @if($heroSlides->count() > 1)
          <div class="vt-hero__dots" role="tablist" aria-label="Choose slide">
            @foreach($heroSlides as $slide)
              <button type="button" class="vt-hero__dot{{ $loop->first ? ' is-selected' : '' }}" role="tab" aria-selected="{{ $loop->first ? 'true' : 'false' }}" aria-label="Slide {{ $loop->iteration }}" data-vt-hero-dot="{{ $loop->index }}"></button>
            @endforeach
          </div>
        @endif
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

      <script>
        // Homepage refresh: open directly on "Shop by Category" before the first paint (no flash of the hero, no visible scroll).
        (function () {
          if (!window.vtHomeReload) return;
          var cats = document.querySelector('.vt-categories'), bar = document.querySelector('[data-vt-header]'), root = document.documentElement;
          if (!cats) return;
          root.style.scrollBehavior = 'auto';
          var hb = bar ? bar.offsetHeight : 0, y = window.pageYOffset, grid = cats.querySelector('.vt-cat-grid');
          var top = cats.getBoundingClientRect().top + y - hb;
          if (grid) {
            var gr = grid.getBoundingClientRect(), room = window.innerHeight - hb;
            // Whole section fits → show it all; otherwise make sure both tile rows are fully on screen.
            if (gr.bottom + y - (top + hb) > room) top = gr.top + y - hb - Math.max(12, (room - gr.height) / 2);
          }
          window.scrollTo(0, Math.max(0, Math.round(top)));
          root.style.scrollBehavior = '';
        })();
      </script>

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
            @include('frontend.partials.brand-icon', ['name' => 'instagram', 'size' => 16])
            <span>{{ $instaIntro?->subtitle && strcasecmp(trim($instaIntro->subtitle), 'On Instagram') !== 0 ? $instaIntro->subtitle : 'Our Instagram Journal' }}</span>
            <span class="vt-journal__line" aria-hidden="true"></span>
          </p>
          <div class="vt-journal__head">
            <h2 class="vt-journal__title" id="vt-insta-title">{{ $instaIntro?->title ?: 'Moments of mindful living' }}</h2>
            <div class="vt-journal__follow">
              <a class="vt-journal__btn" href="{{ $instaUrl }}" target="_blank" rel="noopener">
                @include('frontend.partials.brand-icon', ['name' => 'instagram', 'size' => 18])
                <span>{{ $instaIntro?->button_text ?: 'Follow on Instagram' }}</span>
              </a>
              <span class="vt-journal__handle notranslate" translate="no">{{ $instaHandle }}</span>
            </div>
          </div>
          @if($instaPosts->isNotEmpty())
          <div class="vt-journal__grid">
            @foreach($instaPosts as $post)
              @php $kind = $post['kind'] ?? \App\Support\Instagram::kind($post['url']); @endphp
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
      {{-- "Our Clients" photo strip: glides endlessly like "Sacred Energy" (photos from Admin → Gallery) --}}
      @if($galleryStrip->count() > 1)
        <section class="vt-products vt-photostrip" aria-labelledby="vt-photostrip-title">
          <div class="vt-container">
            <h2 class="vt-h3" id="vt-photostrip-title">Our Clients</h2>
          </div>
          <div class="vt-carousel" data-vt-carousel data-vt-marquee>
            <div class="vt-carousel__track" data-vt-track>
              @foreach($galleryStrip as $photo)
                <div class="vt-carousel__slide">
                  <a class="vt-card vt-card--photo" href="{{ $photo['url'] }}" aria-label="{{ $photo['alt'] }} — open gallery">
                    <span class="vt-card__img"><img src="{{ $photo['image'] }}" alt="{{ $photo['alt'] }}" loading="lazy" decoding="async" width="400" height="400"></span>
                  </a>
                </div>
              @endforeach
            </div>
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
