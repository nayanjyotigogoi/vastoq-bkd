<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\EmailOtpMail;
use App\Mail\OtpMail;
use App\Mail\WelcomeMail;
use App\Models\Otp;
use App\Models\User;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    /**
     * POST /auth/login
     * Authenticate with phone + password.
     */
    public function login(Request $request)
    {
        $request->validate([
            'phone'    => 'required|digits:10',
            'password' => 'required|string|min:6',
        ]);

        $user = User::where('phone', $request->phone)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'error'   => ['message' => 'No account found with this phone number.'],
            ], 401);
        }

        if ($user->is_blocked) {
            return response()->json([
                'success' => false,
                'error'   => ['message' => 'Your account has been blocked. Please contact support.'],
            ], 403);
        }

        if (!$user->password || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'error'   => ['message' => 'Incorrect password.'],
            ], 401);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'user'        => $this->formatUser($user),
                'redirect_to' => $this->getRedirectPath($user->role),
            ],
        ]);
    }

    /**
     * POST /auth/send-phone-otp
     * Generate a 6-digit OTP and (attempt to) send it via SMS.
     * In development, the OTP is only logged — see App\Services\SmsService.
     */
    public function sendPhoneOtp(Request $request)
    {
        $request->validate([
            'phone' => 'required|digits:10|unique:users,phone',
        ]);

        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Invalidate any previous unused OTPs for this phone
        Otp::where('phone', $request->phone)
            ->whereNull('email')          // target phone-only OTPs
            ->where('is_used', false)
            ->update(['is_used' => true]);

        Otp::create([
            'phone'      => $request->phone,
            'email'      => null,
            'otp'        => $otp,
            'expires_at' => now()->addMinutes(10),
            'is_used'    => false,
        ]);

        $sent = SmsService::sendOtp($request->phone, $otp);

        if (!$sent) {
            return response()->json([
                'success' => false,
                'error'   => ['message' => 'Failed to send SMS OTP. Please try again.'],
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'OTP sent to your mobile number.',
        ]);
    }

    /**
     * POST /auth/send-email-otp
     * Send a 6-digit OTP to the given email for sign-up verification.
     */
    public function sendEmailOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email|max:255|unique:users,email',
        ]);

        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Invalidate any previous unused OTPs for this email
        Otp::where('email', $request->email)
            ->where('is_used', false)
            ->update(['is_used' => true]);

        Otp::create([
            'phone'      => '',           // phone column is NOT NULL; keep empty for email OTPs
            'email'      => $request->email,
            'otp'        => $otp,
            'expires_at' => now()->addMinutes(10),
            'is_used'    => false,
        ]);

        try {
            Mail::to($request->email)->send(new OtpMail($otp, $request->email));
        } catch (\Throwable $e) {
            Log::error('OTP email failed', ['email' => $request->email, 'error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'error'   => ['message' => 'Failed to send OTP. Please try again.'],
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'OTP sent to your email address.',
        ]);
    }

    /**
     * POST /auth/register
     * Create a new account. Requires a verified email OTP.
     * Phone is required for owner/worker roles, optional for tenants.
     *
     * FUTURE — MOBILE OTP: When ready to enable SMS verification, add
     *   'phone_otp' => 'required|digits:6'  to the validation array,
     *   un-comment the phone OTP verification block below, and update
     *   the frontend to send `phone_otp` alongside `email_otp`.
     */
    public function register(Request $request)
    {
        $request->validate([
            'name'      => 'required|string|max:255',
            'phone'     => [
                'nullable',
                'digits:10',
                'unique:users,phone',
                // Required only for owner and worker roles
                'required_if:role,owner',
                'required_if:role,worker',
            ],
            'email'     => 'required|email|max:255|unique:users,email',
            'email_otp' => 'required|digits:6',
            // FUTURE — MOBILE OTP: 'phone_otp' => 'required|digits:6',
            'password'  => [
                'required',
                'string',
                Password::min(8)->letters()->numbers(),
            ],
            'role'      => 'required|in:tenant,owner,worker',
        ]);

        // FUTURE — MOBILE OTP: un-comment this block to verify phone OTP
        // $phoneOtpRecord = Otp::where('phone', $request->phone)
        //     ->whereNull('email')
        //     ->where('otp', $request->phone_otp)
        //     ->where('is_used', false)
        //     ->where('expires_at', '>', now())
        //     ->latest()->first();
        // if (!$phoneOtpRecord) {
        //     return response()->json([
        //         'success' => false,
        //         'error'   => ['message' => 'Invalid or expired mobile OTP. Please request a new code.'],
        //     ], 422);
        // }

        // ── Verify email OTP ──────────────────────────────────────────────
        $emailOtpRecord = Otp::where('email', $request->email)
            ->where('otp', $request->email_otp)
            ->where('is_used', false)
            ->where('expires_at', '>', now())
            ->latest()
            ->first();

        if (!$emailOtpRecord) {
            return response()->json([
                'success' => false,
                'error'   => ['message' => 'Invalid or expired email OTP. Please request a new code.'],
            ], 422);
        }

        $user = User::create([
            'name'                   => $request->name,
            'phone'                  => $request->phone,
            'email'                  => $request->email,
            'password'               => Hash::make($request->password),
            'role'                   => $request->role,
            'is_verified'            => true,
            'free_unlocks_remaining' => $request->role === 'tenant' ? 2 : 0, // Welcome gift for tenants only
            'paid_unlocks_remaining' => 0,
        ]);

        // Send welcome email if user has an email address
        if ($user->email) {
            try {
                Mail::to($user->email)->send(new WelcomeMail($user));
            } catch (\Throwable $e) {
                Log::error('[AUTH] Welcome email failed', ['user_id' => $user->id, 'error' => $e->getMessage()]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Account created successfully.',
            'data' => [
                'user'        => $this->formatUser($user),
                'redirect_to' => $this->getRedirectPath($user->role),
            ],
        ], 201);
    }

    /**
     * GET /auth/me?user_id=X
     * Returns the current user's full profile.
     */
    public function me(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $user = User::findOrFail($request->user_id);

        return response()->json([
            'success' => true,
            'data' => [
                'user' => $this->formatUser($user),
            ],
        ]);
    }

    /**
     * POST /auth/logout
     */
    public function logout()
    {
        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully.',
        ]);
    }

    /**
     * POST /auth/update-profile
     */
    public function updateProfile(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'name'    => 'required|string|max:255',
            'email'   => 'nullable|email|max:255',
            'phone'   => 'nullable|digits:10|unique:users,phone,' . $request->user_id,
        ]);

        $user = User::findOrFail($request->user_id);

        $updateData = [
            'name'  => $request->name,
            'phone' => $request->phone,
        ];
        // Only update email if explicitly supplied — never overwrite with null
        if ($request->filled('email')) {
            $updateData['email'] = $request->email;
        }

        $user->update($updateData);

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully.',
            'data'    => ['user' => $this->formatUser($user)],
        ]);
    }

    /**
     * POST /auth/change-password
     */
    public function changePassword(Request $request)
    {
        $request->validate([
            'user_id'      => 'required|exists:users,id',
            'old_password' => 'required|string',
            'new_password' => 'required|string|min:6',
        ]);

        $user = User::findOrFail($request->user_id);

        if ($user->password && !Hash::check($request->old_password, $user->password)) {
            return response()->json([
                'success' => false,
                'error'   => ['message' => 'Current password is incorrect.'],
            ], 422);
        }

        $user->update(['password' => Hash::make($request->new_password)]);

        return response()->json([
            'success' => true,
            'message' => 'Password changed successfully.',
        ]);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function formatUser(User $user): array
    {
        return [
            'id'                     => $user->id,
            'name'                   => $user->name,
            'phone'                  => $user->phone,
            'email'                  => $user->email,
            'role'                   => $user->role,
            'credit_balance'         => $user->credit_balance ?? 0,
            'free_unlocks_remaining' => $user->free_unlocks_remaining ?? 0,
            'vastoq_points'          => $user->vastoq_points ?? 0,
            'is_verified'            => $user->is_verified,
            'profile_photo_url'      => $user->profile_photo_url,
        ];
    }

    //latest update
    private function getRedirectPath(?string $role): string
    {
        return match ($role) {
            'owner'  => '/owner/dashboard',
            'worker' => '/worker/dashboard',
            'admin'  => '/admin',
            default  => '/dashboard',
        };
    }
}
