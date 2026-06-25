<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use App\Models\ListingUnlock;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Services\CouponService;

class ListingUnlockController extends Controller
{
    /**
     * GET /listings/:id/unlock-status?user_id=X
     */
    public function status(Request $request, $id)
    {
        $request->validate(['user_id' => 'required|exists:users,id']);

        $listing = Listing::with(['owner:id,name,phone'])->findOrFail($id);

        $unlock = ListingUnlock::where('listing_id', $listing->id)
            ->where('user_id', $request->user_id)
            ->first();

        if (!$unlock) {
            return response()->json(['success' => true, 'data' => ['unlocked' => false]]);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'unlocked'  => true,
                'phone'     => $listing->owner?->phone,
                'address'   => $listing->address,
                'latitude'  => $listing->latitude,
                'longitude' => $listing->longitude,
            ],
        ]);
    }

    public function unlock(Request $request, $id)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'coupon_code' => 'nullable|string',
        ]);

        $listing = Listing::with([
            'owner:id,name,phone,is_verified'
        ])->findOrFail($id);

        $user = User::findOrFail(
            $request->user_id
        );

        /*
        |--------------------------------------------------------------------------
        | Already unlocked?
        |--------------------------------------------------------------------------
        */
        $existing = ListingUnlock::where(
            'listing_id',
            $listing->id
        )
        ->where(
            'user_id',
            $user->id
        )
        ->first();

        if ($existing) {
            return response()->json([
                'success' => true,
                'message' => 'Already unlocked.',
                'data' => [
                    'phone' => $listing->owner?->phone,
                    'address' => $listing->address,
                    'latitude' => $listing->latitude,
                    'longitude' => $listing->longitude,
                ]
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Coupon Validation
        |--------------------------------------------------------------------------
        */
        $coupon = null;

        if ($request->filled('coupon_code')) {

            $couponResult =
                CouponService::validateCoupon(
                    $request->coupon_code
                );

            if (!$couponResult['valid']) {

                return response()->json([
                    'success' => false,
                    'message' => $couponResult['message']
                ], 422);
            }

            $coupon = $couponResult['coupon'];
        }

        /*
        |--------------------------------------------------------------------------
        | Create Unlock
        |--------------------------------------------------------------------------
        */
        ListingUnlock::create([
            'listing_id' => $listing->id,
            'user_id' => $user->id,
            'coupon_id' => $coupon?->id,
            'amount_paid' => 0,
            'expires_at' => Carbon::now()
                ->addDays(30),
        ]);

        $listing->increment(
            'unlock_count'
        );

        /*
        |--------------------------------------------------------------------------
        | Increment coupon usage
        |--------------------------------------------------------------------------
        */
        if ($coupon) {

            $coupon->increment(
                'used_count'
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Listing unlocked successfully.',
            'data' => [
                'phone' => $listing->owner?->phone,
                'address' => $listing->address,
                'latitude' => $listing->latitude,
                'longitude' => $listing->longitude,
            ]
        ]);
    }
}