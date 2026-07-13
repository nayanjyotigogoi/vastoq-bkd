<?php

namespace App\Observers;

use App\Mail\SavedSearchAlertMail;
use App\Models\Listing;
use App\Models\SavedSearch;
use App\Notifications\ListingApprovedNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ListingObserver
{
    public function updated(Listing $listing): void
    {
        // Only trigger when a listing is freshly approved
        if (!$listing->wasChanged('status') || $listing->status !== 'approved') {
            return;
        }

        // Notify the listing owner
        if ($listing->owner) {
            try {
                $listing->owner->notify(new ListingApprovedNotification($listing));
            } catch (\Throwable $e) {
                Log::error('[LISTING_APPROVED] Notification failed', ['listing_id' => $listing->id, 'error' => $e->getMessage()]);
            }
        }

        $searches = SavedSearch::with('user')
            ->where('is_active', true)
            ->whereNotNull('user_id')
            ->get();

        foreach ($searches as $search) {
            if (!$search->matches($listing)) continue;
            if (!$search->user?->email) continue;

            // Throttle: don't alert the same saved search more than once per hour
            if ($search->last_alerted_at && $search->last_alerted_at->diffInMinutes(now()) < 60) {
                continue;
            }

            try {
                Mail::to($search->user->email)->send(new SavedSearchAlertMail($search, $listing));
                $search->update(['last_alerted_at' => now()]);
            } catch (\Throwable $e) {
                Log::error('[SAVED_SEARCH] Alert mail failed', [
                    'search_id'  => $search->id,
                    'listing_id' => $listing->id,
                    'error'      => $e->getMessage(),
                ]);
            }
        }
    }
}
