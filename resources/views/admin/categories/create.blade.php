@extends('admin.layouts.app')

@section('title', 'Add Category - Vastutathastu')

@section('content')
<div class="page-head">
    <h2>Add Category</h2>
    <a href="{{ route('admin.categories.index') }}" class="btn btn-light">Back</a>
</div>

<div class="card">
    <form method="POST" action="{{ route('admin.categories.store') }}" enctype="multipart/form-data" class="form-grid" id="category-form" novalidate>
        @csrf
        <div class="form-group">
            <label>Title</label>
            <input type="text" name="title" value="{{ old('title') }}" placeholder="Category title">
        </div>

        <div class="form-group">
            <label>Home Subtitle</label>
            <input type="text" name="short_description" value="{{ old('short_description') }}" placeholder="Short line under category on home">
            <p class="hint">Shown on homepage “Complete Collection” cards.</p>
        </div>

        <div class="form-group form-group--full">
            <label>Category page heading</label>
            <input type="text" name="page_heading" maxlength="120" value="{{ old('page_heading', '') }}" placeholder="e.g. Rudraksh">
            <p class="hint">The big title at the top of this category's page. Leave empty to use the category name. Changing it does not rename the category or its web address.</p>
        </div>

        <div class="form-group form-group--full">
            <label>Category page description</label>
            <textarea name="page_description" rows="3" maxlength="500" placeholder="e.g. Authentic Rudraksh from Vastutathastu, chosen with care and energised before dispatch.">{{ old('page_description', '') }}</textarea>
            <p class="hint">The line under the heading on this category's page (up to 500 characters). Leave empty to use the default sentence.</p>
        </div>

        <div class="form-group">
            <label>Select Image</label>
            <input type="file" name="image" accept=".png,.jpg,.jpeg,image/png,image/jpeg">
            <p class="hint">*Recommended Size is 400 x 240 pixels & only png, jpg, jpeg image allowed.</p>
            <p class="hint">Note: We recommended to upload jpg, jpeg image files for best compression ratio.</p>
        </div>

        <div class="form-group">
            <label>Sort Order</label>
            <input type="number" name="sort_order" value="{{ old('sort_order', 0) }}" min="0" max="9999">
        </div>

        <div>
            <h4 style="margin-bottom:8px;">Display</h4>
            <div class="toggle-row">
                <span>Show on Home</span>
                <label class="switch">
                    <input type="checkbox" name="show_on_home" value="1" {{ old('show_on_home') ? 'checked' : '' }}>
                    <span class="slider"></span>
                </label>
            </div>
        </div>

        @include('admin.categories._sections')

        <div>
            <button type="submit" class="btn btn-primary">Save</button>
        </div>
    </form>
</div>
@endsection
