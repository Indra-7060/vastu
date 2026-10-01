@php
  $cartItems = $cartItems ?? collect();
  $cartCount = $cartCount ?? 0;
  $cartSubtotalFormatted = $cartSubtotalFormatted ?? '₹ 0.00';
  $shipping = $shipping ?? ['amount_formatted' => 'Free', 'is_free' => true, 'free_shipping_threshold' => 899];
  $cartTotalFormatted = $cartTotalFormatted ?? $cartSubtotalFormatted;
  $discount = $discount ?? ['code' => null, 'amount' => 0, 'amount_formatted' => '₹ 0.00'];
  $hasDiscount = !empty($discount['code']) && ((float) ($discount['amount'] ?? 0) > 0 || !empty($discount['free_shipping']));
@endphp
<section class="shop-cart pt30 pp-cart-section">
  <div class="container">
    @if(session('error'))
      <div class="alert alert-warning mb-4">{{ session('error') }}</div>
    @endif
    @if(session('success'))
      <div class="alert alert-success mb-4">{{ session('success') }}</div>
    @endif

    @if($cartItems->isEmpty())
      <div class="pp-cart-empty">
        @include('frontend.partials.vastu-empty-state', [
            'title' => 'Your cart is empty',
            'text' => 'Browse the collection and add pieces you love.',
        ])
      </div>
    @else
      <div class="row mt15">
        <div class="col-xl-8 col-lg-7">
          <div class="shopping_cart_table table-responsive mb-5 mb-xl-0">
            <table class="table table-borderless d-none d-lg-table w-100">
              <thead>
                <tr>
                  <th scope="col">PRODUCT</th>
                  <th scope="col">PRICE</th>
                  <th scope="col">QUANTITY</th>
                  <th scope="col">SUBTOTAL</th>
                  <th scope="col"></th>
                </tr>
              </thead>
              <tbody class="table_body">
                @foreach($cartItems as $item)
                  <tr data-cart-row data-product-id="{{ $item['product_id'] }}" data-color="{{ $item['color'] ?? '' }}" data-size="{{ $item['size'] ?? '' }}" data-package-key="{{ $item['package_key'] ?? '' }}">
                    <td>
                      <div class="cart_list d-flex align-items-center gap-3">
                        <a href="{{ $item['url'] }}">
                          <img src="{{ $item['image'] }}" alt="{{ $item['title'] }}" width="90" height="110" style="object-fit:cover;">
                        </a>
                        <div>
                          <a class="cart_title" href="{{ $item['url'] }}" data-vt-orig="{{ $item['title'] }}">{{ $item['title'] }}</a>
                          @if($item['color'] || $item['size'])
                            <div class="text small mt-1">
                              @if($item['color'])Colour: {{ $item['color'] }}@endif
                              @if($item['color'] && $item['size']) &nbsp;-&nbsp; @endif
                              @if($item['size'])Size: {{ $item['size'] }}@endif
                            </div>
                          @endif
                          @if(!empty($item['package_label']))
                            <div class="text small mt-1">Pack: {{ $item['package_label'] }}</div>
                          @endif
                        </div>
                      </div>
                    </td>
                    <td class="cart_price">
                      <span class="pp-price pp-price--has-selling-price">
                        <span class="pp-price__mrp">
                          @if($item['discount_percent'])
                            <s>{{ $item['mrp_formatted'] }}</s>
                          @else
                            {{ $item['mrp_formatted'] }}
                          @endif
                        </span>
                        @if($item['discount_percent'])
                          <span class="pp-price__discount">-{{ $item['discount_percent'] }}%</span>
                        @endif
                        <span class="pp-price__selling">{{ $item['unit_price_formatted'] }}</span>
                      </span>
                    </td>
                    <td>
                      <div class="quantity-block overflow-hidden">
                        <button type="button" class="quantity-arrow-minus inner_page" data-cart-qty="minus" aria-label="Decrease">
                          <span class="fa fa-minus"></span>
                        </button>
                        <input class="quantity-num inner_page" type="number" min="0" max="{{ $item['max_quantity'] }}" value="{{ $item['quantity'] }}" data-cart-qty-input>
                        <button type="button" class="quantity-arrow-plus inner_page" data-cart-qty="plus" aria-label="Increase">
                          <span class="fas fa-plus"></span>
                        </button>
                      </div>
                    </td>
                    <td class="cart_price" data-cart-line-total>{{ $item['line_total_formatted'] }}</td>
                    <td>
                      <button type="button" class="btn btn-link text-dark p-0" data-cart-remove>Remove</button>
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>

            <div class="cart-table-mobile d-block d-lg-none">
              @foreach($cartItems as $item)
                <div class="d-flex mb-4" data-cart-row data-product-id="{{ $item['product_id'] }}" data-color="{{ $item['color'] ?? '' }}" data-size="{{ $item['size'] ?? '' }}" data-package-key="{{ $item['package_key'] ?? '' }}">
                  <div class="item-thumb">
                    <a href="{{ $item['url'] }}"><img src="{{ $item['image'] }}" alt="{{ $item['title'] }}" width="90"></a>
                  </div>
                  <div class="item-details ms-3 position-relative flex-grow-1">
                    <a class="cart_title" href="{{ $item['url'] }}" data-vt-orig="{{ $item['title'] }}">{{ $item['title'] }}</a>
                    <div class="cart_price mb-1" data-cart-line-total>{{ $item['line_total_formatted'] }}</div>
                    @if($item['color'] || $item['size'])
                      <div class="text mb-2">
                        @if($item['color'])Colour: {{ $item['color'] }}@endif
                        @if($item['color'] && $item['size']) &nbsp;-&nbsp; @endif
                        @if($item['size'])Size: {{ $item['size'] }}@endif
                      </div>
                    @endif
                    @if(!empty($item['package_label']))
                      <div class="text mb-2">Pack: {{ $item['package_label'] }}</div>
                    @endif
                    <div class="quantity-block overflow-hidden mx-0 mb-2">
                      <button type="button" class="quantity-arrow-minus inner_page" data-cart-qty="minus"><span class="fa fa-minus"></span></button>
                      <input class="quantity-num inner_page" type="number" min="0" max="{{ $item['max_quantity'] }}" value="{{ $item['quantity'] }}" data-cart-qty-input>
                      <button type="button" class="quantity-arrow-plus inner_page" data-cart-qty="plus"><span class="fas fa-plus"></span></button>
                    </div>
                    <button type="button" class="remove border-0 bg-transparent p-0" data-cart-remove>Remove</button>
                  </div>
                </div>
                <hr>
              @endforeach
            </div>
          </div>
        </div>

        <div class="col-xl-4 col-lg-5">
          <div class="shop_order_box border p-4">
            <h4 class="title mb-3">Order summary</h4>
            <div class="cart_coupon position-relative mb-3" data-coupon-box>
              <div class="input-group">
                <input class="form-control coupon_input" type="text" name="coupon_code" data-coupon-input placeholder="Coupon code" value="{{ $discount['code'] ?? '' }}" {{ !empty($discount['code']) ? 'readonly' : '' }} aria-label="Coupon code">
                <button class="btn btn-dark" type="button" data-coupon-apply {{ !empty($discount['code']) ? 'hidden' : '' }}>Apply</button>
                <button class="btn btn-outline-dark" type="button" data-coupon-remove {{ empty($discount['code']) ? 'hidden' : '' }}>Remove</button>
              </div>
              <div class="small mt-2" data-coupon-message @if(empty($discount['message'])) hidden @endif>{{ $discount['message'] ?? '' }}</div>
            </div>
            <ul class="list-unstyled mb-4">
              <li class="d-flex justify-content-between mb-2">
                <span data-cart-items-label>Items ({{ $cartCount }})</span>
                <strong data-cart-subtotal>{{ $cartSubtotalFormatted }}</strong>
              </li>
              <li class="d-flex justify-content-between mb-2" data-cart-discount-row @if(!$hasDiscount) hidden @endif>
                <span data-cart-discount-label>Discount{{ !empty($discount['code']) ? ' ('.$discount['code'].')' : '' }}</span>
                <strong data-cart-discount>-{{ $discount['amount_formatted'] ?? '₹ 0.00' }}</strong>
              </li>
              <li class="d-flex justify-content-between mb-2">
                <span>Shipping</span>
                <span data-cart-shipping>{{ $shipping['amount_formatted'] }}</span>
              </li>
              <li class="d-flex justify-content-between border-top pt-3 mt-2">
                <span>Total</span>
                <strong data-cart-total>{{ $cartTotalFormatted }}</strong>
              </li>
            </ul>
            <p class="small text-muted mb-3" data-cart-shipping-message>
              @if($shipping['is_free'])
                Free shipping applied.
              @else
                Free shipping on orders of ₹ {{ number_format((float) $shipping['free_shipping_threshold'], 0) }} or more.
              @endif
            </p>
            <a class="btn btn-dark w-100 mb-2" href="{{ route('checkout') }}">Checkout</a>
            <a class="btn btn-outline-dark w-100" href="{{ route('shop') }}">Continue shopping</a>
          </div>
        </div>
      </div>
    @endif
  </div>
</section>
