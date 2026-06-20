<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Otp;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    /**
     * Send OTP
     */
    public function sendOtp(Request $request)
    {
        $request->validate([
            'phone' => 'required|digits:10',
        ]);

        $otp = (string) rand(100000, 999999);

        Otp::create([
            'phone'      => $request->phone,
            'otp'        => $otp,
            'expires_at' => Carbon::now()->addMinutes(5),
            'is_used'    => false,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'OTP sent successfully.',
            'data' => [
                // Development only
                'devOtp' => $otp,
            ]
        ]);
    }

    /**
     * Verify OTP
     */
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'phone' => 'required|digits:10',
            'otp'   => 'required|digits:6',
        ]);

        $otpRecord = Otp::where('phone', $request->phone)
            ->where('otp', $request->otp)
            ->where('is_used', false)
            ->latest()
            ->first();

        if (!$otpRecord) {
            return response()->json([
                'success' => false,
                'error' => [
                    'message' => 'Invalid OTP.',
                ]
            ], 422);
        }

        if ($otpRecord->expires_at < now()) {
            return response()->json([
                'success' => false,
                'error' => [
                    'message' => 'OTP expired.',
                ]
            ], 422);
        }

        $otpRecord->update([
            'is_used' => true,
        ]);

        $user = User::where(
            'phone',
            $request->phone
        )->first();

        if ($user) {

            return response()->json([
                'success' => true,
                'data' => [
                    'is_new_user' => false,
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'phone' => $user->phone,
                        'role' => $user->role,
                    ],
                    'redirect_to' => $this->getRedirectPath(
                        $user->role
                    ),
                ]
            ]);
        }

        $user = User::create([
            'name' => 'User ' . substr($request->phone, -4),
            'phone' => $request->phone,
            'is_verified' => true,
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'is_new_user' => true,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'phone' => $user->phone,
                    'role' => null,
                ],
            ]
        ]);
    }

    /**
     * Select role for new user
     */
    public function selectRole(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'role' => 'required|in:tenant,owner,worker',
        ]);

        $user = User::findOrFail(
            $request->user_id
        );

        $user->update([
            'role' => $request->role,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Role selected successfully.',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'phone' => $user->phone,
                    'role' => $user->role,
                ],
                'redirect_to' => $this->getRedirectPath(
                    $user->role
                ),
            ]
        ]);
    }

    /**
     * Current user
     */
    // public function me()
    // {
    //     return response()->json([
    //         'success' => true,
    //         'message' => 'JWT authentication not implemented yet.',
    //     ]);
    // }

    public function me(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $user = User::findOrFail(
            $request->user_id
        );

        return response()->json([
            'success' => true,
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'phone' => $user->phone,
                    'email' => $user->email,
                    'role' => $user->role,
                    'credit_balance' => $user->credit_balance,
                    'is_verified' => $user->is_verified,
                    'profile_photo_url' => $user->profile_photo_url,
                ]
            ]
        ]);
    }

    /**
     * Logout
     */
    public function logout()
    {
        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully.',
        ]);
    }

    /**
     * Dashboard route mapper
     */
    private function getRedirectPath($role)
    {
        switch ($role) {

            case 'owner':
                return '/owner/dashboard';

            case 'worker':
                return '/worker/dashboard';

            case 'admin':
                return '/admin/dashboard';

            case 'tenant':
            default:
                return '/dashboard';
        }
    }

    public function updateProfile(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
        ]);

        $user = User::findOrFail(
            $request->user_id
        );

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully.',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'phone' => $user->phone,
                    'email' => $user->email,
                    'role' => $user->role,
                ]
            ]
        ]);
    }
}