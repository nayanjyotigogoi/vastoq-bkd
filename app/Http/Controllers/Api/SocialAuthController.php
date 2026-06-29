<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
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

                    if ($role) {
                        $user = User::create([
                            'name'              => $googleUser->name,
                            'email'             => $googleUser->email,
                            'google_id'         => $googleUser->id,
                            'profile_photo_url' => $googleUser->avatar,
                            'password'          => bcrypt(Str::random(24)), // unusable random password
                            'role'              => $role,
                            'is_verified'       => true,
                        ]);
                    } else {
                        $user = User::create([
                            'name'              => $googleUser->name,
                            'email'             => $googleUser->email,
                            'google_id'         => $googleUser->id,
                            'profile_photo_url' => $googleUser->avatar,
                            'password'          => bcrypt(Str::random(24)), // unusable random password
                            'role'              => 'tenant',                 // default role
                            'is_verified'       => true,
                        ]);
                        $isNew = true;
                    }
                }
            }

            // Block check
            if ($user->is_blocked) {
                $frontendUrl = env('FRONTEND_URL', 'http://localhost:3000');
                return redirect($frontendUrl . '/login?error=blocked');
            }

            // Generate a Sanctum personal-access token
            $token = $user->createToken('google_oauth')->plainTextToken;

            // Redirect to the Next.js callback page with the token
            $frontendUrl = env('FRONTEND_URL', 'http://localhost:3000');
            $redirectUrl = $frontendUrl . '/auth/google/callback?token=' . $token;
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
