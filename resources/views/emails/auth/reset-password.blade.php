<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Reset your password</title>
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
                    <div style="font-size:12px;letter-spacing:.16em;text-transform:uppercase;opacity:.85;margin-bottom:6px;">Security</div>
                    <div style="font-family:Georgia,serif;font-size:26px;color:#fff;">Reset your password</div>
                  </td>
                </tr>
                <tr>
                  <td style="padding:28px 32px 12px;font-size:15px;line-height:1.6;color:#3d3045;">
                    Hi {{ $user->name ?: 'there' }},
                    <br><br>
                    We received a request to reset the password for your Vastutathastu account.
                    Click the button below to choose a new password.
                  </td>
                </tr>
                <tr>
                  <td align="center" style="padding:12px 32px 28px;">
                    <a href="{{ $resetUrl }}" style="display:inline-block;background:#3a1651;color:#ffffff;text-decoration:none;font-size:13px;letter-spacing:.08em;text-transform:uppercase;padding:14px 28px;border-radius:2px;">
                      Reset password
                    </a>
                  </td>
                </tr>
                <tr>
                  <td style="padding:0 32px 24px;font-size:13px;line-height:1.6;color:#6b5a74;">
                    This link expires in {{ $expiresMinutes }} minutes.
                    If you did not request a password reset, you can ignore this email — your password will stay the same.
                  </td>
                </tr>
                <tr>
                  <td style="padding:0 32px 28px;font-size:12px;line-height:1.6;color:#8a7a90;word-break:break-all;">
                    Or copy this link into your browser:<br>
                    <a href="{{ $resetUrl }}" style="color:#3a1651;">{{ $resetUrl }}</a>
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
