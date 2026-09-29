@extends('admin.layouts.app')

@section('title', 'Edit Coupon - Vastutathastu')

@section('content')
<div class="page-head">
    <div>
        <a href="{{ route('admin.coupons.index') }}" class="back-link">← Back</a>
    </div>
</div>

<div class="card coupon-form-card">
    <h2 class="coupon-form-title">Edit Coupon</h2>
    @include('admin.coupons._form', [
        'coupon' => $coupon,
        'action' => route('admin.coupons.update', $coupon),
        'method' => 'PUT',
        'imageRequired' => false,
        'offerTypes' => $offerTypes,
        'categories' => $categories,
        'products' => $products,
    ])
</div>
@endsection

@push('scripts')
<script src="https://cdn.ckeditor.com/4.22.1/standard/ckeditor.js"></script>
<script src="{{ asset('js/coupon-form.js') }}"></script>
@endpush
