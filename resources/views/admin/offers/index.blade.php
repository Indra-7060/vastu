@extends('admin.layouts.app')

@section('title', 'Offers - Vastutathastu')

@section('content')
<div class="page-head">
    <h2>Offers</h2>
    <div class="page-head-actions">
        <form method="GET" action="{{ route('admin.offers.index') }}" id="module-search-form">
            <div class="search-box">
                <span>@include('admin.partials.icon', ['name' => 'search', 'size' => 16])</span>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search here..." autocomplete="off">
            </div>
        </form>
        <a href="{{ route('admin.offers.create') }}" class="btn btn-primary">Add New</a>
    </div>
</div>

<div class="category-grid">
    @include('admin.offers._items')
</div>

@if($offers->isEmpty())
    <div class="empty-state">No offers found. <a href="{{ route('admin.offers.create') }}">Add New</a></div>
@endif

<div class="pagination-wrap">
    {{ $offers->links() }}
</div>
@endsection
