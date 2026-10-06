<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Test inbox - Vastutathastu</title>
  <link rel="icon" type="image/svg+xml" href="{{ asset('vastu/images/favicon.svg') }}?v=vt2">
  <link rel="icon" type="image/png" sizes="50x50" href="{{ asset('vastu/images/favicon-50.png') }}?v=vt2">
  <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('vastu/images/favicon-32.png') }}?v=vt2">
  <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('vastu/images/favicon-16.png') }}?v=vt2">
  <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('vastu/images/apple-touch-icon.png') }}?v=vt2">
  <style>
    *{box-sizing:border-box} body{margin:0;font-family:Inter,system-ui,-apple-system,Segoe UI,sans-serif;background:#f5f3ef;color:#2b2420}
    header{display:flex;align-items:center;gap:16px;padding:14px 22px;background:#1e2a24;color:#fff}
    header h1{margin:0;font-size:18px;font-weight:600} header p{margin:0;font-size:13px;opacity:.75}
    header form{margin-left:auto} header button,header a{height:36px;padding:0 14px;border-radius:8px;border:1px solid rgba(255,255,255,.35);background:transparent;color:#fff;font-size:13px;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center}
    .wrap{display:grid;grid-template-columns:360px 1fr;height:calc(100vh - 64px)}
    .list{overflow:auto;border-right:1px solid #e6dfd0;background:#fff}
    .item{display:block;padding:14px 18px;border-bottom:1px solid #f0ebe1;color:inherit;text-decoration:none}
    .item:hover{background:#faf7f1} .item.is-active{background:#f3ece0;box-shadow:inset 3px 0 0 #806559}
    .item b{display:block;font-size:14px;margin-bottom:4px} .item small{display:block;font-size:12px;color:#7a6f66}
    .view{display:flex;flex-direction:column;min-width:0}
    .meta{padding:14px 20px;background:#fff;border-bottom:1px solid #e6dfd0;font-size:13px;line-height:1.7}
    .meta strong{display:inline-block;width:64px;color:#7a6f66;font-weight:600}
    iframe{flex:1;width:100%;min-height:70vh;border:0;background:#f4efe6}
    .empty{padding:40px;text-align:center;color:#7a6f66}
    @media (max-width:800px){.wrap{grid-template-columns:1fr;height:auto}.list{max-height:40vh}iframe{height:80vh}}
  </style>
</head>
<body>
  <header>
    <div>
      <h1>Test inbox</h1>
      <p>Emails the site sends on this computer are caught here instead of being delivered. Local testing only.</p>
    </div>
    <form method="POST" action="{{ route('dev.mailbox.clear') }}" onsubmit="return confirm('Remove all test emails?')">
      @csrf
      <button type="submit">Empty inbox</button>
    </form>
    <a href="{{ route('admin.orders.index') }}">Back to admin</a>
  </header>
  <div class="wrap">
    <nav class="list" aria-label="Emails">
      @forelse($emails as $e)
        <a class="item{{ $current && $current['id'] === $e['id'] ? ' is-active' : '' }}" href="{{ route('dev.mailbox', $e['id']) }}">
          <b>{{ $e['subject'] ?: '(no subject)' }}</b>
          <small>To: {{ implode(', ', $e['to']) }}</small>
          <small>{{ \Carbon\Carbon::parse($e['date'])->format('d M Y, h:i:s A') }}</small>
        </a>
      @empty
        <p class="empty">No emails yet.<br>Place an order or change an order's status in the admin panel, then refresh this page.</p>
      @endforelse
    </nav>
    <section class="view">
      @if($current)
        <div class="meta">
          <div><strong>Subject</strong> {{ $current['subject'] }}</div>
          <div><strong>From</strong> {{ implode(', ', $current['from']) }}</div>
          <div><strong>To</strong> {{ implode(', ', $current['to']) }}</div>
          <div><strong>Date</strong> {{ \Carbon\Carbon::parse($current['date'])->format('d M Y, h:i:s A') }}</div>
        </div>
        <iframe title="Email preview" sandbox="allow-same-origin allow-popups" srcdoc="{{ $current['html'] ?: '<pre>'.e($current['text']).'</pre>' }}"></iframe>
      @else
        <p class="empty">Select an email to read it.</p>
      @endif
    </section>
  </div>
</body>
</html>
