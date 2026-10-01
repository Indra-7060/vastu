<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Expected delivery update — {{ $order->order_number }}</title>
</head>
<body style="margin:0;padding:0;background:#f4efe6;font-family:Arial,Helvetica,sans-serif;color:#2b2420;">
  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f4efe6;padding:32px 16px;">
    <tr>
      <td align="center">
        <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;">
          @include('emails.partials.logo-header')
          <tr>
            <td style="background:#ffffff;border:1px solid #e6dfd0;border-radius:4px;overflow:hidden;">
              <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                <tr>
                  <td style="background:#4a3528;padding:26px 32px;color:#fff;">
                    <div style="font-size:12px;letter-spacing:.16em;text-transform:uppercase;opacity:.85;margin-bottom:6px;">Delivery update</div>
                    <div style="font-family:Georgia,serif;font-size:26px;color:#fff;">Expected delivery date</div>
                  </td>
                </tr>
                <tr>
                  <td style="padding:28px 32px 12px;font-size:15px;line-height:1.6;color:#3b332d;">
                    Hi {{ $order->shipping_name ?: ($order->user_name ?: 'there') }},
                    <br><br>
                    The expected delivery date for your order
                    <strong style="color:#4a3528;">{{ $order->order_number }}</strong> has been updated.
                  </td>
                </tr>
                <tr>
                  <td style="padding:8px 32px 24px;">
                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#faf7f1;border:1px solid #ece5d6;border-radius:4px;">
                      <tr>
                        <td style="padding:18px 16px;text-align:center;">
                          <div style="font-size:12px;letter-spacing:.1em;text-transform:uppercase;color:#6b625a;margin-bottom:8px;">Expected delivery</div>
                          <div style="font-family:Georgia,serif;font-size:28px;color:#4a3528;font-weight:700;">{{ $deliveryDate }}</div>
                          <div style="margin-top:10px;font-size:13px;color:#6b625a;">
                            Current status: <strong style="color:#4a3528;">{{ $order->status_label }}</strong>
                          </div>
                        </td>
                      </tr>
                    </table>
                  </td>
                </tr>
                <tr>
                  <td align="center" style="padding:0 32px 32px;">
                    <a href="{{ route('account', 'orders') }}" style="display:inline-block;background:#4a3528;color:#ffffff;text-decoration:none;font-size:13px;letter-spacing:.08em;text-transform:uppercase;padding:14px 28px;border-radius:2px;">
                      View your order
                    </a>
                  </td>
                </tr>
                <tr>
                  <td style="padding:0 32px 28px;font-size:14px;color:#6b625a;">
                    Thanks,<br>
                    <strong style="color:#4a3528;">{{ config('app.name') }}</strong>
                  </td>
                </tr>
              </table>
            </td>
          </tr>
          <tr>
            <td align="center" style="padding:20px 8px 0;font-size:12px;color:#8a8076;">
              © {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</body>
</html>
