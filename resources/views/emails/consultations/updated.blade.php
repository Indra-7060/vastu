<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{{ $heading }}</title>
</head>
<body style="margin:0;padding:0;background:#f6f1e3;font-family:Arial,Helvetica,sans-serif;color:#2b2420;">
  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f6f1e3;padding:32px 16px;">
    <tr>
      <td align="center">
        <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;">
          @include('emails.partials.logo-header')
          <tr>
            <td style="background:#ffffff;border:1px solid #e6dfd0;">
              <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                <tr>
                  <td style="background:#0b251f;padding:26px 32px;color:#fff;">
                    <div style="font-size:12px;letter-spacing:.16em;text-transform:uppercase;color:#ba8f40;margin-bottom:6px;">Consultation update</div>
                    <div style="font-family:Georgia,serif;font-size:24px;line-height:1.3;color:#fff;">{{ $heading }}</div>
                  </td>
                </tr>
                <tr>
                  <td style="padding:28px 32px 12px;font-size:15px;line-height:1.6;color:#3a322c;">
                    Hi {{ $name }},
                    <br><br>
                    {{ $body }}
                  </td>
                </tr>
                @if($adminMessage)
                <tr>
                  <td style="padding:8px 32px 8px;">
                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#faf7f1;border-left:3px solid #806559;">
                      <tr>
                        <td style="padding:14px 16px;font-size:14px;line-height:1.6;color:#3a322c;">
                          <div style="font-size:11px;letter-spacing:.12em;text-transform:uppercase;color:#806559;margin-bottom:6px;">Message from our team</div>
                          {!! nl2br(e($adminMessage)) !!}
                        </td>
                      </tr>
                    </table>
                  </td>
                </tr>
                @endif
                <tr>
                  <td style="padding:12px 32px 24px;">
                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f8f5ee;border:1px solid #ece5d6;">
                      <tr>
                        <td style="padding:14px 16px;font-size:13px;line-height:1.8;color:#3a322c;">
                          <strong>Interest:</strong> {{ $interest }}<br>
                          <strong>Requested on:</strong> {{ $requestedAt }}<br>
                          <strong>Status:</strong> {{ $statusLabel }}
                        </td>
                      </tr>
                    </table>
                  </td>
                </tr>
                <tr>
                  <td align="center" style="padding:0 32px 32px;">
                    <a href="{{ $actionUrl }}" style="display:inline-block;background:#806559;color:#ffffff;text-decoration:none;font-size:13px;letter-spacing:.08em;text-transform:uppercase;padding:14px 28px;">{{ $actionText }}</a>
                  </td>
                </tr>
                <tr>
                  <td style="padding:0 32px 28px;font-size:14px;color:#6b625a;">
                    With warm regards,<br>
                    <strong style="color:#0b251f;">{{ config('app.name') }}</strong>
                  </td>
                </tr>
              </table>
            </td>
          </tr>
          <tr>
            <td align="center" style="padding:20px 8px 0;font-size:12px;color:#8a7f73;">
              © {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</body>
</html>
