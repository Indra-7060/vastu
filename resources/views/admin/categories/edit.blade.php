@extends('admin.layouts.app')

@section('title', 'Edit Category - Vastutathastu')

@section('content')
<div class="page-head">
    <h2>Edit Category</h2>
    <a href="{{ route('admin.categories.index') }}" class="btn btn-light">Back</a>
</div>

<div class="card">
    <form method="POST" action="{{ route('admin.categories.update', $category) }}" enctype="multipart/form-data" class="form-grid" id="category-form" novalidate>
        @csrf
        @method('PUT')
        <div class="form-group">
            <label>Title</label>
            <input type="text" name="title" value="{{ old('title', $category->title) }}">
        </div>

        <div class="form-group">
            <label>Home Subtitle</label>
            <input type="text" name="short_description" value="{{ old('short_description', $category->short_description) }}" placeholder="Short line under category on home">
            <p class="hint">Shown on homepage “Complete Collection” cards.</p>
        </div>

        <div class="form-group">
            <label>Select Image</label>
            @if($category->image)
                <div style="margin-bottom:8px;">
                    <img src="{{ asset('storage/'.$category->image) }}" alt="" style="max-width:100%;max-height:160px;border-radius:8px;">
                </div>
            @endif
            <input type="file" name="image" accept=".png,.jpg,.jpeg,image/png,image/jpeg">
            <p class="hint">*Recommended Size is 400 x 240 pixels & only png, jpg, jpeg image allowed.</p>
        </div>

        <div class="form-group">
            <label>Sort Order</label>
            <input type="number" name="sort_order" value="{{ old('sort_order', $category->sort_order) }}" min="0" max="9999">
        </div>

        <div>
            <h4 style="margin-bottom:8px;">Display</h4>
            <div class="toggle-row">
                <span>Show on Home</span>
                <label class="switch">
                    <input type="checkbox" name="show_on_home" value="1" {{ old('show_on_home', $category->show_on_home) ? 'checked' : '' }}>
                    <span class="slider"></span>
                </label>
            </div>
        </div>

        @include('admin.categories._sections')

        <div>
            <button type="submit" class="btn btn-primary">Update</button>
        </div>
    </form>
</div>
@endsection
