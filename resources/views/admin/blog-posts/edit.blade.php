@extends('admin.layouts.app')

@section('title', 'Edit Journal Post - Vastutathastu')

@section('content')
<div class="page-head">
    <h2>Edit Journal Post</h2>
    <a href="{{ route('admin.blog-posts.index') }}" class="btn btn-light">Back</a>
</div>

<div class="card">
    <form method="POST"
          action="{{ route('admin.blog-posts.update', $blogPost) }}"
          enctype="multipart/form-data"
          class="form-grid"
          id="blog-post-form"
          novalidate
          data-image-required="0">
        @csrf
        @method('PUT')
        @include('admin.blog-posts._form', ['post' => $blogPost])
        <div>
            <button type="submit" class="btn btn-primary">Update Post</button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script src="https://cdn.ckeditor.com/4.22.1/standard/ckeditor.js"></script>
<script>
if (window.CKEDITOR) {
    CKEDITOR.replace('content');
}
</script>
@endpush
