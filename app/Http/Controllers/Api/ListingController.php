<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreListingRequest;
use App\Http\Requests\UpdateListingRequest;
use App\Models\Listing;
use App\Models\ListingEnquiry;
use App\Models\User;
use App\Mail\ListingEnquiryMail;
use App\Notifications\ListingEnquiryNotification;
use App\Notifications\PaymentSuccessNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ListingController extends Controller
{
    /**
     * Public Listings
     */
    public function index(Request $request)
    {
        // Lazily expire boosts whose window has passed — cheap indexed write,
        // avoids needing a scheduler/cron for this.
        Listing::where('is_featured', true)
            ->where('featured_until', '<', now())
            ->update(['is_featured' => false, 'featured_until' => null]);

        $query = Listing::with([
            'owner:id,name,is_verified,profile_photo_url'
        ])->where('status', 'approved');

        if ($request->filled('latitude') && $request->filled('longitude')) {
            $lat = (double) $request->latitude;
            $lng = (double) $request->longitude;
            $radius = (double) $request->get('radius', 10); // default 10 km

            // Filter listings within radius using Haversine formula
            $query->whereRaw(
                "(6371 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))) <= ?",
                [$lat, $lng, $lat, $radius]
            );

            // Sort by distance unless another sorting option is explicitly requested
            if (!$request->filled('sort')) {
                $query->orderByRaw(
                    "(6371 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))) ASC",
                    [$lat, $lng, $lat]
                );
            }
        } elseif ($request->filled('search')) {
            $this->applySearchFilter($query, trim($request->search));
        }

        if ($request->filled('property_type')) {
            $query->where('property_type', $request->property_type);
        }

        if ($request->filled('bhk_type')) {
            $query->where('bhk_type', $request->bhk_type);
        }

        if ($request->filled('furnishing')) {
            $query->where('furnishing', $request->furnishing);
        }

        if ($request->filled('gender_preference')) {
            $query->where(
                'gender_preference',
                $request->gender_preference
            );
        }

        if ($request->filled('min_rent')) {
            $query->where(
                'rent_per_month',
                '>=',
                $request->min_rent
            );
        }

        if ($request->filled('max_rent')) {
            $query->where(
                'rent_per_month',
                '<=',
                $request->max_rent
            );
        }

        if ($request->boolean('verified_only')) {

            $query->whereHas('owner', function ($q) {
                $q->where('is_verified', true);
            });
        }

        if ($request->filled('listing_class')) {
            $query->where('listing_class', $request->listing_class);
        }

        if ($request->has('is_featured')) {
            $query->where('is_featured', $request->boolean('is_featured'));
        }

        switch ($request->sort) {

            case 'price_asc':
                $query->orderBy('rent_per_month');
                break;

            case 'price_desc':
                $query->orderByDesc('rent_per_month');
                break;

            case 'popular':
                $query->orderByDesc('view_count');
                break;

            default:
                // Boosted listings float to the top, newest first within each group.
                $query->orderByDesc('is_featured')->latest();
        }

        $perPage = min((int) $request->get('per_page', 20), 500);
        $listings = $query->paginate($perPage);

        // Add is_saved / is_unlocked fields when a user_id is provided (passed by Next.js API layer)
        $userId = $request->get('user_id');
        if ($userId) {
            $savedListingIds = \App\Models\SavedListing::where('user_id', $userId)
                ->pluck('listing_id')
                ->toArray();

            $unlockedListingIds = \App\Models\ListingUnlock::where('user_id', $userId)
                ->pluck('listing_id')
                ->toArray();

            foreach ($listings as $listing) {
                $listing['is_saved']    = in_array($listing->id, $savedListingIds);
                $listing['is_unlocked'] = in_array($listing->id, $unlockedListingIds);
            }
        } else {
            foreach ($listings as $listing) {
                $listing['is_saved']    = false;
                $listing['is_unlocked'] = false;
            }
        }

        return response()->json([
            'success' => true,
            'data' => $listings
        ]);
    }

    /**
     * Progressive search: try the full term first, then drop the last word
     * one at a time until at least one listing matches — all within a single
     * request/response cycle (no client-side round trips).
     *
     * e.g. "Dibrugarh West Chariali" → 0 hits → "Dibrugarh West" → 0 hits → "Dibrugarh" → hits ✓
     *
     * Note: `description` is intentionally excluded from the match — it's a
     * large text blob that rarely contains useful unique search terms and
     * costs noticeably more per-row to scan than the short indexed columns.
     */
    private function applySearchFilter($query, string $rawSearch): void
    {
        // Strip commas and clean up search string
        $cleanSearch = str_replace(',', ' ', $rawSearch);
        $words = array_values(array_filter(explode(' ', $cleanSearch)));

        while (count($words) > 0) {
            $term = implode(' ', $words);
            $like = '%' . $term . '%';

            $count = (clone $query)->where(function ($q) use ($like, $term) {
                $q->where('title', 'LIKE', $like)
                  ->orWhere('city', 'LIKE', $like)
                  ->orWhere('locality', 'LIKE', $like)
                  ->orWhere('address', 'LIKE', $like)
                  ->orWhere('pincode', 'LIKE', $like)
                  ->orWhere('locality', 'SOUNDS LIKE', $term)
                  ->orWhere('city', 'SOUNDS LIKE', $term);
            })->count();

            if ($count > 0 || count($words) === 1) {
                $query->where(function ($q) use ($like, $term) {
                    $q->where('title', 'LIKE', $like)
                      ->orWhere('city', 'LIKE', $like)
                      ->orWhere('locality', 'LIKE', $like)
                      ->orWhere('address', 'LIKE', $like)
                      ->orWhere('pincode', 'LIKE', $like)
                      ->orWhere('locality', 'SOUNDS LIKE', $term)
                      ->orWhere('city', 'SOUNDS LIKE', $term);
                });
                return;
            }

            array_pop($words);
        }
    }

    /**
     * Create Listing
     */
    public function store(StoreListingRequest $request)
    {
        
        $listing = Listing::create([

           'owner_id' => $request->owner_id,

            'title' => $request->title,
            'description' => $request->description,

            'property_type' => $request->property_type,
            'bhk_type' => $request->bhk_type,

            'furnishing' => $request->furnishing,
            'listing_class' => $request->listing_class,

            'locality' => $request->locality,
            'city' => $request->city,
            'pincode' => $request->pincode,

            'address' => $request->address,

            'latitude' => $request->latitude,
            'longitude' => $request->longitude,

            'rent_per_month' => $request->rent_per_month,
            'deposit' => $request->deposit ?? 0,

            'area_sqft' => $request->area_sqft,
            'floor_number' => $request->floor_number,

            'gender_preference' =>
                $request->gender_preference ?? 'any',

            'amenities' => $request->amenities ?? [],
            'photos' => $request->photos ?? [],

            'is_broker' => $request->is_broker ?? false,

            'status' => 'approved',

            'view_count' => 0,
            'unlock_count' => 0,

            'is_featured' => false,
        ]);

        // Notify all admins about the new listing
        $admins = User::where('role', 'admin')->get();
        foreach ($admins as $admin) {
            try {
                $admin->notify(new PaymentSuccessNotification(
                    "New listing posted: \"{$listing->title}\" in {$listing->city}",
                    "/admin"
                ));
            } catch (\Throwable $e) {
                Log::error('[LISTING:CREATE] Admin notification failed', ['error' => $e->getMessage()]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Listing created successfully.',
            'data' => $listing->load('owner')
        ], 201);
    }

    /**
     * Single Listing
     */
    public function show($id)
    {
        $listing = Listing::with([
            'owner:id,name,is_verified,profile_photo_url'
        ])->findOrFail($id);

        $listing->increment('view_count');

        return response()->json([
            'success' => true,
            'data' => $listing
        ]);
    }

    /**
     * Update Listing
     */
    public function update(
        UpdateListingRequest $request,
        $id
    ) {
        $listing = Listing::findOrFail($id);

        $listing->update(
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'message' => 'Listing updated successfully.',
            'data' => $listing
        ]);
    }

    /**
     * Delete Listing
     */
    public function destroy($id)
    {
        $listing = Listing::findOrFail($id);

        $listing->delete();

        return response()->json([
            'success' => true,
            'message' => 'Listing deleted successfully.'
        ]);
    }

    /**
     * POST /listings/{id}/enquire
     * Tenant submits contact to get owner details. Free — no payment required.
     */
    public function enquire(Request $request, $id)
    {
        $request->validate([
            'tenant_name'  => 'required|string|max:100',
            'tenant_phone' => 'required|string|max:20',
            'consent'      => 'boolean',
        ]);

        $listing = Listing::with('owner:id,name,phone,email,is_verified,profile_photo_url')
            ->where('status', 'approved')
            ->findOrFail($id);

        ListingEnquiry::create([
            'listing_id'   => $listing->id,
            'tenant_name'  => $request->tenant_name,
            'tenant_phone' => $request->tenant_phone,
            'consent'      => $request->boolean('consent', true),
            'ip_address'   => $request->ip(),
        ]);

        // Notify owner — in-app + email
        $owner = $listing->owner;
        try { $owner?->notify(new ListingEnquiryNotification($listing, $request->tenant_name, $request->tenant_phone)); }
        catch (\Throwable) {}
        if ($owner && $owner->email) {
            try { Mail::to($owner->email)->send(new ListingEnquiryMail($listing, $request->tenant_name, $request->tenant_phone)); }
            catch (\Throwable $e) { Log::error('[ENQUIRY] Owner email failed', ['error' => $e->getMessage()]); }
        }

        return response()->json([
            'success' => true,
            'data' => [
                'phone'    => $listing->owner_phone ?? $listing->owner?->phone,
                'email'    => $listing->owner_email ?? $listing->owner?->email,
                'name'     => $listing->owner?->name,
                'verified' => (bool) $listing->owner?->is_verified,
            ],
        ]);
    }

    /**
     * My Listings
     */
public function myListings(Request $request)
{
    $request->validate([
        'owner_id' => 'required|exists:users,id',
    ]);

    $listings = Listing::where(
        'owner_id',
        $request->owner_id
    )
    ->latest()
    ->get();

    return response()->json([
        'success' => true,
        'data' => $listings
    ]);
}
}