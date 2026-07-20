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
  .reason-box { background:#F5F0E8; border-radius:8px; padding:14px 16px; margin-top:10px; font-size:14px; color:#1A1814; line-height:1.6; }
  .label { font-weight:600; color:#1A1814; }
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
    <h1>Update on Your Report</h1>
    <p>Hi {{ $report->user->name }}, thank you for reaching out. Our team has reviewed your report regarding the following contact:</p>

    <div class="card">
      <p><span class="label">Report Type:</span> {{ ucfirst($report->reportable_type) }}</p>
      @if($report->isListing() && $report->listing)
        <p><span class="label">Property:</span> {{ $report->listing->title }}</p>
        <p><span class="label">Location:</span> {{ $report->listing->locality }}, {{ $report->listing->city }}</p>
        <p><span class="label">Owner Number:</span> {{ $report->listing->owner?->phone ?? 'N/A' }}</p>
      @elseif($report->isWorker() && $report->worker)
        <p><span class="label">Worker:</span> {{ $report->worker->user?->name ?? 'N/A' }}</p>
        <p><span class="label">Category:</span> {{ $report->worker->category }}</p>
        <p><span class="label">Worker Number:</span> {{ $report->worker->user?->phone ?? 'N/A' }}</p>
      @endif
      <p style="margin-top:10px;"><span class="label">Issue Reported:</span> {{ $report->reason_label }}</p>
      @if($report->elaborated_reason)
        <p><span class="label">Your Details:</span> {{ $report->elaborated_reason }}</p>
      @endif
    </div>

    <p>After careful review, we were unable to approve a refund for this report. Here is the reason provided by our team:</p>
    <div class="reason-box">{{ $report->admin_note ?? 'No additional reason provided.' }}</div>

    <p style="margin-top:18px;">If you believe this decision is incorrect, please don't hesitate to reach out to our support team with additional details.</p>
    <a href="mailto:support@vastoq.in" class="btn">Contact Support →</a>
  </div>
  <div class="footer">
    <p>Operated by Anvaya Solution, Dhemaji, Assam. For support: support@vastoq.in</p>
  </div>
</div>
</body>
</html>
