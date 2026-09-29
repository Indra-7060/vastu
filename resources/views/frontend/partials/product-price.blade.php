@php
    $priceProduct = $product ?? $item;
    $priceMrp = (float) $priceProduct->mrp;
    $priceSelling = (float) $priceProduct->selling_price;
    $priceDiscount = $priceMrp > $priceSelling && $priceMrp > 0
        ? (int) round((($priceMrp - $priceSelling) / $priceMrp) * 100)
        : 0;
@endphp
<span class="pp-price {{ $priceSelling > 0 ? 'pp-price--has-selling-price' : 'pp-price--mrp-only' }}">
    @if($priceSelling <= 0 && $priceMrp <= 0)
        <span class="pp-price__selling pp-price--request">Price on request</span>
    @elseif($priceSelling <= 0)
        <span class="pp-price__mrp">{{ \App\Support\Money::format($priceMrp) }}</span>
    @elseif($priceDiscount)
        <span class="pp-price__mrp"><s>{{ \App\Support\Money::format($priceMrp) }}</s></span>
        <span class="pp-price__discount">-{{ $priceDiscount }}%</span>
        <span class="pp-price__selling">{{ \App\Support\Money::format($priceSelling) }}</span>
    @else
        <span class="pp-price__selling">{{ \App\Support\Money::format($priceSelling) }}</span>
    @endif
</span>
