<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
  body { margin:0; padding:0; background:#F5F0E8; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; }
  .wrap { max-width:560px; margin:40px auto; background:#fff; border-radius:16px; overflow:hidden; box-shadow:0 2px 12px rgba(0,0,0,0.08); }
  .header { background:#1B2B6B; padding:32px 40px; }
  .logo { font-size:24px; font-weight:800; color:#fff; }
  .logo span { color:#1D9E75; }
  .body { padding:36px 40px; }
  h1 { margin:0 0 16px; font-size:22px; color:#1A1814; font-weight:700; }
  p { margin:0 0 14px; font-size:15px; color:#4A4640; line-height:1.6; }
  .quote { background:#F8F8F8; border-left:3px solid #1B2B6B; padding:14px 18px; border-radius:0 8px 8px 0; margin:16px 0; font-size:14px; color:#4A4640; }
  .footer { padding:20px 40px; border-top:1px solid #F0EBE3; }
  .footer p { margin:0; font-size:12px; color:#8A8480; }
</style>
</head>
<body>
<div class="wrap">
  <div class="header">
    <div class="logo">Vastoq<span>.</span></div>
  </div>
  <div class="body">
    <h1>We received your message ✅</h1>
    <p>Hi {{ $senderName }}, thank you for reaching out to Vastoq Support.</p>
    <p>We have received your message regarding <strong>"{{ $mailSubject }}"</strong> and our team will get back to you within <strong>24 hours</strong>.</p>
    <p>Your message:</p>
    <div class="quote">{{ $userMessage }}</div>
    <p>If your issue is urgent, you can also email us directly at <a href="mailto:support@tohfaah.online">support@tohfaah.online</a>.</p>
    <p>— Vastoq Support Team<br><small>Operated by Anvaya Solution, Dhemaji, Assam</small></p>
  </div>
  <div class="footer">
    <p>You're receiving this because you submitted a contact form on vastoq.com. Grievance resolution within 15 days as per IT Rules 2021.</p>
  </div>
</div>
</body>
</html>
