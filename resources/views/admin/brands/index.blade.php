@extends('admin.layouts.app')

@section('title', 'Brands - Vastutathastu')

@section('content')
<div class="page-head">
    <h2>Brands</h2>
    <div class="page-head-actions">
        <form method="GET" action="{{ route('admin.brands.index') }}">
            <div class="search-box">
                <span>@include('admin.partials.icon', ['name' => 'search', 'size' => 16])</span>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search here..." autocomplete="off">
            </div>
        </form>
    </div>
</div>

<div class="card" style="margin-bottom:18px;">
    <h3 style="margin-bottom:12px;" id="form-card-title">Add Brand</h3>
    <form method="POST"
          action="{{ route('admin.brands.store') }}"
          enctype="multipart/form-data"
          class="inline-form"
          id="brand-form"
          novalidate
          data-store-action="{{ route('admin.brands.store') }}"
          data-add-title="Add Brand"
          data-edit-title="Edit Brand"
          data-title-id="form-card-title">
        @csrf
        <div class="form-group">
            <label>Name</label>
            <input type="text" name="name">
        </div>
        <div class="form-group">
            <label>Image</label>
            <input type="file" name="image" accept=".png,.jpg,.jpeg,image/png,image/jpeg">
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
                    <th>Slug</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @include('admin.brands._items')
            </tbody>
        </table>
    </div>
    @if($brands->isEmpty())
        <div class="empty-state">No brands found.</div>
    @endif
</div>

<div class="pagination-wrap">
    {{ $brands->links() }}
</div>
@endsection
