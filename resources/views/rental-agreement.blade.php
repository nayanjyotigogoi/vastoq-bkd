<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
  * { box-sizing: border-box; }
  body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 12px; color: #1A1814; margin: 0; padding: 40px; line-height: 1.6; }
  .header { text-align: center; border-bottom: 3px solid #1B2B6B; padding-bottom: 16px; margin-bottom: 24px; }
  .logo { font-size: 26px; font-weight: bold; color: #1B2B6B; letter-spacing: -0.5px; }
  .logo span { color: #1D9E75; }
  .doc-title { font-size: 16px; font-weight: bold; color: #1A1814; margin-top: 6px; }
  .doc-subtitle { font-size: 11px; color: #6F6A63; margin-top: 2px; }
  h2 { font-size: 13px; font-weight: bold; color: #1B2B6B; border-bottom: 1px solid #E5E0D5; padding-bottom: 5px; margin: 20px 0 10px; text-transform: uppercase; letter-spacing: 0.4px; }
  table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
  table td { padding: 6px 8px; font-size: 11.5px; vertical-align: top; }
  table td:first-child { font-weight: bold; width: 38%; color: #4A4640; }
  .clause { margin-bottom: 10px; }
  .clause-num { font-weight: bold; color: #1B2B6B; }
  .highlight-box { background: #F0EBE3; border-left: 3px solid #1B2B6B; padding: 10px 14px; margin: 12px 0; border-radius: 0 6px 6px 0; font-size: 11.5px; }
  .sign-row { display: table; width: 100%; margin-top: 40px; }
  .sign-col { display: table-cell; width: 50%; vertical-align: top; padding: 0 10px; }
  .sign-line { border-top: 1px solid #1A1814; margin-top: 50px; padding-top: 6px; font-size: 11px; color: #4A4640; }
  .footer { margin-top: 30px; border-top: 1px solid #E5E0D5; padding-top: 12px; font-size: 10px; color: #8A8480; text-align: center; }
  .page-break { page-break-before: always; }
</style>
</head>
<body>

<div class="header">
  <div class="logo">Vastoq<span>.</span></div>
  <div class="doc-title">RESIDENTIAL / COMMERCIAL RENTAL AGREEMENT</div>
  <div class="doc-subtitle">Generated on {{ $generatedAt->format('d F Y') }} | Vastoq — Powered by Anvaya Solution, Dhemaji, Assam</div>
</div>

<h2>1. Parties to the Agreement</h2>
<table>
  <tr><td>Landlord (Owner)</td><td>{{ $owner->name }}</td></tr>
  <tr><td>Owner Phone</td><td>{{ $owner->phone ?? 'As discussed' }}</td></tr>
  <tr><td>Tenant</td><td>{{ $tenantName }}</td></tr>
  <tr><td>Tenant Phone</td><td>{{ $tenantPhone }}</td></tr>
  <tr><td>Tenant Address</td><td>{{ $tenantAddress }}</td></tr>
  @if($tenantAadhaar)
  <tr><td>Tenant Aadhaar (last 4)</td><td>XXXX-XXXX-{{ substr($tenantAadhaar, -4) }}</td></tr>
  @endif
</table>

<h2>2. Property Details</h2>
<table>
  <tr><td>Property Title</td><td>{{ $listing->title }}</td></tr>
  <tr><td>Address</td><td>{{ $listing->address }}</td></tr>
  <tr><td>Locality / City</td><td>{{ $listing->locality }}, {{ $listing->city }}@if($listing->pincode) — {{ $listing->pincode }}@endif</td></tr>
  <tr><td>Property Type</td><td>{{ strtoupper(str_replace('_', ' ', $listing->property_type)) }}</td></tr>
  @if($listing->bhk_type !== 'na')
  <tr><td>Configuration</td><td>{{ strtoupper($listing->bhk_type) }}</td></tr>
  @endif
  @if($listing->area_sqft)
  <tr><td>Area</td><td>{{ $listing->area_sqft }} sq. ft.</td></tr>
  @endif
  <tr><td>Furnishing</td><td>{{ ucfirst(str_replace('_', ' ', $listing->furnishing)) }}</td></tr>
</table>

<h2>3. Tenancy Terms</h2>
<table>
  <tr><td>Monthly Rent</td><td>₹{{ number_format($listing->rent_per_month) }} (Rupees {{ $listing->rent_per_month_words ?? number_format($listing->rent_per_month) }} only)</td></tr>
  <tr><td>Security Deposit</td><td>₹{{ number_format($listing->deposit) }}</td></tr>
  <tr><td>Lease Start Date</td><td>{{ $startDate->format('d F Y') }}</td></tr>
  <tr><td>Lease End Date</td><td>{{ $startDate->copy()->addMonths($durationMonths)->format('d F Y') }}</td></tr>
  <tr><td>Duration</td><td>{{ $durationMonths }} month(s)</td></tr>
  <tr><td>Rent Due Date</td><td>1st of every month</td></tr>
</table>

<h2>4. Terms and Conditions</h2>

<div class="clause"><span class="clause-num">4.1 Rent Payment.</span> The Tenant shall pay the monthly rent of ₹{{ number_format($listing->rent_per_month) }} on or before the 1st of each calendar month. A grace period of 5 days is provided. Rent paid after the grace period shall attract a late fee of ₹{{ number_format($listing->rent_per_month * 0.02, 0) }} per day.</div>

<div class="clause"><span class="clause-num">4.2 Security Deposit.</span> The security deposit of ₹{{ number_format($listing->deposit) }} shall be refunded within 30 days of vacating the premises, after deducting any outstanding dues or repair costs caused by the Tenant's negligence.</div>

<div class="clause"><span class="clause-num">4.3 Notice Period.</span> Either party shall give a written notice of at least 30 days before terminating this agreement. In case of non-compliance, one month's rent shall be forfeited.</div>

<div class="clause"><span class="clause-num">4.4 Use of Premises.</span> The Tenant shall use the premises only for the purpose stated and shall not sublease, assign, or transfer the tenancy without the Landlord's prior written consent.</div>

<div class="clause"><span class="clause-num">4.5 Maintenance.</span> The Tenant shall maintain the premises in good condition. Day-to-day minor repairs (under ₹500) shall be borne by the Tenant. Major structural repairs shall be the Landlord's responsibility.</div>

<div class="clause"><span class="clause-num">4.6 Utilities.</span> Electricity, water, internet and other utility charges shall be paid directly by the Tenant as per actual consumption, unless otherwise agreed in writing.</div>

<div class="clause"><span class="clause-num">4.7 Right of Entry.</span> The Landlord may inspect the premises with at least 24 hours prior notice to the Tenant, except in cases of emergency.</div>

<div class="clause"><span class="clause-num">4.8 Alterations.</span> The Tenant shall not make any structural alterations, paint, drill, or fix permanent fixtures without the Landlord's prior written consent.</div>

<div class="clause"><span class="clause-num">4.9 Dispute Resolution.</span> Any disputes arising out of this agreement shall first be resolved through mutual discussion. If unresolved within 15 days, the matter shall be referred to arbitration under the Arbitration and Conciliation Act, 1996, with jurisdiction at {{ $listing->city }}, Assam.</div>

<div class="highlight-box">
  <strong>Note:</strong> This agreement has been generated digitally via Vastoq (vastoq.com). Both parties agree that a signed physical copy of this document holds full legal validity. This document serves as a draft template and should be reviewed and stamped as per applicable state stamp duty rules in Assam.
</div>

<h2>5. Signatures</h2>
<p>This agreement is entered into on <strong>{{ $startDate->format('d F Y') }}</strong> and is binding on both parties named above.</p>

<div class="sign-row">
  <div class="sign-col">
    <div class="sign-line">
      <strong>Landlord / Owner</strong><br>
      Name: {{ $owner->name }}<br>
      Date: _______________
    </div>
  </div>
  <div class="sign-col">
    <div class="sign-line">
      <strong>Tenant</strong><br>
      Name: {{ $tenantName }}<br>
      Date: _______________
    </div>
  </div>
</div>

<div class="footer">
  This document was generated by Vastoq (vastoq.com) on {{ $generatedAt->format('d F Y H:i') }}. It is a digital draft for reference purposes. Please get it printed on stamp paper of appropriate value as per Assam Stamp Act before signing. | support@tohfaah.online
</div>

</body>
</html>
