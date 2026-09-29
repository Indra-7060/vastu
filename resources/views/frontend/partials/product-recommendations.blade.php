{{-- Product recommendation row (product page, cart). Expects $items, $heading, $titleId, optional $variant. --}}
@if(($items ?? collect())->isNotEmpty())
<section class="vt-home vt-recs vt-recs--{{ $variant ?? 'related' }}" aria-labelledby="{{ $titleId }}">
  <h2 class="vt-recs__title" id="{{ $titleId }}">{{ \Illuminate\Support\Str::title(strtolower($heading)) }}</h2>
  <div class="vt-plp__grid vt-recs__grid">
    @foreach($items->take(4) as $item)
      @include('frontend.partials.vastu-product-tile', ['product' => $item])
    @endforeach
  </div>
</section>
@endif
