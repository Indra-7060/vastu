<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Order confirmed — {{ $order->order_number }}</title>
</head>
<body style="margin:0;padding:0;background:#f3eef6;font-family:Georgia,'Times New Roman',serif;color:#2a1a33;">
  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f3eef6;padding:32px 16px;">
    <tr>
      <td align="center">
        <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;">
          @include('emails.partials.logo-header')
          <tr>
            <td style="background:#ffffff;border:1px solid #e4d8ea;border-radius:4px;overflow:hidden;">
              <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                <tr>
                  <td style="background:#3a1651;padding:28px 32px;color:#fff;">
                    <div style="font-size:12px;letter-spacing:.2em;text-transform:uppercase;opacity:.8;margin-bottom:8px;">Order confirmed</div>
                    <div style="font-family:Georgia,serif;font-size:28px;line-height:1.2;color:#fff;">Thank you for your order</div>
                  </td>
                </tr>
                <tr>
                  <td style="padding:28px 32px 8px;font-family:Arial,Helvetica,sans-serif;font-size:15px;line-height:1.6;color:#3d3045;">
                    Hi {{ $order->shipping_name ?: 'there' }},
                    <br><br>
                    We’ve received your order <strong style="color:#3a1651;">{{ $order->order_number }}</strong> and payment was successful.
                  </td>
                </tr>
                <tr>
                  <td style="padding:8px 32px 24px;">
                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f8f4fa;border:1px solid #eadff0;border-radius:4px;">
                      <tr>
                        <td style="padding:14px 16px;font-family:Arial,Helvetica,sans-serif;font-size:12px;color:#6b5a74;text-transform:uppercase;letter-spacing:.08em;width:25%;">Date</td>
                        <td style="padding:14px 16px;font-family:Arial,Helvetica,sans-serif;font-size:12px;color:#6b5a74;text-transform:uppercase;letter-spacing:.08em;width:25%;">Payment</td>
                        <td style="padding:14px 16px;font-family:Arial,Helvetica,sans-serif;font-size:12px;color:#6b5a74;text-transform:uppercase;letter-spacing:.08em;width:25%;">Status</td>
                        <td style="padding:14px 16px;font-family:Arial,Helvetica,sans-serif;font-size:12px;color:#6b5a74;text-transform:uppercase;letter-spacing:.08em;width:25%;">Total</td>
                      </tr>
                      <tr>
                        <td style="padding:0 16px 16px;font-family:Arial,Helvetica,sans-serif;font-size:14px;color:#2a1a33;font-weight:700;">{{ optional($order->ordered_at ?: $order->created_at)->format('d M Y') }}</td>
                        <td style="padding:0 16px 16px;font-family:Arial,Helvetica,sans-serif;font-size:14px;color:#2a1a33;font-weight:700;">{{ strtoupper((string) ($order->payment_mode ?: 'razorpay')) }}</td>
                        <td style="padding:0 16px 16px;font-family:Arial,Helvetica,sans-serif;font-size:14px;color:#3a1651;font-weight:700;">Placed</td>
                        <td style="padding:0 16px 16px;font-family:Arial,Helvetica,sans-serif;font-size:14px;color:#2a1a33;font-weight:700;">₹{{ number_format((float) $order->payable_amount, 2) }}</td>
                      </tr>
                    </table>
                  </td>
                </tr>
                <tr>
                  <td style="padding:8px 32px 12px;font-family:Georgia,serif;font-size:18px;color:#3a1651;">Order summary</td>
                </tr>
                <tr>
                  <td style="padding:0 32px 24px;">
                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;font-family:Arial,Helvetica,sans-serif;">
                      <thead>
                        <tr>
                          <th align="left" style="padding:10px 8px;border-bottom:2px solid #3a1651;font-size:11px;letter-spacing:.1em;text-transform:uppercase;color:#6b5a74;font-weight:600;">Item</th>
                          <th align="center" style="padding:10px 8px;border-bottom:2px solid #3a1651;font-size:11px;letter-spacing:.1em;text-transform:uppercase;color:#6b5a74;font-weight:600;width:60px;">Qty</th>
                          <th align="right" style="padding:10px 8px;border-bottom:2px solid #3a1651;font-size:11px;letter-spacing:.1em;text-transform:uppercase;color:#6b5a74;font-weight:600;width:100px;">Total</th>
                        </tr>
                      </thead>
                      <tbody>
                        @foreach($order->items as $item)
                          <tr>
                            <td style="padding:14px 8px;border-bottom:1px solid #eee4f2;font-size:14px;color:#2a1a33;">
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
                            <td align="center" style="padding:14px 8px;border-bottom:1px solid #eee4f2;font-size:14px;color:#2a1a33;">{{ $item->quantity }}</td>
                            <td align="right" style="padding:14px 8px;border-bottom:1px solid #eee4f2;font-size:14px;color:#2a1a33;font-weight:600;">₹{{ number_format((float) $item->total_price, 2) }}</td>
                          </tr>
                        @endforeach
                        <tr>
                          <td colspan="2" align="right" style="padding:12px 8px 4px;font-size:13px;color:#6b5a74;">Subtotal</td>
                          <td align="right" style="padding:12px 8px 4px;font-size:13px;color:#2a1a33;">₹{{ number_format((float) $order->subtotal, 2) }}</td>
                        </tr>
                        <tr>
                          <td colspan="2" align="right" style="padding:4px 8px;font-size:13px;color:#6b5a74;">Shipping / Delivery</td>
                          <td align="right" style="padding:4px 8px;font-size:13px;color:#2a1a33;">
                            @if((float) $order->delivery_charge > 0)
                              ₹{{ number_format((float) $order->delivery_charge, 2) }}
                            @else
                              Free
                            @endif
                          </td>
                        </tr>
                        @if((float) $order->discount_amount > 0)
                        <tr>
                          <td colspan="2" align="right" style="padding:4px 8px;font-size:13px;color:#6b5a74;">Discount{{ $order->coupon_code ? ' ('.$order->coupon_code.')' : '' }}</td>
                          <td align="right" style="padding:4px 8px;font-size:13px;color:#2a1a33;">-₹{{ number_format((float) $order->discount_amount, 2) }}</td>
                        </tr>
                        @endif
                        <tr>
                          <td colspan="2" align="right" style="padding:10px 8px 4px;font-size:15px;color:#3a1651;font-weight:700;">Total paid</td>
                          <td align="right" style="padding:10px 8px 4px;font-size:15px;color:#3a1651;font-weight:700;">₹{{ number_format((float) $order->payable_amount, 2) }}</td>
                        </tr>
                      </tbody>
                    </table>
                  </td>
                </tr>
                <tr>
                  <td style="padding:8px 32px 8px;font-family:Georgia,serif;font-size:18px;color:#3a1651;">Shipping to</td>
                </tr>
                <tr>
                  <td style="padding:0 32px 28px;font-family:Arial,Helvetica,sans-serif;font-size:14px;line-height:1.7;color:#3d3045;">
                    <strong>{{ $order->shipping_name }}</strong><br>
                    {{ $order->shipping_address }}<br>
                    {{ $order->shipping_city }}{{ $order->shipping_state ? ', '.$order->shipping_state : '' }} {{ $order->shipping_pincode }}<br>
                    {{ $order->shipping_country }}<br>
                    Phone: {{ $order->shipping_phone }}
                  </td>
                </tr>
                @if($guestPassword)
                <tr>
                  <td style="padding:0 32px 28px;">
                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#fff8e8;border:1px solid #f0e0b8;border-radius:4px;">
                      <tr>
                        <td style="padding:16px;font-family:Arial,Helvetica,sans-serif;font-size:14px;line-height:1.6;color:#3d3045;">
                          <strong style="color:#3a1651;">Your account</strong><br>
                          We created an account for this email.<br>
                          Temporary password: <strong>{{ $guestPassword }}</strong><br>
                          Please log in and change it after your first visit.
                        </td>
                      </tr>
                    </table>
                  </td>
                </tr>
                @endif
                <tr>
                  <td align="center" style="padding:0 32px 36px;">
                    <a href="{{ route('account', 'orders') }}" style="display:inline-block;background:#3a1651;color:#ffffff;text-decoration:none;font-family:Arial,Helvetica,sans-serif;font-size:13px;letter-spacing:.08em;text-transform:uppercase;padding:14px 28px;border-radius:2px;">View in your account</a>
                  </td>
                </tr>
                <tr>
                  <td style="padding:0 32px 28px;font-family:Arial,Helvetica,sans-serif;font-size:14px;color:#6b5a74;">
                    Thanks,<br>
                    <strong style="color:#3a1651;">{{ config('app.name') }}</strong>
                  </td>
                </tr>
              </table>
            </td>
          </tr>
          <tr>
            <td align="center" style="padding:20px 8px 0;font-family:Arial,Helvetica,sans-serif;font-size:12px;color:#8a7a90;">
              © {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</body>
</html>
