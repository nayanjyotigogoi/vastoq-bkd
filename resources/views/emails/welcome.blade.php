<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    body {
      margin: 0;
      padding: 0;
      background: #F5F0E8;
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
    }

    .wrap {
      max-width: 560px;
      margin: 40px auto;
      background: #fff;
      border-radius: 16px;
      overflow: hidden;
      box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
    }

    .header {
      background: #1B2B6B;
      padding: 32px 40px;
    }

    .logo {
      font-size: 24px;
      font-weight: 800;
      color: #fff;
      letter-spacing: -0.5px;
    }

    .logo span {
      color: #1D9E75;
    }

    .body {
      padding: 36px 40px;
    }

    h1 {
      margin: 0 0 16px;
      font-size: 22px;
      color: #1A1814;
      font-weight: 700;
    }

    p {
      margin: 0 0 14px;
      font-size: 15px;
      color: #4A4640;
      line-height: 1.6;
    }

    .btn {
      display: inline-block;
      margin-top: 8px;
      padding: 13px 28px;
      background: #1B2B6B;
      color: #fff;
      text-decoration: none;
      border-radius: 8px;
      font-size: 14px;
      font-weight: 600;
    }

    .footer {
      padding: 20px 40px;
      border-top: 1px solid #F0EBE3;
    }

    .footer p {
      margin: 0;
      font-size: 12px;
      color: #8A8480;
    }
  </style>
</head>

<body>
  <div class="wrap">
    <div class="header">
      <div class="logo">Vastoq<span>.</span></div>
    </div>
    <div class="body">
      <h1>Welcome, {{ $user->name }}! 🎉</h1>
      <p>Your Vastoq account has been created successfully. You're now part of Assam's trusted rental and services
        platform.</p>
      <p>Here's what you can do:</p>
      <p>
        @if($user->role === 'owner')
          ✅ List your property and reach thousands of tenants<br>
          ✅ Boost your listing to get to the top<br>
          ✅ Manage enquiries from your dashboard
        @elseif($user->role === 'worker')
          ✅ Set up your worker profile<br>
          ✅ Get discovered by people looking for your skills<br>
          ✅ Build your reputation with verified reviews
        @else
          ✅ Browse verified rentals across Assam<br>
          ✅ Unlock owner contacts instantly<br>
          ✅ Find trusted local workers
        @endif
      </p>
      <a href="{{ env('FRONTEND_URL', 'https://vastoq.com') }}" class="btn">Go to Vastoq →</a>
    </div>
    <div class="footer">
      <p>You're receiving this because you created an account on Vastoq. Operated by Anvaya Solution, Dhemaji, Assam.
      </p>
    </div>
  </div>
</body>

</html>