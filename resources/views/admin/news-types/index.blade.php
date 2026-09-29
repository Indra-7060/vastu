@extends('admin.layouts.app')

@section('title', 'News Types - Vastutathastu')

@section('content')
<div class="page-head">
    <h2>News Types</h2>
    <div class="page-head-actions">
        <form method="GET" action="{{ route('admin.news-types.index') }}">
            <div class="search-box">
                <span>@include('admin.partials.icon', ['name' => 'search', 'size' => 16])</span>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search here..." autocomplete="off">
            </div>
        </form>
    </div>
</div>

<div class="card" style="margin-bottom:18px;">
    <h3 style="margin-bottom:12px;" id="form-card-title">Add News Type</h3>
    <form method="POST"
          action="{{ route('admin.news-types.store') }}"
          class="inline-form"
          id="news-type-form"
          novalidate
          data-store-action="{{ route('admin.news-types.store') }}"
          data-add-title="Add News Type"
          data-edit-title="Edit News Type"
          data-title-id="form-card-title">
        @csrf
        <div class="form-group">
            <label>Title</label>
            <input type="text" name="title" placeholder="Fashion, Luxury, Style...">
        </div>
        <div class="form-group">
            <label>Sort Order</label>
            <input type="number" name="sort_order" value="0" min="0">
        </div>
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Save</button>
            <button type="button" class="btn btn-light js-edit-cancel" hidden>Cancel</button>
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
                    <th>Slug</th>
                    <th>Sort</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @include('admin.news-types._items')
            </tbody>
        </table>
    </div>
    @if($newsTypes->isEmpty())
        <div class="empty-state">No news types found.</div>
    @endif
</div>

<div class="pagination-wrap">
    {{ $newsTypes->links() }}
</div>
@endsection
