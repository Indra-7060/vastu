<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>New order — {{ $order->order_number }}</title>
</head>
<body style="margin:0;padding:0;background:#f3eef6;font-family:Arial,Helvetica,sans-serif;color:#2a1a33;">
  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f3eef6;padding:32px 16px;">
    <tr>
      <td align="center">
        <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;">
          @include('emails.partials.logo-header')
          <tr>
            <td style="background:#ffffff;border:1px solid #e4d8ea;border-radius:4px;overflow:hidden;">
              <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                <tr>
                  <td style="background:#3a1651;padding:24px 32px;color:#fff;">
                    <div style="font-size:12px;letter-spacing:.16em;text-transform:uppercase;opacity:.85;margin-bottom:6px;">New paid order</div>
                    <div style="font-family:Georgia,serif;font-size:26px;color:#fff;">{{ $order->order_number }}</div>
                  </td>
                </tr>
                <tr>
                  <td style="padding:24px 32px 8px;font-size:14px;line-height:1.6;color:#3d3045;">
                    A new order has been paid and placed.
                  </td>
                </tr>
                <tr>
                  <td style="padding:8px 32px 20px;">
                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f8f4fa;border:1px solid #eadff0;border-radius:4px;font-size:14px;">
                      <tr>
                        <td style="padding:12px 16px;border-bottom:1px solid #eadff0;width:120px;color:#6b5a74;">Customer</td>
                        <td style="padding:12px 16px;border-bottom:1px solid #eadff0;font-weight:700;">{{ $order->shipping_name }}</td>
                      </tr>
                      <tr>
                        <td style="padding:12px 16px;border-bottom:1px solid #eadff0;color:#6b5a74;">Email</td>
                        <td style="padding:12px 16px;border-bottom:1px solid #eadff0;">{{ $order->shipping_email ?: $order->user_email }}</td>
                      </tr>
                      <tr>
                        <td style="padding:12px 16px;border-bottom:1px solid #eadff0;color:#6b5a74;">Phone</td>
                        <td style="padding:12px 16px;border-bottom:1px solid #eadff0;">{{ $order->shipping_phone }}</td>
                      </tr>
                      <tr>
                        <td style="padding:12px 16px;border-bottom:1px solid #eadff0;color:#6b5a74;">Payment</td>
                        <td style="padding:12px 16px;border-bottom:1px solid #eadff0;">{{ strtoupper((string) $order->payment_mode) }} · {{ $order->payment_id }}</td>
                      </tr>
                      <tr>
                        <td style="padding:12px 16px;border-bottom:1px solid #eadff0;color:#6b5a74;">Subtotal</td>
                        <td style="padding:12px 16px;border-bottom:1px solid #eadff0;">₹{{ number_format((float) $order->subtotal, 2) }}</td>
                      </tr>
                      <tr>
                        <td style="padding:12px 16px;border-bottom:1px solid #eadff0;color:#6b5a74;">Shipping / Delivery</td>
                        <td style="padding:12px 16px;border-bottom:1px solid #eadff0;">
                          @if((float) $order->delivery_charge > 0)
                            ₹{{ number_format((float) $order->delivery_charge, 2) }}
                          @else
                            Free
                          @endif
                        </td>
                      </tr>
                      @if((float) $order->discount_amount > 0)
                      <tr>
                        <td style="padding:12px 16px;border-bottom:1px solid #eadff0;color:#6b5a74;">Discount{{ $order->coupon_code ? ' ('.$order->coupon_code.')' : '' }}</td>
                        <td style="padding:12px 16px;border-bottom:1px solid #eadff0;">-₹{{ number_format((float) $order->discount_amount, 2) }}</td>
                      </tr>
                      @endif
                      <tr>
                        <td style="padding:12px 16px;color:#6b5a74;">Payable</td>
                        <td style="padding:12px 16px;font-weight:700;color:#3a1651;">₹{{ number_format((float) $order->payable_amount, 2) }}</td>
                      </tr>
                    </table>
                  </td>
                </tr>
                <tr>
                  <td style="padding:4px 32px 10px;font-family:Georgia,serif;font-size:17px;color:#3a1651;">Items</td>
                </tr>
                <tr>
                  <td style="padding:0 32px 24px;">
                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;">
                      <thead>
                        <tr>
                          <th align="left" style="padding:10px 8px;border-bottom:2px solid #3a1651;font-size:11px;letter-spacing:.1em;text-transform:uppercase;color:#6b5a74;">Item</th>
                          <th align="center" style="padding:10px 8px;border-bottom:2px solid #3a1651;font-size:11px;letter-spacing:.1em;text-transform:uppercase;color:#6b5a74;width:60px;">Qty</th>
                          <th align="right" style="padding:10px 8px;border-bottom:2px solid #3a1651;font-size:11px;letter-spacing:.1em;text-transform:uppercase;color:#6b5a74;width:100px;">Total</th>
                        </tr>
                      </thead>
                      <tbody>
                        @foreach($order->items as $item)
                          <tr>
                            <td style="padding:12px 8px;border-bottom:1px solid #eee4f2;font-size:14px;">
                              {{ $item->product_title }}
                              @if($item->color || $item->size)
                                <div style="font-size:12px;color:#6b5a74;margin-top:4px;">
                                  @if($item->color)Colour: {{ $item->color }}@endif
                                  @if($item->color && $item->size) · @endif
                                  @if($item->size)Size: {{ $item->size }}@endif
                                </div>
                              @endif
                              @if($item->package_label)
                                <div style="font-size:12px;color:#6b5a74;margin-top:4px;">Pack: {{ $item->package_label }}</div>
                              @endif
                            </td>
                            <td align="center" style="padding:12px 8px;border-bottom:1px solid #eee4f2;font-size:14px;">{{ $item->quantity }}</td>
                            <td align="right" style="padding:12px 8px;border-bottom:1px solid #eee4f2;font-size:14px;font-weight:600;">₹{{ number_format((float) $item->total_price, 2) }}</td>
                          </tr>
                        @endforeach
                        <tr>
                          <td colspan="2" align="right" style="padding:12px 8px 4px;font-size:13px;color:#6b5a74;">Subtotal</td>
                          <td align="right" style="padding:12px 8px 4px;font-size:13px;">₹{{ number_format((float) $order->subtotal, 2) }}</td>
                        </tr>
                        <tr>
                          <td colspan="2" align="right" style="padding:4px 8px;font-size:13px;color:#6b5a74;">Shipping / Delivery</td>
                          <td align="right" style="padding:4px 8px;font-size:13px;">
                            @if((float) $order->delivery_charge > 0)
                              ₹{{ number_format((float) $order->delivery_charge, 2) }}
                            @else
                              Free
                            @endif
                          </td>
                        </tr>
                        <tr>
                          <td colspan="2" align="right" style="padding:10px 8px 4px;font-size:15px;color:#3a1651;font-weight:700;">Payable</td>
                          <td align="right" style="padding:10px 8px 4px;font-size:15px;color:#3a1651;font-weight:700;">₹{{ number_format((float) $order->payable_amount, 2) }}</td>
                        </tr>
                      </tbody>
                    </table>
                  </td>
                </tr>
                <tr>
                  <td style="padding:0 32px 8px;font-family:Georgia,serif;font-size:17px;color:#3a1651;">Ship to</td>
                </tr>
                <tr>
                  <td style="padding:0 32px 28px;font-size:14px;line-height:1.7;color:#3d3045;">
                    {{ $order->shipping_address }}<br>
                    {{ $order->shipping_city }}{{ $order->shipping_state ? ', '.$order->shipping_state : '' }} {{ $order->shipping_pincode }}<br>
                    {{ $order->shipping_country }}
                    @if($order->order_notes)
                      <br><br><strong>Notes:</strong> {{ $order->order_notes }}
                    @endif
                  </td>
                </tr>
                <tr>
                  <td align="center" style="padding:0 32px 32px;">
                    <a href="{{ url('/admin/orders/'.$order->id) }}" style="display:inline-block;background:#3a1651;color:#ffffff;text-decoration:none;font-size:13px;letter-spacing:.08em;text-transform:uppercase;padding:14px 28px;border-radius:2px;">Open in admin</a>
                  </td>
                </tr>
              </table>
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</body>
</html>
