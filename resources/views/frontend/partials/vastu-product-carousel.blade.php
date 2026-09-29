{{-- Homepage product carousel: glides endlessly, pauses on hover, stops when a product is opened (vastu.js).
     Expects $title, $items (name, sub, image_url, url, optional price/badge) and $id. --}}
<section class="vt-products" aria-labelledby="{{ $id }}-title">
  <div class="vt-container">
    <h2 class="vt-h3" id="{{ $id }}-title">{{ $title }}</h2>
    <div class="vt-carousel" data-vt-carousel data-vt-marquee>
      <div class="vt-carousel__track" data-vt-track>
        @foreach($items as $item)
          <div class="vt-carousel__slide" aria-label="{{ $loop->iteration }} of {{ $loop->count }}">
            <a class="vt-card" href="{{ $item['url'] ?? url('/'.$item['slug']) }}">
              <span class="vt-card__img"><img src="{{ $item['image_url'] ?? asset('vastu/images/'.$item['image']) }}" alt="{{ $item['name'] }}" loading="lazy" width="192" height="192"></span>
              <span class="vt-card__meta">
                @php $badge = $item['badge'] ?? false; @endphp
                <span class="vt-card__badge{{ is_string($badge) ? '' : ' vt-card__badge--empty' }}">{{ is_string($badge) ? $badge : '' }}</span>
                <span class="d-block">{{ $item['name'] }}</span>
                <span class="d-block vt-card__sub">{{ $item['sub'] }}</span>
                <span class="d-block">{{ $item['price'] ?? 'Explore' }}</span>
              </span>
            </a>
          </div>
        @endforeach
      </div>
    </div>
  </div>
</section>
