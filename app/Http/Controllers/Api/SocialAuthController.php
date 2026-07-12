<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    public function redirectToGoogle(\Illuminate\Http\Request $request)
    {
        if ($request->has('role')) {
            session(['google_register_role' => $request->role]);
        }
    
        return Socialite::driver('google')
            ->stateless()
            ->with([
                'prompt' => 'select_account',
            ])
            ->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();

            $user = User::where('google_id', $googleUser->id)->first();
            $isNew = false;

            if (!$user) {
                $user = User::where('email', $googleUser->email)->first();
                if ($user) {
                    $user->update([
                        'google_id'         => $googleUser->id,
                        'profile_photo_url' => $user->profile_photo_url ?? $googleUser->avatar,
                    ]);
                } else {
                    $role = session('google_register_role');
                    session()->forget('google_register_role');
                    if ($role) {
                        $user = User::create([
                            'name'              => $googleUser->name,
                            'email'             => $googleUser->email,
                            'google_id'         => $googleUser->id,
                            'profile_photo_url' => $googleUser->avatar,
                            'password'          => bcrypt(Str::random(24)),
                            'role'              => $role,
                            'is_verified'       => true,
                        ]);
                    } else {
                        $user = User::create([
                            'name'              => $googleUser->name,
                            'email'             => $googleUser->email,
                            'google_id'         => $googleUser->id,
                            'profile_photo_url' => $googleUser->avatar,
                            'password'          => bcrypt(Str::random(24)),
                            'role'              => 'tenant',
                            'is_verified'       => true,
                        ]);
                        $isNew = true;
                    }
                }
            }

            
            if ($user->is_blocked) {
                $frontendUrl = config('app.frontend_url');
                return redirect($frontendUrl . '/auth/google/callback?error=blocked');
            }

            $userData = json_encode([
                'id'                => $user->id,
                'name'              => $user->name,
                'email'             => $user->email,
                'phone'             => $user->phone,
                'role'              => $user->role,
                'credit_balance'    => $user->credit_balance ?? 0,
                'is_verified'       => $user->is_verified,
                'profile_photo_url' => $user->profile_photo_url,
                'exp'               => time() + 300,
            ]);
            $payload = base64_encode($userData);
            $sig     = hash_hmac('sha256', $payload, config('app.key'));
            $token   = $payload . '.' . $sig;

            
            $frontendUrl = config('app.frontend_url');
            $redirectUrl = $frontendUrl . '/auth/google/callback?token=' . urlencode($token);
            if ($isNew) {
                $redirectUrl .= '&is_new=1';
            }
            return redirect($redirectUrl);

        } catch (\Exception $e) {
            Log::error('GOOGLE_AUTH: callback failed', ['error' => $e->getMessage()]);
            $frontendUrl = config('app.frontend_url');
            return redirect($frontendUrl . '/auth/google/callback?error=auth_failed');
        }
    }
}
