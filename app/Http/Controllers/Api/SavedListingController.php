<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SavedListing;
use Illuminate\Http\Request;

class SavedListingController extends Controller
{
    public function toggle(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'listing_id' => 'required|exists:listings,id',
        ]);

        $saved = SavedListing::where(
            'user_id',
            $request->user_id
        )
        ->where(
            'listing_id',
            $request->listing_id
        )
        ->first();

        if ($saved) {

            $saved->delete();

            return response()->json([
                'success' => true,
                'saved' => false,
                'message' => 'Listing removed from saved.',
            ]);
        }

        SavedListing::create([
            'user_id' => $request->user_id,
            'listing_id' => $request->listing_id,
        ]);

        return response()->json([
            'success' => true,
            'saved' => true,
            'message' => 'Listing saved successfully.',
        ]);
    }

    public function index(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $savedListings = SavedListing::with([
            'listing.owner'
        ])
        ->whereHas('listing')
        ->where(
            'user_id',
            $request->user_id
        )
        ->latest()
        ->get();

        return response()->json([
            'success' => true,
            'data' => $savedListings,
        ]);
    }
}