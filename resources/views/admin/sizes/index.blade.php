@extends('admin.layouts.app')

@section('title', 'Sizes - Vastutathastu')

@section('content')
<div class="page-head">
    <h2>Sizes</h2>
    <div class="page-head-actions">
        <form method="GET" action="{{ route('admin.sizes.index') }}">
            <div class="search-box">
                <span>@include('admin.partials.icon', ['name' => 'search', 'size' => 16])</span>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search here..." autocomplete="off">
            </div>
        </form>
    </div>
</div>

<div class="card" style="margin-bottom:18px;">
    <h3 style="margin-bottom:12px;" id="form-card-title">Add Size</h3>
    <form method="POST"
          action="{{ route('admin.sizes.store') }}"
          class="inline-form"
          id="size-form"
          novalidate
          data-store-action="{{ route('admin.sizes.store') }}"
          data-add-title="Add Size"
          data-edit-title="Edit Size"
          data-title-id="form-card-title">
        @csrf
        <div class="form-group">
            <label>Name</label>
            <input type="text" name="name" placeholder="M / L / XL">
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
                    <th>Name</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @include('admin.sizes._items')
            </tbody>
        </table>
    </div>
    @if($sizes->isEmpty())
        <div class="empty-state">No sizes found.</div>
    @endif
</div>

<div class="pagination-wrap">
    {{ $sizes->links() }}
</div>
@endsection
