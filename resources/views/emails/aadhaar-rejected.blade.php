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
  .card { background:#FFF4F4; border:1px solid #D8404030; border-radius:10px; padding:18px 20px; margin:20px 0; }
  .card p { margin:4px 0; font-size:14px; }
  .btn { display:inline-block; margin-top:8px; padding:13px 28px; background:#1B2B6B; color:#fff; text-decoration:none; border-radius:8px; font-size:14px; font-weight:600; }
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
    <h1>Aadhaar verification unsuccessful</h1>
    <p>Hi {{ $user->name }}, we were unable to verify your Aadhaar at this time.</p>
    <div class="card">
      <p style="font-weight:600;color:#D84040;">Reason for rejection:</p>
      <p style="margin-top:6px;">{{ $reason }}</p>
    </div>
    <p>Please resubmit clear photos of both sides of your Aadhaar card. Make sure the images are well-lit, in focus, and all details are readable.</p>
    <a href="{{ env('FRONTEND_URL', 'https://vastoq.com') }}/worker/dashboard" class="btn">Resubmit Aadhaar →</a>
  </div>
  <div class="footer">
    <p>Operated by Anvaya Solution, Dhemaji, Assam. For support: support@tohfaah.online</p>
  </div>
</div>
</body>
</html>
