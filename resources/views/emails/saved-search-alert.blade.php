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
  h1 { margin:0 0 8px; font-size:22px; color:#1A1814; font-weight:700; }
  .subtitle { margin:0 0 24px; font-size:14px; color:#8A8480; }
  p { margin:0 0 14px; font-size:15px; color:#4A4640; line-height:1.6; }
  .card { border:1px solid #E5E0D5; border-radius:12px; padding:20px 24px; margin:20px 0; }
  .card-title { font-size:16px; font-weight:700; color:#1A1814; margin:0 0 6px; }
  .card-meta { font-size:13px; color:#6F6A63; margin:0 0 16px; }
  .price { font-size:22px; font-weight:800; color:#1B2B6B; }
  .price small { font-size:13px; font-weight:400; color:#6F6A63; }
  .tags { display:flex; flex-wrap:wrap; gap:6px; margin:12px 0 0; }
  .tag { background:#F0EBE3; border-radius:6px; padding:4px 10px; font-size:12px; color:#4A4640; font-weight:600; }
  .btn { display:inline-block; background:#1B2B6B; color:#fff; text-decoration:none; padding:12px 28px; border-radius:10px; font-size:14px; font-weight:700; margin-top:20px; }
  .footer { padding:20px 40px; border-top:1px solid #F0EBE3; }
  .footer p { margin:0; font-size:12px; color:#8A8480; }
  .footer a { color:#1B2B6B; }
</style>
</head>
<body>
<div class="wrap">
  <div class="header">
    <div class="logo">Vastoq<span>.</span></div>
  </div>
  <div class="body">
    <h1>New listing matches your search</h1>
    <p class="subtitle">Alert: "{{ $savedSearch->name }}"</p>

    <p>Hi {{ $savedSearch->user->name }}, a new listing just went live that matches your saved search.</p>

    <div class="card">
      <div class="card-title">{{ $listing->title }}</div>
      <div class="card-meta">{{ $listing->locality }}, {{ $listing->city }}</div>
      <div class="price">₹{{ number_format($listing->rent_per_month) }} <small>/ month</small></div>
      <div class="tags">
        <span class="tag">{{ strtoupper(str_replace('_', ' ', $listing->property_type)) }}</span>
        @if($listing->bhk_type !== 'na')
          <span class="tag">{{ strtoupper($listing->bhk_type) }}</span>
        @endif
        <span class="tag">{{ ucfirst(str_replace('_', ' ', $listing->furnishing)) }}</span>
        @if($listing->area_sqft)
          <span class="tag">{{ $listing->area_sqft }} sqft</span>
        @endif
      </div>
    </div>

    <a href="{{ env('FRONTEND_URL', 'https://vastoq.com') }}/rentals/{{ $listing->id }}" class="btn">View this listing →</a>

    <p style="margin-top:24px; font-size:13px; color:#8A8480;">
      New listings are reviewed and approved before going live, so this has already been verified by our team.
    </p>
  </div>
  <div class="footer">
    <p>You're getting this because you saved a search alert on <a href="{{ env('FRONTEND_URL', 'https://vastoq.com') }}">vastoq.com</a>. <a href="{{ env('FRONTEND_URL', 'https://vastoq.com') }}/dashboard">Manage alerts</a>.</p>
  </div>
</div>
</body>
</html>
