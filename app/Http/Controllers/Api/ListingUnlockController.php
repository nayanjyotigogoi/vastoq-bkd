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
    const POINTS_COST = 20; // Vastoq Points required to unlock a listing

    /**
     * GET /listings/:id/unlock-status?user_id=X
     */
    public function status(Request $request, $id)
    {
        $request->validate(['user_id' => 'required|exists:users,id']);

        $listing = Listing::with(['owner:id,name,phone'])->findOrFail($id);
        $user    = User::findOrFail($request->user_id);

        $unlock = ListingUnlock::where('listing_id', $listing->id)
            ->where('user_id', $user->id)
            ->first();

        if (!$unlock) {
            return response()->json([
                'success' => true,
                'data'    => [
                    'unlocked'               => false,
                    'free_unlocks_remaining' => $user->free_unlocks_remaining ?? 0,
                    'vastoq_points'          => $user->vastoq_points ?? 0,
                ],
            ]);
        }

        return response()->json([
            'success' => true,
            'data'    => [
                'unlocked'               => true,
                'phone'                  => $listing->owner?->phone,
                'address'                => $listing->address,
                'latitude'               => $listing->latitude,
                'longitude'              => $listing->longitude,
                'free_unlocks_remaining' => $user->free_unlocks_remaining ?? 0,
                'vastoq_points'          => $user->vastoq_points ?? 0,
            ],
        ]);
    }

    public function unlock(Request $request, $id)
    {
        $request->validate([
            'user_id'     => 'required|exists:users,id',
            'coupon_code' => 'nullable|string',
        ]);

        $listing = Listing::with([
            'owner:id,name,phone,is_verified'
        ])->findOrFail($id);

        $user = User::findOrFail($request->user_id);

        /*
        |--------------------------------------------------------------------------
        | Already unlocked?
        |--------------------------------------------------------------------------
        */
        $existing = ListingUnlock::where('listing_id', $listing->id)
            ->where('user_id', $user->id)
            ->first();

        if ($existing) {
            return response()->json([
                'success' => true,
                'message' => 'Already unlocked.',
                'data'    => [
                    'phone'                  => $listing->owner?->phone,
                    'address'                => $listing->address,
                    'latitude'               => $listing->latitude,
                    'longitude'              => $listing->longitude,
                    'free_unlocks_remaining' => $user->free_unlocks_remaining ?? 0,
                    'vastoq_points'          => $user->vastoq_points ?? 0,
                ],
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | 1. Coupon path
        |--------------------------------------------------------------------------
        */
        if ($request->filled('coupon_code')) {

            $couponResult = CouponService::validateCoupon($request->coupon_code);

            if (!$couponResult['valid']) {
                return response()->json([
                    'success' => false,
                    'message' => $couponResult['message'],
                ], 422);
            }

            $coupon = $couponResult['coupon'];

            ListingUnlock::create([
                'listing_id'  => $listing->id,
                'user_id'     => $user->id,
                'coupon_id'   => $coupon?->id,
                'amount_paid' => 0,
                'expires_at'  => Carbon::now()->addDays(30),
            ]);

            $listing->increment('unlock_count');
            $coupon->increment('used_count');

            return response()->json([
                'success' => true,
                'message' => 'Listing unlocked successfully.',
                'data'    => [
                    'phone'                  => $listing->owner?->phone,
                    'address'                => $listing->address,
                    'latitude'               => $listing->latitude,
                    'longitude'              => $listing->longitude,
                    'free_unlocks_remaining' => $user->free_unlocks_remaining ?? 0,
                    'vastoq_points'          => $user->vastoq_points ?? 0,
                ],
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | 2. Free credits (welcome gift)
        |--------------------------------------------------------------------------
        */
        if (($user->free_unlocks_remaining ?? 0) > 0) {

            $user->decrement('free_unlocks_remaining');

            ListingUnlock::create([
                'listing_id'  => $listing->id,
                'user_id'     => $user->id,
                'coupon_id'   => null,
                'amount_paid' => 0,
                'expires_at'  => Carbon::now()->addDays(30),
            ]);

            $listing->increment('unlock_count');
            $user->refresh();

            return response()->json([
                'success' => true,
                'message' => 'Unlocked using your free credit!',
                'data'    => [
                    'phone'                  => $listing->owner?->phone,
                    'address'                => $listing->address,
                    'latitude'               => $listing->latitude,
                    'longitude'              => $listing->longitude,
                    'free_unlocks_remaining' => $user->free_unlocks_remaining ?? 0,
                    'vastoq_points'          => $user->vastoq_points ?? 0,
                ],
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | 3. Vastoq Points wallet (costs 20 points per listing unlock)
        |--------------------------------------------------------------------------
        */
        if (($user->vastoq_points ?? 0) >= self::POINTS_COST) {

            $user->decrement('vastoq_points', self::POINTS_COST);

            ListingUnlock::create([
                'listing_id'  => $listing->id,
                'user_id'     => $user->id,
                'coupon_id'   => null,
                'amount_paid' => 0,
                'expires_at'  => Carbon::now()->addDays(30),
            ]);

            $listing->increment('unlock_count');
            $user->refresh();

            return response()->json([
                'success' => true,
                'message' => '20 Vastoq Points used. Listing unlocked!',
                'data'    => [
                    'phone'                  => $listing->owner?->phone,
                    'address'                => $listing->address,
                    'latitude'               => $listing->latitude,
                    'longitude'              => $listing->longitude,
                    'free_unlocks_remaining' => $user->free_unlocks_remaining ?? 0,
                    'vastoq_points'          => $user->vastoq_points ?? 0,
                ],
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | 4. No credits — ask frontend to handle payment
        |--------------------------------------------------------------------------
        */
        return response()->json([
            'success' => false,
            'message' => 'Not enough Vastoq Points. You need 20 points to unlock a listing. Buy a 100-point pack for just ₹99.',
            'code'    => 'PAYMENT_REQUIRED',
        ], 402);
    }
}