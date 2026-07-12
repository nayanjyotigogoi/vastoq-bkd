<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Worker;
use App\Models\WorkerUnlock;
use App\Models\User;
use App\Services\CouponService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class WorkerUnlockController extends Controller
{
    const POINTS_COST = 10; // Vastoq Points required to unlock a worker profile

    /**
     * GET /workers/{id}/unlock-status?user_id=X
     */
    public function status(Request $request, $id)
    {
        $request->validate(['user_id' => 'required|exists:users,id']);

        $worker = Worker::with('user:id,name,phone')->findOrFail($id);
        $user   = User::findOrFail($request->user_id);

        $unlock = WorkerUnlock::where('worker_id', $worker->id)
            ->where('user_id', $request->user_id)
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
                'phone'                  => $worker->user?->phone,
                'service_areas'          => $worker->service_areas ?? [],
                'free_unlocks_remaining' => $user->free_unlocks_remaining ?? 0,
                'vastoq_points'          => $user->vastoq_points ?? 0,
            ],
        ]);
    }

    /**
     * POST /workers/{id}/unlock
     * Priority: coupon → free unlocks → Vastoq Points (10 pts) → payment required
     */
    public function unlock(Request $request, $id)
    {
        $request->validate([
            'user_id'     => 'required|exists:users,id',
            'coupon_code' => 'nullable|string',
        ]);

        $worker = Worker::with('user:id,name,phone')->findOrFail($id);
        $user   = User::findOrFail($request->user_id);

        // Already unlocked — return details immediately
        $existing = WorkerUnlock::where('worker_id', $worker->id)
            ->where('user_id', $user->id)
            ->first();

        if ($existing) {
            return response()->json([
                'success' => true,
                'message' => 'Already unlocked.',
                'data'    => [
                    'phone'                  => $worker->user?->phone,
                    'service_areas'          => $worker->service_areas ?? [],
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
            $result = CouponService::validateCoupon($request->coupon_code);
            if (!$result['valid']) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'],
                ], 422);
            }

            $coupon = $result['coupon'];

            WorkerUnlock::create([
                'worker_id'   => $worker->id,
                'user_id'     => $user->id,
                'coupon_id'   => $coupon?->id,
                'amount_paid' => 0,
                'expires_at'  => Carbon::now()->addDays(30),
            ]);

            $worker->increment('contact_unlocks');
            $coupon->increment('used_count');

            return response()->json([
                'success' => true,
                'message' => 'Worker unlocked successfully.',
                'data'    => [
                    'phone'                  => $worker->user?->phone,
                    'service_areas'          => $worker->service_areas ?? [],
                    'free_unlocks_remaining' => $user->free_unlocks_remaining ?? 0,
                    'vastoq_points'          => $user->vastoq_points ?? 0,
                ],
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | 2. Free credits (welcome gift) — works for workers too
        |--------------------------------------------------------------------------
        */
        if (($user->free_unlocks_remaining ?? 0) > 0) {
            $user->decrement('free_unlocks_remaining');

            WorkerUnlock::create([
                'worker_id'   => $worker->id,
                'user_id'     => $user->id,
                'coupon_id'   => null,
                'amount_paid' => 0,
                'expires_at'  => Carbon::now()->addDays(30),
            ]);

            $worker->increment('contact_unlocks');
            $user->refresh();

            return response()->json([
                'success' => true,
                'message' => 'Unlocked using your free credit!',
                'data'    => [
                    'phone'                  => $worker->user?->phone,
                    'service_areas'          => $worker->service_areas ?? [],
                    'free_unlocks_remaining' => $user->free_unlocks_remaining ?? 0,
                    'vastoq_points'          => $user->vastoq_points ?? 0,
                ],
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | 3. Vastoq Points wallet (costs 10 points per worker unlock)
        |--------------------------------------------------------------------------
        */
        if (($user->vastoq_points ?? 0) >= self::POINTS_COST) {
            $user->decrement('vastoq_points', self::POINTS_COST);

            WorkerUnlock::create([
                'worker_id'   => $worker->id,
                'user_id'     => $user->id,
                'coupon_id'   => null,
                'amount_paid' => 0,
                'expires_at'  => Carbon::now()->addDays(30),
            ]);

            $worker->increment('contact_unlocks');
            $user->refresh();

            return response()->json([
                'success' => true,
                'message' => '10 Vastoq Points used. Worker unlocked!',
                'data'    => [
                    'phone'                  => $worker->user?->phone,
                    'service_areas'          => $worker->service_areas ?? [],
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
            'message' => 'Not enough Vastoq Points. You need 10 points to unlock a worker. Buy a 100-point pack for just ₹99.',
            'code'    => 'PAYMENT_REQUIRED',
        ], 402);
    }
}
