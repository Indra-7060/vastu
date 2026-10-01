<div class="blog-header pb-5">
  <div class="text-center">
    <h1 class="title vt-journal__title">{{ optional($journalBanner)->subtitle ?: 'Journal' }}</h1>
    <h5 class="sub-title mt20">{{ optional($journalBanner)->description ?: "Vedic wisdom for harmonious living" }}</h5>
  </div>
  @if(count($newsTypes))
  <div class="blog-filter vt-journal__filters d-flex flex-wrap mt25">
    <a href="{{ route('blog') }}" class="filter-btn flex-grow-1 text-center {{ ! $activeType ? 'active' : '' }}">All posts</a>
    @foreach($newsTypes as $type)
    <a href="{{ route('blog', ['type' => $type->slug]) }}" class="filter-btn flex-grow-1 text-center {{ ($activeType?->id === $type->id) ? 'active' : '' }}">{{ $type->title }}</a>
    @endforeach
  </div>
  @endif
</div>

@if($featuredPost)
@php
  $bannerImg = $journalBanner?->images->first();
  $featuredImage = $bannerImg
    ? (str_starts_with($bannerImg->image, 'frontend/') ? asset($bannerImg->image) : asset('storage/'.$bannerImg->image))
    : $featuredPost->banner_image_url;
@endphp
<div class="for-blog blog-big position-relative">
  <div class="thumb overflow-hidden">
    <img src="{{ $featuredImage }}" alt="{{ $featuredPost->title }}" class="img-fluid w-100">
  </div>
  <div class="details position-absolute w-100 h-100 top-0 start-0 p60">
    <div class="info">
      <div class="post-date pb30 d-flex align-items-center gap-3">
        <p class="mb-0 name">BY {{ strtoupper($featuredPost->author_name ?: 'ADMIN') }}, {{ strtoupper(optional($featuredPost->published_at)->format('F d Y') ?? '') }}</p>
      </div>
      <h4 class="title pb30">
        <a href="{{ route('blog.single', $featuredPost->slug) }}">{{ $featuredPost->title }}</a>
      </h4>
      <a class="su-btn-4-black text-white su-left-right" href="{{ route('blog.single', $featuredPost->slug) }}">
        <span class="mr10 su-text d-inline-block">READ MORE</span>
        <span class="su-arrow-angle">
          <svg class="su-arrow-svg-top-right" xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 10.00 10.00">
            <path d="M1.018 10.009 0 8.991l7.569-7.582H1.723L1.737 0h8.26v8.274H8.574l.013-5.847Z"></path>
            <path d="M1.018 10.009 0 8.991l7.569-7.582H1.723L1.737 0h8.26v8.274H8.574l.013-5.847Z"></path>
          </svg>
        </span>
      </a>
    </div>
  </div>
</div>
@endif

<div class="row g-4 mt-0">
  @forelse($posts as $blogPost)
  <div class="col-lg-4 col-sm-6">
    <div class="for-blog position-relative">
      <div class="thumb overflow-hidden mb20 position-relative">
        <a href="{{ route('blog.single', $blogPost->slug) }}">
          <img src="{{ $blogPost->image_url }}" alt="{{ $blogPost->title }}" class="img-fluid w-100">
        </a>
        @if($blogPost->newsType)
        <span class="label position-absolute">{{ strtoupper($blogPost->newsType->title) }}</span>
        @endif
      </div>
      <div class="details">
        <div class="info">
          <div class="post-date pb15 d-flex align-items-center gap-3">
            <p class="mb-0 name">BY {{ strtoupper($blogPost->author_name ?: 'ADMIN') }}, {{ strtoupper(optional($blogPost->published_at)->format('F d Y') ?? '') }}</p>
          </div>
          <h4 class="title pb10">
            <a href="{{ route('blog.single', $blogPost->slug) }}">{{ $blogPost->title }}</a>
          </h4>
          <a class="su-btn-4-black su-left-right" href="{{ route('blog.single', $blogPost->slug) }}">
            <span class="mr10 su-text d-inline-block">READ MORE</span>
            <span class="su-arrow-angle">
              <svg class="su-arrow-svg-top-right" xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 10.00 10.00">
                <path d="M1.018 10.009 0 8.991l7.569-7.582H1.723L1.737 0h8.26v8.274H8.574l.013-5.847Z"></path>
                <path d="M1.018 10.009 0 8.991l7.569-7.582H1.723L1.737 0h8.26v8.274H8.574l.013-5.847Z"></path>
              </svg>
            </span>
          </a>
        </div>
      </div>
    </div>
  </div>
  @empty
  <div class="col-12">
    @include('frontend.partials.vastu-empty-state', [
        'title' => 'Articles coming soon',
        'text' => 'Vedic insights, Vastu tips and product guidance will be published here soon. Please check back later.',
        'icon' => 'info',
    ])
  </div>
  @endforelse
</div>

@if($posts->total() > 0)
<div class="collection-page__pagination pt60 pb90">
  <p class="collection-page__count mb-3">
    Showing {{ $posts->firstItem() }}–{{ $posts->lastItem() }} of {{ $posts->total() }} posts
  </p>
  @if($posts->hasPages())
    {{ $posts->links('vendor.pagination.frontend') }}
  @endif
</div>
@endif
