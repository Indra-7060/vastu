@extends('admin.layouts.app')

@section('title', 'Edit Product - Vastutathastu')

@section('content')
<div class="page-head">
    <div>
        <a href="{{ route('admin.products.index') }}" class="back-link">← Back</a>
        <h2>Edit Product</h2>
    </div>
    <a href="{{ route('admin.products.reviews', $product) }}" class="btn btn-light">Reviews ({{ $product->reviews()->count() }})</a>
</div>

<form method="POST" action="{{ route('admin.products.update', $product) }}" enctype="multipart/form-data" class="product-form" id="product-form" novalidate data-image-required="0" data-check-title="{{ route('admin.products.check-title') }}" data-ignore-id="{{ $product->id }}">
    @csrf
    @method('PUT')
    @include('admin.products._form')
</form>
@endsection

@push('scripts')
<script src="{{ asset('js/product-form.js') }}?v=product-details-5"></script>
@endpush
