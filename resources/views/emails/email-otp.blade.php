<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
  body { margin:0; padding:0; background:#F5F0E8; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; }
  .wrap { max-width:560px; margin:40px auto; background:#fff; border-radius:16px; overflow:hidden; box-shadow:0 2px 12px rgba(0,0,0,0.08); }
  .header { background:#1B2B6B; padding:32px 40px; }
  .logo { font-size:24px; font-weight:800; color:#fff; letter-spacing:-0.5px; }
  .logo span { color:#1D9E75; }
  .body { padding:36px 40px; }
  h1 { margin:0 0 16px; font-size:22px; color:#1A1814; font-weight:700; }
  p { margin:0 0 14px; font-size:15px; color:#4A4640; line-height:1.6; }
  .otp-box { margin:24px 0; text-align:center; padding:24px; background:#E8ECF8; border-radius:12px; }
  .otp-code { font-size:40px; font-weight:800; color:#1B2B6B; letter-spacing:12px; }
  .note { font-size:13px; color:#8A8480; margin-top:8px; }
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
    <h1>Verify your email</h1>
    <p>Use the code below to complete your Vastoq registration. It expires in <strong>10 minutes</strong>.</p>
    <div class="otp-box">
      <div class="otp-code">{{ $otp }}</div>
      <p class="note">Enter this code on the registration page</p>
    </div>
    <p>If you did not request this, you can safely ignore this email.</p>
  </div>
  <div class="footer">
    <p>Vastoq · Operated by Anvaya Solution, Dhemaji, Assam.</p>
  </div>
</div>
</body>
</html>
