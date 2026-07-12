<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Your Vastoq Verification Code</title>
    <style>
        body { margin: 0; padding: 0; background: #f4f4f7; font-family: Arial, sans-serif; }
        .wrapper { max-width: 600px; margin: 40px auto; background: #ffffff; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.08); }
        .header { background: linear-gradient(135deg, #2563eb, #7c3aed); padding: 40px 30px; text-align: center; }
        .header h1 { color: #ffffff; margin: 0; font-size: 28px; letter-spacing: 1px; }
        .header p { color: rgba(255,255,255,0.85); margin: 8px 0 0; font-size: 15px; }
        .body { padding: 36px 30px; color: #374151; text-align: center; }
        .body h2 { font-size: 20px; margin-top: 0; color: #111827; }
        .body p { line-height: 1.7; color: #6b7280; font-size: 15px; }
        .otp-box { display: inline-block; margin: 24px auto; padding: 18px 40px; background: #f0f4ff; border: 2px dashed #2563eb; border-radius: 12px; }
        .otp-code { font-size: 40px; font-weight: 900; letter-spacing: 10px; color: #1B2B6B; font-family: 'Courier New', monospace; }
        .expiry { margin-top: 8px; font-size: 13px; color: #9ca3af; }
        .warning { margin-top: 20px; font-size: 13px; color: #ef4444; background: #fef2f2; border-radius: 8px; padding: 10px 16px; display: inline-block; }
        .footer { background: #f9fafb; padding: 20px 30px; text-align: center; font-size: 12px; color: #9ca3af; border-top: 1px solid #e5e7eb; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="header">
            <h1>Vastoq</h1>
            <p>Your trusted rental &amp; worker platform</p>
        </div>
        <div class="body">
            <h2>Email Verification Code</h2>
            <p>Use the code below to verify your email address and complete your sign-up.</p>

            <div class="otp-box">
                <div class="otp-code">{{ $otp }}</div>
                <div class="expiry">Expires in 10 minutes</div>
            </div>

            <p>Enter this code on the Vastoq sign-up page to verify your email.</p>

            <div class="warning">
                🔒 Do not share this code with anyone. Vastoq will never ask for it.
            </div>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} Vastoq. All rights reserved.<br/>
            If you did not request this code, please ignore this email.
        </div>
    </div>
</body>
</html>
