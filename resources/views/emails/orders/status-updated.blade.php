@php
  use App\Support\OrderStatuses;
  $status = strtolower((string) $order->status);
  $name = $order->shipping_name ?: ($order->user_name ?: 'there');
  $firstName = \Illuminate\Support\Str::ucfirst(trim(explode(' ', trim($name))[0]) ?: $name);
  $copy = [
      'packed' => ['kicker' => 'Order packed', 'title' => 'Your order is packed', 'intro' => 'Good news! Your order has been carefully packed and will be handed to our delivery partner shortly.'],
      'shipped' => ['kicker' => 'On its way', 'title' => 'Your order has been shipped', 'intro' => 'Your order has left us and is on its way to you.'],
      'delivered' => ['kicker' => 'Delivered', 'title' => 'Your order has been delivered', 'intro' => 'Your order has been delivered. We hope it brings positivity and harmony to your space. Thank you for choosing Vastutathastu.'],
      'cancelled' => ['kicker' => 'Order cancelled', 'title' => 'Your order has been cancelled', 'intro' => 'Your order has been cancelled. If you have any questions about this, please get in touch — we are happy to help.'],
  ][$status] ?? ['kicker' => 'Order update', 'title' => 'Order '.$statusLabel, 'intro' => 'There is an update on your order.'];
  // Only show the admin's note when it adds something beyond the standard sentence.
  $note = trim((string) $description);
  if ($note === OrderStatuses::defaultMessage($status)) { $note = ''; }

  // Progress: Ordered → Packed → Shipped → Delivered (a later step also marks earlier ones done).
  $steps = ['placed' => 'Ordered', 'packed' => 'Packed', 'shipped' => 'Shipped', 'delivered' => 'Delivered'];
  $keys = array_keys($steps);
  $reached = array_search($status, $keys, true);
  if ($status === 'cancelled') {
      $reached = 0;
      foreach ($keys as $i => $k) { if ($order->statusLogs->where('status', $k)->isNotEmpty()) { $reached = $i; } }
  }
  $green = $status === 'cancelled' ? '#b9ae9f' : '#1a7a45';
  $S = \App\Support\SiteSettings::class;
  $phone = trim((string) $S::get('contact_phone'));
  $wa = preg_replace('/\D+/', '', (string) $S::get('contact_whatsapp'));
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{{ $copy['title'] }} — {{ $order->order_number }}</title>
</head>
<body style="margin:0;padding:0;background:#f4efe6;font-family:Arial,Helvetica,sans-serif;color:#2b2420;">
  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f4efe6;padding:32px 16px;">
    <tr>
      <td align="center">
        <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;">
          @include('emails.partials.logo-header')
          <tr>
            <td style="background:#ffffff;border:1px solid #e6dfd0;">
              <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                <tr>
                  <td style="background:{{ $status === 'cancelled' ? '#7a2e2a' : '#4a3528' }};padding:26px 32px;color:#fff;">
                    <div style="font-size:12px;letter-spacing:.16em;text-transform:uppercase;opacity:.85;margin-bottom:6px;">{{ $copy['kicker'] }}</div>
                    <div style="font-family:Georgia,serif;font-size:26px;line-height:1.25;color:#fff;">{{ $copy['title'] }}</div>
                  </td>
                </tr>
                <tr>
                  <td style="padding:26px 32px 8px;font-size:15px;line-height:1.65;color:#3b332d;">
                    Hi {{ $firstName }},<br><br>
                    {{ $copy['intro'] }}
                  </td>
                </tr>

                @if(in_array($status, ['packed', 'shipped'], true) && $order->expected_delivery_date)
                <tr>
                  <td style="padding:10px 32px 0;">
                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#eef7f1;border:1px solid #cfe7d8;">
                      <tr><td style="padding:14px 16px;font-size:15px;color:#2b2420;">Arriving by <strong style="color:#1a7a45;">{{ $order->expected_delivery_date->format('l, j F Y') }}</strong></td></tr>
                    </table>
                  </td>
                </tr>
                @endif

                {{-- Progress --}}
                <tr>
                  <td style="padding:22px 24px 6px;">
                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                      <tr>
                        @foreach($steps as $key => $label)
                          @php $i = $loop->index; $done = $reached !== false && $i <= $reached; @endphp
                          <td align="center" width="25%" style="font-size:12px;color:{{ $done ? '#2b2420' : '#a39a90' }};">
                            <div style="width:18px;height:18px;line-height:18px;margin:0 auto 6px;border-radius:50%;background:{{ $done ? $green : '#ffffff' }};border:2px solid {{ $done ? $green : '#d6cdbd' }};color:#fff;font-size:11px;font-weight:bold;">{{ $done ? '✓' : '' }}</div>
                            <strong style="font-weight:{{ $done ? 'bold' : 'normal' }};">{{ $label }}</strong>
                          </td>
                        @endforeach
                      </tr>
                    </table>
                  </td>
                </tr>

                @if($note !== '')
                <tr>
                  <td style="padding:14px 32px 0;">
                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#faf7f1;border-left:3px solid #806559;">
                      <tr><td style="padding:12px 14px;font-size:14px;line-height:1.6;color:#3b332d;"><strong style="display:block;font-size:11px;letter-spacing:.12em;text-transform:uppercase;color:#806559;margin-bottom:4px;">Note from Vastutathastu</strong>{!! nl2br(e($note)) !!}</td></tr>
                    </table>
                  </td>
                </tr>
                @endif

                {{-- Order summary --}}
                <tr>
                  <td style="padding:22px 32px 6px;">
                    <div style="font-size:11px;letter-spacing:.14em;text-transform:uppercase;color:#806559;margin-bottom:8px;">Order {{ $order->order_number }}</div>
                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-top:1px solid #ece5d6;">
                      @foreach($order->items as $item)
                        <tr>
                          <td style="padding:10px 0;border-bottom:1px solid #ece5d6;font-size:14px;color:#2b2420;">{{ $item->product_title }} <span style="color:#8a8076;">× {{ (int) $item->quantity }}</span></td>
                          <td align="right" style="padding:10px 0;border-bottom:1px solid #ece5d6;font-size:14px;color:#2b2420;white-space:nowrap;">{{ \App\Support\Money::format($item->total_price) }}</td>
                        </tr>
                      @endforeach
                      <tr>
                        <td style="padding:12px 0 0;font-size:14px;font-weight:bold;color:#2b2420;">Total</td>
                        <td align="right" style="padding:12px 0 0;font-size:15px;font-weight:bold;color:#2b2420;white-space:nowrap;">{{ \App\Support\Money::format($order->payable_amount) }}</td>
                      </tr>
                    </table>
                  </td>
                </tr>

                <tr>
                  <td align="center" style="padding:24px 32px 8px;">
                    <a href="{{ route('account', 'orders') }}" style="display:inline-block;background:#4a3528;color:#ffffff;text-decoration:none;font-size:13px;letter-spacing:.08em;text-transform:uppercase;padding:13px 28px;">View my order</a>
                  </td>
                </tr>

                @if($status === 'cancelled' && ($phone || $wa))
                <tr>
                  <td align="center" style="padding:6px 32px 4px;font-size:14px;color:#3b332d;">
                    Need help?
                    @if($phone) Call <a href="tel:{{ preg_replace('/[^\d+]/', '', $phone) }}" style="color:#4a3528;">{{ $phone }}</a>@endif
                    @if($phone && $wa) or @endif
                    @if($wa)<a href="https://wa.me/{{ strlen($wa) === 10 ? '91'.$wa : $wa }}" style="color:#1a7a45;">chat on WhatsApp</a>@endif.
                  </td>
                </tr>
                @endif

                <tr>
                  <td style="padding:18px 32px 28px;font-size:14px;color:#6b625a;">
                    With warm regards,<br>
                    <strong style="color:#4a3528;">Team Vastutathastu</strong>
                  </td>
                </tr>
              </table>
            </td>
          </tr>
          <tr>
            <td align="center" style="padding:20px 8px 0;font-size:12px;color:#8a8076;">
              You are receiving this email about your order on {{ config('app.name') }}.<br>
              © {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</body>
</html>
