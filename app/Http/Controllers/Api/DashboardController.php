<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
   public function tenant(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $user = User::findOrFail(
            $request->user_id
        );

        $unlocks = $user
            ->listingUnlocks()
            ->with([
                'listing.owner'
            ])
            ->latest()
            ->get();

        $savedListings = $user
            ->savedListings()
            ->with([
                'listing.owner'
            ])
            ->latest()
            ->get();

        return response()->json([
            'success' => true,

            'data' => [

                'stats' => [
                    'unlocks_used' => $unlocks->count(),
                    'saved_listings' =>
                        $savedListings->count(),
                    'unlock_credits' =>
                        $user->credit_balance ?? 0,
                ],

                'unlocks' => $unlocks,

                'saved_listings_data' =>
                    $savedListings,
            ]
        ]);
    }
}