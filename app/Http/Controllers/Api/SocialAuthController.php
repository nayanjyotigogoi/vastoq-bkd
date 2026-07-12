<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\WelcomeMail;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    /**
     * GET /api/auth/google
     * Redirect the browser to Google's OAuth consent screen.
     */
    public function redirectToGoogle(\Illuminate\Http\Request $request)
    {
        if ($request->has('role')) {
            session(['google_register_role' => $request->role]);
        }
        return Socialite::driver('google')->stateless()->redirect();
    }

    /**
     * GET /api/auth/google/callback
     * Handle the redirect back from Google.
     * Find or create the user, then redirect to the Next.js callback page
     * with a short-lived Sanctum token in the query string.
     */
    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();

            // 1. Try to find by google_id first (fastest path for returning users)
            $user = User::where('google_id', $googleUser->id)->first();
            $isNew = false;

            if (!$user) {
                // 2. Try to find by email (user already has a phone/password account)
                $user = User::where('email', $googleUser->email)->first();

                if ($user) {
                    // Link the existing account to Google
                    $user->update([
                        'google_id'         => $googleUser->id,
                        'profile_photo_url' => $user->profile_photo_url ?? $googleUser->avatar,
                    ]);
                } else {
                    // 3. Brand-new user — create with defaults suitable for Vastoq
                    $role = session('google_register_role');
                    session()->forget('google_register_role');
                    $isNew = true;

                    if ($role) {
                        $user = User::create([
                            'name'                   => $googleUser->name,
                            'email'                  => $googleUser->email,
                            'google_id'              => $googleUser->id,
                            'profile_photo_url'      => $googleUser->avatar,
                            'password'               => bcrypt(Str::random(24)), // unusable random password
                            'role'                   => $role,
                            'is_verified'            => true,
                            'free_unlocks_remaining' => $role === 'tenant' ? 2 : 0, // Welcome gift for tenants only
                            'paid_unlocks_remaining' => 0,
                        ]);
                    } else {
                        $user = User::create([
                            'name'                   => $googleUser->name,
                            'email'                  => $googleUser->email,
                            'google_id'              => $googleUser->id,
                            'profile_photo_url'      => $googleUser->avatar,
                            'password'               => bcrypt(Str::random(24)), // unusable random password
                            'role'                   => 'tenant',                 // default role
                            'is_verified'            => true,
                            'free_unlocks_remaining' => 2, // Welcome gift for tenants
                            'paid_unlocks_remaining' => 0,
                        ]);
                        $isNew = true;
                    }
                    // Send welcome email for brand-new Google users
                    try {
                        Mail::to($user->email)->send(new WelcomeMail($user));
                    } catch (\Throwable $e) {
                        Log::error('[GOOGLE_AUTH] Welcome email failed', ['user_id' => $user->id, 'error' => $e->getMessage()]);
                    }
                }
            }

            // Block check
            if ($user->is_blocked) {
                $frontendUrl = env('FRONTEND_URL', 'http://localhost:3000');
                return redirect($frontendUrl . '/auth/google/callback?error=blocked');
            }

            // Build a self-contained signed token:
            //   payload  = base64(json({ user data, exp: now+5min }))
            //   token    = payload . "." . hmac_sha256(payload, APP_KEY)
            // The frontend verifies HMAC locally — no extra round-trip needed.
            $userData = json_encode([
                'id'                     => $user->id,
                'name'                   => $user->name,
                'email'                  => $user->email,
                'phone'                  => $user->phone,
                'role'                   => $user->role,
                'credit_balance'         => $user->credit_balance ?? 0,
                'free_unlocks_remaining' => $user->free_unlocks_remaining ?? 0,
                'paid_unlocks_remaining' => $user->paid_unlocks_remaining ?? 0,
                'is_verified'            => $user->is_verified,
                'profile_photo_url'      => $user->profile_photo_url,
                'exp'                    => time() + 300,
            ]);
            $payload = base64_encode($userData);
            $sig     = hash_hmac('sha256', $payload, config('app.key'));
            $token   = $payload . '.' . $sig;

            // Redirect to the Next.js callback page with the token
            $frontendUrl = env('FRONTEND_URL', 'http://localhost:3000');
            $redirectUrl = $frontendUrl . '/auth/google/callback?token=' . urlencode($token);
            if ($isNew) {
                $redirectUrl .= '&is_new=1';
            }
            return redirect($redirectUrl);

        } catch (\Exception $e) {
            Log::error('GOOGLE_AUTH: callback failed', ['error' => $e->getMessage()]);

            $frontendUrl = env('FRONTEND_URL', 'http://localhost:3000');
            return redirect($frontendUrl . '/auth/google/callback?error=auth_failed');
        }
    }
}
