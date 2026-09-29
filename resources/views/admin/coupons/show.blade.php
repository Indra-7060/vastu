@extends('admin.layouts.app')

@section('title', 'Coupon Detail - Vastutathastu')

@section('content')
<div class="page-head">
    <div>
        <a href="{{ route('admin.coupons.index') }}" class="back-link">← Back</a>
        <h2>Coupon Detail</h2>
    </div>
    <div class="page-head-actions">
        <a href="{{ route('admin.coupons.edit', $coupon) }}" class="btn btn-primary">Edit</a>
    </div>
</div>

<div class="card coupon-detail">
    <div class="coupon-detail-hero" style="background-image:url('{{ $coupon->image_url }}');">
        <span>{{ $coupon->discount_label }}</span>
    </div>

    <div class="coupon-detail-grid">
        <div><strong>Coupon Code</strong><span>{{ $coupon->code }}</span></div>
        <div><strong>Offer Type</strong><span>{{ $coupon->offer_type_label }}</span></div>
        <div><strong>Status</strong><span>{{ $coupon->is_active ? 'Active' : 'Inactive' }}</span></div>
        <div><strong>Website Visibility</strong><span>{{ $coupon->is_public ? 'Public' : 'Private code only' }}</span></div>
        <div><strong>Discount Type</strong><span>{{ $coupon->discount_type === 'percent' ? 'Percentage' : 'Amount' }}</span></div>
        <div><strong>Discount</strong><span>{{ $coupon->discount_label }}</span></div>
        <div><strong>Max Discount Status</strong><span>{{ $coupon->max_discount_status ? 'True' : 'False' }}</span></div>
        <div><strong>Max Discount Amount</strong><span>{{ $coupon->max_discount_status ? '₹ '.number_format((float) $coupon->max_discount_amount, 2) : '—' }}</span></div>
        <div><strong>Min Cart Status</strong><span>{{ $coupon->min_cart_status ? 'True' : 'False' }}</span></div>
        <div><strong>Min Cart Amount</strong><span>{{ $coupon->min_cart_status ? '₹ '.number_format((float) $coupon->min_cart_amount, 2) : '—' }}</span></div>
        <div><strong>Applies To</strong><span>{{ ucfirst(str_replace('_', ' ', (string) $coupon->applies_to)) }}</span></div>
        <div><strong>Min Quantity</strong><span>{{ $coupon->min_quantity ?: '—' }}</span></div>
        @if($coupon->offer_type === 'bogo')
            <div><strong>BOGO Rule</strong><span>Buy {{ $coupon->bogo_buy_quantity }}, get {{ $coupon->bogo_get_quantity }} free</span></div>
        @endif
        <div><strong>New Customers Only</strong><span>{{ $coupon->new_customers_only ? 'True' : 'False' }}</span></div>
        <div><strong>Members Only</strong><span>{{ $coupon->members_only ? 'True' : 'False' }}</span></div>
        <div><strong>Free Shipping</strong><span>{{ $coupon->free_shipping ? 'True' : 'False' }}</span></div>
        <div><strong>Starts At</strong><span>{{ $coupon->starts_at?->format('d-m-Y h:i A') ?: '—' }}</span></div>
        <div><strong>Ends At</strong><span>{{ $coupon->ends_at?->format('d-m-Y h:i A') ?: '—' }}</span></div>
        <div><strong>Usage Limit</strong><span>{{ $coupon->usage_limit !== null ? $coupon->used_count.'/'.$coupon->usage_limit : ($coupon->used_count.' used') }}</span></div>
        <div><strong>Max Use Per User</strong><span>{{ $coupon->max_use_per_user ? 'True' : 'False' }}</span></div>
        <div><strong>Created</strong><span>{{ $coupon->created_at?->format('d-m-Y h:i A') }}</span></div>
    </div>

    @if($coupon->description)
        <div class="coupon-detail-desc">
            <h3>Description</h3>
            <div class="coupon-html">{!! $coupon->description !!}</div>
        </div>
    @endif
</div>
@endsection
