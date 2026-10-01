@extends('admin.layouts.app')

@section('title', 'Order Summary - Vastutathastu')

@section('content')
<div class="page-head">
    <h2>Order Summary</h2>
    <div class="page-head-actions">
        <a href="{{ route('admin.orders.index') }}" class="btn btn-primary">« Back</a>
        <a href="{{ route('admin.orders.print', $order) }}" target="_blank" class="btn btn-print">@include('admin.partials.icon', ['name' => 'printer', 'size' => 16]) Print</a>
    </div>
</div>

<div class="card order-summary-card">
    <div class="order-summary-grid">
        <div>
            <h3 class="section-title">Order Details</h3>
            <table class="detail-table">
                <tr><th>Order ID</th><td>{{ $order->order_number }}</td></tr>
                <tr><th>Ordered On</th><td>{{ $order->ordered_at?->format('d-m-Y h:i A') }}</td></tr>
                <tr><th>Payment Mode</th><td>{{ $order->payment_mode ?: '—' }}</td></tr>
                <tr><th>Payment ID</th><td>{{ $order->payment_id ?: '—' }}</td></tr>
                <tr><th>Payment Status</th><td><span class="{{ $order->payment_badge_class }}">{{ $order->payment_label }}</span></td></tr>
                <tr>
                    <th>Order Status</th>
                    <td><span class="status-badge {{ $order->status_badge_class }}">{{ $order->status_label }}</span></td>
                </tr>
                <tr>
                    <th>Expected Delivery</th>
                    <td>{{ $order->expected_delivery_date?->format('d-m-Y') ?: '—' }}</td>
                </tr>
            </table>
        </div>
        <div>
            <h3 class="section-title">Billing & Shipping Details</h3>
            <table class="detail-table">
                <tr><th>Name</th><td>{{ $order->shipping_name ?: ($order->user_name ?: '—') }}</td></tr>
                <tr><th>Phone number</th><td>{{ $order->shipping_phone ?: ($order->user_phone ?: '—') }}</td></tr>
                <tr><th>Address</th><td>{{ $order->shipping_address ?: '—' }}</td></tr>
            </table>
        </div>
    </div>
</div>

<div class="card" style="margin-top:16px;">
    <div class="user-tab-toolbar" style="justify-content:space-between;">
        <h3 class="section-title" style="margin:0;border:none;padding:0;">Products</h3>
        <button type="button" class="btn btn-primary js-open-status" data-order-id="{{ $order->id }}">Update Status</button>
    </div>

    <div class="table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Price</th>
                    <th>Saving</th>
                    <th>Qty</th>
                    <th>Total Price</th>
                    <th>Status</th>
                    <th>Update</th>
                </tr>
            </thead>
            <tbody>
                @forelse($order->items as $item)
                    <tr>
                        <td>
                            <div class="order-product-cell">
                                @if($item->product_image)
                                    <img src="{{ \App\Support\Media::url($item->product_image) }}" alt="" class="table-thumb">
                                @endif
                                <div>
                                    <span>{{ $item->product_title }}</span>
                                    @if($item->color || $item->size)
                                        <div class="text-muted" style="font-size:12px;margin-top:4px;">
                                            @if($item->color)Colour: {{ $item->color }}@endif
                                            @if($item->color && $item->size) · @endif
                                            @if($item->size)Size: {{ $item->size }}@endif
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td>₹ {{ number_format($item->price, 2) }}</td>
                        <td>₹ {{ number_format($item->saving, 2) }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>₹ {{ number_format($item->total_price, 2) }}</td>
                        <td><span class="status-badge {{ \App\Support\OrderStatuses::badgeClass($item->status) }}">{{ \App\Support\OrderStatuses::label($item->status) }}</span></td>
                        <td>
                            <button type="button" class="action-status-btn js-open-status" data-order-id="{{ $order->id }}" title="Update order status and expected delivery date">@include('admin.partials.icon', ['name' => 'truck', 'size' => 16])<span>Update<span class="vt-hide-md"> status</span></span></button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="empty-state" style="border:none;">No products in this order.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="order-totals">
        <div class="order-totals-row"><span>Total</span><strong>₹ {{ number_format($order->subtotal, 2) }}</strong></div>
        <div class="order-totals-row">
            <span>Discount{{ $order->coupon_code ? ' ('.$order->coupon_code.')' : '' }}</span>
            <strong>- ₹ {{ number_format($order->discount_amount, 2) }}</strong>
        </div>
        <div class="order-totals-row">
            <span>Delivery Charge</span>
            <strong>{{ $order->delivery_charge > 0 ? '₹ '.number_format($order->delivery_charge, 2) : 'Free' }}</strong>
        </div>
        <div class="order-totals-row payable"><span>Payable Amount</span><strong>₹ {{ number_format($order->payable_amount, 2) }}</strong></div>
    </div>
</div>

@include('admin.orders._status-modal')
@endsection

@push('scripts')
<script>
window.ORDER_STATUS_URL = @json(url('/admin/orders'));
window.CSRF_TOKEN = @json(csrf_token());
</script>
<script src="{{ asset('js/order-admin.js') }}?v=bulk-1"></script>
@endpush
