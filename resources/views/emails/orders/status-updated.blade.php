<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Order status update — {{ $order->order_number }}</title>
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
                  <td style="background:#3a1651;padding:26px 32px;color:#fff;">
                    <div style="font-size:12px;letter-spacing:.16em;text-transform:uppercase;opacity:.85;margin-bottom:6px;">Order update</div>
                    <div style="font-family:Georgia,serif;font-size:26px;color:#fff;">{{ $statusLabel }}</div>
                  </td>
                </tr>
                <tr>
                  <td style="padding:28px 32px 12px;font-size:15px;line-height:1.6;color:#3d3045;">
                    Hi {{ $order->shipping_name ?: ($order->user_name ?: 'there') }},
                    <br><br>
                    Your order <strong style="color:#3a1651;">{{ $order->order_number }}</strong> status has been updated to
                    <strong style="color:#3a1651;">{{ $statusLabel }}</strong>.
                  </td>
                </tr>
                <tr>
                  <td style="padding:8px 32px 24px;">
                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f8f4fa;border:1px solid #eadff0;border-radius:4px;">
                      <tr>
                        <td style="padding:16px;font-size:14px;line-height:1.6;color:#3d3045;">
                          {{ $description }}
                          @if($order->expected_delivery_date)
                            <br><br>
                            <strong style="color:#3a1651;">Expected delivery:</strong>
                            {{ $order->expected_delivery_date->format('d-m-Y') }}
                          @endif
                        </td>
                      </tr>
                    </table>
                  </td>
                </tr>
                <tr>
                  <td align="center" style="padding:0 32px 32px;">
                    <a href="{{ route('account', 'orders') }}" style="display:inline-block;background:#3a1651;color:#ffffff;text-decoration:none;font-size:13px;letter-spacing:.08em;text-transform:uppercase;padding:14px 28px;border-radius:2px;">
                      View your order
                    </a>
                  </td>
                </tr>
                <tr>
                  <td style="padding:0 32px 28px;font-size:14px;color:#6b5a74;">
                    Thanks,<br>
                    <strong style="color:#3a1651;">{{ config('app.name') }}</strong>
                  </td>
                </tr>
              </table>
            </td>
          </tr>
          <tr>
            <td align="center" style="padding:20px 8px 0;font-size:12px;color:#8a7a90;">
              © {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</body>
</html>
