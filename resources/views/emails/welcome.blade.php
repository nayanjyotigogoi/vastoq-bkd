<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Welcome to Vastoq</title>
    <style>
        body { margin: 0; padding: 0; background: #f4f4f7; font-family: Arial, sans-serif; }
        .wrapper { max-width: 600px; margin: 40px auto; background: #ffffff; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.08); }
        .header { background: linear-gradient(135deg, #2563eb, #7c3aed); padding: 40px 30px; text-align: center; }
        .header h1 { color: #ffffff; margin: 0; font-size: 28px; letter-spacing: 1px; }
        .header p { color: rgba(255,255,255,0.85); margin: 8px 0 0; font-size: 15px; }
        .body { padding: 36px 30px; color: #374151; }
        .body h2 { font-size: 22px; margin-top: 0; color: #111827; }
        .body p { line-height: 1.7; color: #6b7280; }
        .btn { display: inline-block; margin-top: 20px; padding: 14px 32px; background: linear-gradient(135deg, #2563eb, #7c3aed); color: #ffffff; text-decoration: none; border-radius: 8px; font-size: 15px; font-weight: bold; }
        .footer { background: #f9fafb; padding: 20px 30px; text-align: center; font-size: 12px; color: #9ca3af; border-top: 1px solid #e5e7eb; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="header">
            <h1>Vastoq</h1>
            <p>Your trusted rental & worker platform</p>
        </div>
        <div class="body">
            <h2>Welcome, {{ $user->name }}! 🎉</h2>
            <p>We are thrilled to have you on board. Your account has been successfully created on Vastoq — the platform that connects tenants, property owners, and workers seamlessly.</p>
            <p>Here is what you can do right now:</p>
            <ul>
                <li>Browse available listings in your area</li>
                <li>Post your own property or service</li>
                <li>Connect with trusted workers for home services</li>
            </ul>
            <a href="{{ env('FRONTEND_URL', 'http://localhost:3000') }}/dashboard" class="btn">Go to Dashboard</a>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} Vastoq. All rights reserved.<br/>
            If you did not create this account, please ignore this email.
        </div>
    </div>
</body>
</html>
