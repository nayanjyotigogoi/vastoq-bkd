<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\ListingApprovedMail;
use App\Mail\ListingRejectedMail;
use App\Models\Listing;
use App\Notifications\ListingApprovedNotification;
use App\Notifications\PaymentSuccessNotification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class AdminListingController extends Controller
{
    /**
     * GET /admin/listings
     * Returns all listings regardless of status, newest first.
     */
    public function index()
    {
        $listings = Listing::with('owner:id,name,is_verified')
            ->latest()
            ->get();

        return response()->json(['success' => true, 'data' => $listings]);
    }

    /**
     * PATCH /admin/listings/{id}
     * Actions: approve | reject | feature | unfeature
     */
    public function action(Request $request, $id)
    {
        $request->validate([
            'action' => 'required|in:approve,reject,feature,unfeature',
            'reason' => 'nullable|string|max:500',
        ]);

        $listing = Listing::with('owner:id,name,email,phone')->findOrFail($id);

        switch ($request->action) {
            case 'approve':
                $listing->update(['status' => 'approved', 'rejection_reason' => null]);
                $this->notifyOwnerApproved($listing);
                break;

            case 'reject':
                $reason = $request->reason ?? 'Does not meet platform guidelines.';
                $listing->update(['status' => 'rejected', 'rejection_reason' => $reason]);
                $this->notifyOwnerRejected($listing, $reason);
                break;

            case 'feature':
                $listing->update(['is_featured' => true]);
                break;

            case 'unfeature':
                $listing->update(['is_featured' => false]);
                break;
        }

        return response()->json([
            'success' => true,
            'message' => ucfirst($request->action) . ' action applied.',
            'data'    => $listing->fresh(),
        ]);
    }

    private function notifyOwnerRejected(Listing $listing, string $reason): void
    {
        $owner = $listing->owner;
        if (!$owner) return;
        try { $owner->notify(new PaymentSuccessNotification("Your listing \"{$listing->title}\" was not approved. Reason: {$reason}", "/owner/listings")); }
        catch (\Throwable $e) { Log::error('[ADMIN:REJECT] Owner notification failed', ['error' => $e->getMessage()]); }
        if ($owner->email) {
            try { Mail::to($owner->email)->send(new ListingRejectedMail($owner, $listing, $reason)); }
            catch (\Throwable $e) { Log::error('[ADMIN:REJECT] Owner email failed', ['error' => $e->getMessage()]); }
        }
    }

    private function notifyOwnerApproved(Listing $listing): void
    {
        $owner = $listing->owner;
        if (!$owner) return;

        // In-app notification
        try { $owner->notify(new ListingApprovedNotification($listing)); }
        catch (\Throwable $e) { Log::error('[ADMIN:APPROVE] Owner notification failed', ['error' => $e->getMessage()]); }

        // Email
        if ($owner->email) {
            try { Mail::to($owner->email)->send(new ListingApprovedMail($owner, $listing)); }
            catch (\Throwable $e) { Log::error('[ADMIN:APPROVE] Owner email failed', ['error' => $e->getMessage()]); }
        }
    }
}
