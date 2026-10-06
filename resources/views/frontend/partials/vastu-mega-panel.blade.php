{{-- One desktop mega-menu flyout. Expects $item (label, url, mega, slug?) and $megaMenu. --}}
@php
  $panel = null;
  $columns = [];
  $cards = collect();
  $teaser = null;

  $cardsTitle = null;
  $navRow = [];
  if ($item['mega'] === 'new') {
      // Categories in two columns, working shortcuts, then the "Top picks" product cards.
      $catLinks = $megaMenu['categories']->map(fn ($c) => ['label' => $c->title, 'url' => $c->frontendUrl()])->values();
      $half = (int) ceil($catLinks->count() / 2);
      $columns[] = ['title' => 'Shop by category', 'links' => $catLinks->take($half)->all()];
      if ($catLinks->count() > $half) {
          $columns[] = ['title' => "\u{00A0}", 'links' => $catLinks->slice($half)->values()->all()];
      }
      $columns[] = ['title' => 'Highlights', 'links' => [
          ['label' => 'All products', 'url' => route('shop')],
          ['label' => 'Top picks', 'url' => route('shop', ['featured' => 1])],
          ['label' => 'Under ₹1,000', 'url' => route('shop', ['price' => ['under-1000']])],
          ['label' => '₹1,000 – ₹2,500', 'url' => route('shop', ['price' => ['1000-2500']])],
          ['label' => 'Book a consultation', 'url' => route('info', 'book-a-consultation')],
      ]];
      $cards = $megaMenu['picks'];
      $cardsTitle = 'Top picks for you';
  } elseif ($item['mega'] === 'category' && isset($megaMenu['byslug'][$item['slug']])) {
      $data = $megaMenu['byslug'][$item['slug']];
      $category = $data['category'];
      if ($data['types']->isNotEmpty()) {
          $columns[] = ['title' => 'Shop by type', 'links' => $data['types']->map(fn ($t) => ['label' => $t->title, 'url' => $category->frontendUrl().'?type[]='.$t->slug])->all()];
      }
      if ($data['materials']->isNotEmpty()) {
          $columns[] = ['title' => 'Shop by material', 'links' => $data['materials']->map(fn ($m) => ['label' => $m, 'url' => $category->frontendUrl().'?material[]='.urlencode($m)])->all()];
      }
      $columns[] = ['title' => 'Featured', 'links' => [
          ['label' => 'All '.$category->title, 'url' => $category->frontendUrl()],
          ['label' => 'New arrivals', 'url' => $category->frontendUrl().'?badge[]=New'],
          ['label' => 'Bestsellers', 'url' => $category->frontendUrl().'?badge[]=Bestseller'],
      ]];
      $cards = $data['products'];
      $cardsTitle = 'Recommended';
  } elseif ($item['mega'] === 'services') {
      foreach ($megaMenu['services'] as $group) {
          // A service with the same name as its group becomes the group heading's link (no duplicate line).
          $same = $group['products']->first(fn ($p) => strcasecmp($p->title, $group['title']) === 0);
          $links = $group['products']->reject(fn ($p) => $same && $p->id === $same->id)
              ->map(fn ($p) => ['label' => $p->title, 'url' => route('shop.single', $p->slug)])->values()->all();
          if ($same) {
              $links[] = ['label' => 'Book a consultation', 'url' => route('info', 'book-a-consultation')];
          }
          $columns[] = ['title' => $group['title'], 'url' => $same ? route('shop.single', $same->slug) : route('info', $group['page']), 'links' => $links];
      }
      $teaser = ['image' => asset('vastu/images/founder-services.jpg'), 'title' => 'Consult Makrannd Sardeshmukh', 'url' => route('info', 'book-a-consultation')];
  } elseif ($item['mega'] === 'gallery') {
      // Gallery categories as one row of links, like the main menu.
      $navRow = \App\Support\GalleryCategories::links();
  } elseif ($item['mega'] === 'world') {
      $columns[] = ['title' => 'World of Vastutathastu', 'links' => [
          ['label' => 'Our world', 'url' => route('about')],
          ['label' => 'Meet the founder', 'url' => route('founder')],
          ['label' => 'Our story', 'url' => route('info', 'our-story')],
          ['label' => '22+ years of guidance', 'url' => route('info', '22-years-of-guidance')],
          ['label' => 'Testimonials', 'url' => route('info', 'testimonials')],
      ]];
      $columns[] = ['title' => 'Guidance', 'links' => [
          ['label' => 'Vastu consultation', 'url' => route('info', 'vastu-consultation')],
          ['label' => 'Astrology', 'url' => route('info', 'astrology')],
          ['label' => 'Numerology', 'url' => route('info', 'numerology')],
          ['label' => 'Book a consultation', 'url' => route('info', 'book-a-consultation')],
      ]];
      $teaser = ['image' => asset('vastu/images/founder-makrannd-sardeshmukh.jpg'), 'title' => 'Meet Makrannd Sardeshmukh', 'url' => route('founder')];
  }
@endphp
@if($columns)
  <div class="vt-mega" data-vt-mega-panel role="region" aria-label="{{ $item['label'] }}">
    <div class="vt-mega__inner">
      <div class="vt-mega__cols">
        @foreach($columns as $column)
          <div class="vt-mega__col">
            @if(!empty($column['url']))
              <p class="vt-mega__title"><a href="{{ $column['url'] }}">{{ $column['title'] }}</a></p>
            @else
              <p class="vt-mega__title">{{ $column['title'] }}</p>
            @endif
            <ul>
              @foreach($column['links'] as $link)
                <li style="--i: {{ $loop->index }}"><a href="{{ $link['url'] }}">{{ $link['label'] }}</a></li>
              @endforeach
            </ul>
          </div>
        @endforeach
      </div>
      @if($cards->isNotEmpty())
        <div class="vt-mega__picks">
          @if($cardsTitle)<p class="vt-mega__title">{{ $cardsTitle }}</p>@endif
          <div class="vt-mega__cards">
            @foreach($cards as $product)
              @php $price = (float) $product->selling_price > 0 ? (float) $product->selling_price : (float) $product->mrp; @endphp
              <a class="vt-mega__card" href="{{ route('shop.single', $product->slug) }}" style="--i: {{ $loop->index }}">
                <span class="vt-mega__img">
                  <img src="{{ $product->tile_image_url }}" alt="" loading="lazy" width="320" height="320">
                  <span class="vt-mega__cta" aria-hidden="true">Shop now</span>
                </span>
                <span class="vt-mega__name">{{ $product->title }}</span>
                <span class="vt-mega__price">{{ $price > 0 ? '₹ '.number_format($price, 2) : 'Price on request' }}</span>
              </a>
            @endforeach
          </div>
        </div>
      @elseif($teaser)
        <a class="vt-mega__teaser" href="{{ $teaser['url'] }}">
          <span class="vt-mega__img vt-mega__img--wide"><img src="{{ $teaser['image'] }}" alt="" loading="lazy"></span>
          <span class="vt-mega__name">{{ $teaser['title'] }}</span>
        </a>
      @endif
    </div>
  </div>
@elseif($navRow)
  <div class="vt-mega vt-mega--row" data-vt-mega-panel role="region" aria-label="{{ $item['label'] }}">
    <ul class="vt-mega__row">
      @foreach($navRow as $link)
        <li style="--i: {{ $loop->index }}"><a href="{{ $link['url'] }}"@if(request()->routeIs('gallery') && (request()->route('category') ?: 'our-product-users') === $link['slug']) aria-current="page"@endif>{{ $link['label'] }}</a></li>
      @endforeach
    </ul>
  </div>
@endif
