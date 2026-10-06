<!DOCTYPE html>
<html lang="en">
<body style="margin:0;padding:24px;background:#f4efe6;font-family:Arial,Helvetica,sans-serif;color:#2b2420;">
  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:560px;margin:0 auto;background:#fff;border-radius:10px;overflow:hidden;">
    <tr><td style="background:#0c3f33;color:#fff;padding:20px 24px;font-size:18px;font-weight:bold;">New enquiry from the website</td></tr>
    <tr><td style="padding:22px 24px;font-size:15px;line-height:1.6;">
      <table role="presentation" cellpadding="0" cellspacing="0" style="width:100%;font-size:15px;">
        <tr><td style="padding:4px 0;color:#7a6f66;width:120px;">Name</td><td style="padding:4px 0;"><strong>{{ $contact->name }}</strong></td></tr>
        <tr><td style="padding:4px 0;color:#7a6f66;">Phone</td><td style="padding:4px 0;"><a href="tel:{{ preg_replace('/[^\d+]/', '', (string) $contact->phone) }}" style="color:#0c3f33;">{{ $contact->phone ?: '—' }}</a></td></tr>
        <tr><td style="padding:4px 0;color:#7a6f66;">Email</td><td style="padding:4px 0;">{{ $contact->email ?: '—' }}</td></tr>
        <tr><td style="padding:4px 0;color:#7a6f66;">City</td><td style="padding:4px 0;">{{ $contact->city ?: '—' }}</td></tr>
        <tr><td style="padding:4px 0;color:#7a6f66;">Enquiry about</td><td style="padding:4px 0;">{{ $contact->subject ?: '—' }}</td></tr>
      </table>
      <p style="margin:18px 0 6px;color:#7a6f66;">Message</p>
      <div style="padding:14px 16px;background:#f7f4ee;border-radius:8px;white-space:pre-line;">{{ $contact->message }}</div>
      <p style="margin:18px 0 0;font-size:13px;color:#7a6f66;">Reply to this email to answer {{ $contact->name }} directly. All enquiries are also listed in Admin → Contact List.</p>
    </td></tr>
  </table>
</body>
</html>
