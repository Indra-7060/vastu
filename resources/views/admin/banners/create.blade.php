@extends('admin.layouts.app')

@section('title', 'Add Banner - Vastutathastu')

@section('content')
<div class="page-head">
    <div>
        <a href="{{ route('admin.banners.index') }}" class="back-link">← Back</a>
        <h2>Add Banner</h2>
    </div>
</div>

<form method="POST" action="{{ route('admin.banners.store') }}" enctype="multipart/form-data" class="product-form" id="banner-form" novalidate data-image-required="1">
    @csrf
    @include('admin.banners._form')
</form>
@endsection

@push('scripts')
<script src="{{ asset('js/banner-form.js') }}?v=vastu-2"></script>
@endpush
