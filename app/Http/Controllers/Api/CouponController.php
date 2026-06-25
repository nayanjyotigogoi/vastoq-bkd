<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CouponService;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    /**
     * POST /coupons/validate
     * Check if a coupon code is valid and what it grants.
     */
    public function check(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:50',
        ]);

        $result = CouponService::validateCoupon($request->code);

        if (!$result['valid']) {
            return response()->json([
                'success' => false,
                'error'   => ['message' => $result['message']],
            ], 422);
        }

        $coupon = $result['coupon'];

        return response()->json([
            'success' => true,
            'data' => [
                'code'       => $coupon->code,
                'type'       => $coupon->type,       // flat | percent | free_unlock
                'value'      => $coupon->value,
                'is_free'    => $coupon->type === 'free_unlock',
            ],
        ]);
    }
}
