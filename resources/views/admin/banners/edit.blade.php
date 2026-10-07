@extends('admin.layouts.app')

@section('title', 'Edit Banner - Vastutathastu')

@section('content')
<div class="page-head">
    <div>
        <a href="{{ \App\Support\BannerSections::listUrl($banner->section) }}" class="back-link">← Back</a>
        <h2>Edit Banner</h2>
    </div>
</div>

<form method="POST" action="{{ route('admin.banners.update', $banner) }}" enctype="multipart/form-data" class="product-form" id="banner-form" novalidate data-image-required="0">
    @csrf
    @include('admin.banners._form')
</form>
@endsection

@push('scripts')
<script src="{{ asset('js/banner-form.js') }}?v=vastu-5"></script>
@endpush
