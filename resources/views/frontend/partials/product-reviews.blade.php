{{-- Dynamic product reviews — guest reviews allowed (login optional) --}}
<section class="shop-review-area pt90 pb-0 gap-60" id="reviews">
  <div class="container">
    <div class="shop-title mb50 style13 text-center">
      <h2 class="title">REVIEWS</h2>
    </div>

    @if(session('success'))
      <div class="alert alert-success text-center mb30">{{ session('success') }}</div>
    @endif
    @if($errors->any())
      <div class="alert alert-danger text-center mb30">{{ $errors->first() }}</div>
    @endif

    <div class="review-info">
      <div class="row g-4 bb1 pb40">
        <div class="col-xl-4 col-lg-6">
          <div class="review-info-box">
            <div class="rating-progress">
              @foreach($ratingBreakdown as $star => $data)
              <div class="progress-item d-flex align-items-center">
                <h4 class="star flex-shrink-0">{{ $star }} Star</h4>
                <div class="progress flex-grow-1" role="progressbar" aria-valuenow="{{ $data['percent'] }}" aria-valuemin="0" aria-valuemax="100">
                  <div class="progress-bar" style="width: {{ $data['percent'] }}%"></div>
                </div>
                <span class="num flex-shrink-0">{{ $data['count'] }}</span>
              </div>
              @endforeach
            </div>
          </div>
        </div>
        <div class="col-xl-4 col-lg-6"></div>
        <div class="col-xl-4 col-lg-6">
          <div class="review-info-box">
            <h4 class="review-number mb10 d-flex align-items-center">
              <strong>{{ $reviewCount ? number_format($avgRating, 1) : '0.0' }}</strong>
              out of 5 stars
              <span class="ms-2 text-muted fz14">({{ $reviewCount }})</span>
            </h4>
            <a class="su-btn-4 btn-black-border w-100 text-center su-left-right" href="#write-review">
              <span class="mr10 su-text d-inline-block">WRITE A REVIEW</span>
            </a>
          </div>
        </div>
      </div>
    </div>

    <div class="review-list-wrapper pt50">
      <div class="review-list-main">
        @forelse($activeReviews as $review)
        <div class="review-list-item d-xl-flex align-items-start mb40">
          <div class="left-content flex-shrink-0 me-xl-4 mb20 mb-xl-0">
            <div class="rating mb10">
              <ul class="list-unstyled d-flex align-items-center gap-2 mb-0">
                @for($i = 1; $i <= 5; $i++)
                  <li style="opacity: {{ $i <= $review->rating ? 1 : 0.25 }}">★</li>
                @endfor
              </ul>
            </div>
            <h5 class="mb5">{{ $review->reviewer_name }}</h5>
            <p class="mb-0 text-muted">{{ optional($review->created_at)->format('F d, Y') }}</p>
          </div>
          <div class="right-content flex-grow-1">
            <p class="mb-0">{{ $review->comment }}</p>
          </div>
        </div>
        @empty
        <div class="text-center py-4">
          <p class="mb-0">No reviews yet. Be the first to review this product.</p>
        </div>
        @endforelse
      </div>
    </div>

    <div class="submit-review-box shop-s9 mt60" id="write-review">
      <div class="text-center">
        <h3 class="title mb20">{{ $reviewCount ? 'WRITE A REVIEW' : 'BE THE FIRST TO REVIEW “'.strtoupper($product->title).'”' }}</h3>
        <p class="text mb15">Login is optional. Guests can review with name and email. Required fields are marked *</p>
      </div>

      <form method="POST" action="{{ route('shop.review.store', $product->slug) }}" id="product-review-form" novalidate>
        @csrf
        <div id="review-form-alert" class="alert alert-danger text-center mb20 d-none" role="alert" hidden aria-live="assertive"></div>
        <p class="rate-text mb-2">Overall rating*</p>
        <div class="rating mb20" id="review-star-picker">
          <input type="hidden" name="rating" id="review-rating" value="{{ old('rating', 5) }}">
          <ul class="list-unstyled d-flex align-items-center gap-3 mb-0" role="listbox" aria-label="Rating">
            @for($i = 1; $i <= 5; $i++)
              <li>
                <button type="button" class="review-star-btn border-0 bg-transparent p-0" data-rating="{{ $i }}" aria-label="{{ $i }} stars" style="font-size:22px;line-height:1;color:#1d1d1d;cursor:pointer;">★</button>
              </li>
            @endfor
          </ul>
          <div class="field-error text-danger mt1" id="rating-error" aria-live="polite"></div>
        </div>

        <div class="review-box mt30">
          <div class="row g-4">
            <div class="col-lg-12">
              <div class="form-floating form-group">
                <textarea name="comment" class="form-control textarea shadow-none" placeholder="Your Review*" id="review-comment" required minlength="10">{{ old('comment') }}</textarea>
                <label for="review-comment">Review *</label>
                <div class="field-error text-danger" aria-live="polite"></div>
              </div>
            </div>
            <div class="col-lg-6">
              <div class="form-floating form-group">
                <input type="text" name="reviewer_name" id="reviewer-name" class="form-control shadow-none" placeholder="Name*" value="{{ old('reviewer_name', optional(auth()->user())->name) }}" required maxlength="255">
                <label for="reviewer-name">Name *</label>
                <div class="field-error text-danger" aria-live="polite"></div>
              </div>
            </div>
            <div class="col-lg-6">
              <div class="form-floating form-group">
                <input type="email" name="reviewer_email" id="reviewer-email" class="form-control shadow-none" placeholder="Email*" value="{{ old('reviewer_email', optional(auth()->user())->email) }}" required maxlength="255">
                <label for="reviewer-email">Email *</label>
                <div class="field-error text-danger" aria-live="polite"></div>
              </div>
            </div>
            <div class="col-lg-12">
              <button type="submit" class="su-btn-4 su-btn-16-black w-100 su-left-right">
                <span class="mr10 su-text d-inline-block">WRITE A REVIEW</span>
              </button>
            </div>
          </div>
        </div>
      </form>
    </div>
  </div>
</section>
