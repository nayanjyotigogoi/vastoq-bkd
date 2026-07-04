<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
  body { margin:0; padding:0; background:#F5F0E8; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; }
  .wrap { max-width:560px; margin:40px auto; background:#fff; border-radius:16px; overflow:hidden; }
  .header { background:#D84040; padding:24px 36px; }
  .header h2 { margin:0; color:#fff; font-size:18px; }
  .body { padding:28px 36px; }
  p { margin:0 0 10px; font-size:14px; color:#4A4640; }
  .label { font-weight:700; color:#1A1814; }
  .msg { background:#F8F8F8; border:1px solid #E5E0D5; border-radius:8px; padding:14px; margin:16px 0; font-size:14px; white-space:pre-wrap; }
</style>
</head>
<body>
<div class="wrap">
  <div class="header"><h2>📬 New Contact Message — Vastoq</h2></div>
  <div class="body">
    <p><span class="label">From:</span> {{ $contactMessage->name }}</p>
    <p><span class="label">Email:</span> {{ $contactMessage->email }}</p>
    @if($contactMessage->phone)
    <p><span class="label">Phone:</span> {{ $contactMessage->phone }}</p>
    @endif
    <p><span class="label">Subject:</span> {{ $contactMessage->subject }}</p>
    <p><span class="label">Type:</span> {{ ucfirst($contactMessage->type) }}</p>
    <p><span class="label">Message:</span></p>
    <div class="msg">{{ $contactMessage->message }}</div>
    <p style="font-size:12px;color:#8A8480;">Submitted: {{ $contactMessage->created_at->format('d M Y, H:i') }}</p>
  </div>
</div>
</body>
</html>
