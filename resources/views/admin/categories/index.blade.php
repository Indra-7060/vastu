@extends('admin.layouts.app')

@section('title', 'Categories - Vastutathastu')

@section('content')
<div class="page-head">
    <h2>Categories</h2>
    <div class="page-head-actions">
        <form method="GET" action="{{ route('admin.categories.index') }}" id="module-search-form">
            <div class="search-box">
                <span>@include('admin.partials.icon', ['name' => 'search', 'size' => 16])</span>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search here..." autocomplete="off">
            </div>
        </form>
        <a href="{{ route('admin.categories.create') }}" class="btn btn-primary">Add New</a>
    </div>
</div>

<div class="category-grid">
    @include('admin.categories._items')
</div>

@if($categories->isEmpty())
    <div class="empty-state">No categories found. <a href="{{ route('admin.categories.create') }}">Add New</a></div>
@endif

<div class="pagination-wrap">
    {{ $categories->links() }}
</div>
@endsection
