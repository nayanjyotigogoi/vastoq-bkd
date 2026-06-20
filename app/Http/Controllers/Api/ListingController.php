<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreListingRequest;
use App\Http\Requests\UpdateListingRequest;
use App\Models\Listing;
use Illuminate\Http\Request;

class ListingController extends Controller
{
    /**
     * Public Listings
     */
    public function index(Request $request)
    {

    
        $query = Listing::with([
            'owner:id,name,is_verified,profile_photo_url'
        ])->where('status', 'approved');

        if ($request->filled('search')) {

            $search = trim($request->search);

            $query->where(function ($q) use ($search) {

                $q->where('title', 'LIKE', "%{$search}%")
                ->orWhere('city', 'LIKE', "%{$search}%")
                ->orWhere('locality', 'LIKE', "%{$search}%")
                ->orWhere('address', 'LIKE', "%{$search}%")
                ->orWhere('pincode', 'LIKE', "%{$search}%")
                ->orWhere('description', 'LIKE', "%{$search}%");
            });
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
                $query->latest();
        }

        $listings = $query->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $listings
        ]);
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

            'status' => 'pending',

            'view_count' => 0,
            'unlock_count' => 0,

            'is_featured' => false,
        ]);

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