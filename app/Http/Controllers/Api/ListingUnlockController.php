<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use App\Models\ListingUnlock;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Services\CouponService;
use App\Mail\ListingUnlockedOwnerMail;
use App\Mail\ListingUnlockedUserMail;
use App\Notifications\ListingUnlockedOwnerNotification;
use App\Notifications\PaymentSuccessNotification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class ListingUnlockController extends Controller
{
    const POINTS_COST = 20;

    public function status(Request $request, $id)
    {
        $request->validate(['user_id' => 'required|exists:users,id']);

        $listing = Listing::with(['owner:id,name,phone'])->findOrFail($id);
        $user    = User::findOrFail($request->user_id);

        $unlock = ListingUnlock::where('listing_id', $listing->id)
            ->where('user_id', $user->id)
            ->first();

        if (!$unlock) {
            return response()->json(['success' => true, 'data' => [
                'unlocked'               => false,
                'free_unlocks_remaining' => $user->free_unlocks_remaining ?? 0,
                'vastoq_points'          => $user->vastoq_points ?? 0,
            ]]);
        }

        return response()->json(['success' => true, 'data' => [
            'unlocked'               => true,
            'phone'                  => $listing->owner?->phone,
            'address'                => $listing->address,
            'latitude'               => $listing->latitude,
            'longitude'              => $listing->longitude,
            'free_unlocks_remaining' => $user->free_unlocks_remaining ?? 0,
            'vastoq_points'          => $user->vastoq_points ?? 0,
        ]]);
    }

    public function unlock(Request $request, $id)
    {
        $request->validate([
            'user_id'     => 'required|exists:users,id',
            'coupon_code' => 'nullable|string',
        ]);

        $listing = Listing::with(['owner:id,name,phone,email,is_verified'])->findOrFail($id);
        $user    = User::findOrFail($request->user_id);

        // Already unlocked
        $existing = ListingUnlock::where('listing_id', $listing->id)->where('user_id', $user->id)->first();
        if ($existing) {
            return response()->json(['success' => true, 'message' => 'Already unlocked.', 'data' => [
                'phone'                  => $listing->owner?->phone,
                'address'                => $listing->address,
                'latitude'               => $listing->latitude,
                'longitude'              => $listing->longitude,
                'free_unlocks_remaining' => $user->free_unlocks_remaining ?? 0,
                'vastoq_points'          => $user->vastoq_points ?? 0,
            ]]);
        }

        // 1. Coupon path
        if ($request->filled('coupon_code')) {
            $couponResult = CouponService::validateCoupon($request->coupon_code);
            if (!$couponResult['valid']) {
                return response()->json(['success' => false, 'message' => $couponResult['message']], 422);
            }
            $coupon = $couponResult['coupon'];
            ListingUnlock::create(['listing_id' => $listing->id, 'user_id' => $user->id, 'coupon_id' => $coupon?->id, 'amount_paid' => 0, 'expires_at' => Carbon::now()->addDays(30)]);
            $listing->increment('unlock_count');
            $coupon->increment('used_count');
            $this->sendUnlockNotifications($listing->load('owner'), $user, 'coupon');
            return response()->json(['success' => true, 'message' => 'Listing unlocked successfully.', 'data' => [
                'phone' => $listing->owner?->phone, 'address' => $listing->address,
                'latitude' => $listing->latitude, 'longitude' => $listing->longitude,
                'free_unlocks_remaining' => $user->free_unlocks_remaining ?? 0,
                'vastoq_points' => $user->vastoq_points ?? 0,
            ]]);
        }

        // 2. Free credits
        if (($user->free_unlocks_remaining ?? 0) > 0) {
            $user->decrement('free_unlocks_remaining');
            ListingUnlock::create(['listing_id' => $listing->id, 'user_id' => $user->id, 'coupon_id' => null, 'amount_paid' => 0, 'expires_at' => Carbon::now()->addDays(30)]);
            $listing->increment('unlock_count');
            $user->refresh();
            $this->sendUnlockNotifications($listing->load('owner'), $user, 'free');
            return response()->json(['success' => true, 'message' => 'Unlocked using your free credit!', 'data' => [
                'phone' => $listing->owner?->phone, 'address' => $listing->address,
                'latitude' => $listing->latitude, 'longitude' => $listing->longitude,
                'free_unlocks_remaining' => $user->free_unlocks_remaining ?? 0,
                'vastoq_points' => $user->vastoq_points ?? 0,
            ]]);
        }

        // 3. Vastoq Points
        if (($user->vastoq_points ?? 0) >= self::POINTS_COST) {
            $user->decrement('vastoq_points', self::POINTS_COST);
            ListingUnlock::create(['listing_id' => $listing->id, 'user_id' => $user->id, 'coupon_id' => null, 'amount_paid' => 0, 'expires_at' => Carbon::now()->addDays(30)]);
            $listing->increment('unlock_count');
            $user->refresh();
            $this->sendUnlockNotifications($listing->load('owner'), $user, 'points', self::POINTS_COST);
            return response()->json(['success' => true, 'message' => '20 Vastoq Points used. Listing unlocked!', 'data' => [
                'phone' => $listing->owner?->phone, 'address' => $listing->address,
                'latitude' => $listing->latitude, 'longitude' => $listing->longitude,
                'free_unlocks_remaining' => $user->free_unlocks_remaining ?? 0,
                'vastoq_points' => $user->vastoq_points ?? 0,
            ]]);
        }

        // 4. No credits
        return response()->json(['success' => false, 'message' => 'Not enough Vastoq Points. You need 20 points to unlock a listing. Buy a 100-point pack for just ₹99.', 'code' => 'PAYMENT_REQUIRED'], 402);
    }

    private function sendUnlockNotifications(Listing $listing, User $tenant, string $method = 'free', int $pointsSpent = 0): void
    {
        if ($tenant->email) {
            try { Mail::to($tenant->email)->send(new ListingUnlockedUserMail($tenant, $listing, $method, $pointsSpent)); }
            catch (\Throwable $e) { Log::error('[UNLOCK] Tenant email failed', ['error' => $e->getMessage()]); }
        }
        try { $tenant->notify(new PaymentSuccessNotification("You unlocked contact details for \"{$listing->title}\".", "/rentals/{$listing->id}")); }
        catch (\Throwable $e) { Log::error('[UNLOCK] Tenant notification failed', ['error' => $e->getMessage()]); }

        $owner = $listing->owner;
        if ($owner && $owner->email) {
            try { Mail::to($owner->email)->send(new ListingUnlockedOwnerMail($owner, $listing, $tenant)); }
            catch (\Throwable $e) { Log::error('[UNLOCK] Owner email failed', ['error' => $e->getMessage()]); }
        }
        if ($owner) {
            try { $owner->notify(new ListingUnlockedOwnerNotification($listing)); }
            catch (\Throwable $e) { Log::error('[UNLOCK] Owner notification failed', ['error' => $e->getMessage()]); }
        }
    }
}
