{{-- Gallery (Admin → Home Content → Sections & Images → Gallery Photos). --}}
@php $pageTitle = 'Gallery - Vastutathastu'; @endphp
<!DOCTYPE html>
<html dir="ltr" lang="en">

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="Moments from Vastutathastu — consultations, events and visits with Makrannd Sardeshmukh.">
  @if($photos->isEmpty())<meta name="robots" content="noindex">@endif
  <link rel="stylesheet" href="{{ asset('frontend/css/bootstrap.min.css') }}">
  <link rel="stylesheet" href="{{ asset('frontend/css/style.css') }}?v=vastu-4">
  <link rel="stylesheet" href="{{ asset('frontend/css/custom.css') }}?v=vastu-3">
  <link rel="stylesheet" href="{{ asset('frontend/css/site-drawers.css') }}?v=vastu-2">
  <link rel="stylesheet" href="{{ asset('vastu/css/vastu.css') }}?v=120">
  <title>{{ $pageTitle }}</title>
  <link rel="icon" type="image/png" href="{{ asset('vastu/images/favicon.png') }}">
  <link rel="apple-touch-icon" href="{{ asset('vastu/images/favicon.png') }}">
</head>

<body class="vt-page">
  <div class="wrapper ovh">
    @include('frontend.partials.site-header')

    <main class="vt-home vt-gallery">
      <section class="vt-gallery__head">
        <h1 class="vt-h2">Gallery</h1>
      </section>

      @if($photos->isEmpty())
        <div class="vt-content-page__inner">
          @include('frontend.partials.vastu-empty-state', [
              'title' => 'Photos coming soon',
              'text' => 'Moments from our consultations and events will be shared here soon. Please check back later.',
              'icon' => 'info',
          ])
        </div>
      @else
        <ul class="vt-gallery__grid" data-vt-gallery>
          @foreach($photos as $photo)
            <li class="vt-gallery__item">
              <button type="button" class="vt-gallery__open" data-vt-gallery-open="{{ $loop->index }}" aria-label="View photo {{ $loop->iteration }}@if($photo['caption']): {{ $photo['caption'] }}@endif">
                <img src="{{ $photo['image'] }}" alt="{{ $photo['caption'] }}" loading="lazy">
              </button>
              @if($photo['caption'])<p class="vt-gallery__caption">{{ $photo['caption'] }}</p>@endif
            </li>
          @endforeach
        </ul>

        <div class="vt-gallery__viewer" data-vt-gallery-viewer role="dialog" aria-modal="true" aria-label="Photo viewer" hidden>
          <button type="button" class="vt-gallery__close" data-vt-gallery-close aria-label="Close">&times;</button>
          <button type="button" class="vt-gallery__nav vt-gallery__nav--prev" data-vt-gallery-prev aria-label="Previous photo">&#8249;</button>
          <figure><img src="" alt="" data-vt-gallery-img><figcaption data-vt-gallery-caption></figcaption></figure>
          <button type="button" class="vt-gallery__nav vt-gallery__nav--next" data-vt-gallery-next aria-label="Next photo">&#8250;</button>
        </div>
      @endif
    </main>

    @include('frontend.partials.site-footer')
  </div>

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
  @if($photos->isNotEmpty())
  <script>
    // Full-screen photo viewer: click a photo, arrows / keys to move, Esc or × to close.
    (function () {
      var viewer = document.querySelector('[data-vt-gallery-viewer]');
      var img = viewer.querySelector('[data-vt-gallery-img]');
      var cap = viewer.querySelector('[data-vt-gallery-caption]');
      var items = Array.prototype.slice.call(document.querySelectorAll('[data-vt-gallery-open]'));
      var index = 0, last = null;
      function show(i) {
        index = (i + items.length) % items.length;
        var src = items[index].querySelector('img');
        img.src = src.src;
        img.alt = src.alt;
        cap.textContent = src.alt;
      }
      function close() { viewer.hidden = true; document.body.style.overflow = ''; if (last) last.focus(); }
      items.forEach(function (btn, i) {
        btn.addEventListener('click', function () { last = btn; show(i); viewer.hidden = false; document.body.style.overflow = 'hidden'; viewer.querySelector('[data-vt-gallery-close]').focus(); });
      });
      viewer.querySelector('[data-vt-gallery-close]').addEventListener('click', close);
      viewer.querySelector('[data-vt-gallery-prev]').addEventListener('click', function () { show(index - 1); });
      viewer.querySelector('[data-vt-gallery-next]').addEventListener('click', function () { show(index + 1); });
      viewer.addEventListener('click', function (e) { if (e.target === viewer) close(); });
      document.addEventListener('keydown', function (e) {
        if (viewer.hidden) return;
        if (e.key === 'Escape') close();
        else if (e.key === 'ArrowLeft') show(index - 1);
        else if (e.key === 'ArrowRight') show(index + 1);
      });
    })();
  </script>
  @endif
</body>

</html>
