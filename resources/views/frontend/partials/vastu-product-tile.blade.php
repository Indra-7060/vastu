{{-- Product tile (listing grid). Expects $product. --}}
@php
  $images = collect([$product->tile_image_url, $product->featured_image_2 ? $product->hover_image_url : null])
      ->merge($product->relationLoaded('images') ? $product->images->map(fn ($image) => $product->imageUrl($image->image)) : [])
      ->filter()->unique()->take(3)->values();
  $price = (float) $product->selling_price > 0 ? (float) $product->selling_price : (float) $product->mrp;
  $mrp = (float) $product->mrp;
  $flag = $product->is_new_arrival ? 'New' : null;
  $chip = $product->badge && $product->badge !== 'New' ? $product->badge : null;
  $subtitle = collect([$product->material, $product->subCategory?->title])->filter()->unique()->implode(', ');
  $url = route('shop.single', $product->slug);
@endphp
<article class="vt-tile" data-vt-reveal>
  <a class="vt-tile__link" href="{{ $url }}">
    <div class="vt-tile__media" data-vt-swiper data-count="{{ $images->count() }}">
      @foreach($images as $i => $src)
        <img class="vt-tile__img{{ $i === 0 ? ' is-active' : '' }}" src="{{ $src }}" alt="{{ $i === 0 ? $product->title : '' }}" loading="lazy" width="600" height="600">
      @endforeach
      @if($images->count() > 1)
        <span class="vt-tile__progress" aria-hidden="true"><span class="vt-tile__progress-bar" style="width: {{ 100 / $images->count() }}%"></span></span>
      @endif
    </div>
    <div class="vt-tile__info">
      @if($chip)
        <span class="vt-tile__chip">{{ $chip }}</span>
      @endif
      @if($flag)
        <span class="vt-tile__flag">{{ $flag }}</span>
      @endif
      <h3 class="vt-tile__name">{{ $product->title }}</h3>
      @if($subtitle)
        <p class="vt-tile__sub">{{ $subtitle }}</p>
      @endif
      <p class="vt-tile__price">
        <span>{{ $price > 0 ? '₹ '.number_format($price, 2) : 'Price on request' }}</span>
        @if($mrp > $price)
          <s>₹ {{ number_format($mrp, 2) }}</s>
        @endif
      </p>
    </div>
  </a>
  <button class="vt-tile__wish" type="button" data-wishlist-product="{{ $product->id }}" aria-label="Add {{ $product->title }} to wishlist" aria-pressed="false">
    @include('frontend.partials.vt-icon', ['name' => 'heart', 'size' => 20, 'stroke' => 1.6])
  </button>
</article>
