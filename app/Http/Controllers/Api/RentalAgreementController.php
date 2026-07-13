<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use Illuminate\Http\Request;

class RentalAgreementController extends Controller
{
    public function generate(Request $request, $listingId)
    {
        $request->validate([
            'tenant_name'    => 'required|string|max:100',
            'tenant_phone'   => 'required|digits:10',
            'tenant_address' => 'required|string|max:300',
            'tenant_aadhaar' => 'nullable|digits:12',
            'start_date'     => 'required|date',
            'duration_months'=> 'required|integer|min:1|max:60',
        ]);

        $listing = Listing::with('owner')->findOrFail($listingId);

        $data = [
            'listing'         => $listing,
            'owner'           => $listing->owner,
            'tenantName'      => $request->tenant_name,
            'tenantPhone'     => $request->tenant_phone,
            'tenantAddress'   => $request->tenant_address,
            'tenantAadhaar'   => $request->tenant_aadhaar,
            'startDate'       => \Carbon\Carbon::parse($request->start_date),
            'durationMonths'  => (int) $request->duration_months,
            'generatedAt'     => now(),
        ];

        $pdf = app('dompdf.wrapper');
        $pdf->loadView('rental-agreement', $data);
        $pdf->setPaper('A4', 'portrait');

        $filename = 'vastoq-agreement-' . $listingId . '-' . now()->format('Ymd') . '.pdf';

        return $pdf->download($filename);
    }
}
