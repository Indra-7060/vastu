@extends('admin.layouts.app')

@section('title', 'Coupons - Vastutathastu')

@section('content')
<div class="product-panel">
    <div class="product-panel-top">
        <h2>Coupons</h2>
        <div class="product-panel-actions">
            <form method="GET" action="{{ route('admin.coupons.index') }}" id="module-search-form" class="product-search-form">
                <div class="product-search">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search here..." autocomplete="off">
                    <button type="submit" class="product-search-btn" aria-label="Search">@include('admin.partials.icon', ['name' => 'search', 'size' => 16])</button>
                </div>
            </form>
            <a href="{{ route('admin.coupons.create') }}" class="btn btn-primary product-add-btn">Add New</a>
        </div>
    </div>
</div>

<div class="coupon-grid">
    @include('admin.coupons._items')
</div>

@if($coupons->isEmpty())
    <div class="empty-state">No coupons found. <a href="{{ route('admin.coupons.create') }}">Add New</a></div>
@endif

<div class="pagination-wrap">
    {{ $coupons->links() }}
</div>
@endsection
