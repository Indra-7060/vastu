@extends('admin.layouts.app')

@section('title', 'Add Product - Vastutathastu')

@section('content')
<div class="page-head">
    <div>
        <a href="{{ route('admin.products.index') }}" class="back-link">← Back</a>
        <h2>Add Product</h2>
    </div>
</div>

<form method="POST" action="{{ route('admin.products.store') }}" enctype="multipart/form-data" class="product-form" id="product-form" novalidate data-image-required="1" data-check-title="{{ route('admin.products.check-title') }}">
    @csrf
    @include('admin.products._form')
</form>
@endsection

@push('scripts')
<script src="{{ asset('js/product-form.js') }}?v=product-details-5"></script>
@endpush
