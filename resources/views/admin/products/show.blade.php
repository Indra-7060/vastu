@extends('admin.layouts.app')

@section('title', 'View Product - Vastutathastu')

@section('content')
<div class="page-head">
    <div>
        <a href="{{ route('admin.products.index') }}" class="back-link">← Back</a>
        <h2>{{ $product->title }}</h2>
    </div>
    <div class="page-head-actions">
        <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-primary">Edit</a>
        <a href="{{ route('admin.products.reviews', $product) }}" class="btn btn-light">Reviews ({{ $product->reviews->count() }})</a>
    </div>
</div>

<div class="product-view-grid">
    <div class="card">
        <div class="product-view-media">
            @if($product->featured_image)
                <img src="{{ asset('storage/'.$product->featured_image) }}" alt="{{ $product->title }}">
            @endif
            @if($product->featured_image_2)
                <img src="{{ asset('storage/'.$product->featured_image_2) }}" alt="">
            @endif
        </div>
        @if($product->images->count())
            <div class="gallery-existing" style="margin-top:12px;">
                @foreach($product->images as $image)
                    <div class="gallery-thumb">
                        <img src="{{ asset('storage/'.$image->image) }}" alt="">
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <div class="card">
        <table class="detail-table">
            <tr><th>Category</th><td>{{ $product->category->title ?? '—' }}</td></tr>
            <tr><th>Sub-Category</th><td>{{ $product->subCategory->title ?? '—' }}</td></tr>
            {{-- Brands section hidden (not removed). Set $showBrands = true to show again. --}}
            @php($showBrands = false)
            @if($showBrands)
            <tr><th>Brand</th><td>{{ $product->brand->name ?? '—' }}</td></tr>
            @endif
            <tr><th>Offer</th><td>{{ $product->offer ? $product->offer->title.' ('.$product->offer->discount_percent.'%)' : '—' }}</td></tr>
            <tr><th>M.R.P</th><td>{{ number_format($product->mrp, 2) }}</td></tr>
            <tr><th>Selling Price</th><td>{{ number_format($product->selling_price, 2) }}</td></tr>
            <tr><th>Max Unit Buy</th><td>{{ $product->max_unit_buy }}</td></tr>
            <tr><th>Delivery Charge</th><td>{{ number_format($product->delivery_charge, 2) }}</td></tr>
            <tr><th>Status</th><td>{{ $product->is_active ? 'Enabled' : 'Disabled' }}</td></tr>
            <tr><th>Featured</th><td>{{ $product->is_featured ? 'Yes' : 'No' }}</td></tr>
            <tr><th>New Arrival</th><td>{{ $product->is_new_arrival ? 'Yes' : 'No' }}</td></tr>
            <tr><th>Material</th><td>{{ $product->material ?: '—' }}</td></tr>
            <tr><th>Badge</th><td>{{ $product->badge ?: '—' }}</td></tr>
        </table>

        @if($product->short_description)
            <h4 style="margin:16px 0 8px;">Short Description</h4>
            <p>{{ $product->short_description }}</p>
        @endif

        @if($product->features)
            <h4 style="margin:16px 0 8px;">Features</h4>
            <div>{!! nl2br(e($product->features)) !!}</div>
        @endif
    </div>
</div>
@endsection
