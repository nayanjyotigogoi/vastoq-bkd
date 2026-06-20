<?php

namespace App\Services;

use App\Models\Coupon;
use Carbon\Carbon;

class CouponService
{
    public static function validateCoupon(string $code)
    {
        $coupon = Coupon::where(
            'code',
            strtoupper(trim($code))
        )->first();

        if (!$coupon) {
            return [
                'valid' => false,
                'message' => 'Coupon not found.'
            ];
        }

        if (!$coupon->is_active) {
            return [
                'valid' => false,
                'message' => 'Coupon is inactive.'
            ];
        }

        if (
            $coupon->starts_at &&
            Carbon::now()->lt($coupon->starts_at)
        ) {
            return [
                'valid' => false,
                'message' => 'Coupon not active yet.'
            ];
        }

        if (
            $coupon->expires_at &&
            Carbon::now()->gt($coupon->expires_at)
        ) {
            return [
                'valid' => false,
                'message' => 'Coupon expired.'
            ];
        }

        if (
            $coupon->usage_limit &&
            $coupon->used_count >= $coupon->usage_limit
        ) {
            return [
                'valid' => false,
                'message' => 'Coupon usage limit reached.'
            ];
        }

        return [
            'valid' => true,
            'coupon' => $coupon
        ];
    }
}