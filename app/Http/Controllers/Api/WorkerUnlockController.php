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
    /**
     * GET /workers/{id}/unlock-status?user_id=X
     */
    public function status(Request $request, $id)
    {
        $request->validate(['user_id' => 'required|exists:users,id']);

        $worker = Worker::with('user:id,name,phone')->findOrFail($id);
        $unlock = WorkerUnlock::where('worker_id', $worker->id)
            ->where('user_id', $request->user_id)
            ->first();

        if (!$unlock) {
            return response()->json(['success' => true, 'data' => ['unlocked' => false]]);
        }

        return response()->json([
            'success' => true,
            'data'    => [
                'unlocked'     => true,
                'phone'        => $worker->user?->phone,
                'service_areas'=> $worker->service_areas ?? [],
            ],
        ]);
    }

    /**
     * POST /workers/{id}/unlock
     * Requires: user_id (from auth), coupon_code (optional)
     */
    public function unlock(Request $request, $id)
    {
        $request->validate([
            'user_id'     => 'required|exists:users,id',
            'coupon_code' => 'nullable|string',
        ]);

        $worker = Worker::with('user:id,name,phone')->findOrFail($id);
        $userId = $request->user_id;

        // Already unlocked — return details immediately
        $existing = WorkerUnlock::where('worker_id', $worker->id)
            ->where('user_id', $userId)
            ->first();

        if ($existing) {
            return response()->json([
                'success' => true,
                'message' => 'Already unlocked.',
                'data'    => [
                    'phone'        => $worker->user?->phone,
                    'service_areas'=> $worker->service_areas ?? [],
                ],
            ]);
        }

        // Validate coupon (required for now — paid path coming later)
        $coupon = null;
        if ($request->filled('coupon_code')) {
            $result = CouponService::validateCoupon($request->coupon_code);
            if (!$result['valid']) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'],
                ], 422);
            }
            $coupon = $result['coupon'];
        } else {
            return response()->json([
                'success' => false,
                'message' => 'A coupon code is required to unlock.',
            ], 402);
        }

        // Record the unlock
        WorkerUnlock::create([
            'worker_id'  => $worker->id,
            'user_id'    => $userId,
            'coupon_id'  => $coupon?->id,
            'amount_paid'=> 0,
            'expires_at' => Carbon::now()->addDays(30),
        ]);

        $worker->increment('contact_unlocks');

        if ($coupon) {
            $coupon->increment('used_count');
        }

        return response()->json([
            'success' => true,
            'message' => 'Worker unlocked successfully.',
            'data'    => [
                'phone'        => $worker->user?->phone,
                'service_areas'=> $worker->service_areas ?? [],
            ],
        ]);
    }
}
