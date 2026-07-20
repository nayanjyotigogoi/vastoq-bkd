<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ContactReport;
use App\Models\Listing;
use App\Models\ListingUnlock;
use App\Models\User;
use App\Models\Worker;
use App\Models\WorkerUnlock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ContactReportController extends Controller
{
    /**
     * POST /contact-reports
     *
     * Body:
     *  user_id          required
     *  reportable_type  required  listing|worker
     *  reportable_id    required  ID of the listing or worker
     *  reason           required  already_rented|invalid_details|extra_brokerage|other
     *  elaborated_reason optional  free-text elaboration
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id'           => 'required|exists:users,id',
            'phone'             => 'nullable|string|digits:10',
            'reportable_type'   => 'required|in:listing,worker',
            'reportable_id'     => 'required|integer|min:1',
            'reason'            => 'required|in:already_rented,invalid_details,extra_brokerage,other',
            'elaborated_reason' => [
                'nullable',
                'string',
                'max:1000',
                // 'other' requires the elaborated field
                function ($attribute, $value, $fail) use ($request) {
                    if ($request->reason === 'other' && empty($value)) {
                        $fail('Please describe the issue when selecting "Other".');
                    }
                },
            ],
        ]);

        $user = User::findOrFail($data['user_id']);

        // Update user profile phone if missing and provided
        if (!empty($data['phone']) && empty($user->phone)) {
            $user->update(['phone' => $data['phone']]);
        }

        $reporterPhone = $data['phone'] ?? $user->phone;

        // Verify the user actually unlocked this listing/worker
        if ($data['reportable_type'] === 'listing') {
            $hasUnlock = ListingUnlock::where('listing_id', $data['reportable_id'])
                ->where('user_id', $user->id)
                ->exists();
        } else {
            $hasUnlock = WorkerUnlock::where('worker_id', $data['reportable_id'])
                ->where('user_id', $user->id)
                ->exists();
        }

        if (!$hasUnlock) {
            return response()->json([
                'success' => false,
                'message' => 'You have not unlocked this contact.',
            ], 403);
        }

        // Prevent duplicate reports
        $existing = ContactReport::where('user_id', $user->id)
            ->where('reportable_type', $data['reportable_type'])
            ->where('reportable_id', $data['reportable_id'])
            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'You have already submitted a report for this contact.',
                'data'    => ['status' => $existing->status],
            ], 409);
        }

        $report = ContactReport::create([
            'user_id'           => $user->id,
            'reporter_phone'    => $reporterPhone,
            'reportable_type'   => $data['reportable_type'],
            'reportable_id'     => $data['reportable_id'],
            'reason'            => $data['reason'],
            'elaborated_reason' => $data['elaborated_reason'] ?? null,
            'status'            => 'pending',
        ]);

        Log::info('[CONTACT REPORT] New report', [
            'report_id' => $report->id,
            'user_id'   => $user->id,
            'type'      => $data['reportable_type'],
            'id'        => $data['reportable_id'],
            'reason'    => $data['reason'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Your report has been submitted. Our team will review it within 24–48 hours.',
            'data'    => ['report_id' => $report->id, 'status' => 'pending'],
        ], 201);
    }

    /**
     * GET /contact-reports/status?user_id=X&reportable_type=listing&reportable_id=Y
     * Used by the dashboard and detail pages to show report status.
     */
    public function userStatus(Request $request)
    {
        $request->validate([
            'user_id'         => 'required|exists:users,id',
            'reportable_type' => 'required|in:listing,worker',
            'reportable_id'   => 'required|integer',
        ]);

        $report = ContactReport::where('user_id', $request->user_id)
            ->where('reportable_type', $request->reportable_type)
            ->where('reportable_id', $request->reportable_id)
            ->first();

        if (!$report) {
            return response()->json(['success' => true, 'data' => ['reported' => false]]);
        }

        return response()->json([
            'success' => true,
            'data'    => [
                'reported'       => true,
                'status'         => $report->status,
                'reason'         => $report->reason,
                'reason_label'   => $report->reason_label,
                'admin_note'     => $report->admin_note,
                'points_refunded'=> $report->points_refunded,
                'created_at'     => $report->created_at,
            ],
        ]);
    }
}
