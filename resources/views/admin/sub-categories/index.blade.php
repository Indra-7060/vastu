@extends('admin.layouts.app')

@section('title', 'Sub-Categories - Vastutathastu')

@section('content')
<div class="page-head">
    <h2>Sub-Categories</h2>
    <div class="page-head-actions">
        <form method="GET" action="{{ route('admin.sub-categories.index') }}">
            <div class="search-box">
                <span>@include('admin.partials.icon', ['name' => 'search', 'size' => 16])</span>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search here..." autocomplete="off">
            </div>
        </form>
    </div>
</div>

<div class="card" style="margin-bottom:18px;">
    <h3 style="margin-bottom:12px;">Add Sub-Category</h3>
    <form method="POST" action="{{ route('admin.sub-categories.store') }}" enctype="multipart/form-data" class="inline-form" id="subcategory-form" novalidate>
        @csrf
        <div class="form-group">
            <label>Category</label>
            <select name="category_id">
                <option value="">Select category</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->title }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label>Title</label>
            <input type="text" name="title">
        </div>
        <div class="form-group">
            <label>Image</label>
            <input type="file" name="image" accept=".png,.jpg,.jpeg,image/png,image/jpeg">
        </div>
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Save</button>
        </div>
    </form>
</div>

<div class="card">
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Title</th>
                    <th>Category</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @include('admin.sub-categories._items')
            </tbody>
        </table>
    </div>
    @if($subCategories->isEmpty())
        <div class="empty-state">No sub-categories found.</div>
    @endif
</div>

<div class="pagination-wrap">
    {{ $subCategories->links() }}
</div>
@endsection
