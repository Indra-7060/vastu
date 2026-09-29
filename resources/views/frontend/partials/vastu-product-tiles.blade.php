{{-- Product tiles with optional editorial teasers inserted at fixed grid positions. --}}
@php $teasers = collect($teasers ?? [])->keyBy('position'); @endphp
@foreach($products as $index => $product)
  @if($teasers->has($index))
    @php $teaser = $teasers->get($index); @endphp
    <a class="vt-teaser{{ ($teaser['align'] ?? 'left') === 'right' ? ' vt-teaser--right' : '' }}" href="{{ $teaser['href'] }}" data-vt-reveal>
      <span class="vt-teaser__media"><img src="{{ $teaser['image'] }}" alt="" loading="lazy" style="object-position: {{ $teaser['focus'] ?? 'center' }}"></span>
      <span class="vt-teaser__details">
        <span class="vt-teaser__copy">
          <span class="vt-teaser__title">{{ $teaser['title'] }}</span>
          <span class="vt-teaser__text">{{ $teaser['text'] }}</span>
        </span>
        <span class="vt-teaser__btn">Discover more</span>
      </span>
    </a>
  @endif
  @include('frontend.partials.vastu-product-tile', ['product' => $product])
@endforeach
