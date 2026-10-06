<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Print {{ $order->order_number }} - Vastutathastu</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('vastu/images/favicon.svg') }}?v=vt2">
    <link rel="icon" type="image/png" sizes="50x50" href="{{ asset('vastu/images/favicon-50.png') }}?v=vt2">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('vastu/images/favicon-32.png') }}?v=vt2">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('vastu/images/favicon-16.png') }}?v=vt2">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('vastu/images/apple-touch-icon.png') }}?v=vt2">
    <style>
        body { font-family: Arial, sans-serif; color: #333; margin: 24px; }
        h1 { font-size: 22px; margin: 0 0 16px; }
        h2 { font-size: 16px; margin: 18px 0 8px; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; font-size: 13px; }
        th { background: #f7f7f7; }
        .meta { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px; }
        .totals { margin-top: 12px; text-align: right; }
        .totals div { margin: 4px 0; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body onload="window.print()">
    <button class="no-print" onclick="window.print()" style="margin-bottom:12px;">Print</button>
    <h1>Order Summary — {{ $order->order_number }}</h1>

    <div class="meta">
        <div>
            <h2>Order Details</h2>
            <p><strong>Ordered On:</strong> {{ $order->ordered_at?->format('d-m-Y h:i A') }}</p>
            <p><strong>Payment Mode:</strong> {{ $order->payment_mode ?: '—' }}</p>
            <p><strong>Payment ID:</strong> {{ $order->payment_id ?: '—' }}</p>
            <p><strong>Status:</strong> {{ $order->status_label }}</p>
            <p><strong>Expected Delivery:</strong> {{ $order->expected_delivery_date?->format('d-m-Y') ?: '—' }}</p>
        </div>
        <div>
            <h2>Billing & Shipping</h2>
            <p><strong>Name:</strong> {{ $order->shipping_name ?: ($order->user_name ?: '—') }}</p>
            <p><strong>Phone:</strong> {{ $order->shipping_phone ?: ($order->user_phone ?: '—') }}</p>
            <p><strong>Address:</strong> {{ $order->shipping_address ?: '—' }}</p>
        </div>
    </div>

    <h2>Products</h2>
    <table>
        <thead>
            <tr>
                <th>Product</th>
                <th>Price</th>
                <th>Saving</th>
                <th>Qty</th>
                <th>Total</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $item)
                <tr>
                    <td>
                        {{ $item->product_title }}
                        @if($item->color || $item->size)
                            <div style="font-size:12px;color:#666;margin-top:2px;">
                                @if($item->color)Colour: {{ $item->color }}@endif
                                @if($item->color && $item->size) · @endif
                                @if($item->size)Size: {{ $item->size }}@endif
                            </div>
                        @endif
                    </td>
                    <td>₹ {{ number_format($item->price, 2) }}</td>
                    <td>₹ {{ number_format($item->saving, 2) }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td>₹ {{ number_format($item->total_price, 2) }}</td>
                    <td>{{ \App\Support\OrderStatuses::label($item->status) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totals">
        <div>Total: <strong>₹ {{ number_format($order->subtotal, 2) }}</strong></div>
        <div>Discount: <strong>- ₹ {{ number_format($order->discount_amount, 2) }}</strong></div>
        <div>Delivery: <strong>{{ $order->delivery_charge > 0 ? '₹ '.number_format($order->delivery_charge, 2) : 'Free' }}</strong></div>
        <div>Payable: <strong>₹ {{ number_format($order->payable_amount, 2) }}</strong></div>
    </div>
</body>
</html>
